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

RUN mkdir -p /app/var/cache && chown -R 82:82 /app/var/cache
RUN mkdir -p /app/var/logs && chown -R 82:82 /app/var/logs

WORKDIR /app
