<?php

class Prompt {
    public static $msg = array(
        'invalid_username' => '用户名只能使用字母，数字，中文及字符（.@-_），长度至少为5位',
        'invalid_email' => '注册邮箱必须是可正常接收电子邮件的邮箱地址',
        'invalid_password' => '口令长度不能超过36个字符',
        'register_ok' => '注册成功，请继续',
        'duplicate_username' => '该用户名已被注册',
        'register_failed' => '注册失败，请稍后重试',
        'password_not_same' => '两次输入的口令不一致',
        'login_failed' => '登录失败，用户名和密码不匹配',
        'login_ok' => '登录成功',
        'db_oops' => '数据库服务器未知错误，请稍后重试',
        'upload_enc_failed' => '文件上传加密失败',
        'upload_mkdir_failed' => '文件上传目录创建失败',
        'upload_sign_failed' => '文件签名失败',
        'decrypt_oops' => '文件解密失败',
        'download_failed_in_db' => '文件不存在或未获得访问授权',
        'download_failed_in_param' => '文件不存在',
        'duplicate_file' => '云端已存在相同文件，请不要重复上传',
        'unsuppose_file' => '文件类型不支持上传',
        'delete_file_not_found' => '未找到要删除的文件，删除文件失败',
        'delete_file_err' => '删除文件失败',
        'file_ownership_mismatch' => '无权分享当前文件',
        'share_file_failed_in_create_file' => '分享文件时创建分享文件写入失败',
        'share_file_expired' => '已过期的分享文件，无法访问',
        'share_file_exceed_down_limit' => '超过允许的下载次数，无法访问',
        'share_file_invalid_access_code' => '验证码错误',
        'share_file_not_found' => '错误的文件分享链接',
        'unregister_username'=>'未被注册的邮箱',
        'check_email'=>'#验证邮件已发送',
        'symmetry_oops'=>'文件加密密钥获取失败',
        'key_oops'=>'密钥获取失败'
    );
    public static $uploadErr = array(
        UPLOAD_ERR_INI_SIZE => '上传的文件超过了10MB',
        UPLOAD_ERR_FORM_SIZE => '上传文件的大小超过了 HTML 表单中 MAX_FILE_SIZE 选项指定的值',
        UPLOAD_ERR_PARTIAL => '文件只有部分被上传',
        UPLOAD_ERR_NO_FILE => '没有文件被上传',
        UPLOAD_ERR_NO_TMP_DIR => '找不到临时文件夹来保存上传的文件',
        UPLOAD_ERR_CANT_WRITE => '文件写入失败'
    );
}
