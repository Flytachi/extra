FROM php:8.2-fpm
WORKDIR /var/www/html

RUN apt-get update
RUN apt-get install -y libpq-dev
RUN apt-get clean

RUN docker-php-ext-install pcntl
RUN docker-php-ext-install pdo
RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install pdo_pgsql

# Install php-igbinary
RUN pecl install igbinary && docker-php-ext-enable igbinary
# Install php-redis
RUN pecl install redis && docker-php-ext-enable redis

RUN rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*
