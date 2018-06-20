#!/usr/bin/env python
# -*- coding: utf-8 -*-

from cryptography.hazmat.primitives.asymmetric import rsa
from cryptography.hazmat.primitives import serialization
from cryptography.hazmat.backends import default_backend
from cryptography.hazmat.primitives import hashes
from cryptography import x509
from cryptography.x509.oid import NameOID

import datetime

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

# X.509 证书信息填写
# 对自签发证书来说，被签发证书和签发者信息是一模一样的
# 如果是自己模拟 CA 签发证书，可以先模拟自签发证书签发一张根 CA 证书
# 然后创建一个中级 CA 证书并用根 CA 证书签名
# 最后，用中级 CA 证书签发最终的「用户」证书
subject = issuer = x509.Name([
    x509.NameAttribute(NameOID.COUNTRY_NAME, u"CN"),
    x509.NameAttribute(NameOID.STATE_OR_PROVINCE_NAME, u"Beijing"),
    x509.NameAttribute(NameOID.LOCALITY_NAME, u"Chaoyang"),
    x509.NameAttribute(NameOID.ORGANIZATION_NAME, u"CUC"),
    x509.NameAttribute(NameOID.COMMON_NAME, u"sec.cuc.edu.cn"),
])
cert = x509.CertificateBuilder().subject_name(
    subject
).issuer_name(
    issuer
).public_key(
    key.public_key()
).serial_number(
    x509.random_serial_number()
).not_valid_before(
    datetime.datetime.utcnow()
).not_valid_after(
    # 证书有效期硬编码为 10 天
    datetime.datetime.utcnow() + datetime.timedelta(days=10)
).add_extension(
    x509.SubjectAlternativeName([
        # 证书可用于的子站点域名列表
        x509.DNSName(u"sec.cuc.edu.cn"),
        x509.DNSName(u"python.sec.cuc.edu.cn"),
        x509.DNSName(u"php.sec.cuc.edu.cn"),
	]),
    critical=False,
# 使用 0_generate_private_key.py 创建的私钥文件来「签发」证书
).sign(key, hashes.SHA256(), default_backend())
# 保存证书到磁盘文件
with open("self-signed_key.pem", "wb") as f:
    f.write(cert.public_bytes(serialization.Encoding.PEM))

# 查看证书信息
# openssl x509 -in self-signed_key.pem -text -noout

