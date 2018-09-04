<?php

require 'db.php';
require 'utils.php';

function download_file($id, $uid, $sess_privkey, $sess_passphrase_key,$sess_passphrasenonce) {

    if(filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1)) === false) {
        $ret = [
            Prompt::$msg['download_failed_in_param'],
            '', '', ''
        ];
        return $ret;
    }

    try {
        list($enc_key, $nonce_in_db,$filename, $filesize, $sha256, $create_time) = getSavedCipherTextFromDb($id, $uid);
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

    //获取公钥加密密钥对
     $ad = 'Additional (public) data';
     $privkey = sodium_crypto_aead_chacha20poly1305_decrypt(
        sodium_hex2bin($sess_privkey),
        $ad,
        sodium_hex2bin($sess_passphrasenonce),
        sodium_hex2bin($sess_passphrase_key)
     );  //可用于加密的公钥对


    if($privkey === false) {
        $ret = [
            Prompt::$msg['decrypt_oops'],
            '', '', ''
        ];
        return $ret;
    }

    $plaintext = sodium_crypto_box_open(
        sodium_hex2bin($enc_key),
        sodium_hex2bin($nonce_in_db),
        $privkey
    );

    if($plaintext===false) {
        $ret = [
            Prompt::$msg['decrypt_oops'],
            '', '', ''
        ];
        return $ret;
    }



    $saved_ciphertext = file_get_contents(getUploadFilePath($uid, $sha256, $create_time));
    $decrypted_content = decryptFile($saved_ciphertext, $plaintext);
    //file_put_contents('debug.log', "wenjianss ".$saved_ciphertext."\n",FILE_APPEND);

    return array('', $filename, $filesize, $decrypted_content);
}




function download_encryptedFile($id, $uid) {


    try {
        list($enc_key, $filename, $filesize, $sha256, $create_time) = getSavedCipherTextFromDb($id, $uid);
    } catch(PDOException $e) {
        $ret = [
            Prompt::$msg['db_oops'],
            '', '', ''
        ];
        return $ret;
    }
    $saved_ciphertext = file_get_contents(getUploadFilePath($uid, $sha256, $create_time));

    return $saved_ciphertext;
}
