<?php


require_once 'utils.php';
require_once 'db.php';
require_once 'lang.php';
require_once 'config.php';



$servername = 'db';
$username = getenv('DB_AC_USERNAME');
$password = getenv('DB_AC_PASSWORD');
$dbname = getenv('DB_AC_DBNAME');
file_put_contents('debug.log', "test ".$servername ." ".$username." ".$password." ".$dbname." ". "\n", FILE_APPEND);
$charset = "utf8mb4";
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_PERSISTENT => true
);
try{
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=$charset", $username, $password, $options);
}catch (PDOException $e){
  die($e->getMessage());
}
echo "dede";
$name ="dede2";

$userInfo = getUserInfoInDb($name);
echo json_encode($userInfo);