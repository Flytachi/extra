FROM alpine:3.21.2
WORKDIR /var/www/html

# Установка необходимых пакетов
RUN apk update && apk upgrade && apk add --no-cache php83 \
    php83-common php83-phar php83-pcntl \
    php83-posix php83-mbstring php83-simplexml \
    php83-pgsql php83-mysqli php83-mysqlnd \
    php83-pdo php83-pdo_pgsql php83-pdo_mysql \
    php83-fpm php83-curl php83-redis \
    php83-openssl php83-sockets \
    curl nginx runit

# Создание необходимых файлов и директорий
RUN mkdir -p /var/run/ && touch /run/php8.3-fpm.pid

# Настройка PHP и Nginx
RUN test -f /usr/bin/php || ln -s /usr/bin/php83 /usr/bin/php

COPY . /var/www/html
COPY ./docker/php-fpm.conf /etc/php83/php-fpm.d/www.conf
COPY ./docker/nginx.conf /etc/nginx/nginx.conf

# Установка Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --no-plugins --no-interaction --optimize-autoloader

# Настройка сервисов
RUN mkdir -p /etc/service/php-fpm \
    && echo -e "#!/bin/sh\nexec php-fpm83 -F --allow-to-run-as-root" > /etc/service/php-fpm/run \
    && chmod +x /etc/service/php-fpm/run

RUN mkdir -p /etc/service/nginx \
    && echo -e "#!/bin/sh\nexec nginx -g 'daemon off;'" > /etc/service/nginx/run \
    && chmod +x /etc/service/nginx/run

RUN mkdir -p /etc/service/logs \
    && echo -e "#!/bin/sh\nwhile true; do\n    ls /var/www/html/storage/logs/frame*.log 1>/dev/null 2>/dev/null && tail -F /var/www/html/storage/logs/frame*.log 2>/dev/null || inotifywait -e create /var/www/html/storage/logs >/dev/null 2>&1; sleep 1;\ndone" > /etc/service/logs/run \
    && chmod +x /etc/service/logs/run

# Установка прав доступа
RUN chown -R nginx:nginx /var/www/html \
    && chmod -R 755 /var/www/html/public \
    && chmod -R 775 /var/www/html/storage

EXPOSE 80
ENTRYPOINT ["runsvdir", "/etc/service"]
