<?php

require 'db.php';
require 'utils.php';
require_once 'config.php';

function listFiles($uid, $offset, $limit) {

    $total   = getFileCountInDb($uid);
    $content = listFilesInDb($uid, $offset, $limit);
    $ret = [
        'total' => $total,
        'rows' => $content
    ];

    return $ret;
}


