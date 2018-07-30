<?php

require 'db.php';
require_once 'lang.php';
require_once 'utils.php';

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
        $_SESSION['encrypted'] = (bool)getenv('SESSION_AC_ENCRYPTED');

        if($_SESSION['encrypted'] === false) {
          $_SESSION['passphrase'] = base64_encode(openssl_random_pseudo_bytes(Config::$asymmetricEncKeyLen)); // 会话用非对称加密秘钥
          // 使用用户口令解密内存中保存的刚刚创建的用户私钥
          $privkey = openssl_pkey_get_private($userInfo['privkey'], $postArr['password']);
          if($privkey !== false) {
            // 将用户私钥重新用新生成的随机口令$_SESSION['passphrase']重新加密后保存到内存
            if(openssl_pkey_export($privkey, $n_privkey, $_SESSION['passphrase'])) {
              $_SESSION['privkey'] = $n_privkey;
              // 释放内存中的明文私钥
              openssl_pkey_free($privkey);
            }
          }
        }
      }
    } catch(Exception $e) {
      $retMsg['msg'] = Prompt::$msg['db_oops'];
    }
  }

  echo json_encode($retMsg);

}



