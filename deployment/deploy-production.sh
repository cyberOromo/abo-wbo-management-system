#!/bin/bash

# ABO-WBO Management System Production Deployment Script
# This script handles the complete deployment process for production environment

set -e  # Exit on any error

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
BACKUP_DIR="$PROJECT_ROOT/deployment/backups/$(date +%Y%m%d_%H%M%S)"
ENV_FILE="$PROJECT_ROOT/.env.production"

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

# Check if running as root
check_root() {
    if [[ $EUID -eq 0 ]]; then
        error "This script should not be run as root for security reasons"
        exit 1
    fi
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
    
    # Check if environment file exists
    if [[ ! -f "$ENV_FILE" ]]; then
        error "Production environment file not found: $ENV_FILE"
        error "Please copy .env.production and configure it with your production values"
        exit 1
    fi
    
    log "Prerequisites check passed"
}

# Load environment variables
load_environment() {
    log "Loading production environment variables..."
    
    # Source the environment file
    set -a  # Automatically export all variables
    source "$ENV_FILE"
    set +a  # Stop automatically exporting
    
    # Validate critical environment variables
    if [[ -z "$DB_PASSWORD" ]]; then
        error "DB_PASSWORD is not set in environment file"
        exit 1
    fi
    
    if [[ -z "$APP_KEY" ]]; then
        error "APP_KEY is not set in environment file"
        exit 1
    fi
    
    if [[ -z "$JWT_SECRET" ]]; then
        error "JWT_SECRET is not set in environment file"
        exit 1
    fi
    
    log "Environment variables loaded successfully"
}

# Create backup
create_backup() {
    log "Creating deployment backup..."
    
    mkdir -p "$BACKUP_DIR"
    
    # Backup database if it exists
    if docker ps --format "table {{.Names}}" | grep -q "abo-wbo-mysql"; then
        log "Backing up database..."
        docker exec abo-wbo-mysql mysqldump \
            -u "${DB_USERNAME}" \
            -p"${DB_PASSWORD}" \
            "${DB_DATABASE}" > "$BACKUP_DIR/database.sql"
    fi
    
    # Backup uploaded files
    if [[ -d "$PROJECT_ROOT/public/uploads" ]]; then
        log "Backing up uploaded files..."
        cp -r "$PROJECT_ROOT/public/uploads" "$BACKUP_DIR/"
    fi
    
    # Backup current environment file
    if [[ -f "$PROJECT_ROOT/.env" ]]; then
        cp "$PROJECT_ROOT/.env" "$BACKUP_DIR/.env.backup"
    fi
    
    log "Backup created at: $BACKUP_DIR"
}

# Build Docker images
build_images() {
    log "Building Docker images..."
    
    cd "$PROJECT_ROOT"
    
    # Build application image
    docker build \
        -f deployment/docker/Dockerfile.app \
        --target production \
        -t abo-wbo/app:latest \
        -t abo-wbo/app:$(date +%Y%m%d_%H%M%S) \
        .
    
    # Build Nginx image
    docker build \
        -f deployment/docker/Dockerfile.nginx \
        -t abo-wbo/nginx:latest \
        -t abo-wbo/nginx:$(date +%Y%m%d_%H%M%S) \
        .
    
    # Build backup image
    docker build \
        -f deployment/docker/Dockerfile.backup \
        -t abo-wbo/backup:latest \
        -t abo-wbo/backup:$(date +%Y%m%d_%H%M%S) \
        .
    
    log "Docker images built successfully"
}

