# ============================================================
# EasyBank Dockerfile
# ============================================================

# Use latest patched PHP 8.4 on Debian Trixie
FROM php:8.4.16-apache-trixie

# Install OS updates and required packages
RUN apt-get update && \
    apt-get upgrade -y && \
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

# Copy application files and fix permissions
COPY --chown=www-data:www-data . /var/www/html/

# Drop root user for security
USER www-data

# Run Apache in foreground
CMD ["apache2-foreground"]
