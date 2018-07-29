<?php
 require 'check.bo.php';

 $email = empty($_GET['email']) ? '' : $_GET['email'];
 $token = empty($_GET['token']) ? '' : $_GET['token'];

 if(empty($_POST['password'])){

 }else{
  $password = $_POST['password'];
 }

if(validateCheckLink($token,$email)){
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
      $ret = <<<HTML
<script language='javascript' type='text/javascript'> 
    window.onload = function(){
      var checkReset = function(){
        var iPassword = document.getElementById('iPassword').value;
        var reiPassword = document.getElementById('re-iPassword').value;
        var password2prompt = document.getElementById('password2prompt');
        var checkpassword = document.getElementById('checkpassword');
        var iPasswordDiv = document.getElementById('iPasswordDiv');
        var reiPasswordDiv = document.getElementById('re-iPasswordDiv');
        var resetBtn = document.getElementById('resetBtn');
        if(iPassword.length != 0){
          if(false == (RegExp("[A-Z]").test(iPassword))||false == (RegExp("[a-z]").test(iPassword))||false == (RegExp("[0-9]").test(iPassword)) || iPassword.length<8){
            checkpassword.innerHTML = "密码必须包含大小写字母，数字，长度≥8";
            checkpassword.classList.add('has-warning');
            reiPasswordDiv.classList.add('has-warning');
            resetBtn.disabled = true;
            
          }else{
            password2prompt.innerHTML ="";
            iPasswordDiv.classList.remove('has-warning');
            reiPasswordDiv.classList.remove('has-warning');
            resetBtn.disabled = false;
            if (iPassword != reiPassword) {
              password2prompt.innerHTML = "两次输入的口令不一致";
              iPasswordDiv.classList.add('has-warning');
              reiPasswordDiv.classList.add('has-warning');
              resetBtn.disabled = true;
            }else {
                  // input is valid -- reset the error message
                password2prompt.innerHTML = '';
                iPasswordDiv.classList.remove('has-warning');
                reiPasswordDiv.classList.remove('has-warning');
                resetBtn.disabled = false;
            }

          }
          
        } 
      };
      document.getElementById("iPassword").addEventListener('input', checkReset, false);
      document.getElementById("re-iPassword").addEventListener('input', checkReset, false);
      document.getElementById("re-iPassword").addEventListener('blur', checkReset, false);
  
      //给密码输入框 注册键放开事件
      var textInput = document.getElementById("iPassword");
      textInput.onkeyup = function(){
        var pwd = this.value;
        var level = 0;
        if(RegExp("[A-Z]").test(pwd) && RegExp("[a-z]").test(pwd) && RegExp("[0-9]").test(pwd) && pwd.length > 7){
          level = 1;
          if(RegExp("[^A-Za-z0-9]").test(pwd)){
            level = 2;
          }
        }

        var blocks = document.getElementsByClassName("strength"); 
        for(var index = 0; index < level+1; index++){
          blocks[index].style.backgroundColor = "orange";
        }
        for(level++; level<blocks.length; level++){
          blocks[level].style.backgroundColor = "";
        }

        if(pwd.length == 0){
          for(var index = 0; index<blocks.length; index++){
            blocks[index].style.backgroundColor = "";
          }
        }
      }
  }    
  </script>

<!DOCTYPE html>
<html lang="zh">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>中传放心传</title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  <style type="text/css">
  .strength {
    display: block;
    float: left;   
    width: 58px;
    margin-right: 2px; 
    text-align: center;
  }
  #Password-strength{
    display: block;
    margin-top: 10px;
    width: 180px;
  }
  </style>
</head>

<body>
  <div id="ReSetPasswordDiv" class="containter">
    <form id=ReSetPasswordForm method="post">
      <h1>重置密码</h1>
        <div id="iPasswordDiv" class="form-group">
          <label for="iPassword">请输入密码</label>
          <input type="password" class="form-control" id="iPassword" placeholder="请输入密码" name="ipassword" required="required" autofocus required maxlength="128">
          <p class="help-block" id="checkpassword"></p>
          <div id='Password-strength'>
            <div class='strength'>差</div>
            <div class='strength'>一般</div>
            <div class='strength'>强</div>
          </div>
        </div>

        <div id="re-iPasswordDiv" class="form-group">
          <label for="re-iPassword">请再次输入密码</label>
          <input type="password" class="form-control" id="re-iPassword" placeholder="请再次输入密码" name="re-ipassword" required="required" autofocus required maxlength="128">
          <p class="help-block" id="password2prompt"></p>
        </div>
        </div>
        <button type="submit" class="btn btn-primary btn-lg" id="resetBtn" name="reset" disabled>确定</button>
      </div>
    </form>
  </div>
</body>
</html>
HTML;
          echo $ret;
          exit();
    }
    $password = $_POST['ipassword'];
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
        if(updateResetUsers($email,$hashedPassword,sodium_bin2hex($salt),sodium_bin2hex($nonce))){
          $ret = <<<EOF
<script type="text/javascript">
window.top.location.href = "/index.html";
</script>
EOF;
        }else{
          $ret = "密码重置失败，请重试";
        }
  }

}else{
  $ret = "无效链接，请重试";
}
updateTokenCountInDb1($email);
echo $ret;