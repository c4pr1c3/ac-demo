#!/usr/bin/env bash

# ref: https://jamielinux.com/docs/openssl-certificate-authority/create-the-intermediate-pair.html

if [[ ! -d "intermediate/private" ]];then
  cd intermediate
  mkdir certs crl csr newcerts private
  chmod 700 private
  touch index.txt
  echo 1000 > serial
  echo 1000 > ../crlnumber
  cd ..
fi

cat <<EOF
-------------------- Notes Below --------------------
Enter pass phrase for intermediate.key.pem: secretpassword
Verifying - Enter pass phrase for intermediate.key.pem: secretpassword
-------------------- Notes Above --------------------
Enter pass phrase for intermediate.key.pem: secretpassword
EOF

if [[ ! -f "intermediate/private/intermediate.key.pem" ]];then

openssl genrsa -aes256 \
      -out intermediate/private/intermediate.key.pem 4096

chmod 400 intermediate/private/intermediate.key.pem

if [[ ! -f "intermediate/openssl.cnf" ]];then
  cat "intermediate/openssl.cnf.example" | sed "s#<CA_default_dir>#$PWD/intermediate#g"  > "intermediate/openssl.cnf"
fi

openssl req -config intermediate/openssl.cnf -new -sha256 \
      -key intermediate/private/intermediate.key.pem \
      -out intermediate/csr/intermediate.csr.pem

openssl ca -config openssl.cnf -extensions v3_intermediate_ca \
      -days 3650 -notext -md sha256 \
      -in intermediate/csr/intermediate.csr.pem \
      -out intermediate/certs/intermediate.cert.pem

chmod 444 intermediate/certs/intermediate.cert.pem

openssl x509 -noout -text \
      -in intermediate/certs/intermediate.cert.pem

openssl verify -CAfile certs/ca.cert.pem \
      intermediate/certs/intermediate.cert.pem

cat intermediate/certs/intermediate.cert.pem \
      certs/ca.cert.pem > intermediate/certs/ca-chain.cert.pem

chmod 444 intermediate/certs/ca-chain.cert.pem
fi


