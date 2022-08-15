<?php
require 'db.php';
function checkGetSha256($fid) {

    $Get_sha256=getSha256InDb($fid);

    $decrypted_Get_sha256 = hash('sha256',$_SESSION['content']);
    file_put_contents('/tmp/test.log','-----'.PHP_EOL,FILE_APPEND);
    file_put_contents('/tmp/test.log',$Get_sha256.PHP_EOL,FILE_APPEND);
    file_put_contents('/tmp/test.log','====='.PHP_EOL,FILE_APPEND);
    file_put_contents('/tmp/test.log',$decrypted_Get_sha256.PHP_EOL,FILE_APPEND);
    if($Get_sha256!=$decrypted_Get_sha256)
    {
       return 'wrong';
     
        }
       else{
       return 'ok';}
   }
