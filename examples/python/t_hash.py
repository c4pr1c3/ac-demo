#!/usr/bin/env python
# -*- coding: utf-8 -*-

import hashlib # https://docs.python.org/3/library/hashlib.html
import hmac 

t_str = "中国传媒大学"
t_bytes = t_str.encode('utf8')

m = hashlib.sha256() # 比 new('sha256') 方式的性能好
m.update(t_bytes)

print('原始格式数据：', end='')
print(m.digest())
print('16 进制表示：' + m.digest().hex())
print('16 进制表示：' + m.hexdigest())

h = hashlib.new('sha256')
h.update(t_bytes)
print(h.hexdigest())

hmac = hmac.new(b'password', t_bytes, 'sha256') # https://docs.python.org/3/library/hmac.html
print(hmac.hexdigest()) # php -r 'echo hash_hmac('sha256', '中国传媒大学', 'password');'

# salt should be about 16 or more bytes from a proper source, e.g. os.urandom(16).
# php -r 'echo hash_pbkdf2("sha256", "password", "salt", 100000);'
dk = hashlib.pbkdf2_hmac('sha256', b'password', b'salt', 100000) 
print(dk.hex())



