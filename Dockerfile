FROM php:8.2-cli

RUN apt-get update 
RUN apt-get install -y lame libzip-dev zip

RUN docker-php-ext-install zip

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /usr/src/app