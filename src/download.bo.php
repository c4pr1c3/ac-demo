<?php

require 'db.php';
require 'utils.php';

function download_file($id, $uid, $sess_privkey, $sess_passphrase) {

    if(filter_var($id, FILTER_VALIDATE_INT, array('min_range' => 1)) === false) {
        $ret = [
            Prompt::$msg['download_failed_in_param'],
            '', '', ''
        ];
        return $ret;
    }

    try {
        list($enc_key, $filename, $filesize, $sha256, $create_time) = getSavedCipherTextFromDb($id, $uid);
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

    $privkey = openssl_pkey_get_private($sess_privkey, $sess_passphrase);

    if($privkey === false) {
        $ret = [
            Prompt::$msg['decrypt_oops'],
            '', '', ''
        ];
        return $ret;
    }

    if(!openssl_private_decrypt(base64_decode($enc_key), $n_enc_key, $privkey)) {
        $ret = [
            Prompt::$msg['decrypt_oops'],
            '', '', ''
        ];
        return $ret;
    }

    $saved_ciphertext = file_get_contents(getUploadFilePath($uid, $sha256, $create_time));

	$decrypted_content = decryptFile($saved_ciphertext, $n_enc_key);



    return array('', $filename, $filesize, $decrypted_content);
}

