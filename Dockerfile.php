FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libcurl4-openssl-dev \
    && docker-php-ext-install curl pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache-chrono.conf /etc/apache2/conf-available/chrono.conf
RUN a2enconf chrono

COPY . /var/www/html/

EXPOSE 80
