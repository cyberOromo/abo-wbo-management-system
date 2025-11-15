# Production PHP-FPM Application Dockerfile
# Multi-stage build for optimized production image

FROM php:8.2-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    freetype-dev \
    g++ \
    gcc \
    git \
    icu-dev \
    jpeg-dev \
    libc-dev \
    libpng-dev \
    libzip-dev \
    make \
    mysql-client \
    nodejs \
    npm \
    oniguruma-dev \
    openssl-dev \
    zip \
    unzip

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        bcmath \
        gd \
        intl \
        mysqli \
        opcache \
        pdo \
        pdo_mysql \
        zip

# Install Redis extension
RUN pecl install redis \
    && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create application user
RUN addgroup -g 1000 www \
    && adduser -u 1000 -G www -s /bin/sh -D www

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock ./

# Development stage
FROM base AS development

# Install Xdebug for development
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

# Copy PHP development configuration
COPY deployment/php/php-dev.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY deployment/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

# Install development dependencies
RUN composer install --no-scripts --no-autoloader

# Copy application code
COPY . .

# Generate autoloader
RUN composer dump-autoload --optimize

# Set permissions
RUN chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/public/uploads

USER www

EXPOSE 9000 9003

CMD ["php-fpm"]

# Production stage
FROM base AS production

# Copy PHP production configuration
COPY deployment/php/php-prod.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY deployment/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Install production dependencies only
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Copy application code
COPY . .

# Generate optimized autoloader
RUN composer dump-autoload --optimize --classmap-authoritative

# Install Node.js dependencies and build assets
RUN npm ci --only=production \
    && npm run build \
    && npm cache clean --force \
    && rm -rf node_modules

# Set permissions
RUN chown -R www:www /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/public/uploads \
    && chmod -R 644 /var/www/html/public/assets

# Remove unnecessary files for production
RUN rm -rf \
    .git \
    .gitignore \
    .env.example \
    tests \
    deployment/docker \
    node_modules \
    package*.json \
    webpack.config.js \
    .babelrc

# Health check
COPY deployment/docker/healthcheck.php /usr/local/bin/healthcheck.php
RUN chmod +x /usr/local/bin/healthcheck.php

HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD php /usr/local/bin/healthcheck.php

USER www

EXPOSE 9000

CMD ["php-fpm"]