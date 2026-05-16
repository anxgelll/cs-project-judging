FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY . /var/www/html/

RUN mkdir -p /var/www/html/data /var/data \
    && chown -R www-data:www-data /var/www/html/data /var/data

ENV DB_PATH=/var/data/grading.sqlite

EXPOSE 80
