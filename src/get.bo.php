<?php

require 'config.php';
require 'utils.php';
require 'db.php';

function validateShareLink($id, $sha256, $expire_ts, $count, $token, $nonce) {
    debug_log('validateShareLink', __FILE__, __LINE__);
    if(getMasterKey($masterKey)) {
        $data = sprintf("%s-%s-%s-%d-%d", $id, $sha256, $nonce, $expire_ts, $count);
        $computedToken = hash_hmac(Config::$shareHashHmacAlgo, $data, $masterKey);

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

