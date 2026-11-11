# PHP 8.3 + Apache. Works on Render, Railway, Google Cloud Run, Azure, Fly.io.
FROM php:8.3-apache

# Cloud hosts pass the port in $PORT (Render, Cloud Run); default 80.
ENV PORT=80
RUN sed -ri 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
 && sed -ri 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-available/000-default.conf \
 && a2enmod rewrite headers

COPY . /var/www/html/
RUN mkdir -p /var/www/html/data && chown -R www-data:www-data /var/www/html/data

EXPOSE 80
