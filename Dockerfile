FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    mysql-client \
    mysql-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    curl-dev \
    oniguruma-dev \
    icu-dev \
    git \
    unzip \
    bash \
    wget \
    && \
    docker-php-ext-install mysqli && \
    docker-php-ext-install pdo pdo_mysql && \
    docker-php-ext-configure gd \
        --with-freetype \
        --with-jpeg && \
    docker-php-ext-install gd && \
    docker-php-ext-install mbstring && \
    docker-php-ext-install opcache && \
    docker-php-ext-install zip && \
    docker-php-ext-install exif && \
    apk del mysql-dev libpng-dev libjpeg-turbo-dev freetype-dev \
        libzip-dev curl-dev oniguruma-dev icu-dev

COPY php.ini /usr/local/etc/php/php.ini
COPY php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

WORKDIR /var/www/filmoteka

COPY db.php login.php logout.php registracija.php landing.php ./
COPY index.php profil.php pogledaj_profil.php edit_profil.php ./
COPY admin_panel.php admin_funkcije.php admin_feedback.php ./
COPY detalji.php zanr.php trendovi.php akcije_zbirka.php ./
COPY kolekcije_handler.php kontakt.php o_nama.php ./
COPY script.js stil.css ./

RUN mkdir -p /var/www/filmoteka/uploads && \
    mkdir -p /var/log/php && \
    touch /var/log/php/error.log && \
    chown -R www-data:www-data /var/www/filmoteka && \
    chmod -R 755 /var/www/filmoteka && \
    chmod -R 775 /var/www/filmoteka/uploads

EXPOSE 9000

HEALTHCHECK --interval=30s --timeout=10s --start-period=20s --retries=3 \
    CMD php-fpm -t 2>/dev/null || exit 1

CMD ["php-fpm", "-F"]
