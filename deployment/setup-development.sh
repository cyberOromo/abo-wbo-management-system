#!/bin/bash

# ABO-WBO Management System Development Setup Script
# This script sets up the development environment

set -e  # Exit on any error

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
ENV_FILE="$PROJECT_ROOT/.env.development"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')] $1${NC}"
}

error() {
    echo -e "${RED}[ERROR] $1${NC}" >&2
}

warn() {
    echo -e "${YELLOW}[WARNING] $1${NC}"
}

info() {
    echo -e "${BLUE}[INFO] $1${NC}"
}

# Check prerequisites
check_prerequisites() {
    log "Checking prerequisites..."
    
    # Check if Docker is installed and running
    if ! command -v docker &> /dev/null; then
        error "Docker is not installed"
        exit 1
    fi
    
    if ! docker info &> /dev/null; then
        error "Docker is not running"
        exit 1
    fi
    
    # Check if Docker Compose is installed
    if ! command -v docker-compose &> /dev/null; then
        error "Docker Compose is not installed"
        exit 1
    fi
    
    log "Prerequisites check passed"
}

# Setup development environment
setup_environment() {
    log "Setting up development environment..."
    
    cd "$PROJECT_ROOT"
    
    # Copy development environment file if .env doesn't exist
    if [[ ! -f "$PROJECT_ROOT/.env" ]]; then
        if [[ -f "$ENV_FILE" ]]; then
            cp "$ENV_FILE" "$PROJECT_ROOT/.env"
            log "Copied development environment file"
        else
            error "Development environment file not found: $ENV_FILE"
            exit 1
        fi
    else
        info ".env file already exists, skipping copy"
    fi
    
    # Load environment variables
    set -a
    source "$PROJECT_ROOT/.env"
    set +a
    
    log "Environment setup completed"
}

# Build development images
build_dev_images() {
    log "Building development Docker images..."
    
    cd "$PROJECT_ROOT"
    
    # Build development application image
    docker build \
        -f deployment/docker/Dockerfile.app \
        --target development \
        -t abo-wbo/app:dev \
        .
    
    # Build development Nginx image
    docker build \
        -f deployment/docker/Dockerfile.nginx-dev \
        -t abo-wbo/nginx:dev \
        .
    
    log "Development images built successfully"
}

# Start development services
start_dev_services() {
    log "Starting development services..."
    
    cd "$PROJECT_ROOT"
    
    # Start all development services
    docker-compose -f docker-compose.dev.yml up -d
    
    # Wait for services to be ready
    log "Waiting for services to start..."
    sleep 15
    
    log "Development services started"
}

# Setup development database
setup_dev_database() {
    log "Setting up development database..."
    
    # Wait for MySQL to be ready
    log "Waiting for MySQL to start..."
    timeout=60
    while ! docker exec abo-wbo-mysql-dev mysqladmin ping -h"localhost" --silent; do
        timeout=$((timeout-1))
        if [[ $timeout -eq 0 ]]; then
            error "MySQL failed to start within 60 seconds"
            exit 1
        fi
        sleep 1
    done
    
    log "MySQL is ready"
    
    # Run migrations
    log "Running database migrations..."
    docker-compose -f docker-compose.dev.yml exec app php database/migrate.php
    
    # Seed development data
    log "Seeding development data..."
    if [[ -f "$PROJECT_ROOT/database/seeds/development-seed.sql" ]]; then
        docker exec -i abo-wbo-mysql-dev mysql \
            -u "${DB_USERNAME}" \
            -p"${DB_PASSWORD}" \
            "${DB_DATABASE}" < "$PROJECT_ROOT/database/seeds/development-seed.sql"
    fi
    
    log "Development database setup completed"
}

# Install dependencies
install_dependencies() {
    log "Installing dependencies..."
    
    # Install PHP dependencies
    docker-compose -f docker-compose.dev.yml exec app composer install
    
    # Install Node.js dependencies (if package.json exists)
    if [[ -f "$PROJECT_ROOT/package.json" ]]; then
        docker-compose -f docker-compose.dev.yml exec app npm install
    fi
    
    log "Dependencies installed successfully"
}

