FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl

COPY . /var/www/html/

RUN mkdir -p /var/www/html/qrcodes && chmod -R 755 /var/www/html/qrcodes

# ✅ Point to root, not presentation/
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
        Options Indexes FollowSymLinks\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

EXPOSE 80
