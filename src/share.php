<?php

require 'auth.php';
require 'download.bo.php';
require 'share.bo.php';

$expire_hours = empty($_POST['expire_hours']) ? Config::$dftDldExpHours : (int)$_POST['expire_hours'];
$allowed_download_count = empty($_POST['allowed_download_count']) ? Config::$dftAllowedDldCount : (int)$_POST['allowed_download_count'];
$fid = empty($_POST['fid']) ? '' : (int)$_POST['fid'];
$sha256 = empty($_POST['fkey']) ? '' : $_POST['fkey'];

// TODO param validation

$uid = $_SESSION['uid'];

// validate authorization
if(validateUserFileOwnership($uid, $fid, $sha256)) {


    list($error, $filename, $filesize, $decrypted_content) = download_file($fid, $uid,$_SESSION['encrypt_pair'], $_SESSION['passphrase_key'],$_SESSION['passphrase_nonce']);

  //  list($error, $filename, $filesize, $decrypted_content) = download_file($id, $uid,$_SESSION['encrypt_pair'], $_SESSION['passphrase_key'],$_SESSION['passphrase_nonce']);
    //file_put_contents('debug.log', "刚下载的新鲜的文本 ".$decrypted_content."\n",FILE_APPEND);

    $shareKey= random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES);
    $enc_key = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES);
    $nonce = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES);

    //file_put_contents('debug.log', "enckey ".sodium_bin2hex($enc_key)."\n",FILE_APPEND);
    //file_put_contents('debug.log', "sharekey ".sodium_bin2hex($shareKey)."\n",FILE_APPEND);

    $shareKeysh = sodium_crypto_pwhash_str(
        sodium_bin2hex($shareKey),
        SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
        SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
    );


    $enc_key_in_db = encryptFile(sodium_bin2hex($enc_key), $shareKey,'enckey');  //返回加密后的文件
    $encryptedFile = encryptFile($decrypted_content, $enc_key, $filename);
    //file_put_contents('debug.log', "wenjian ".sodium_bin2hex($decrypted_content)."\n",FILE_APPEND);


    $shareFilePath = getShareFilePath($uid, $sha256);
    $shareFileRoot = dirname($shareFilePath);




       $ad = 'Additional (public) data';
       $sign_seckey = sodium_crypto_aead_chacha20poly1305_decrypt(
            sodium_hex2bin($_SESSION['sign_secrkey']),
            $ad,
            sodium_hex2bin($_SESSION['passphrase_nonce'] ),
            sodium_hex2bin($_SESSION['passphrase_key'])
        );


        // 获取用户签名的私钥  公钥  公钥直接随链接分享  私钥直接对密文进行加密
       // $pri_key = openssl_pkey_get_private($_SESSION['privkey'], $_SESSION['passphrase']);

        $sign_pub_key = $_SESSION['sign_pubkey'];
        $_sign = sodium_crypto_sign_detached(
            $encryptedFile,
            $sign_seckey
        );


        if (!is_dir($shareFileRoot)) {
            mkdir($shareFileRoot, 0755, true);
        }



        if (file_put_contents($shareFilePath, $encryptedFile) !== false) { // 分享的文件单独加密存储在区别于用户上传目录的另一个目录
            $ret = saveShareFileInfo($fid, sodium_bin2hex($shareKeysh), $enc_key_in_db, $shareFilePath, sodium_bin2hex($nonce), sodium_bin2hex($_sign)); // 文件分享信息保存到数据库
            debug_log($ret, __FILE__, __LINE__);
            if (empty($err)) {

                $params = generateShareLink($fid, $sha256, $expire_hours, $allowed_download_count, sodium_bin2hex($nonce),$sign_pub_key);

               // file_put_contents('debug.log', "前面已经计算成功  进入生成链接阶段 ".$params."\n",FILE_APPEND);

                $ret = array('error' => '', 'url' => getUriRoot().'/get.php'.$params, 'access_code' => sodium_bin2hex($shareKey));
            } else {
                $ret = array('error' => $err);
            }
        } else {
            $ret = array('error' => Prompt::$msg['share_file_failed_in_create_file']);
        }


}
echo json_encode($ret);

