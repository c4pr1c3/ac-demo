#!/usr/bin/env bash

source 2_sign_server_and_client_certs.sh

openssl rsa -in intermediate/private/$domain.key.pem -out intermediate/private/$domain.key.nopass.pem

cat intermediate/certs/$domain.cert.pem intermediate/certs/ca-chain.cert.pem > intermediate/certs/$domain.chained.cert.pem


