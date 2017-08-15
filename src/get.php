<?php

require 'get.bo.php';

$fid = $_GET['id'];
$key = $_GET['key'];
$expire = $_GET['expire'];
$count = $_GET['count'];
$nonce = $_GET['nonce'];
$token = $_GET['token'];

$access_code = $_POST['access_code'];

if(validateShareLink($fid, $key, $expire, $count, $token, $nonce)) {
    // 检查是否过期
    $now = time();
    if($now > $expire) {
        $ret = Prompt::$msg['share_file_expired'];
        echo $ret;
        return;
    }

    $fileShareInfo = getFileShareInfo($fid, $nonce);
    if(!empty($fileShareInfo)) {
        if($fileShareInfo['dcount'] > $count) {
            $ret = Prompt::$msg['share_file_exceed_down_limit'];
            echo $ret;
            return;
        }
        if(password_verify($access_code, $fileShareInfo['sharekey'])) {
            // 用户提供的access_code是正确的
            $enc_key = decryptFile($fileShareInfo['enckey'], $access_code);
            $decrypted_content = decryptFile($fileShareInfo['filepath'], $enc_key);

            if($decrypted_content === false) {
                $error = Prompt::$msg['decrypt_oops'];
            }

            if(empty($error)) {
                updateDownloadCount($fid, $nonce);
                $filename = $fileShareInfo['name'];
                $filesize = $fileShareInfo['size'];
                header('Content-Description: Decrypted File Download');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: private');
                header('Content-Length: ' . $filesize); 
                ob_clean();
                flush();

                echo $decrypted_content;
                exit();
            } else {
                $ret = $error;
            }
        }
    }

    // 检查下载次数是否超出分享限制

}

echo $ret;


