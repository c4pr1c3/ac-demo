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
      $hashedPassword = checkRegisterInDb($postArr['userName'], $postArr['userName']);  
      if(sodium_crypto_pwhash_str_verify($hashedPassword, $postArr['password'])) {
        $retMsg['has-warning'] = false;
        $retMsg['msg'] = Prompt::$msg['login_ok'];
        $_SESSION['loggedInUser'] = $postArr['userName'];
        setcookie('loggedInUser', $postArr['userName']);

        // 读取用户表中其他信息并保存在session中
        $userInfo = getUserInfoInDb($postArr['userName']);
        $_SESSION['uid']  = $userInfo['id'];
        $_SESSION['nonce'] = $userInfo['nonce'];
        $keypairs = getKeyPairs(sodium_hex2bin($userInfo['salt']), $postArr['password']);
        $_SESSION['box']  = $keypairs['box'];
        $_SESSION['sign'] = $keypairs['sign'];

        // TODO 用户登录时增加选项“为本次登录会话记住口令”
        //      以下状态变量设置为false时表示上述session变量privkey为非加密状态
        $_SESSION['encrypted'] = (bool)getenv('SESSION_AC_ENCRYPTED');

        if($_SESSION['encrypted'] === false) {
          $key = random_bytes(SODIUM_CRYPTO_BOX_KEYPAIRBYTES);
          $_SESSION['passphrase'] = base64_safe_encode($key); // 会话用非对称加密秘钥
          // 将用户私钥重新用新生成的随机口令$_SESSION['passphrase']重新加密后保存到内存
          $encrypted_box = sodium_crypto_box(
              $keypairs['box'],
              sodium_hex2bin($_SESSION['nonce']),
              $key
          );  
          if($encrypted_box) {
            $_SESSION['box'] = $encrypted_box;
            // 释放内存中的明文密钥
            // sodium_memzero();
          }
        }
      }
    } catch(Exception $e) {
      $retMsg['msg'] = Prompt::$msg['db_oops'];
    }
  }
  echo json_encode($retMsg);

}



