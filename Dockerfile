FROM php:8.4-cli

RUN docker-php-ext-install pdo pdo_mysql bcmath

WORKDIR /var/www
