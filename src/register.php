<?php
session_start();
?>
<!DOCTYPE html>
<html lang="zh">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>中传放心传 - 注册</title>

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

<?php
require_once 'register.bo.php';

// 根据客户端请求类型是GET还是POST，分别设置页面中不同div是否可见
setupPageLayout($_SERVER['REQUEST_METHOD'], $pageLayout);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    doRegister($_POST, $pageLayout);
}

?>

<script language='javascript' type='text/javascript'> 
    window.onload = function(){
      var checkRegister = function(){
        var iPassword = document.getElementById('iPassword').value;
        var reiPassword = document.getElementById('re-iPassword').value;
        var password2prompt = document.getElementById('password2prompt');
        var iPasswordDiv = document.getElementById('iPasswordDiv');
        var reiPasswordDiv = document.getElementById('re-iPasswordDiv');
        var registerBtn = document.getElementById('registerBtn');
        if(iPassword.length != 0){
          if (iPassword != reiPassword) {
            password2prompt.innerHTML = "两次输入的口令不一致";
            iPasswordDiv.classList.add('has-warning');
            reiPasswordDiv.classList.add('has-warning');
            registerBtn.disabled = true;
          } else {
              // input is valid -- reset the error message
              password2prompt.innerHTML = '';
              iPasswordDiv.classList.remove('has-warning');
              reiPasswordDiv.classList.remove('has-warning');
              registerBtn.disabled = false;
          }
        } 
      };
      document.getElementById("iPassword").addEventListener('input', checkRegister, false);
      document.getElementById("re-iPassword").addEventListener('input', checkRegister, false);
      document.getElementById("re-iPassword").addEventListener('blur', checkRegister, false);
  
      //给密码输入框 注册键放开事件
      var textInput = document.getElementById("iPassword");
      textInput.onkeyup=function(){
        var pwd=this.value;
        var level = 0;
        if(RegExp("[A-Z]").test(pwd) && RegExp("[a-z]").test(pwd) && RegExp("[0-9]").test(pwd) && pwd.length>7){
          level = 1;
          if(RegExp("[^A-Za-z0-9]").test(pwd)){
            level = 2;
          }
        }

        var blocks=document.getElementsByClassName("strength"); 
        for(var index=0;index<level+1;index++){
          blocks[index].style.backgroundColor="orange";
        }
        for(level++;level<blocks.length;level++){
          blocks[level].style.backgroundColor="";
        }

        if(pwd.length == 0){
          for(var index=0;index<blocks.length;index++){
            blocks[index].style.backgroundColor="";
          }
        }

      }
  }    
  </script>


  <div class="<?= $pageLayout['showRegFormOrNot'] ?>">
    <form action="register.php" method="post">
      <h1>中传放心传</h1>
      <div class="form-group">
        <div class="form-group <?= $pageLayout['userName-has-warning'] ?>">
          <label for="iUserName">用户名</label>
          <input type="text" class="form-control" id="iUserName" name="userName" placeholder="请输入用户名" autofocus required maxlength="128" value="<?= $_SESSION['userName'] ?>">
          <p class="help-block"><?= $pageLayout['userNameMsg'] ?></p>
        </div>
        <div class="form-group <?= $pageLayout['userEmail-has-warning'] ?>">
          <label for="iUserEmail">注册邮箱</label>
          <input type="email" class="form-control" id="iUserEmail" name="userEmail" placeholder="请输入邮箱" autofocus required maxlength="128" value="<?= $_SESSION['userEmail'] ?>">
          <p class="help-block"><?= $pageLayout['userEmailMsg'] ?></p>
        </div>
        <div id="iPasswordDiv" class="form-group <?= $pageLayout['password-has-warning'] ?>">
          <label for="iPassword">口令</label>
          <input type="password" class="form-control" id="iPassword" name="password" placeholder="请输入登录口令">
          <div id='Password-strength'>
            <div class='strength'>差</div>
            <div class='strength'>一般</div>
            <div class='strength'>强</div>
          </div>
          <p class="help-block"><?= $pageLayout['userPasswordMsg'] ?></p>
        </div>
        <div id="re-iPasswordDiv" class="form-group <?= $pageLayout['password-has-warning'] ?>">
          <label for="re-iPassword">确认口令</label>
          <input type="password" class="form-control" id="re-iPassword" name="password2" placeholder="请再次输入登录口令" >
          <p class="help-block" id="password2prompt"></p>

        </div>
        <button type="submit" class="btn btn-primary btn-lg" id="registerBtn" name="register" disabled>注册</button>
      </div>
    </form>
  </div>

  <div class="<?= $pageLayout['showRegMsgOrNot'] ?>">
    <?php if($pageLayout['has-warning'] === false) { ?>
    <a class="btn btn-link btn-lg" href="index.html" role="button"><?= $pageLayout['retMsg'] ?>登录</a>
    <?php } else {?>
    <a class="btn btn-link btn-lg" href="register.php" role="button"><?= $pageLayout['retMsg'] ?>注册</a>
    <?php } ?>
  </div>

  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
</body>

</html>
