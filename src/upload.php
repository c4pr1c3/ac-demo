<?php

require 'upload.bo.php';

$ret = doFileUpload();

$pub_key=openssl_get_publickey($_SESSION['pubkey']);
   // var_dump($_SESSION['pubkey']);
    //var_dump($pub_key);
 
echo json_encode($ret);
