FROM alpine:3.20.0
WORKDIR /var/www/html

# Installing bash
#RUN apk update && apk add bash
#RUN sed -i 's/bin\/ash/bin\/bash/g' /etc/passwd

RUN mkdir -p /var/run/
RUN touch /run/php8.3-fpm.pid

# Установка PHP 8.3 и PHP-FPM в Alpine
RUN apk add --no-cache \
    php83 \
    php83-common \
    php83-phar \
    php83-openssl \
    php83-pcntl \
    php83-posix \
    php83-mbstring \
    php83-simplexml \
    php83-fpm \
    php83-curl \
    php83-pdo \
    nginx \
    curl \
    runit

#  =>  PostgreSql
RUN apk add --no-cache php83-pgsql php83-pdo_pgsql
#  =>  Redis
RUN apk add --no-cache php83-redis
#  =>  Mysql / MariaDb
RUN apk add --no-cache php83-mysqli php83-pdo_mysql php83-mysqlnd

# clean
RUN rm -rf /var/cache/apk/*

# php configure
RUN test -f /usr/bin/php || ln -s /usr/bin/php83 /usr/bin/php

COPY . /var/www/html
COPY ./docker/php-fpm.conf /etc/php83/php-fpm.d/www.conf
COPY ./docker/nginx.conf /etc/nginx/nginx.conf

# => Composer install
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer update --ignore-platform-req=ext-sockets --no-plugins --no-scripts --no-interaction

RUN mkdir -p /etc/service/php-fpm  \
    && echo -e "#!/bin/sh\nexec php-fpm83 -F --allow-to-run-as-root" > /etc/service/php-fpm/run \
    && chmod +x /etc/service/php-fpm/run

RUN mkdir -p /etc/service/nginx  \
    && echo -e "#!/bin/sh\nexec nginx -g 'daemon off;'" > /etc/service/nginx/run \
    && chmod +x /etc/service/nginx/run

RUN chown -R nginx:nginx /var/www/html \
    && chmod -R 755 /var/www/html/public \
    && chmod -R 777 /var/www/html/storage

EXPOSE 80
CMD runsvdir /etc/service