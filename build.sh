#!/bin/bash
docker build -t c4pr1c3/ac-demo-db:1.0  -f docker/ac-demo-db/Dockerfile .
docker build -t c4pr1c3/ac-demo-php:1.0  -f docker/ac-demo-php/Dockerfile .
docker run --rm -it -v "${PWD}/src:/src" -w /src node:11-alpine npm install
docker-compose -f docker/docker-compose.yml up -d
