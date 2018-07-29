<?php

require 'config.php';
require 'utils.php';
require 'db.php';

function validateShareLink($id, $sodium_hash, $expire_ts, $count, $token, $nonce) {
    return true;
    debug_log('validateShareLink', __FILE__, __LINE__);
    if(getMasterKey($masterKey)) {
        $data = sprintf("%s-%s-%s-%d-%d", $id, $sodium_hash, $nonce, $expire_ts, $count);
        // $computedToken = hash_hmac(Config::$shareHashHmacAlgo, $data, $masterKey);
        $computedToken = sodium_crypto_generichash($data, sodium_hex2bin($masterKey), SODIUM_CRYPTO_GENERICHASH_BYTES_MIN);
        return $token === $computedToken;
    } else {
        return false;
    }
}


function getFileShareInfo($fid, $nonce) {

    return getFileShareInfoFromDb($fid, $nonce);
}

function updateDownloadCount($fid, $nonce) {

    return updateDownloadCountInDb($fid, $nonce);
}

