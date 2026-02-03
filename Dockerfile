# Render / Docker: PHP + nginx + MongoDB (Atlas)
FROM richarvey/nginx-php-fpm:3.1.6

# Extensión MongoDB para PHP 8.2 (imagen usa PHP 8.2)
RUN apk add --no-cache php82-pecl-mongodb || \
    (apk add --no-cache php81-pecl-mongodb || true)

# Composer y dependencias
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
COPY composer.json composer.lock* ./
RUN composer install --no-dev --ignore-platform-reqs
COPY . /var/www/html
RUN composer install --no-dev --ignore-platform-reqs

ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html
ENV PHP_ERRORS_STDERR 1
EXPOSE 80
CMD ["/start.sh"]
