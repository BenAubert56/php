FROM php:8.2-apache

# Installer les paquets système + extensions PHP nécessaires
RUN apt-get update && apt-get install -y unzip git zip curl \
    && docker-php-ext-install pdo pdo_mysql

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/local/bin/composer

# Config Apache pour Symfony
COPY .docker/apache-vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Dossier de travail Symfony
WORKDIR /var/www/html
