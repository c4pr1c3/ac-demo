<?php
/**
 * Created by PhpStorm.
 * User: xhl
 * Date: 18-7-29
 * Time: 下午1:11
 */

//<?php
//$dbhost = 'localhost:3306';  // mysql服务器主机地址
//$dbuser = 'root';            // mysql用户名
//$dbpass = '123456';          // mysql用户名密码
//$conn = mysqli_connect($dbhost, $dbuser, $dbpass);
//if(! $conn )
//{
//    die('Could not connect: ' . mysqli_error());
//}
////echo '数据库连接成功！';

//mysqli_select_db( $conn, 'USER' );                        //选择数据库
//$username = stripslashes(trim($_POST['username']));
//$password = md5(trim($_POST['password']));                 //加密密码
//$email = trim($_POST['email']);                            //邮箱
//$regtime = time();                                         //注册时间
//$token = md5($username.$password.$regtime);                //创建用于激活识别码
//$token_exptime = time()+60*60*24;                          //设置激活码有效时间,过期时间为24小时后

//
//$sql = "INSERT INTO sheet1 ".
//    "(user_name,password, email,token,token_exptime,regtime) ".
//    "VALUES ".
//    "('$username','$password','$email','$token','$token_exptime','$regtime')";
//
//$retval = mysqli_query( $conn, $sql );
//if(! $retval )
//{
//    die('无法插入数据: ' . mysqli_error($conn));
//}
////echo "数据插入成功\n";

include_once "class.phpmailer.php";//获取一个外部文件的内容
include_once "class.smtp.php";
require_once "register.bo.php";


sendEmail("123","456","3417934244@qq.com");