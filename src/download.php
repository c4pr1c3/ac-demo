<?php

require 'auth.php';
require 'download.bo.php';


$id = empty($_GET['id']) ? '' : $_GET['id'];

$uid = $_SESSION['uid'];

if($_SESSION['encrypted'] === false) {
    //debug_log('implemented plaintext SESSION' . json_encode($_SESSION) , __FILE__, __LINE__);
} else {
    // TODO 用户输入当前文件的用户加密口令
    debug_log('not implemented encrypted SESSION' . json_encode($_SESSION) , __FILE__, __LINE__);
    return;
}

list($error, $filename, $filesize, $decrypted_content) = download_file($id, $uid,$_SESSION['encrypt_pair'], $_SESSION['passphrase_key'],$_SESSION['passphrase_nonce']);


//file_put_contents('debug.log', "wenjiansss ".$decrypted_content."\n",FILE_APPEND);


if(empty($error)) {
    header('Content-Description: Decrypted File Download');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: private');
    header('Content-Length: ' . $filesize); 
    ob_clean();
    flush();

    echo $decrypted_content;
    exit();
} else {
    echo "$error";
}


