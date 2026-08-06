FROM php:8.3-apache

# Install dependencies, the rm removes unnecessary lists of remaining files
RUN apt-get update  && \
    apt-get install -y \
    libzip-dev git\
    netcat-openbsd\
    unzip zip\
    libpq-dev \
    && rm -rf /var/lib/apt/lists/*

# Enable mod_rewrite to allow better looking URLs
RUN a2enmod rewrite

# Install PHP extensions
RUN docker-php-ext-install pdo_pgsql pgsql

# Make apache host files on var/www/html/public so that laravel gets what it expects
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf
        
# Copy the entire application code into the location var/www/html
COPY . /var/www/html

# FIx Laravel permisssions for Apache
RUN mkdir -p /var/www/html/storage/framework/views /var/www/html/storage/framework/cache /var/www/html/storage/framework/sessions \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache

# Set the working directory
WORKDIR /var/www/html

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install project dependencies
RUN composer install

# Remove old cached files
RUN php artisan view:clear

# Copy entrypoint.sh into the container image and make it executable
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["apache2-foreground"]

