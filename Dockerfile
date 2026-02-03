# Render (y cualquier host Docker): app PHP con nginx + PHP-FPM
FROM richarvey/nginx-php-fpm:3.1.6

COPY . /var/www/html

# Raíz de la app = raíz del repo (no hay carpeta public/)
ENV SKIP_COMPOSER 1
ENV WEBROOT /var/www/html
ENV PHP_ERRORS_STDERR 1

# Render expone el puerto 80; la imagen ya escucha en 80
EXPOSE 80

CMD ["/start.sh"]
