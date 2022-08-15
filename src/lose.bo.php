<?php
require_once 'db.php';
require_once 'lang.php';
require_once 'config.php';
require_once 'utils.php';

function setupPageLayout($req_method, &$pageLayout)
{
    $pageLayout['showLoseFormOrNot'] = 'container';//忘记密码第一页：输入用户名、PIN、新密码、确认新密码以及验证码
    $pageLayout['showLoseMsgOrNot'] = 'container';//忘记密码第二页：跳转到登录页
    $pageLayout['userNameMsg'] = Prompt::$msg['invalid_username'];
    $pageLayout['userPasswordMsg'] =  Prompt::$msg['invalid_password'];
    $pageLayout['loseMsg'] = Prompt::$msg['forget_password_ok'];
    $pageLayout['userName-has-warning'] = '';
    $pageLayout['userPinMsg'] = Prompt::$msg['non-specified_pin'];
    $pageLayout['userCodeMsg'] = Prompt::$msg['invalid_code'];
    $pageLayout['password-has-warning'] = '';
    $pageLayout['code-has-warning'] = '';
    $pageLayout['pin-has-warning'] = '';
    $pageLayout['has-warning'] = false;
    $pageLayout['passwordErrs'] = array();

    switch ($req_method) {
    case 'POST'://展示验证身份的页面，用户名以及PIN码的输入
        $pageLayout['showLoseFormOrNot'] = 'hidden';
       break;
    case 'GET'://展示要跳转登录的页面
        $pageLayout['showLoseMsgOrNot'] = 'hidden';
        break;
    default:
        # code...
        break;
    }
}

function checkPassword($pwd, &$errors) {
    $errors_init = $errors;

    foreach(Config::$password['rules'] as $key => $rule) {
      if (!preg_match($rule[0], $pwd)) {
          $errors[] = $rule[1];
      }
    }

    return ($errors == $errors_init);
}

function isusername($chars,$encoding='utf8'){
    $pattern =($encoding=='utf8')?'/[\x{4e00}-\x{9fa5}a-zA-Z0-9]/u':'/[\x80-\xFF]/';
    if(
    preg_match_all($pattern,$chars,$result))
       {
        return true;
        }
        else
        { 
        return false;
        }
}

function isInvalidLoseRegister($postArr, &$pageLayout)
{
    if(empty($postArr['userName']) || !filter_var($postArr['userName'], FILTER_CALLBACK,
    array("options"=>"isusername"))) {
        $pageLayout['userName-has-warning'] = 'has-warning';
        $pageLayout['has-warning'] = true;
        $pageLayout['showLoseFormOrNot'] = 'container';
        $pageLayout['showLoseMsgOrNot'] = 'hidden';
        $postArr['userName'] = '';
        //file_put_contents('/tmp/test.log','0'.PHP_EOL,FILE_APPEND);
}

if(empty($postArr['password']) || !checkPassword($postArr['password'], $pageLayout['passwordErrs'])) {
    $pageLayout['password-has-warning'] = 'has-warning';
    $pageLayout['has-warning'] = true;
    $pageLayout['showLoseFormOrNot'] = 'container';
    $pageLayout['showLoseMsgOrNot'] = 'hidden';
    $pageLayout['userPasswordMsg'] = implode(',', $pageLayout['passwordErrs']);
    //file_put_contents('/tmp/test.log','1'.PHP_EOL,FILE_APPEND);
}

if(!preg_match('~^[0-9]*$~',$postArr['pin']))
{   $pageLayout['pin-has-warning'] = 'has-warning';
    $pageLayout['has-warning'] = true;
    $pageLayout['showLoseFormOrNot'] = 'container';
    $pageLayout['showLoseMsgOrNot'] = 'hidden';
    //file_put_contents('/tmp/test.log','2'.PHP_EOL,FILE_APPEND);
}
if (strtolower($_REQUEST['code'])!=$_SESSION['authcode'])
{
    $pageLayout['code-has-warning'] = 'has-warning';
    $pageLayout['userCodeMsg'] = Prompt::$msg['wrong_code'];
    $pageLayout['has-warning'] = true;
    $pageLayout['showLoseFormOrNot'] = 'container';
    $pageLayout['showLoseMsgOrNot'] = 'hidden';
    
}
$_SESSION['userName'] = $postArr['userName'];
setcookie('userName', $postArr['userName']);

return $pageLayout['has-warning'];
}



function doLoseRegister($postArr,&$pageLayout)
{
    // 数据校验
    if(isInvalidLoseRegister($postArr, $pageLayout)) {
        return;
    }
    // file_put_contents('/tmp/test.log','-----'.PHP_EOL,FILE_APPEND);
    // file_put_contents('/tmp/test.log',$postArr['userName'].PHP_EOL,FILE_APPEND);
    // file_put_contents('/tmp/test.log','-----'.PHP_EOL,FILE_APPEND);
    $name = $postArr['userName'];
    $password = $postArr['password'];
    $pin = $postArr['pin'];
    // file_put_contents('/tmp/test.log','====='.PHP_EOL,FILE_APPEND);
    // file_put_contents('/tmp/test.log',$postArr['userName'].PHP_EOL,FILE_APPEND);
    // file_put_contents('/tmp/test.log','====='.PHP_EOL,FILE_APPEND);
    
try{
    //检查用户名是否存在
    if(empty(checkPinInDb($name)))//用户未注册的情况
    {
        setupPageLayout('GET', $pageLayout);
        $pageLayout['userName-has-warning'] = 'has-warning';
        $pageLayout['userNameMsg'] = Prompt::$msg['null_username'];
        $pageLayout['has-warning'] = true;
        //file_put_contents('/tmp/test.log','3'.PHP_EOL,FILE_APPEND);

    }
    else{//用户名存在
        $db_pin =checkPinInDb($name);//从数据库中获取PIN码
        if($db_pin != $pin)//可能有变量类型的问题，报错时需考虑一下子
            {
                setupPageLayout('GET', $pageLayout);
                $pageLayout['pin-has-warning'] ='has-warning' ;
                $pageLayout['userPinMsg'] = Prompt::$msg['wrong_pin'];
                $pageLayout['has-warning'] = true;
            }
        else{//也即用户名和PIN码能够对应上
            $hashedPassword = checkRegisterInDb($postArr['userName']);
            if(password_verify($postArr['password'], $hashedPassword))
                {//密码不能与近期使用过的相同，虽然这里没什么用，但是很多成熟的APP修改新密码都有这个要求
                    setupPageLayout('GET', $pageLayout);
                    $pageLayout['password-has-warning'] = 'has-warning';
                    $pageLayout['userPasswordMsg'] = Prompt::$msg['same_password'];
                    $pageLayout['has-warning'] = true;
                    //file_put_contents('/tmp/test.log','5'.PHP_EOL,FILE_APPEND);
                }
            else{//身份验证成功，且密码不与之前的重复

                $hashedPassword_new = password_hash($password, PASSWORD_DEFAULT);//新的口令的hash
                $ret = getPubAndPrivKeys($name, $password);
                $pubkey = $ret['pubkey'];//公钥
                $privkey = $ret['privkey'];//被口令加密的私钥
                updatePassword($name,$hashedPassword_new,$pubkey, $privkey);   
                }
        }     
        }

    }catch(Exception $e)
        {
        setupPageLayout('POST', $pageLayout);
        $pageLayout['has-warning'] = true;
        $pageLayout['loseMsg'] = Prompt::$msg['db_oops'];
        }
}

