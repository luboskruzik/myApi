FROM php:7.4-apache

COPY ./001-default.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

RUN docker-php-ext-install pdo_mysql


