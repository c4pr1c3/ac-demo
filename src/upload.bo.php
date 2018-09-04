<?php

require 'auth.php';
require 'utils.php';
require 'db.php';

function doFileTypeFilter($filename) {
    // TODO 文件类型校验
    $allowed=array(
        'doc' => 'application/msword',
        'docx' => 'application/msword',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.ms-excel',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.ms-powerpoint',
//        'zip' => 'application/zip',
//        'pdf' => 'application/pdf',
        'gif' => 'image/gif',
        'jpg' => 'image/jpeg',
        'bmp' => 'image/png',
        'png' => 'image/png',
    );

    $finfo=finfo_open(FILEINFO_MIME_TYPE);
    $type=finfo_file($finfo,$filename);
    finfo_close($finfo);

    if(false=== array_search($type,$allowed)){//可能返回等同FALSE的非布尔值
        return false;
    }else{
        return true;
    }

}

function doFileUpload() {
    $p1 = $p2 = [];
    $files = $_FILES['cucFiles'];


    for ($i = 0; $i < count($files['name']); $i++) {
        if($files['error'][$i] != UPLOAD_ERR_OK) {
            $ret = [
                'error' => Prompt::$uploadErr[$files['error'][$i]]
            ];
            return $ret;
        }

        //文件类型错误
        if(!doFileTypeFilter($files['tmp_name'][$i])){
            $ret=[
                'error' => Prompt::$msg['type_not_allowed']
            ];
            return $ret;
        }


        // TODO 文件“秒传”功能依赖于客户端先上传sha256校验和，未找到相同散列值再上传文件
        // TODO 文件“秒传”功能对于文件在文件系统上采用加密存储机制来说是“无法完全实现”的
        // TODO 文件“秒传”功能只能针对同一个用户上传重复文件时有效，可以自动根据散列值去重


        $uploadFileName = $files['name'][$i];
        $uploadFilePathParts =  pathinfo($uploadFileName);
        $basename = $uploadFilePathParts['filename'];
        $extname = $uploadFilePathParts['extension'];
        $sha256 = hash_file('sha256', $files['tmp_name'][$i]); //   对文件进行哈西以后再存储
        $delApiUrl = 'delete.php'; // FIXME 添加服务端的文件删除API

        try {                                                       //  判断文件是否已经上传
            $dup = findDuplicateFileInDb($sha256,$_SESSION['uid']);     // 查看该用户是否已经 上传该文件
            debug_log($dup, __FILE__, __LINE__);
            if($dup > 0) {
                $ret = [
                    'error' => Prompt::$msg['duplicate_file']
                ];
                return $ret;
            }
        } catch(PDOException $e) {
            $ret = [
                'error' => Prompt::$msg['db_oops']
            ];
            return $ret;
        }


        // TODO 文件加密
        // 密文结构： $加密算法$enc_options$IV$原始文件名$密文
        // 文件保存路径： /<非WEB根目录>/md5(用户名)/YYYY/MM/DD/md5(原文件名).enc
        // 重名文件自动重命名为：原文件名-<getMax+1>.enc

        $ad = 'Additional (public) data';
        $crypt_keypair = sodium_crypto_aead_chacha20poly1305_decrypt(
            sodium_hex2bin($_SESSION['encrypt_pair']),
            $ad,
            sodium_hex2bin($_SESSION['passphrase_nonce']),
            sodium_hex2bin($_SESSION['passphrase_key'])
        );  //可用于加密的公钥对
        $sign_seckey = sodium_crypto_aead_chacha20poly1305_decrypt(
            sodium_hex2bin($_SESSION['sign_secrkey']),
            $ad,
            sodium_hex2bin($_SESSION['passphrase_nonce'] ),
            sodium_hex2bin($_SESSION['passphrase_key'])
        );


        $enc_key  = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES);  //生成对称加密的key
        $nonce_enc= random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);  //存储该对称密钥时 将对应的nonce  一并存储
        $enc_in_db = sodium_bin2hex(sodium_crypto_box($enc_key, $nonce_enc, $crypt_keypair));
        $nonce_in_db = sodium_bin2hex($nonce_enc);



        $uid = $_SESSION['uid'];
        $datetime = date('Y-m-d H:i:s');
        $uploadfile = getUploadFilePath($uid, $sha256, $datetime);
        $uploaddir = dirname($uploadfile);
        if(!is_dir($uploaddir)) {
            if(!mkdir($uploaddir, 0755, true)) {
                // 加密失败处理
                $ret = [
                    'error' => Prompt::$msg['upload_mkdir_failed']
                ];
                return $ret;
            }
        }
        debug_log($uploadfile, __FILE__, __LINE__);



        // 清理文件名中可能会包含的$
        $filename = base64_encode($files['name'][$i]);
        $encryptedFile = encryptFile($files['tmp_name'][$i], $enc_key, $filename);  //返回加密后的文件


        if($encryptedFile !== false) {
            if(file_put_contents($uploadfile, $encryptedFile) !== false) { //上传根路径 / 用户id / 年月日  /sha256签名
                                                                            //对称密钥加密后的明文
                try {
                    //首先进行文件签名  然后上传哈西值
                   // openssl_sign($encryptedFile,$signature_up,$pri_key,7) ;  //使用 sha256 进行签名
                    $signature = sodium_crypto_sign_detached(
                        $encryptedFile,
                        $sign_seckey
                    );

                    file_put_contents('debug.log', "输入加密后的签名文件".sodium_bin2hex($signature)."\n", FILE_APPEND);
                    if($signature !==false){
                        if(!uploadFileInDb($uploadFileName, filesize($files['tmp_name'][$i]), $enc_in_db,$nonce_in_db, $sha256, $uid, $datetime,sodium_bin2hex($signature))) {
                            $ret = ['error' => Prompt::$msg['db_oops']];
                            return $ret;
                        }
                    }

                } catch(PDOException $e) {
                    $ret = [
                        'error' => Prompt::$msg['db_oops']
                    ];
                    return $ret;
                }
            } else {
                // 加密失败处理
                $ret = [
                    'error' => Prompt::$msg['upload_enc_failed']
                ];
                return $ret;
            }
        } else {
            // 加密失败处理
            $ret = [
                'error' => Prompt::$msg['upload_enc_failed']
            ];
            return $ret;
        }

        $p1[$i] = ''; // FIXME 文件下载地址
        $p2[$i] = ['caption' => sprintf('%s.%s', $basename, $extname), 'size' => filesize($files['tmp_name'][$i]), 'width' => '120px', 'key' => $sha256, 'url' => $delApiUrl,];

        $ret = [
            'initialPreview' => $p1, 
            'initialPreviewConfig' => $p2,   
            'append' => true
        ];
    }

    return $ret;
}


