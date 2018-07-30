<?php

require 'auth.php';
require 'utils.php';
require 'db.php';

function doFileTypeFilter() {
	$allowed_types = array('doc', 'docx', 'xls','xlsx','ppt','pptx','zip','pdf','jpg','jpeg','bmp','png');
	$filename = $_FILES['cucFiles']['name'];	
	$ext1 = strtolower($ext[1]);
	if(!in_array($ext1, $allowed_types)){
		return $filename;
	}
	else
	{
		return true;
	}
}

function doFileUpload() {
    $p1 = $p2 = [];
    $files = $_FILES['cucFiles'];

    for ($i = 0; $i < count($files['name']); $i++) {
        // 文件上传错误检查和处理
        // ref: http://php.net/manual/zh/features.file-upload.errors.php
        if($files['error'][$i] != UPLOAD_ERR_OK) {
            $ret = [
                'error' => Prompt::$uploadErr[$files['error'][$i]]
            ];
            return $ret;
        }
		$tep = doFileTypeFilter();

		if($tep!=true)
		{
			$tep = $tep."文件格式不符";
			$ret = ['error'=>$tep];
			return $ret;
		}
//		$tep = doFileTypeFilter();
//		$ret = ['error'=>$tep];
//		return $ret;

        // TODO 文件“秒传”功能依赖于客户端先上传sha256校验和，未找到相同散列值再上传文件
        // TODO 文件“秒传”功能对于文件在文件系统上采用加密存储机制来说是“无法完全实现”的
        // TODO 文件“秒传”功能只能针对同一个用户上传重复文件时有效，可以自动根据散列值去重

        $uploadFileName = $files['name'][$i];
        $uploadFilePathParts =  pathinfo($uploadFileName);
        $basename = $uploadFilePathParts['filename'];
        $extname = $uploadFilePathParts['extension'];
        $sha256 = hash_file('sha256', $files['tmp_name'][$i]); // FIXME 上传后保存的加密文件名
        $delApiUrl = 'delete.php'; // FIXME 添加服务端的文件删除API

        try {
            $dup = findDuplicateFileInDb($sha256);
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
        $enc_key = base64_encode(openssl_random_pseudo_bytes(Config::$symmetricEncKeyLen)); // 对称加密秘钥，应妥善保存
        $pub_key = openssl_get_publickey($_SESSION['pubkey']);
        // TODO save $enc_key_in_db to DB
        if(!openssl_public_encrypt($enc_key, $enc_key_in_db, $pub_key)) {
            // 加密失败处理
            $ret = [
                'error' => Prompt::$msg['upload_enc_failed']
            ];
            return $ret;
		} // http://php.net/manual/zh/features.file-upload.post-method.php 
		// 构造文件保存路径
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
        $encryptedFile = encryptFile($files['tmp_name'][$i], $enc_key, $filename);
		//用服务器私钥对数据进行签名
		$myfile = fopen("/CA/apache.key", "r");	
		$ser_pri_key_d = fread($myfile,filesize("/CA/apache.key"));
		fclose($myfile);
		$ser_prikey = openssl_pkey_get_private($ser_pri_key_d);
		openssl_sign($encryptedFile,$encrypedFile_sign,$ser_prikey);
		//------------------------
	
        if($encryptedFile !== false) {
            if(file_put_contents($uploadfile, $encryptedFile) !== false && file_put_contents($uploadfile."_sign",$encrypedFile_sign)!==false) {
                try {
                    if(!uploadFileInDb($uploadFileName, filesize($files['tmp_name'][$i]), base64_encode($enc_key_in_db), $sha256, $uid, $datetime)) {
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

        $p1[$i] = ''; // FIXME 文件下载地址
        $p2[$i] = ['caption' => sprintf('%s.%s', $basename, $extname), 'size' => filesize($files['tmp_name'][$i]), 'width' => '120px', 'key' => $sha256, 'url' => $delApiUrl,];

        $ret = [
            'initialPreview' => $p1, 
            'initialPreviewConfig' => $p2,   
            'append' => true // whether to append these configurations to initialPreview.
            // if set to false it will overwrite initial preview
            // if set to true it will append to initial preview
            // if this propery not set or passed, it will default to true.
        ];
    }
    return $ret;
}


