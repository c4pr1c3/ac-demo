FROM php:7.3-apache
MAINTAINER c4pr1c3 "c4pr1c3@gmail.com"

RUN docker-php-ext-install pdo pdo_mysql

RUN mkdir '/srv/acdemo'
RUN chown -R www-data:www-data '/srv/acdemo'

EXPOSE 80
EXPOSE 443
HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD curl --fail http://localhost/ || exit 1
