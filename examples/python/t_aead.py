#!/usr/bin/env python
# -*- coding: utf-8 -*-

from cryptography.hazmat.primitives.ciphers.aead import AESGCM

import os
import binascii

plaintext = b'HelloCUCPlaintextIsVeryGoooooooooood'
aad = b"authenticated but unencrypted data"
print('AAD - ' + str(aad))
key = AESGCM.generate_key(bit_length=256)
print('KEY - ' + str(binascii.hexlify(key)))
aesgcm = AESGCM(key)
nonce = os.urandom(12)
print('NONCE - ' + str(binascii.hexlify(nonce)))
ct = aesgcm.encrypt(nonce, plaintext, aad)
print(ct)
print(binascii.hexlify(ct))
decrypted = aesgcm.decrypt(nonce, ct, aad)
print(decrypted)


