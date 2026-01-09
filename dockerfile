FROM php:8.2-apache

# Enable Apache rewrite
RUN a2enmod rewrite

# Copy app files
COPY . /var/www/html/

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

# IMPORTANT: Force Apache to stay in foreground
CMD ["apache2-foreground"]
