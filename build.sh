#!/bin/bash
docker build -t cuc_ac_demo_db:1.0  -f docker/mariadb/Dockerfile .
docker build -t cuc_ac_demo_php:1.0  -f docker/php-apache/Dockerfile .
docker-compose -f docker/docker-compose.yml up -d 
