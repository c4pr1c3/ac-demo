#!/usr/bin/env bash

domain="${1:-ac-demo.me}" 

openssl rsa -in intermediate/private/$domain.key.pem -out intermediate/private/$domain.key.nopass.pem

cat intermediate/certs/$domain.cert.pem intermediate/certs/ca-chain.cert.pem > intermediate/certs/$domain.chained.cert.pem


