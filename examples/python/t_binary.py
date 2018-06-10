#!/usr/bin/env python
# -*- coding: utf-8 -*-

# https://docs.python.org/3/library/binascii.html
import binascii

# 创建空字节对象
empty_bytes = bytes(4)
print(type(empty_bytes))
print(empty_bytes)

# 字节数组
## 可变字节数组
mutable_bytes = bytearray(b'\x00\x0F')
mutable_bytes[0] = 255
mutable_bytes.append(255)
print(mutable_bytes)

## 不可变字节
immutable_bytes = bytes(mutable_bytes)
print(immutable_bytes)

## 写文件
with open("test_file.dat", "wb") as binary_file:
    binary_file.write(immutable_bytes)

## 按字节（二进制模式）从文件中读取
with open("test_file.dat", "rb") as binary_file:
    # 一次将文件内容读取进内存
    data = binary_file.read()
    print(data)

    # 按需读取
    binary_file.seek(0)  # 移动文件句柄指针到文件起始处
    couple_bytes = binary_file.read(2)
    print(couple_bytes)

# 文本编码
# binary_data = b'中国传媒大学' # python bytes 只能包含 ASCII 字符
## 对「字节对象」使用正确「编码方案」进行「解码」
binary_data = b'Hello CUC'
print(binary_data.decode('utf-8'))
print(binary_data.decode('iso8859-1')) # ref: https://docs.python.org/3/library/codecs.html

## 对 Unicode 字符串选择「编码方案」编码为字节对象
text = '中国传媒大学'
print(text.encode('utf-8'))
print(text.encode('gbk'))

## 对「字节对象」使用正确「编码方案」进行「解码」
utf8_data = b'\xe4\xb8\xad\xe5\x9b\xbd\xe4\xbc\xa0\xe5\xaa\x92\xe5\xa4\xa7\xe5\xad\xa6'
print(utf8_data.decode('utf-8'))
# print(utf8_data.decode('gbk')) # UnicodeDecodeError: 'gbk' codec can't decode byte 0xad in position 2: illegal multibyte sequence
print(utf8_data.decode('iso8859-1'))

gbk_data = b'\xd6\xd0\xb9\xfa\xb4\xab\xc3\xbd\xb4\xf3\xd1\xa7'
# print(gbk_data.decode('utf-8')) # UnicodeDecodeError: 'utf-8' codec can't decode byte 0xd6 in position 0: invalid continuation byte
print(gbk_data.decode('gbk'))
print(gbk_data.decode('iso8859-1'))

binary_data = bytes([65, 66, 67])  # ASCII values for A, B, C
text = binary_data.decode('utf-8')
print(text)

## 16 进制转换
cuc_hex = 'e4b8ade59bbde4bca0e5aa92e5a4a7e5ada6'
print(binascii.unhexlify(cuc_hex).decode('utf8'))
print(binascii.hexlify('中国传媒大学'.encode('utf8')).decode('utf8'))

## 单字符格式化
a_byte = b'\xff'  # 255
i = ord(a_byte)   # 16 进制转 2 进制

bin = "{0:b}".format(i) # binary: 11111111
hex = "{0:x}".format(i) # hexadecimal: ff
oct = "{0:o}".format(i) # octal: 377

print(bin)
print(hex)
print(oct)

# 按（比特）位操作
byte1 = int('11110000', 2)  # 240
byte2 = int('00001111', 2)  # 15
byte3 = int('01010101', 2)  # 85

## 按位「补」操作
print(~byte1)

## AND
print(byte1 & byte2)

## OR
print(byte1 | byte2)

## XOR
print(byte1 ^ byte3)

## 右移位
print(byte2 >> 3)

## 左移位
print(byte2 << 1)

## 检查指定比特位是否「置位」（ == 1 ）：指定位「与」操作
bit_mask = int('00000001', 2)  # Bit 1
print(bit_mask & byte1)  # 0
print(bit_mask & byte2)  # 1


