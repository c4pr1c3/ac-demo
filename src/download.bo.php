<?php

require 'db.php';
require 'utils.php';

function download_file($id, $uid, $sign, $box, $sess_key, $sess_nonce) {

    if(filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1)) === false) {
        $ret = [
            Prompt::$msg['download_failed_in_param'],
            '', '', ''
        ];
        return $ret;
    }

    try {
        list($enc_key, $filename, $filesize, $sodium_hash, $nonce, $create_time) = getSavedCipherTextFromDb($id, $uid);
    } catch(PDOException $e) {
        $ret = [
            Prompt::$msg['db_oops'],
            '', '', ''
        ];
        return $ret;
    } 

    if(empty($enc_key)) {
        $ret = [
            Prompt::$msg['download_failed_in_db'],
            '', '', ''
        ];
        return $ret;
    }

    //获取私钥
    $privkey = sodium_crypto_box_open(
            $box,
            sodium_hex2bin($sess_nonce),
            base64_safe_decode($sess_key)
        );
    if($privkey === false) {
        $ret = [
            Prompt::$msg['key_oops'],
            '', '', ''
        ];
        return $ret;
    }

    // 获取对称密钥
    $n_enc_key = sodium_crypto_box_open(
        base64_safe_decode($enc_key),
        sodium_hex2bin($sess_nonce),
        $privkey
    );
    if($n_enc_key === false) {
        $ret = [
            Prompt::$msg['symmetry_oops'],
            '', '', ''
        ];
        return $ret;
    }

    // 获取公钥，验证签名
    $saved_ciphertext = getUploadFilePath($uid, $sodium_hash, $create_time);
    $encryptedFile = file_get_contents($saved_ciphertext);
    if($encryptedFile){
        $decrypted_content = decryptFile(base64_safe_encode($filename), sodium_hex2bin($nonce), $encryptedFile, $n_enc_key);
    }

    return array('', $filename, $filesize, $decrypted_content);
}

