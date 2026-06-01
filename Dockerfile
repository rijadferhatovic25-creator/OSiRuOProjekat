FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx \
    bash \
    curl \
    mysql-client \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql mbstring gd zip exif opcache
    
COPY backend /var/www/html
COPY frontend/nginx.conf /etc/nginx/http.d/default.conf

RUN mkdir -p /var/www/html/uploads \
    && chown -R www-data:www-data /var/www/html

COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
