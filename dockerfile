# Use a patched slim variant for faster security updates
FROM php:8.2-apache-bullseye-slim

# Update OS packages and remove unnecessary packages
RUN apt-get update && apt-get upgrade -y \
    && apt-get install -y --no-install-recommends \
       libzip-dev zip unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Enable Apache rewrite and allow overrides
RUN a2enmod rewrite \
 && sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy app files
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# Default command
CMD ["apache2-foreground"]
