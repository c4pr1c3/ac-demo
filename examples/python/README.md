
## 选型依据

Python 3.x 官方给出的密码学相关算法实现主要包括[跨平台的 Cryptographic Services](https://docs.python.org/3/library/crypto.html) 和 [Unix 平台上的 crypt —  Function to check Unix passwords](https://docs.python.org/3/library/crypt.html#module-crypt) ，其中 [Cryptographic Services](https://docs.python.org/3/library/crypto.html) 提供的密码学算法只有：

* hashlib — Secure hashes and message digests
* hmac — Keyed-Hashing for Message Authentication
* secrets — Generate secure random numbers for managing secrets

这对于密码学应用实践来说是捉襟见肘的，感谢 Python 社区的繁荣昌盛。例如，知名 Python 开发者 [Kenneth Reitz](https://github.com/kennethreitz) 推荐了 Python 密码学库 [Cryptography](http://docs.python-guide.org/en/latest/scenarios/crypto/) 。在众多密码学算法的 Python 实现中为什么要选择它呢？摘抄一下[Cryptography 官方 FAQ 中的 Why use ``cryptography``](https://cryptography.io/en/latest/faq/#why-use-cryptography)

If you’ve done cryptographic work in Python before you have likely encountered other libraries in Python such as ***M2Crypto***, ***PyCrypto***, or ***PyOpenSSL***. In building cryptography we wanted to address a few issues we observed in the legacy libraries:

* Extremely error prone APIs and insecure defaults.
* Use of poor implementations of algorithms (i.e. ones with known side-channel attacks).
* Lack of maintenance.
* Lack of high level APIs.
* Lack of PyPy and Python 3 support.
* Absence of algorithms such as AES-GCM and HKDF.

除了官方给出的优势和亮点之外，``Cryptography`` 的官方文档质量是非常高的。从整个 API 设计分层来说，**高层 API** 「The Recipes Layer, ``cryptography.fernet``」面向密码学知识欠缺的普通程序员，**底层 API**「The Hazardous Materials, ``cryptography.hazmat``」面向密码学知识丰富的优秀程序员。对于在什么场景下，如何正确选择密码学算法、如何正确选择密码学算法中使用到的参数，提供了详细的文档说明。例如，在[Key derivation functions](https://cryptography.io/en/latest/hazmat/primitives/key-derivation-functions/) 一节中，作者将``秘钥推导函数``的应用场景细分为：``密码学意义上的秘钥扩展（Cryptographic key derivation）``和``口令存储（Password storage）``，并在每一个 API 的说明文档中明确警告哪些不能用于 ``口令存储``。

不过，在研究了一番 [Cryptography](http://docs.python-guide.org/en/latest/scenarios/crypto/) 之后，我发现该库缺少对 [安全口令散列算法的竞赛 PHC 的胜出算法 Argon2](https://password-hashing.net/) 的支持。为了解决这个不大不小的**缺陷**，我找到了[PassLib](http://passlib.readthedocs.io/en/stable/index.html)，该库在实现[TOTP: Time-Based One-Time Password Algorithm](https://tools.ietf.org/html/rfc6238.html) 时使用的是 [Cryptography](https://cryptography.io/en/latest/) 。

经过以上算法库的调研选型，本课程计划在对称密码算法、非对称密码算法的相关工程实现中首选 [Cryptography](https://cryptography.io/en/latest/) ，口令安全存储相关算法实现首选 [PassLib](http://passlib.readthedocs.io/en/stable/index.html)，随机数生成、消息摘要相关算法使用 Python 原生函数。

## 安装 python 相关依赖

* 安装 [pipenv](https://github.com/pypa/pipenv)
* 执行 pipenv 命令完成自动安装依赖 lib

```bash
# 安装相关依赖 lib
pipenv install

# 激活当前目录下的 virtualenv 环境
pipenv shell
```

