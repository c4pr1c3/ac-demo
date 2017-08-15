<?php

require 'utils.php';

function generateShareLink($id, $sha256, $expire, $count) {
    if(getMasterKey($masterKey)) {
        $expire_ts = $expire * 3600 + time();
        $data = sprintf("%s-%s-%d-%d", $id, $sha256, $expire_ts, $count);
        $token = hash_hmac(Config::$shareHashHmacAlgo, $data, $masterKey);

        return sprintf("?id=%s&key=%s&expire=%d&count=%d&token=%s", $id, $sha256, $expire_ts, $count, $token);
    } else {
        return false;
    }
}