# Setup development tools
setup_dev_tools() {
    log "Setting up development tools..."
    
    # Create storage directories
    mkdir -p "$PROJECT_ROOT/storage/logs"
    mkdir -p "$PROJECT_ROOT/storage/cache"
    mkdir -p "$PROJECT_ROOT/storage/sessions"
    mkdir -p "$PROJECT_ROOT/storage/testing"
    mkdir -p "$PROJECT_ROOT/public/uploads"
    
    # Set permissions
    chmod -R 755 "$PROJECT_ROOT/storage"
    chmod -R 755 "$PROJECT_ROOT/public/uploads"
    
    # Generate application key if not set
    if grep -q "APP_KEY=$" "$PROJECT_ROOT/.env"; then
        log "Generating application key..."
        # Generate a simple key for development
        NEW_KEY="base64:$(openssl rand -base64 32)"
        sed -i "s/APP_KEY=$/APP_KEY=$NEW_KEY/" "$PROJECT_ROOT/.env"
    fi
    
    log "Development tools setup completed"
}

# Run tests
run_tests() {
    log "Running tests..."
    
    # Check if PHPUnit is available
    if docker-compose -f docker-compose.dev.yml exec app test -f vendor/bin/phpunit; then
        # Run PHPUnit tests
        docker-compose -f docker-compose.dev.yml exec app vendor/bin/phpunit --testdox
        log "Tests completed successfully"
    else
        warn "PHPUnit not found, skipping tests"
    fi
}

# Print development summary
print_dev_summary() {
    log "Development Environment Summary"
    echo "==============================="
    echo "Application: http://localhost:8080"
    echo "PHPMyAdmin: http://localhost:8081"
    echo "MailHog: http://localhost:8025"
    echo "Redis Commander: http://localhost:8082"
    echo "Elasticsearch: http://localhost:9201"
    echo ""
    echo "Services Status:"
    docker-compose -f docker-compose.dev.yml ps
    echo ""
    echo "Useful Commands:"
    echo "  View logs: docker-compose -f docker-compose.dev.yml logs -f [service]"
    echo "  Execute shell: docker-compose -f docker-compose.dev.yml exec app bash"
    echo "  Run tests: docker-compose -f docker-compose.dev.yml exec app vendor/bin/phpunit"
    echo "  Stop services: docker-compose -f docker-compose.dev.yml down"
    echo ""
    log "Development environment is ready!"
}

# Main setup process
main() {
    log "Starting ABO-WBO Management System Development Setup"
    
    check_prerequisites
    setup_environment
    build_dev_images
    start_dev_services
    setup_dev_database
    install_dependencies
    setup_dev_tools
    
    # Optional: Run tests
    if [[ "${RUN_TESTS:-true}" == "true" ]]; then
        run_tests
    fi
    
    print_dev_summary
}

# Handle script arguments
case "${1:-setup}" in
    "setup")
        main
        ;;
    "start")
        log "Starting development services..."
        cd "$PROJECT_ROOT"
        docker-compose -f docker-compose.dev.yml up -d
        log "Services started"
        ;;
    "stop")
        log "Stopping development services..."
        cd "$PROJECT_ROOT"
        docker-compose -f docker-compose.dev.yml down
        log "Services stopped"
        ;;
    "restart")
        log "Restarting development services..."
        cd "$PROJECT_ROOT"
        docker-compose -f docker-compose.dev.yml restart
        log "Services restarted"
        ;;
    "logs")
        cd "$PROJECT_ROOT"
        docker-compose -f docker-compose.dev.yml logs -f
        ;;
    "test")
        cd "$PROJECT_ROOT"
        docker-compose -f docker-compose.dev.yml exec app vendor/bin/phpunit --testdox
        ;;
    "shell")
        cd "$PROJECT_ROOT"
        docker-compose -f docker-compose.dev.yml exec app bash
        ;;
    "clean")
        log "Cleaning development environment..."
        cd "$PROJECT_ROOT"
        docker-compose -f docker-compose.dev.yml down -v
        docker system prune -f
        log "Environment cleaned"
        ;;
    *)
        echo "Usage: $0 {setup|start|stop|restart|logs|test|shell|clean}"
        echo ""
        echo "Commands:"
        echo "  setup   - Initial development environment setup (default)"
        echo "  start   - Start development services"
        echo "  stop    - Stop development services"
        echo "  restart - Restart development services"
        echo "  logs    - Show service logs"
        echo "  test    - Run tests"
        echo "  shell   - Open shell in app container"
        echo "  clean   - Clean up development environment"
        exit 1
        ;;
esac