#!/usr/bin/env python
# -*- coding: utf-8 -*-

# https://password-hashing.net/

import timeit

# import the hash algorithm
from passlib.hash import pbkdf2_sha256
from passlib.hash import bcrypt
from passlib.hash import argon2

# http://passlib.readthedocs.io/en/stable/lib/passlib.hash.pbkdf2_digest.html
def t_pbkdf2_sha256(plain_password):
    # generate new salt, and hash a password
    return pbkdf2_sha256.hash(plain_password)

def t_pbkdf2_sha256_verify(plain_password, hash):
    # verifying the password
    return pbkdf2_sha256.verify(plain_password, hash)

# http://passlib.readthedocs.io/en/stable/lib/passlib.hash.bcrypt.html
def t_bcrypt(plain_password):
    # Use the CRYPT_BLOWFISH algorithm to create the hash.
    return bcrypt.using(rounds=12).hash(plain_password)

def t_bcrypt_verify(plain_password, h):
    return bcrypt.verify(plain_password, h)

# http://passlib.readthedocs.io/en/stable/lib/passlib.hash.argon2.html
# https://wiki.php.net/rfc/argon2_password_hash added in PHP 7.2 
# https://github.com/riverrun/comeonin/wiki/Choosing-the-password-hashing-algorithm
def t_argon2(plain_password): 
    return argon2.using(rounds=256, memory_cost=1024).hash(plain_password)

def t_argon2_verify(plain_password, h):
    return argon2.verify(plain_password, h)

# https://docs.python.org/3/library/timeit.html
print(timeit.timeit(stmt="print(t_pbkdf2_sha256(plain_password))", setup='from __main__ import t_pbkdf2_sha256; plain_password = "HelloCUCPassword"', number=10))
print(timeit.timeit(stmt="print(t_pbkdf2_sha256_verify(plain_password, hash))", setup='from __main__ import t_pbkdf2_sha256_verify, t_pbkdf2_sha256; plain_password = "HelloCUCPassword"; hash=t_pbkdf2_sha256(plain_password)', number=10))
print(timeit.timeit(stmt="print(t_bcrypt(plain_password))", setup='from __main__ import t_bcrypt; plain_password = "HelloCUCPassword"', number=10))
print(timeit.timeit(stmt="print(t_bcrypt_verify(plain_password, hash))", setup='from __main__ import t_bcrypt, t_bcrypt_verify; plain_password = "HelloCUCPassword"; hash=t_bcrypt(plain_password)', number=10))
print(timeit.timeit(stmt="print(t_argon2(plain_password))", setup='from __main__ import t_argon2; plain_password = "HelloCUCPassword"', number=10))
print(timeit.timeit(stmt="print(t_argon2_verify(plain_password, hash))", setup='from __main__ import t_argon2, t_argon2_verify; plain_password = "HelloCUCPassword"; hash=t_argon2(plain_password)', number=10))

