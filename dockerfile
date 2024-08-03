FROM php:8.2-cli

# ARG DEBIAN_FRONTEND=noninteractive
ARG COMPOSER_ALLOW_SUPERUSER=1
USER root
WORKDIR /var/www/html

COPY . .

RUN apt-get update \
    && apt-get install libzip-dev -y  \
    && apt-get install libpq-dev -y \
    && apt-get install  libcurl4-openssl-dev -y \
    && docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql \
    && docker-php-ext-configure curl -with-curl=/usr/local/curl \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip pgsql curl

# RUN apt -y install software-properties-common \
# && add-apt-repository ppa:ondrej/php

# RUN apt update

# RUN apt install php8.2 -y 

# RUN apt install php8.2-curl -y \
# && apt install php8.2-dom -y \ 
# && apt install php8.2-xsl -y \
# && apt install php8.2-zip -y \
# && apt install php8.2-mbstring -y \
# && apt install php8.2-pgsql -y

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"\
&& php -r "if (hash_file('sha384', 'composer-setup.php') === 'dac665fdc30fdd8ec78b38b9800061b4150413ff2e3b6f88543c636f7cd84f6db9189d43a81e5503cda447da73c7e5b6') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
&& php composer-setup.php \
&& php -r "unlink('composer-setup.php');" 

RUN php composer.phar install

# RUN php artisan migrate 

ENTRYPOINT [ "php", "artisan", "serve" ]
CMD ["--host=0.0.0.0"]

