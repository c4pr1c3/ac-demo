FROM php:7.3-apache
MAINTAINER c4pr1c3 "c4pr1c3@gmail.com"

RUN docker-php-ext-install pdo pdo_mysql

RUN mkdir '/srv/acdemo' && chown -R www-data:www-data '/srv/acdemo'

RUN apt-get update && apt-get install -y libcap2-bin

#setcap to bind to privileged ports as non-root
RUN setcap 'cap_net_bind_service=+ep' /usr/sbin/apache2
EXPOSE 80
EXPOSE 443
USER www-data
HEALTHCHECK --interval=10s --timeout=3s --retries=3 CMD curl --fail http://localhost/ || exit 1
