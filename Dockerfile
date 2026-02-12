FROM php:8.2-apache

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    nano \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql mysqli zip

RUN a2enmod rewrite

RUN touch $APACHE_CONFDIR/conf-available/docker-php.conf && \
    cat > $APACHE_CONFDIR/conf-available/docker-php.conf <<'FILE'
<FilesMatch \.php$>
    SetHandler application/x-httpd-php
</FilesMatch>

DirectoryIndex disabled
DirectoryIndex index.php index.html

DocumentRoot /var/www/html/public

<Directory /var/www/html/public>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

<Directory /var/www/html/config>
    Require all denied
</Directory>

<Directory /var/www/html/src>
    Require all denied
</Directory>
FILE

RUN a2enconf docker-php

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]