# Generate SSL certificates (if needed)
setup_ssl() {
    log "Setting up SSL certificates..."
    
    SSL_DIR="$PROJECT_ROOT/deployment/ssl"
    mkdir -p "$SSL_DIR"
    
    # Check if certificates already exist
    if [[ -f "$SSL_DIR/abo-wbo.org.crt" && -f "$SSL_DIR/abo-wbo.org.key" ]]; then
        log "SSL certificates already exist"
        return
    fi
    
    warn "SSL certificates not found. Please ensure you have valid SSL certificates."
    warn "For production, use certificates from a trusted CA like Let's Encrypt."
    
    # Generate self-signed certificates for testing (NOT for production)
    if [[ "${GENERATE_SELF_SIGNED:-false}" == "true" ]]; then
        warn "Generating self-signed certificates for testing purposes..."
        
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
            -keyout "$SSL_DIR/abo-wbo.org.key" \
            -out "$SSL_DIR/abo-wbo.org.crt" \
            -subj "/C=US/ST=State/L=City/O=ABO-WBO/CN=abo-wbo.org"
        
        # Copy for API subdomain
        cp "$SSL_DIR/abo-wbo.org.crt" "$SSL_DIR/api.abo-wbo.org.crt"
        cp "$SSL_DIR/abo-wbo.org.key" "$SSL_DIR/api.abo-wbo.org.key"
        
        # Generate DH parameters
        openssl dhparam -out "$SSL_DIR/dhparam.pem" 2048
    fi
}

# Setup database
setup_database() {
    log "Setting up database..."
    
    # Start MySQL container first
    docker-compose -f docker-compose.prod.yml up -d mysql
    
    # Wait for MySQL to be ready
    log "Waiting for MySQL to start..."
    timeout=60
    while ! docker exec abo-wbo-mysql mysqladmin ping -h"localhost" --silent; do
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
    docker-compose -f docker-compose.prod.yml run --rm app php database/migrate.php
    
    log "Database setup completed"
}

# Deploy application
deploy_application() {
    log "Deploying application..."
    
    cd "$PROJECT_ROOT"
    
    # Copy environment file
    cp "$ENV_FILE" "$PROJECT_ROOT/.env"
    
    # Pull latest images (if using registry)
    if [[ "${USE_REGISTRY:-false}" == "true" ]]; then
        docker-compose -f docker-compose.prod.yml pull
    fi
    
    # Start all services
    docker-compose -f docker-compose.prod.yml up -d
    
    # Wait for services to be healthy
    log "Waiting for services to become healthy..."
    sleep 30
    
    # Check service health
    check_service_health
    
    log "Application deployed successfully"
}

