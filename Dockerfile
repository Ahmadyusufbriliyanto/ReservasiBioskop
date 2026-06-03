FROM php:8.3-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    pkg-config \
    libssl-dev

RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Hindari konflik MPM Apache
RUN a2dismod mpm_event || true
RUN a2enmod mpm_prefork rewrite

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

# Script startup untuk Railway PORT
RUN echo '#!/bin/bash\n\
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf\n\
sed -i "s/*:80/*:${PORT}/g" /etc/apache2/sites-available/000-default.conf\n\
apache2-foreground' > /start.sh && chmod +x /start.sh

CMD ["/start.sh"]
