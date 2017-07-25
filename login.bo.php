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
      }
    } catch(Exception $e) {
      $retMsg['msg'] = Prompt::$msg['db_oops'];
    }
  }

  echo json_encode($retMsg);

}
