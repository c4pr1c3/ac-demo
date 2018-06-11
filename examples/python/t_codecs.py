#!/usr/bin/env python
# -*- coding: utf-8 -*-

# https://docs.python.org/3/library/codecs.html
import codecs
import base64

print(codecs.encode('PHP', 'rot_13'))
print(codecs.encode(b'PHP', 'base64'))
print(codecs.encode('中国传媒大学'.encode('utf8'), 'base64'))
print(codecs.encode('中国传媒大学信息安全'.encode('utf8'), 'base64'))

print(codecs.decode('PHP', 'rot_13'))
print(codecs.decode(b'UEhQ', 'base64'))
print(codecs.decode(b'5Lit5Zu95Lyg5aqS5aSn5a2m', 'base64').decode('utf8'))

print(base64.b64encode(b'PHP'))
print(base64.b64decode('UEhQ'))
print(base64.urlsafe_b64encode('中国传媒大学信息安全'.encode('utf8'))) # 标准的Base64编码后可能出现字符+和/，在URL中就不能直接作为参数，所以又有一种"url safe"的base64编码，其实就是把字符+和/分别变成-和_



