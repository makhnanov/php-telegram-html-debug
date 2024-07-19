FROM php:7.1-cli-buster

COPY --from=composer:2.2.24 /usr/bin/composer /usr/local/bin/composer

RUN apt-get -y update && apt-get -y upgrade && apt-get -y install zip
