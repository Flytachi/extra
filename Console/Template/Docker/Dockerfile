FROM alpine:latest
WORKDIR /var/www/html

# Installing bash
#RUN apk update && apk add bash
#RUN sed -i 's/bin\/ash/bin\/bash/g' /etc/passwd

RUN mkdir -p /var/run/
RUN touch /run/php8.2-fpm.pid

# Установка PHP 8.2 и PHP-FPM в Alpine
RUN apk add --no-cache \
    php82 \
    php82-common \
    php82-openssl \
    php82-pcntl \
    php82-mbstring \
    php82-fpm \
    php82-curl \
    php82-pdo \
    nginx \
    runit

#  =>  PostgreSql
RUN apk add --no-cache php82-pgsql php82-pdo_pgsql
#  =>  Redis
RUN apk add --no-cache php82-redis
#  =>  Mysql / MariaDb
RUN apk add --no-cache php82-mysqli php82-pdo_mysql php82-mysqlnd

# clean
RUN rm -rf /var/cache/apk/*

COPY . /var/www/html
COPY ./docker/php-fpm.conf /etc/php82/php-fpm.d/www.conf
COPY ./docker/nginx.conf /etc/nginx/nginx.conf


RUN mkdir -p /etc/service/php-fpm  \
    && echo -e "#!/bin/sh\nexec php-fpm82 -F" > /etc/service/php-fpm/run \
    && chmod +x /etc/service/php-fpm/run

RUN mkdir -p /etc/service/nginx  \
    && echo -e "#!/bin/sh\nexec nginx -g 'daemon off;'" > /etc/service/nginx/run \
    && chmod +x /etc/service/nginx/run

RUN chown -R nobody:nobody /var/www/html \
    && chmod -R 755 /var/www/html/public \
    && chmod -R 777 /var/www/html/storage

EXPOSE 80
CMD runsvdir /etc/service