# Check service health
check_service_health() {
    log "Checking service health..."
    
    local services=("app" "nginx" "mysql" "redis")
    local failed_services=()
    
    for service in "${services[@]}"; do
        container_name="abo-wbo-${service}"
        if [[ "$service" == "app" ]]; then
            container_name="abo-wbo-app"
        fi
        
        if docker ps --format "table {{.Names}}\t{{.Status}}" | grep "$container_name" | grep -q "healthy\|Up"; then
            log "Service $service is healthy"
        else
            error "Service $service is not healthy"
            failed_services+=("$service")
        fi
    done
    
    if [[ ${#failed_services[@]} -gt 0 ]]; then
        error "The following services failed health checks: ${failed_services[*]}"
        error "Check logs with: docker-compose -f docker-compose.prod.yml logs [service_name]"
        exit 1
    fi
}

# Setup monitoring
setup_monitoring() {
    log "Setting up monitoring..."
    
    # Start monitoring services
    docker-compose -f docker-compose.prod.yml up -d prometheus grafana
    
    # Wait for services to start
    sleep 10
    
    # Check if Grafana is accessible
    if curl -f http://localhost:3000/api/health &> /dev/null; then
        log "Grafana is accessible at http://localhost:3000"
        info "Default login: admin / \${GRAFANA_ADMIN_PASSWORD}"
    else
        warn "Grafana may not be fully started yet. Please check manually."
    fi
    
    # Check if Prometheus is accessible
    if curl -f http://localhost:9090/api/v1/status/config &> /dev/null; then
        log "Prometheus is accessible at http://localhost:9090"
    else
        warn "Prometheus may not be fully started yet. Please check manually."
    fi
    
    log "Monitoring setup completed"
}

# Setup backup system
setup_backup() {
    log "Setting up backup system..."
    
    # Start backup service
    docker-compose -f docker-compose.prod.yml up -d backup
    
    # Verify backup configuration
    if docker exec abo-wbo-backup test -f /backup/scripts/backup.sh; then
        log "Backup system is configured"
        
        # Test backup script
        docker exec abo-wbo-backup /backup/scripts/test-backup.sh
        log "Backup system test completed"
    else
        warn "Backup system may not be properly configured"
    fi
    
    log "Backup system setup completed"
}

# Post-deployment tasks
post_deployment_tasks() {
    log "Running post-deployment tasks..."
    
    # Clear application cache
    docker-compose -f docker-compose.prod.yml exec app php -r "
        require_once 'vendor/autoload.php';
        \$cache = new \App\Utils\CacheManager();
        \$cache->flush();
        echo 'Cache cleared successfully\n';
    "
    
    # Warm up application cache
    log "Warming up application cache..."
    curl -s http://localhost/health > /dev/null || true
    curl -s http://localhost/api/health > /dev/null || true
    
    # Send deployment notification
    if [[ -n "${DEPLOYMENT_WEBHOOK_URL:-}" ]]; then
        log "Sending deployment notification..."
        curl -X POST "$DEPLOYMENT_WEBHOOK_URL" \
            -H "Content-Type: application/json" \
            -d '{
                "text": "ABO-WBO Management System deployed successfully",
                "timestamp": "'$(date -u +%Y-%m-%dT%H:%M:%SZ)'",
                "version": "'$(date +%Y%m%d_%H%M%S)'"
            }' || warn "Failed to send deployment notification"
    fi
    
    log "Post-deployment tasks completed"
}

# Print deployment summary
print_summary() {
    log "Deployment Summary"
    echo "=================="
    echo "Application URL: ${APP_URL}"
    echo "API URL: ${API_URL}"
    echo "Monitoring: http://localhost:3000 (Grafana)"
    echo "Metrics: http://localhost:9090 (Prometheus)"
    echo ""
    echo "Services Status:"
    docker-compose -f docker-compose.prod.yml ps
    echo ""
    echo "Backup Location: $BACKUP_DIR"
    echo ""
    log "Deployment completed successfully!"
}

# Rollback function
rollback() {
    error "Deployment failed. Starting rollback..."
    
    # Stop current deployment
    docker-compose -f docker-compose.prod.yml down
    
    # Restore database if backup exists
    if [[ -f "$BACKUP_DIR/database.sql" ]]; then
        log "Restoring database..."
        docker-compose -f docker-compose.prod.yml up -d mysql
        sleep 10
        docker exec -i abo-wbo-mysql mysql \
            -u "${DB_USERNAME}" \
            -p"${DB_PASSWORD}" \
            "${DB_DATABASE}" < "$BACKUP_DIR/database.sql"
    fi
    
    # Restore environment file
    if [[ -f "$BACKUP_DIR/.env.backup" ]]; then
        cp "$BACKUP_DIR/.env.backup" "$PROJECT_ROOT/.env"
    fi
    
    # Restore uploaded files
    if [[ -d "$BACKUP_DIR/uploads" ]]; then
        rm -rf "$PROJECT_ROOT/public/uploads"
        cp -r "$BACKUP_DIR/uploads" "$PROJECT_ROOT/public/"
    fi
    
    error "Rollback completed. Please check the logs and fix issues before retrying deployment."
    exit 1
}

# Trap errors and rollback
trap rollback ERR

# Main deployment process
main() {
    log "Starting ABO-WBO Management System Production Deployment"
    
    check_root
    check_prerequisites
    load_environment
    create_backup
    setup_ssl
    build_images
    setup_database
    deploy_application
    setup_monitoring
    setup_backup
    post_deployment_tasks
    print_summary
}

# Run main function
main "$@"