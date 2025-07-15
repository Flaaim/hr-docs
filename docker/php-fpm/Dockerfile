FROM php:8.1-fpm-alpine

RUN apk add --no-cache autoconf g++ make \
    && pecl install xdebug-3.1.6 \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable xdebug

RUN apk add --no-cache libzip-dev zip unzip \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

RUN docker-php-ext-install pdo pdo_mysql

RUN mv $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini

COPY ./php/conf.d /usr/local/etc/php/conf.d
COPY ./php-fpm/conf.d /usr/local/etc/php/conf.d

RUN mkdir -p /app/var/cache && \
    chown -R www-data:www-data /app/var/cache && \
    chmod -R 755 /app/var/cache

RUN mkdir -p /app/var/logs && \
    chown -R www-data:www-data /app/var/logs && \
    chmod -R 755 /app/var/logs

RUN mkdir -p /app/var/lock && \
    chown -R www-data:www-data /app/var/lock && \
    chmod -R 755 /app/var/lock \

WORKDIR /app
