#!/usr/bin/env python
# -*- coding: utf-8 -*-
# https://cryptography.io/en/latest/fernet/
# https://www.python.org/dev/peps/pep-0272/ API for Block Encryption Algorithms v1.0

from cryptography.fernet import Fernet

import base64
import binascii
import datetime

# https://cryptography.io/en/latest/fernet/#implementation

plaintext = b'HelloCUCPlaintextIsVeryGoooooooooood'
key = Fernet.generate_key() # 需要安全保存该「秘钥」
print(binascii.hexlify(key))
f = Fernet(key) # Enc & Dec function Object
token = f.encrypt(plaintext) # PEP-0272 compatible
print(token)

# 注意密文结构设计和密文合法性校验算法
# TODO 自己用 cryptography.hazmat.* 里的算法实现 cryptography.fernet.Fernet 类里的 encrypt() 和 decrypt()
# TODO 用 cryptography.hazmat.* 里的算法实现对「大文件」的加密和解密 API
# https://github.com/fernet/spec/blob/master/Spec.md#token-format
token_hex = binascii.hexlify(base64.urlsafe_b64decode(token)).decode('latin1')
print(token_hex)

print(token_hex[0:2]) # Version

timestampe_hex = token_hex[2:2+8*2] # Timestamp in hex format
timestampe_int = int.from_bytes(binascii.unhexlify(timestampe_hex), byteorder='big', signed=False) # Timestamp in int format
print(datetime.datetime.fromtimestamp(timestampe_int).strftime('%Y-%m-%d %H:%M:%S'))

iv = token_hex[(2+8*2):(2+8*2+16*2)] # IV in hex
print(iv)

ciphertext = token_hex[(2+8*2+16*2):-32*2]
print(ciphertext)

hmac = token_hex[-32*2:]
print(hmac)

print(f.decrypt(token)) # PEP-0272 compatible
# print(f.extract_timestamp(token)) # new in v2.3

