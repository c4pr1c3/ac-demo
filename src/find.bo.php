<?php

require_once 'utils.php';
require_once 'db.php';
require_once 'lang.php';
require_once 'config.php';

//检查邮箱是否注册
function checkEmailUseOrNot($email){
    if(!checkRegisterInDb("",$email)){
        return false;
    }
    return true;
}


function sendmail($email,$url){ 
    include_once "class.phpmailer.php";//获取一个外部文件的内容
    include_once "class.smtp.php";
    $time = date('Y-m-d H:i:s', time());
    $mail=new PHPMailer();
    $mail->SMTPDebug = 0;              //设置调试信息  如果设置为1或者2 发送不成功会输出报错信息
    $body = "dear ".$email."：<br/>at".$time." you had post a request to reset you password,please click the url <br/><a href='".$url."'target='_blank'>".$url."</a>"; 
    $mail->IsSMTP();
    $mail->SMTPAuth=true;
    $mail->SMTPKeepAlive=true;
    $mail->SMTPSecure= "ssl";
    $mail->Host="smtp.example.com";
    $mail->Port=465;
    $mail->Username="example";  //设置发送方
    $mail->Password="wxample";  
    $mail->From="example";      //设置发送方
    $mail->FromName="ResetPassword_CUC";
    $mail->Subject="Reset your password";
    $mail->AltBody=$body;
    $mail->WordWrap=50;                  // 设置自动换行
    $mail->MsgHTML($body);
    $mail->AddReplyTo("example","ResetPassword_CUC");//设置回复地址
    $mail->AddAddress($email,"hello");  //设置邮件接收方的邮箱和姓名
    $mail->IsHTML(true);                //使用HTML格式发送邮件
    if(!$mail->Send()){//通过Send方法发送邮件,根据发送结果做相应处理
        $ret = "Mailer Error:".$mail->ErrorInfo;
    }else{
        $ret = "Message has been sent"; 
    }
    return $ret;

}

function generateFindLink($email){
    $ret = getFileLinkInfoFromDb($email);
    $id = $ret[0]['id'];
    if(getMasterKey($masterKey)) {

        $expire_ts = $expire * 3600 + time();
        $data = sprintf("%s-%s-%s", $id, $email,$expire_ts);
        $token = sodium_bin2hex(sodium_crypto_generichash($data, sodium_bin2hex($masterKey), SODIUM_CRYPTO_GENERICHASH_BYTES_MIN));
        saveToken($email,$token);
        return sprintf("?id=%s&token=%s&email=%s", $id, $token,$email);
    } else {

        return false;
}
    return true;
}


function doCheck($postArr){

    $email = $postArr['Email'];
    $_SESSION['checkemail'] = $email;
    if(!checkEmailUseOrNot($email)){
        $ret = array('error' => Prompt::$msg['unregister_username']);
        
    }else{
        if (false == generateFindLink($email)){
            $ret = array('error' => Prompt::$msg['find_error']);
        }else{
            updateCheckTime($email,0);
            $params = generateFindLink($email);
            $url = array('error' => '', 'url' => getUriRoot() . '/check.php' . $params, 'access_code' => sodium_bin2hex($shareKey));
            $ret = sendmail($email,$url['url']);


        }
    }
    return $ret;
     

}
