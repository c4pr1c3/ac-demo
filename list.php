<?php

require 'auth.php';
require 'list.bo.php';

$offset = empty($_GET['offset']) ? 0 : $_GET['offset'];
$limit  = empty($_GET['limit']) ? Config::$pageSize : $_GET['limit'];

// TODO
$search = empty($_GET['search']) ? '' : $_GET['search'];
$order  = empty($_GET['order']) ? 'asc' : $_GET['order'];

if($order != 'asc' || $order != 'desc') {
    $order = 'asc';
}

$ret = listFiles($_SESSION['uid'], $offset, $limit);

echo json_encode($ret);

