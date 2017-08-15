<?php

require 'auth.php';
require 'share.bo.php';

$expire_hours = empty($_POST['expire_hours']) ? Config::$dftDldExpHours : (int)$_POST['expire_hours'];
$allowed_download_count = empty($_POST['allowed_download_count']) ? Config::$dftAllowedDldCount : (int)$_POST['allowed_download_count'];
$fid = empty($_POST['fid']) ? '' : (int)$_POST['fid'];
$sha256 = empty($_POST['fkey']) ? '' : $_POST['fkey'];

// TODO param validation

// TODO validate authorization

// TODO encrypt shared file

$params = generateShareLink($fid, $sha256, $expire_hours, $allowed_download_count);

$ret = array('error' => '', 'url' => 'get.php' . $params);

echo json_encode($ret);

