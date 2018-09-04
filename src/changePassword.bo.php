<?php


require_once 'utils.php';
require_once 'db.php';
require_once 'lang.php';
require_once 'config.php';

require_once "class.phpmailer.php";//获取一个外部文件的内容
require_once "class.smtp.php";

function test($postarr)
{
    echo $postarr['userName'];
    if(isValidUsername($postarr['userName'],$postarr['emailName'])) {

        $username = $postarr['userName'];
        $email = $postarr['emailName'];
        $access_time = time()+60*60*24;  //设置激活码24小时以内有效
        $access_key = hash('sha256',$username.$email.$access_time);
        $valid =1;

        resetInDb($username, $email, $access_time, $access_key,$valid);
        sendEmail($username,$access_key,$email);
        echo "\n重置密码链接已发送到您的邮箱 请尽快登录邮箱使用  24小时后失效";

    }else {
        echo "用户名与邮箱不符 请重试";
    }


}

function  resetPassword($postarr)
{
    if(isvalidReset($_POST['verify'],$_POST['username']))
    {
        $email = setInvalidReset($_POST['verify'],$_POST['username']); //首先将再次重设密码设置为错误
        alterPassword($_POST['username'],$_POST['password'],$email); //修改密码
    }



}
function  alterPassword($username,$password,$emailName)
{
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);   //更改密码也更新所有的公私钥 吗？？？？？？
//    // 生成公私钥对，并用用户登录口令加密生成的私钥
//    $ret = getPubAndPrivKeys($emailName, $password);
//    $pubkey = $ret['pubkey'];
//    $privkey = $ret['privkey'];
//
    resetP($username,$hashedPassword);
}
function isvalidReset($verify,$username)
{
    $reset = getResetinfo($username,$verify);
    return $reset;
}

function isValidUsername($username,$email)
{
    $userInfo = getUserInfoInDb($username);
    if($userInfo['email'] === $email){
        return true;
    }
    return false;
}







function sendEmail($username,$token,$email)
{

    $mail=new PHPMailer();
    $mail->SMTPDebug = 0;              //设置调试信息  如果设置为1或者2 发送不成功会输出报错信息

    $mail->MsgHTML($body);
    $mail->Body = "亲爱的".$username."：<br/>重置密码。<br/>请点击链接修改您的密码。<br/>
    <a href='http://192.168.29.122:8080/changePassword2.php?verify=".$token."&name=".$username."' target ='_blank'>请点击链接</a><br/>
    ";




//设置smtp参数
    $mail->IsSMTP();
    $mail->SMTPAuth=true;
    $mail->SMTPKeepAlive=true;
    $mail->SMTPSecure= "ssl";
//$mail->SMTPSecure= "tls";
    $mail->Host="smtp.qq.com";
    $mail->Port=465;
//$mail->Port=587;

//填写email账号和密码

    $mail->Username="2939906971@qq.com";  //设置发送方
    $mail->Password="rxrrgwptxhgmdcje";   //注意这里也要填写授权码就是我在上面QQ邮箱开启SMTP中提到的，不能填邮箱登录的密码哦。
    $mail->From="2939906971@qq.com";      //设置发送方
    $mail->FromName="中传放心传";
    $mail->Subject="中传放心传发来的一封邮件";
    $mail->AltBody=$body;
    $mail->WordWrap=50;                  // 设置自动换行

    $mail->AddReplyTo("2939906971@qq.com","中传***");//设置回复地址
    $mail->AddAddress($email,"hello");  //设置邮件接收方的邮箱和姓名
    $mail->IsHTML(true);                //使用HTML格式发送邮件
    if(!$mail->Send()){//通过Send方法发送邮件,根据发送结果做相应处理
        echo "Mailer Error:".$mail->ErrorInfo;
    }else{
        echo "Message has been sent"; }


}





function setupPageLayout($req_method, &$pageLayout)
{
$pageLayout['showRegFormOrNot'] = 'container';
$pageLayout['showRegMsgOrNot'] = 'container';
$pageLayout['userNameMsg'] = Prompt::$msg['invalid_username'];
$pageLayout['emailNameMsg'] = Prompt::$msg['invalid_emailname'];
//$pageLayout['userPasswordMsg'] =  Prompt::$msg['invalid_password'];
$pageLayout['retMsg'] = Prompt::$msg['register_ok'];
$pageLayout['userName-has-warning'] = '';
$pageLayout['emailName-has-warning'] = '';
$pageLayout['password-has-warning'] = '';
$pageLayout['has-warning'] = false;
$pageLayout['passwordErrs'] = array();
//file_put_contents('debug.log', "first has set it  ".$pageLayout['has-warning']."\n",FILE_APPEND);
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



function setupPageLayout2($req_method, &$pageLayout)
{
    $pageLayout['showRegFormOrNot'] = 'container';
    $pageLayout['showRegMsgOrNot'] = 'hidden';
    $pageLayout['userNameMsg'] = Prompt::$msg['invalid_username'];
    $pageLayout['emailNameMsg'] = Prompt::$msg['invalid_emailname'];
//$pageLayout['userPasswordMsg'] =  Prompt::$msg['invalid_password'];
    $pageLayout['retMsg'] = Prompt::$msg['register_ok'];
    $pageLayout['userName-has-warning'] = '';
    $pageLayout['emailName-has-warning'] = '';
    $pageLayout['password-has-warning'] = '';
    $pageLayout['has-warning'] = false;
    $pageLayout['passwordErrs'] = array();
//file_put_contents('debug.log', "first has set it  ".$pageLayout['has-warning']."\n",FILE_APPEND);
    switch ($req_method) {
        case 'POST':
           // $pageLayout['showRegFormOrNot'] = 'hidden';
            break;
        case 'GET':
            $pageLayout['showRegMsgOrNot'] = 'hidden';
            break;
        default:
# code...
            break;
    }
}



function setupPageLayout3(&$pageLayout)  //设置html内容不可见
{
    $pageLayout['showRegFormOrNot'] = 'container';
    $pageLayout['showRegMsgOrNot'] = 'container';
    $pageLayout['userNameMsg'] = Prompt::$msg['invalid_username'];
    $pageLayout['emailNameMsg'] = Prompt::$msg['invalid_emailname'];
//$pageLayout['userPasswordMsg'] =  Prompt::$msg['invalid_password'];
    $pageLayout['retMsg'] = Prompt::$msg['register_ok'];
    $pageLayout['userName-has-warning'] = '';
    $pageLayout['emailName-has-warning'] = '';
    $pageLayout['password-has-warning'] = '';
    $pageLayout['has-warning'] = false;
    $pageLayout['passwordErrs'] = array();
//f

    $pageLayout['showRegFormOrNot'] = 'hidden';
    $pageLayout['showRegMsgOrNot'] = 'hidden';


}







