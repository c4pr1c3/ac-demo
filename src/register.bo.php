<?php

require_once 'utils.php';
require_once 'db.php';
require_once 'lang.php';
require_once 'config.php';

function setupPageLayout($req_method, &$pageLayout)
{
    $pageLayout['showRegFormOrNot'] = 'container';
    $pageLayout['showRegMsgOrNot'] = 'container';
    $pageLayout['userNameMsg'] = Prompt::$msg['invalid_username'];
    $pageLayout['userEmailMsg'] = Prompt::$msg['invalid_email'];
    $pageLayout['userPasswordMsg'] =  Prompt::$msg['invalid_password'];
    $pageLayout['retMsg'] = Prompt::$msg['register_ok'];
    $pageLayout['userName-has-warning'] = '';
    $pageLayout['userEmail_has_warning'] = '';
    $pageLayout['password-has-warning'] = '';
    $pageLayout['has-warning'] = false;
    $pageLayout['passwordErrs'] = array();

    switch ($req_method) {
    case 'POST':
        $pageLayout['showRegFormOrNot'] = 'hidden';
        break;
    case 'GET':
        $pageLayout['showRegMsgOrNot'] = 'hidden';
        break;
    default:
        # code...
        break;
    }
}

#判断用户输入用户名是否符合要求
function checkUserName($name, &$errors){
  $errors_init = $errors;
  $preg = '/^[A-Za-z0-9_@\.\-\x{4e00}-\x{9fa5}]+$/u';
  if(mb_strlen($name, 'utf8')<5 || mb_strlen($name, 'utf8')>36){
    $errors[] = '用户名长度位为5-36位';
  }
  else if(!preg_match_all($preg, $name, $matches)){
    $errors[] = '用户名只能包括中文，英文，数字，-_.@';  
  }
  return ($errors == $errors_init);
}

function checkPassword($pwd, &$errors, &$strength) {
    $errors_init = $errors;

    foreach(Config::$password['rules'] as $key => $rule) {
      if (!preg_match($rule[0], $pwd)) {
          $errors[] = $rule[1];
      }
    }

    return ($errors == $errors_init);
}

function isInvalidRegister($postArr, &$pageLayout) {
    if(empty($postArr['userName']) || !checkUsername($postArr['userName'], $pageLayout['usernameErrs'])) {
        $pageLayout['userName-has-warning'] = 'has-warning';
        $pageLayout['has-warning'] = true;
        $pageLayout['showRegFormOrNot'] = 'container';
        $pageLayout['showRegMsgOrNot'] = 'hidden';
        $pageLayout['userNameMsg'] = implode(',', $pageLayout['usernameErrs']);
        $postArr['userName'] = '';
    }
    if(empty($postArr['userEmail']) || !filter_var($postArr['userEmail'], FILTER_VALIDATE_EMAIL)) {
        $pageLayout['userEmail-has-warning'] = 'has-warning';
        $pageLayout['has-warning'] = true;
        $pageLayout['showRegFormOrNot'] = 'container';
        $pageLayout['showRegMsgOrNot'] = 'hidden';
        $postArr['userEmail'] = '';
    }
    if(empty($postArr['password']) || !checkPassword($postArr['password'], $pageLayout['passwordErrs'], $pageLayout['password-strength'])) {
        $pageLayout['password-has-warning'] = 'has-warning';
        $pageLayout['has-warning'] = true;
        $pageLayout['showRegFormOrNot'] = 'container';
        $pageLayout['showRegMsgOrNot'] = 'hidden';
        $pageLayout['userPasswordMsg'] = implode(',', $pageLayout['passwordErrs']);
    }

    $_SESSION['userName'] = $postArr['userName']; // 服务器端
    // $_SESSION['userEmail'] = $postArr['userEmail'];
    // setcookie('userName', $postArr['userName']); // 客户端

    return $pageLayout['has-warning'];
}

function doRegister($postArr, &$pageLayout)
{
    // 数据校验
    if(isInvalidRegister($postArr, $pageLayout)) {
        return;
    }

    $userName = $postArr['userName'];
    $useremail = $postArr['userEmail'];
    $password = $postArr['password'];

    // $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // bcrypt
    // $hashedPassword = password_hash($password, PASSWORD_ARGON2I);
 
    try {
      // 检查用户名是否可用
      if(empty(checkRegisterInDb($userName, $useremail))) {
        $hashedPassword = sodium_crypto_pwhash_str(
            $password,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, // 4
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE // 33554432
        );
        // 生成公私钥对，并用用户登录口令加密生成的私钥
        // $ret = getPubAndPrivKeys($userName, $password);
        // $pubkey = $ret['pubkey'];
        // $privkey = $ret['privkey'];
        $salt = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES); // 16
        $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES); // 24
          // 用户注册信息数据库写入操作
          if(!registerInDb($userName, $useremail, $hashedPassword, sodium_bin2hex($salt), sodium_bin2hex($nonce))) {
            // 如果注册失败，则设置相应的错误提示信息，否则，默认只显示注册成功消息和对应的DIV片段代码
            setupPageLayout('GET', $pageLayout);
            $pageLayout['has-warning'] = true;
            $pageLayout['retMsg'] = Prompt::$msg['register_failed'];
          }
          else{
          	echo "<meta http-equiv='refresh' content='3;url=index.html?name=".$_SESSION['userName']."'>" ; //注册成功，3s后跳转
          }
      } else {
          // 如果注册失败，则设置相应的错误提示信息，否则，默认只显示注册成功消息和对应的DIV片段代码
          setupPageLayout('GET', $pageLayout);
          $pageLayout['userName-has-warning'] = 'has-warning';
          $pageLayout['userNameMsg'] = Prompt::$msg['duplicate_username'];
          $pageLayout['has-warning'] = true;
      }
    } catch(Exception $e) {
      setupPageLayout('POST', $pageLayout);
      $pageLayout['has-warning'] = true;
      $pageLayout['retMsg'] = Prompt::$msg['db_oops'];
    }

}