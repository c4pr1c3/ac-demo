#!/usr/bin/env python
# -*- coding: utf-8 -*-
# https://cryptography.io/en/latest/x509/tutorial/

from cryptography.hazmat.backends import default_backend
from cryptography.hazmat.primitives import serialization
from cryptography.hazmat.primitives.asymmetric import rsa
import sys

# 加密私钥的口令
passphrase = b"passphrase"

# 私钥文件名
if len(sys.argv) == 2:
    outfile = sys.argv[1]
else:
    outfile = "key.pem"

# Generate our key
key = rsa.generate_private_key(
    public_exponent=65537,
    key_size=2048,
    backend=default_backend()
)

# 保存证书私钥
with open(outfile, "wb") as f:
    f.write(key.private_bytes(
        encoding=serialization.Encoding.PEM,
        format=serialization.PrivateFormat.TraditionalOpenSSL,
        encryption_algorithm=serialization.BestAvailableEncryption(passphrase),
    ))

# 验证私钥格式正确与否
# openssl rsa -in key.pem -check

