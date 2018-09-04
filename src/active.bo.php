<?php
require_once 'utils.php';
require_once 'db.php';
require_once 'lang.php';
require_once 'config.php';





function doRegisterActive($postArr, &$pageLayout)
{
    // 数据校验
   // iconv("gbk","utf-8",$_GET['name'])
   //$userName =  iconv("gbk","utf-8", $postArr['userName']);
    //file_put_contents('debug.log', "待激活的用户名  ".json_encode($postArr['userName'])."\n",FILE_APPEND);
     $userName = $postArr['userName'];
     $verify = $postArr['verify'];


    try {
        //连接数据库

         if(checkRegisterActive($userName,$verify)==1)
         {
              $pageLayout['showRegFormOrNot'] ='hidden';
              echo  "激活成功";
         }else{
              $pageLayout['showRegFormOrNot'] ='hidden';
              echo " 激活失败";
         }

        } catch(PDOException $e) {
        throw  $e ;
    }

}