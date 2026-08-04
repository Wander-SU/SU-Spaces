FROM php:8.2-cli

# Install system utilities & PostgreSQL client libraries
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libpq-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install standard PHP extensions required for Laravel & PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql bcmath gd

WORKDIR /var/www

COPY . .

# Install Composer dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]