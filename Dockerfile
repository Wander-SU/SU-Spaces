FROM php:8.2-cli

# Install official helper for smooth PHP extension installations
COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/

# Install exact extensions required for Laravel and PostgreSQL
RUN install-php-extensions pdo_pgsql gd zip bcmath

WORKDIR /var/www

COPY . .

# Install Composer dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

EXPOSE 8080

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]