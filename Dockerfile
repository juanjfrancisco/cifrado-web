# Use official PHP-FPM with Nginx image
FROM php:8.2-fpm-alpine

# Install required packages
RUN apk add --no-cache \
    nginx \
    sqlite \
    sqlite-dev \
    && docker-php-ext-install pdo_sqlite

# Configure PHP
RUN echo "date.timezone = UTC" > /usr/local/etc/php/conf.d/timezone.ini

# Create necessary directories
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html/data \
    && chmod 775 /var/www/html/data

# Copy application files
COPY . /var/www/html/

# Copy Nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Create PHP-FPM configuration
RUN echo '[www]' > /usr/local/etc/php-fpm.d/www.conf \
    && echo 'user = www-data' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'group = www-data' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'listen = 127.0.0.1:9000' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm = dynamic' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.max_children = 5' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.start_servers = 2' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.min_spare_servers = 1' >> /usr/local/etc/php-fpm.d/www.conf \
    && echo 'pm.max_spare_servers = 3' >> /usr/local/etc/php-fpm.d/www.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod 775 /var/www/html/data

# Create startup script
RUN echo '#!/bin/sh' > /start.sh \
    && echo 'echo "Iniciando servicios..."' >> /start.sh \
    && echo 'php-fpm &' >> /start.sh \
    && echo 'sleep 2' >> /start.sh \
    && echo 'echo "Inicializando base de datos..."' >> /start.sh \
    && echo 'php /var/www/html/init_db.php' >> /start.sh \
    && echo 'echo "Iniciando Nginx..."' >> /start.sh \
    && echo 'nginx -g "daemon off;"' >> /start.sh \
    && chmod +x /start.sh

# Expose port 8069
EXPOSE 8069

# Start both PHP-FPM and Nginx
CMD ["/start.sh"]