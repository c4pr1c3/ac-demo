<?php

function generateShareLink($id, $sodium_hash, $expire, $count, $nonce) {
    if(getMasterKey($masterKey)) {
        $expire_ts = $expire * 3600 + time();
        $data = sprintf("%s-%s-%s-%d-%d", $id, $sodium_hash, $nonce, $expire_ts, $count);
        // $token = hash_hmac(Config::$shareHashHmacAlgo, $data, $masterKey);
        $token = sodium_crypto_generichash($data, sodium_hex2bin($masterKey), SODIUM_CRYPTO_GENERICHASH_BYTES_MIN);
        $addr = sprintf("?id=%s&key=%s&expire=%d&count=%d&nonce=%s&token=%s", $id, $sodium_hash, $expire_ts, $count, $nonce, sodium_bin2hex($token));
        return $addr;
    } else {
        return false;
    }
}

function validateUserFileOwnership($uid, $fid, $sodium_hash) {
    $ret = validateUserFileOwnershipInDb($uid, $fid, $sodium_hash);
    return ($ret == 1);
}

function saveShareFileInfo($fid, $shareKeyHash, $enc_key_in_db, $shareFilePath, $nonce) {
    $ret = saveShareFileInfoInDb($fid, $shareKeyHash, $enc_key_in_db, $shareFilePath, $nonce);
    return $ret;
}