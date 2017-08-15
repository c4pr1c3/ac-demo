<?php

require 'config.php';
require 'utils.php';

function validateShareLink($id, $sha256, $expire_ts, $count, $token) {
    if(getMasterKey($masterKey)) {
        $data = sprintf("%s-%s-%d-%d", $id, $sha256, $expire_ts, $count);
        $computedToken = hash_hmac(Config::$shareHashHmacAlgo, $data, $masterKey);

        return $token === $computedToken;
    } else {
        return false;
    }
}


