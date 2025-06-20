# Use the official PHP 8.1 image with Apache
FROM php:8.1-apache

# Install system dependencies and PHP extensions for PostgreSQL
RUN apt-get update && \
    apt-get install -y \
        libzip-dev \
        libpq-dev \
        unzip \
        git && \
    docker-php-ext-install zip pdo pdo_pgsql && \
    a2enmod rewrite && \
    rm -rf /var/lib/apt/lists/*



# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer


# Add to Dockerfile after installing extensions
RUN docker-php-ext-install opcache && \
    docker-php-ext-enable opcache 
       

# Install additional dependencies
RUN apt-get update && apt-get install -y ca-certificates postgresql-client

# Create uploads directory and set permissions
RUN mkdir -p /var/www/html/uploads && \
    chown -R www-data:www-data /var/www/html/uploads && \
    chmod 775 /var/www/html/uploads

# Set working directory
WORKDIR /var/www/html

# Copy existing application files
COPY . /var/www/html

# Install PHP dependencies
RUN if [ -f composer.json ]; then composer install --no-dev --optimize-autoloader; fi

# Set permissions for entire directory
RUN chown -R www-data:www-data /var/www/html && \
    find /var/www/html -type d -exec chmod 755 {} \; && \
    find /var/www/html -type f -exec chmod 644 {} \; && \
    chmod -R 775 /var/www/html/uploads  # Extra permission safeguard


# Add to Dockerfile
RUN { \
    echo "<IfModule mod_expires.c>"; \
    echo "  ExpiresActive On"; \
    echo "  ExpiresByType image/jpg 'access plus 1 year'"; \
    echo "  ExpiresByType image/jpeg 'access plus 1 year'"; \
    echo "  ExpiresByType image/gif 'access plus 1 year'"; \
    echo "  ExpiresByType image/png 'access plus 1 year'"; \
    echo "  ExpiresByType text/css 'access plus 1 month'"; \
    echo "  ExpiresByType application/pdf 'access plus 1 month'"; \
    echo "</IfModule>"; \
} >> /etc/apache2/conf-available/expires.conf && \
a2enmod expires
    
# Expose port 443 (Render will map this automatically)
EXPOSE 443

# Start Apache in the foreground
CMD ["apache2-foreground"]