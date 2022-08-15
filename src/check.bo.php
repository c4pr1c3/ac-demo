<?php

require 'db.php';


function check_sha256($fid,$uid) {
    
    $sha256=getSha256InDb($fid);
    file_put_contents('/tmp/test.log','-----'.PHP_EOL,FILE_APPEND);
    file_put_contents('/tmp/test.log',$sha256.PHP_EOL,FILE_APPEND);
    file_put_contents('/tmp/test.log','====='.PHP_EOL,FILE_APPEND);

    $decrypted_sha256 = hash('sha256',$_SESSION['doc_content'] );
    file_put_contents('/tmp/test.log',$decrypted_sha256.PHP_EOL,FILE_APPEND);
    if($sha256!=$decrypted_sha256)
  
    {
  
    $ret = [
         'error'=> Prompt::$msg['different_sha256']
    ];
  
    return $ret;
  
     }
    else{
    return 'ok';}
}