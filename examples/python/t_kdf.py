#!/usr/bin/env python
# -*- coding: utf-8 -*-
# https://cryptography.io/en/latest/hazmat/primitives/key-derivation-functions/

import os
import binascii
from passlib.crypto.digest import pbkdf1
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC
from cryptography.hazmat.backends import default_backend

passphrase = b'admin123' # 人类可记忆「口令」

# 随机产生盐值，并不需要持久化存储以用于
# 1. 加密算法秘钥的再次延展生成
# 2. 口令散列存储的验证算法
salt = os.urandom(16) 
print(binascii.hexlify(salt))

algo = 'sha256'
rounds = 1000
outlen = 32

# 从人类可记忆「口令」生成面向加密算法用途的「秘钥」
password = pbkdf1(algo, passphrase.decode('latin1'), salt, rounds, keylen=outlen)
print(binascii.hexlify(password))

# ref: https://cryptography.io/en/latest/hazmat/primitives/key-derivation-functions/
backend = default_backend()

# 扩展秘钥
kdf = PBKDF2HMAC(
    algorithm=hashes.SHA256(),
    length=outlen,
    salt=salt,
    iterations=rounds,
    backend=backend
)
password = kdf.derive(passphrase)

print(binascii.hexlify(password))

# verify
kdf = PBKDF2HMAC(
    algorithm=hashes.SHA256(),
    length=outlen,
    salt=salt,
    iterations=rounds,
    backend=backend
)
try:
    kdf.verify(passphrase, password)
    print('Key Verification OK!')
except Exception as e:
    print(e)

