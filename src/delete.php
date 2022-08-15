<?php

require 'auth.php';
require 'delete.bo.php';

$key = empty($_POST['key']) ? '' : $_POST['key'];

$uid = $_SESSION['uid'];

echo json_encode(delete_file($key, $uid));


