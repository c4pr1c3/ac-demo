<?php

require 'db.php';
require_once 'lang.php';
require_once 'utils.php';

function checkLogin($postArr) {
    //file_put_contents('debug.log', json_encode($postArr) . "\n", FILE_APPEND);
  $retMsg = array(
    'has-warning' => true,
    'msg' => Prompt::$msg['login_failed']
  );


  if(!empty($postArr['userName']) && !empty($postArr['password'])) {
    try {
      $ret= checkRegisterInDb($postArr['userName']);  //返回是否能查找到该用户名


     if(sodium_crypto_pwhash_str_verify($ret['password'], $postArr['password'])){


         $out_len = SODIUM_CRYPTO_SIGN_SEEDBYTES;
         $seed = sodium_crypto_pwhash(
             $out_len,
             $postArr['password'],
             sodium_hex2bin($ret['salt']),
             SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
             SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
         );
         // 同一个密码 用同一个salt 会生成同一个种子  可使用该种子生成用户的签名和加密的密码

         $user_sign_kp = sodium_crypto_sign_seed_keypair($seed);
         $user_sign_secretkey = sodium_crypto_sign_secretkey($user_sign_kp);
         $user_sign_publickey = sodium_crypto_sign_publickey($user_sign_kp);
         $user_encrypt_kp = sodium_crypto_box_seed_keypair($seed);



         $retMsg['has-warning'] = false;
        $retMsg['msg'] = Prompt::$msg['login_ok'];
        $_SESSION['loggedInUser'] = $postArr['userName'];
        setcookie('loggedInUser', $postArr['userName']);

        // 读取用户表中其他信息并保存在session中
        $userInfo = getUserInfoInDb($postArr['userName']);
        $_SESSION['uid']  = $userInfo['id'];
        //$_SESSION['pubkey']  = $userInfo['pubkey'];
        //$_SESSION['privkey'] = $userInfo['privkey'];

        // TODO 用户登录时增加选项“为本次登录会话记住口令”
        //      以下状态变量设置为false时表示上述session变量privkey为非加密状态
        $_SESSION['encrypted'] = (bool)getenv('SESSION_AC_ENCRYPTED');

        if($_SESSION['encrypted'] === false) {

            $key = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_KEYBYTES);  //生成对称加密的key
            $nonce = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_NPUBBYTES);//生成加密对称密钥
            $ad = 'Additional (public) data';
            $sign_secretkey= sodium_bin2hex(sodium_crypto_aead_chacha20poly1305_encrypt(
                $user_sign_secretkey,
                $ad,
                $nonce,
                $key
            ));// 将签名私钥加密后存储
            $crypt_keypair = sodium_bin2hex(sodium_crypto_aead_chacha20poly1305_encrypt(
                $user_encrypt_kp,
                $ad,
                $nonce,
                $key
            ));// 将加密密钥对加密存储
            $_SESSION['passphrase_key'] = sodium_bin2hex($key);
            $_SESSION['passphrase_nonce'] = sodium_bin2hex($nonce);
            $_SESSION['sign_pubkey'] =  sodium_bin2hex(  $user_sign_publickey);
            $_SESSION['encrypt_pair'] = $crypt_keypair;
            $_SESSION['sign_secrkey'] = $sign_secretkey;

        }
      }
    } catch(Exception $e) {
      $retMsg['msg'] = Prompt::$msg['db_oops'];
    }
  }

  echo json_encode($retMsg);

}



