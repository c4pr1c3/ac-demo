<?php

require_once 'utils.php';
require_once 'db.php';
require_once 'lang.php';
require_once 'config.php';

function checkPassword($pwd) {
	$ret = true;
    foreach(Config::$password['rules'] as $key => $rule) {
      if (!preg_match($rule[0], $pwd)) {
		  $ret = false;
      }
    }

    return $ret;
}


function isInvalidRegister($postArr) {
	$ret = false;
    if(empty($postArr['userName']) || !filter_var($postArr['userName'], FILTER_VALIDATE_EMAIL)) {
		$ret = true;
    }
    if(empty($postArr['password']) || !checkPassword($postArr['password'])) {
		$ret = true;
    }
	return $ret;

}

function doRegister($postArr)
{
    // 数据校验
    if(isInvalidRegister($postArr)) {
        return false;
    }

    $userName = $postArr['userName'];
    $password = $postArr['password'];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // 生成公私钥对，并用用户登录口令加密生成的私钥
    $ret = getPubAndPrivKeys($userName, $password);

    //var_dump($ret);

    $pubkey = $ret['pubkey'];
    $privkey = $ret['privkey'];

    try {
      // 检查用户名是否可用
      if(empty(checkRegisterInDb($userName))) {
          // 用户注册信息数据库写入操作
          if(!registerInDb($userName, $hashedPassword, $pubkey, $privkey)) {
			return false;
          }
		  else
		  {
			  return true;
		  }
      } else {
		  return false;
      }
    } catch(Exception $e) {
		return false;
    }
		return false;

}
