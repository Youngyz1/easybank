# Use a newer, patched slim base image
FROM php:8.2-apache-bookworm-slim

# Install OS updates and required packages
RUN apt-get update && \
    apt-get upgrade -y && \
    apt-get dist-upgrade -y && \
    apt-get install -y --no-install-recommends \
        libzip-dev \
        zip \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# Enable only required Apache modules
RUN a2enmod rewrite && \
    sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy application files
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# Drop root (Apache runs as www-data internally)
USER www-data

# Run Apache
CMD ["apache2-foreground"]
