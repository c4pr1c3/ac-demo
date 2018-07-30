FROM mariadb:5.5
MAINTAINER c4pr1c3 "c4pr1c3@gmail.com"

ENV MYSQL_DATABASE=acdemo \
    MYSQL_ROOT_PASSWORD=example

ADD db/0_create_usr.sql /docker-entrypoint-initdb.d
ADD db/1_init_db.sql /docker-entrypoint-initdb.d

VOLUME ["/var/lib/mysql"]

EXPOSE 3306

