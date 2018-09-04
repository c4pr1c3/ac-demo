<?php

function generateShareLink($id, $sha256, $expire, $count, $nonce,$sign_pub_key) {
    if(getMasterKey($masterKey)) {
        $expire_ts = $expire * 3600 + time();
        $data = sprintf("%s-%s-%s-%d-%d", $id, $sha256, $nonce, $expire_ts, $count);
        $token = hash_hmac(Config::$shareHashHmacAlgo, $data, $masterKey);

        return sprintf("?id=%s&key=%s&expire=%d&count=%d&nonce=%s&token=%s&pubkey=%s", $id, $sha256, $expire_ts, $count, $nonce, $token,$sign_pub_key);
    } else {
        return false;
    }
}

function validateUserFileOwnership($uid, $fid, $sha256) {
    $ret = validateUserFileOwnershipInDb($uid, $fid, $sha256);
    return ($ret == 1);
}

function saveShareFileInfo($fid, $shareKeyHash, $enc_key_in_db, $shareFilePath, $nonce,$_sign) {
    $ret = saveShareFileInfoInDb($fid, $shareKeyHash, $enc_key_in_db, $shareFilePath, $nonce,$_sign);
    return $ret;
}



