<?php
require 'check.bo.php';

$email = empty($_GET['email']) ? '' : $_GET['email'];
$token = empty($_GET['token']) ? '' : $_GET['token'];

// if(empty($_POST['password'])){
// }else{
//  $password = $_POST['password'];
// }

if(validateCheckLink($token, $email)){
	$now = time();
	$ret = findCheckTime($email);
	$time = $ret[0]["time"];
	//判断链接超时（10min)
	if ($now - $time > 600){
		$ret = "链接超时，请重新找回密码";
		echo $ret;
    return;
  }
  $emailDbArray = getResetFromDb($email);
  if(!empty($emailDbArray)){
    if(empty($_POST)){
      $ret = file_get_contents("check.html");
      echo $ret;
       exit();
    }
    if(isset($_POST['ipassword'])){
      $password = $_POST['ipassword'];
    } else{
      $ret = "找回密码出错";
      echo $ret;
      return;
    }
    $emailDb = $emailDbArray['email'];
   	$hashedPassword = sodium_crypto_pwhash_str(
            $password,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
        );
    // 生成公私钥对，并用用户登录口令加密生成的私钥
    // $ret = getPubAndPrivKeys($userName, $password);
    // $pubkey = $ret['pubkey'];
    // $privkey = $ret['privkey'];
    $salt = random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES);
    $nonce = random_bytes(SODIUM_CRYPTO_BOX_NONCEBYTES);
    if(updateResetUsers($email, $hashedPassword, sodium_bin2hex($salt), sodium_bin2hex($nonce))){
      $ret = <<<EOF
<script type="text/javascript">
window.top.location.href = "/index.html";
</script>
EOF;
    } else{
      $ret = "密码重置失败，请重试";
      }
  	}
} else{
  	$ret = "无效链接，请重试";
}
updateTokenCountInDb1($email);
echo $ret;