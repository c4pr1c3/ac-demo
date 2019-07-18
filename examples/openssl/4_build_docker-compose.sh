#!/usr/bin/env bash

domain="${1:-ac-demo.me}"

cat << EOF > docker-compose.yml
version: '3'
services:
  proxy:
    image: nginx:stable-alpine
    container_name: nginx-https-demo
    ports:
      - 127.0.0.1:443:443
    volumes:
      - ./default.conf:/etc/nginx/conf.d/default.conf
      - ./intermediate/private/$domain.key.nopass.pem:/etc/nginx/ssl/server.key
      - ./intermediate/certs/$domain.chained.cert.pem:/etc/nginx/ssl/fullchain.pem
EOF

