<?php

class Prompt {
    public static $msg = array(
        'invalid_username' => '用户名必须是可正常接收电子邮件的邮箱地址',
        'invalid_password' => '口令长度不能超过36个字符',
        'register_ok' => '注册成功，请继续',
        'duplicate_username' => '该用户名已被注册',
        'register_failed' => '注册失败，请稍后重试',
        'password_not_same' => '两次输入的口令不一致',
        'login_failed' => '登录失败，用户名和密码不匹配',
        'login_ok' => '登录成功',
        'db_oops' => '数据库服务器未知错误，请稍后重试'
    );
}
