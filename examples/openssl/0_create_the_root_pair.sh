#!/usr/bin/env bash

# ref: https://jamielinux.com/docs/openssl-certificate-authority/create-the-root-pair.html

if [[ ! -d "certs" ]];then
  mkdir certs crl newcerts private
  chmod 700 private
  touch index.txt
  echo 1000 > serial
fi

if [[ -d "private" && ! -f "private/ca.key.pem" ]];then
cat <<EOF
-------------------- Notes Below --------------------
Enter pass phrase for ca.key.pem: secretpassword
Verifying - Enter pass phrase for ca.key.pem: secretpassword
-------------------- Notes Above --------------------
EOF
openssl genrsa -aes256 -out private/ca.key.pem 4096
chmod 400 private/ca.key.pem

cat <<EOF
-------------------- Notes Below --------------------
Enter pass phrase for ca.key.pem: secretpassword
You are about to be asked to enter information that will be incorporated
into your certificate request.
-----
Country Name (2 letter code) [XX]:CN
State or Province Name []:Beijing
Locality Name []:Chaoyang
Organization Name []:Communication University of China
Organizational Unit Name []:School of Computer Science and Cybersecurity
Common Name []:A101E LAB Root CA
Email Address []:admin@a101e.lab
-------------------- Notes Above --------------------
EOF

if [[ ! -f "openssl.cnf" ]];then
  cat "openssl.cnf.example" | sed "s#<CA_default_dir>#$PWD#g"  > "openssl.cnf"
fi

openssl req -config openssl.cnf \
      -key private/ca.key.pem \
      -new -x509 -days 7300 -sha256 -extensions v3_ca \
      -out certs/ca.cert.pem

# 验证根 CA 证书
openssl x509 -noout -text -in certs/ca.cert.pem
fi


