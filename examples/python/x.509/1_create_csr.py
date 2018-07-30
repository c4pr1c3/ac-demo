#!/usr/bin/env python
# -*- coding: utf-8 -*-
# https://cryptography.io/en/latest/x509/tutorial/

from cryptography import x509
from cryptography.x509.oid import NameOID
from cryptography.hazmat.backends import default_backend
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives import serialization

passphrase = b"passphrase" # 加密私钥的口令

if len(sys.argv) == 2:
    priv_key_file = sys.argv[1]
else:
    priv_key_file = 'key.pem'  # 私钥文件路径

# 加载上一步创建的私钥
with open(priv_key_file, "rb") as f:
    key = serialization.load_pem_private_key(
        f.read(), 
        password=passphrase, 
        backend=default_backend()
    )

# 创建 CSR: Certificate Signing Request 用于给 CA 签发证书
csr = x509.CertificateSigningRequestBuilder().subject_name(x509.Name([
    # Provide various details about who we are.
    x509.NameAttribute(NameOID.COUNTRY_NAME, u"CN"),
    x509.NameAttribute(NameOID.STATE_OR_PROVINCE_NAME, u"Beijing"),
    x509.NameAttribute(NameOID.LOCALITY_NAME, u"Chaoyang"),
    x509.NameAttribute(NameOID.ORGANIZATION_NAME, u"CUC"),
    x509.NameAttribute(NameOID.COMMON_NAME, u"sec.cuc.edu.cn"),
])).add_extension(
x509.SubjectAlternativeName([
        # 证书可用于的子站点域名列表
        x509.DNSName(u"sec.cuc.edu.cn"),
        x509.DNSName(u"python.sec.cuc.edu.cn"),
        x509.DNSName(u"php.sec.cuc.edu.cn"),
    ]),
    critical=False, # https://cryptography.io/en/latest/x509/reference/#cryptography.x509.CertificateBuilder.add_extension
# 使用上一步创建的「私钥」签名 CSR
    ).sign(key, hashes.SHA256(), default_backend())
# 保存 CSR 到磁盘文件
with open("csr.pem", "wb") as f:
    f.write(csr.public_bytes(serialization.Encoding.PEM))

# 查看并验证 CSR 文件
# openssl req -text -noout -verify -in csr.pem


