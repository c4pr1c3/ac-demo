<?php

require 'db.php';
require 'lang.php';

function checkLogin($postArr) {
  $retMsg = array(
    'has-warning' => true,
    'msg' => Prompt::$msg['login_failed']
  );

  if(!empty($postArr['userName']) && !empty($postArr['password'])) {
    try {
      $hashedPassword = checkRegisterInDb($postArr['userName']);
      if(password_verify($postArr['password'], $hashedPassword)) {
        $retMsg['has-warning'] = false;
        $retMsg['msg'] = Prompt::$msg['login_ok'];
        $_SESSION['loggedInUser'] = $postArr['userName'];
        setcookie('loggedInUser', $postArr['userName']);

        // 读取用户表中其他信息并保存在session中
        $userInfo = getUserInfoInDb($postArr['userName']);
        $_SESSION['uid']  = $userInfo['id'];
        $_SESSION['pubkey']  = $userInfo['pubkey'];
        $_SESSION['privkey'] = $userInfo['privkey'];
        
        // TODO 用户登录时增加选项“为本次登录会话记住口令”
        //      以下状态变量设置为false时表示上述session变量privkey为非加密状态
        $_SESSION['encrypted'] = true; 
      }
    } catch(Exception $e) {
      $retMsg['msg'] = Prompt::$msg['db_oops'];
    }
  }

  echo json_encode($retMsg);

}



