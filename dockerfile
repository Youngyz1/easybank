# Use a newer, patched slim base image (PHP 8.4)
FROM php:8.4.16-apache-trixie

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
RUN a2enmod rewrite

# Change Apache to listen on port 8080
RUN sed -i 's/80/8080/' /etc/apache2/ports.conf && \
    sed -i 's/:80/:8080/' /etc/apache2/sites-available/000-default.conf

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy application files
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# Drop root (Apache runs as www-data internally)
USER www-data

# Expose port 8080
EXPOSE 8080

# Run Apache
CMD ["apache2-foreground"]
