FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo pdo_mysql
RUN apt-get update && apt-get install -y libcurl4-openssl-dev \
    && docker-php-ext-install curl

COPY . /var/www/html/

RUN mkdir -p /var/www/html/qrcodes && chmod -R 755 /var/www/html/qrcodes

# Set presentation as the web root entry point
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html/presentation\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

RUN a2enmod rewrite

EXPOSE 80
