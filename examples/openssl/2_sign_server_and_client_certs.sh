#!/usr/bin/env bash

# ref: https://jamielinux.com/docs/openssl-certificate-authority/sign-server-and-client-certificates.html

# 修改为你要签发的证书对应的域名
# 同时需要修改 intermediate/openssl.cnf 中 DNS.1 的域名
# ref: https://stackoverflow.com/questions/7580508/getting-chrome-to-accept-self-signed-localhost-certificate/43666288#43666288
domain="ac-demo.me" 


if [[ ! -f "intermediate/private/$domain.key.pem" ]];then

openssl genrsa -aes256 \
      -out intermediate/private/$domain.key.pem 2048

chmod 400 intermediate/private/$domain.key.pem

openssl req -config intermediate/openssl.cnf \
      -key intermediate/private/$domain.key.pem \
      -new -sha256 -out intermediate/csr/$domain.csr.pem

openssl ca -config intermediate/openssl.cnf \
      -extensions server_cert -days 375 -notext -md sha256 \
      -in intermediate/csr/$domain.csr.pem \
      -out intermediate/certs/$domain.cert.pem
chmod 444 intermediate/certs/$domain.cert.pem

openssl x509 -noout -text \
      -in intermediate/certs/$domain.cert.pem

openssl verify -CAfile intermediate/certs/ca-chain.cert.pem \
      intermediate/certs/$domain.cert.pem
fi

cat << EOF
./intermediate/certs/ca-chain.cert.pem
./intermediate/certs/ac-demo.me.cert.pem
./intermediate/private/ac-demo.me.key.pem
EOF

