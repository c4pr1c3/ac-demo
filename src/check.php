<?php
ob_flush();

require 'auth.php';
require 'check.bo.php';
$fid = empty($_POST['fid']) ? '' : $_POST['fid'];

$uid = $_SESSION['uid'];

echo json_encode(check_sha256($fid,$uid));
flush();