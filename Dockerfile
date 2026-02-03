# Render / Docker: PHP + nginx + MongoDB (Atlas)
FROM richarvey/nginx-php-fpm:3.1.6

# Extensión MongoDB para PHP (Alpine)
RUN apk add --no-cache php82-pecl-mongodb 2>/dev/null || apk add --no-cache php81-pecl-mongodb 2>/dev/null || true

# Composer para mongodb/mongodb
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
COPY composer.json composer.lock* ./
RUN composer install --no-dev --ignore-platform-reqs 2>/dev/null || true
COPY . /var/www/html
RUN composer install --no-dev --ignore-platform-reqs 2>/dev/null || true

ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html
ENV PHP_ERRORS_STDERR 1
EXPOSE 80
CMD ["/start.sh"]
