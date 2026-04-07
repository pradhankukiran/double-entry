FROM php:8.4-apache

RUN docker-php-ext-install pdo pdo_mysql bcmath

# Enable mod_rewrite
RUN a2enmod rewrite

# Point Apache DocumentRoot to public/
ENV APACHE_DOCUMENT_ROOT=/var/www/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides in /var/www/
RUN sed -ri -e '/<Directory \/var\/www\/>/,/<\/Directory>/s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

WORKDIR /var/www
