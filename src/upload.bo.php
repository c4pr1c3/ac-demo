<?php

require 'auth.php';
require 'utils.php';
require 'db.php';

function doFileTypeFilter($file_path) {
    // TODO 文件类型校验

	// extension
	// $file_extension = pathinfo($file_path, PATHINFO_EXTENSION);

    // MIME, magic number
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $type_array = array(
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'zip' => 'application/zip',
        'pdf' => 'application/pdf',
    	'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'bmp' => 'image/bmp',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon'
    );
   
    $type = finfo_file($finfo, $file_path);
    return array_search($type, $type_array);
}

function doFileUpload() {
    $p1 = $p2 = [];
    $files = $_FILES['cucFiles'];
    // $hashList = $_POST['hash'];

    for ($i = 0; $i < count($files['name']); $i++) {
        // if(findFileByHashAndUid($hashList[$i], $_SESSION['uid'])){
        //     $ret = [
        //             'error' => Prompt::$msg['duplicate_file']
        //         ];
        //     return $ret;
        // }

        // 文件上传错误检查和处理
        // ref: http://php.net/manual/zh/features.file-upload.errors.php
        if($files['error'][$i] != UPLOAD_ERR_OK) {
            $ret = [
                'error' => Prompt::$uploadErr[$files['error'][$i]]
            ];
            return $ret;
        }
        if ($files['size'][$i] > 10485760) {
            $ret = [
                'error' => Prompt::$uploadErr[UPLOAD_ERR_INI_SIZE]
            ];
            return $ret;
        }
        if(false == doFileTypeFilter($files['tmp_name'][$i])){
            $ret = [
                'error' => Prompt::$msg['unsuppose_file']
            ];
            return $ret;
        }

        // TODO 文件“秒传”功能依赖于客户端先上传hash校验和，未找到相同散列值再上传文件
        // TODO 文件“秒传”功能对于文件在文件系统上采用加密存储机制来说是“无法完全实现”的
        // TODO 文件“秒传”功能只能针对同一个用户上传重复文件时有效，可以自动根据散列值去重
        $uploadFileName = $files['name'][$i];
        $uploadFilePathParts =  pathinfo($uploadFileName);
        $basename = $uploadFilePathParts['filename'];
        $extname = $uploadFilePathParts['extension'];
        $sodium_hash = sodium_bin2hex(hashFileSodium($files['tmp_name'][$i])); // 上传后保存的加密文件名
        $delApiUrl = 'delete.php'; // FIXME 添加服务端的文件删除API

        try {
            $dup = findDuplicateFileInDb($sodium_hash);
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
        // 文件保存路径： /<非WEB根目录>/用户id/YYYY/MM/DD/hash(原文件名).enc
        // 重名文件自动重命名为：原文件名-<getMax+1>.enc
    	$enc_key = random_bytes(SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES); // 对称加密密钥，应妥善保存 32
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES); //12
        // TODO save $enc_key_in_db to DB 公钥加密对称密钥
        $box = sodium_crypto_box_open(
              $_SESSION['box'],
              sodium_hex2bin($_SESSION['nonce']),
              base64_safe_decode($_SESSION['passphrase'])
          );  
        $enc_key_in_db = sodium_crypto_box(
            $enc_key,
            sodium_hex2bin($_SESSION['nonce']),
            $box
        );
        if(!$enc_key_in_db) {
            // 加密失败处理
            $ret = [
                'error' => Prompt::$msg['upload_enc_failed']
            ];
            return $ret;
        }

        // http://php.net/manual/zh/features.file-upload.post-method.php
        // 构造文件保存路径
        $uid = $_SESSION['uid'];
        $datetime = date('Y-m-d H:i:s');
        $uploadfile = getUploadFilePath($uid, $sodium_hash, $datetime);
        $uploaddir = dirname($uploadfile);
        if(!is_dir($uploaddir)) {
            if(!mkdir($uploaddir, 0755, true)) { //0755读/写/执行-目录、 0644读/写-文件
                // 加密失败处理
                $ret = [
                    'error' => Prompt::$msg['upload_mkdir_failed']
                ];
                return $ret;
            }
        }
        debug_log($uploadfile, __FILE__, __LINE__);

        // 构造文件名
        $filename = base64_safe_encode($files['name'][$i]);
        $encryptedFile = encryptFile($files['tmp_name'][$i], $enc_key, $filename, $nonce);
        if($encryptedFile !== false) {
            // 对加密后的文件进行数字签名
            $sign_secretkey = sodium_crypto_sign_secretkey($_SESSION['sign']);
            $signedFile = sodium_crypto_sign(
                $encryptedFile,
                $sign_secretkey
            );
            if ($signedFile === false) {
                $ret = [
                    'error' => Prompt::$msg['upload_sign_failed']
                ];
                return $ret;
            } 
            if(file_put_contents($uploadfile, $signedFile) !== false) {
                try {
                    if(!chmod($uploadfile, 0644) || !uploadFileInDb($uploadFileName, filesize($files['tmp_name'][$i]), base64_safe_encode($enc_key_in_db), $sodium_hash, sodium_bin2hex($nonce), $uid, $datetime)) {
                        $ret = [
                            'error' => Prompt::$msg['db_oops']
                        ];
                        return $ret;
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
        $p1[$i] = getUriRoot().$uploadfile; // FIXME 文件下载地址
        $p2[$i] = ['caption' => sprintf('%s.%s', $basename, $extname), 'size' => filesize($files['tmp_name'][$i]), 'width' => '120px', 'key' => $sodium_hash, 'url' => $delApiUrl];

        $ret = [
            'initialPreview' => $p1, // initial preview thumbnails for server uploaded files if you want it displayed immediately after upload
            'initialPreviewConfig' => $p2,   
            'append' => true // whether to append these configurations to initialPreview.
            // if set to false it will overwrite initial preview
            // if set to true it will append to initial preview
            // if this propery not set or passed, it will default to true.
        ];
    }

    return $ret;
}


