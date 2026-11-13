
FROM php:8.4-apache

# Install required system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    && docker-php-ext-install \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        zip \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Configure Apache
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri \
    -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

RUN a2enmod rewrite headers

# Configure Render port
RUN sed -i 's/Listen 80/Listen 10000/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:10000>/' /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/html

# Copy Laravel project
COPY . .

# Install production dependencies
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-progress \
    -vvv

# Create persistent SQLite directory
RUN mkdir -p /var/data \
    && chown -R www-data:www-data \
    storage \
    bootstrap/cache \
    database \
    && chmod -R 775 \
    storage \
    bootstrap/cache \
    database /var/data

EXPOSE 10000

# Initialize SQLite and run migrations
CMD ["bash", "-lc", "mkdir -p database && touch database/database.sqlite && chown -R www-data:www-data database storage bootstrap/cache && chmod -R 775 database storage bootstrap/cache && php artisan migrate --force && exec apache2-foreground"]