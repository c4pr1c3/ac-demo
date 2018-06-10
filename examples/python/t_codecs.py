#!/usr/bin/env python
# -*- coding: utf-8 -*-

# https://docs.python.org/3/library/codecs.html
import codecs

print(codecs.encode('PHP', 'rot_13'))
print(codecs.encode(b'PHP', 'base64'))
print(codecs.encode('中国传媒大学'.encode('utf8'), 'base64'))

print(codecs.decode('PHP', 'rot_13'))
print(codecs.decode(b'UEhQ', 'base64'))
print(codecs.decode(b'5Lit5Zu95Lyg5aqS5aSn5a2m', 'base64').decode('utf8'))



