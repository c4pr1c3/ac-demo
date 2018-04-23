本项目是中国传媒大学密码学应用实践课程的一个样例工程，主要完成了以下功能：

* 基于网页的用户注册与登录系统
  * 允许用户注册到系统
    * 用户名强制要求为电子邮件地址
    * 用户口令长度限制在36个字符之内
    * 对用户输入的口令进行强度校验，禁止使用弱口令
  * 使用合法用户名和口令登录系统
  * 禁止使用明文存储用户口令 
    * 存储的口令即使被公开，也无法还原/解码出原始明文口令
    * 扫码登录
* 基于网页的文件上传加密与数字签名系统
  * 已完成《基于网页的用户注册与登录系统》所有要求
  * 限制文件大小：&lt; 10MB（可通过代码配置）
  * 限制文件类型：office文档
  * 匿名用户禁止上传文件
  * 对文件进行对称加密存储到文件系统，禁止明文存储文件 
  * 文件秒传：服务器上已有的文件，客户端禁止重复上传
  * 支持多文件同时上传
  * 提供文件上传预览
  * 用户可以浏览自己上传的所有文件
  * 用户可以删除自己上传的文件
* 基于网页的加密文件下载与解密
  * 已完成《基于网页的文件上传加密与数字签名系统》所有要求 
  * 客户端输入文件分享码获取解密后的文件
  * 提供已登录用户下载自己上传的解密后文件
  * 下载URL设置有效期（限制时间和限制下载次数），过期后禁止访问 

样例工程完成的功能和实际小学期要求完成的大作业功能有一些差异，具体请查看[课程教学Wiki](http://sec.cuc.edu.cn/huangwei/wiki/teaching_ac.html)。

同学们可以参考这个样例工程中的代码，自行修改或采用其中的代码片段以完成尽可能多的作业要求。

本样例工程的主要技术栈如下：

* 前端使用的 js 框架依赖已在 src/package.json 中定义，可以通过在 ``src/`` 目录下执行 ``npm install`` 直接安装所有前端依赖文件；
* 后端使用原生 PHP 编写，没有使用任何框架；
* 程序部署运行环境采用 docker，如果本机已配置好 docker 运行时环境可以通过执行 ``bash build.sh`` 自动完成程序的发布和部署；

本样例工程通过 ``bash build.sh`` 方式部署后，打开浏览器访问： [http://localhost:8080](http://localhost:8080) 即可快速体验系统所有功能。

## 依赖环境安装补充说明

* ``build.sh`` 的执行需要 ``root`` 权限，普通用户可以 ``sudo bash build.sh``
* 国内特殊网络环境条件下，安装 ``docker`` 和从 ``Docker Hub`` 拉取基础镜像可能会无法正常完成，建议使用可靠镜像源和缓存。推荐：
    * [安装 docker 主程序指南](http://mirrors.ustc.edu.cn/help/docker-ce.html)
    * [加速访问 Docker Hub 指南](http://mirrors.ustc.edu.cn/help/dockerhub.html)
* 以 ``Ubuntu 16.04`` 为例，安装前端依赖文件所需要的操作步骤如下：

```bash
# 以下命令请在检出代码仓库后的本地仓库根目录下执行
# 在等待安装 docker 和等待执行 bash build.sh 的过程中可以同时执行以下命令
sudo apt update && sudo apt install -y npm && cd src/ && npm install
```

## 附录：项目测试验证环境信息

```
Client:
 Version:	17.12.0-ce
 API version:	1.35
 Go version:	go1.9.2
 Git commit:	c97c6d6
 Built:	Wed Dec 27 20:03:51 2017
 OS/Arch:	darwin/amd64

Server:
 Engine:
  Version:	17.12.0-ce
  API version:	1.35 (minimum version 1.12)
  Go version:	go1.9.2
  Git commit:	c97c6d6
  Built:	Wed Dec 27 20:12:29 2017
  OS/Arch:	linux/amd64
  Experimental:	false
```

