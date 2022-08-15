<?php
ob_start();
session_start();
?>
<!DOCTYPE html>
<html lang="zh">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>中传放心传 - 找回密码</title>

  <!-- Bootstrap -->
  <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">

  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.2/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>

<?php
require_once 'lose.bo.php';

setupPageLayout($_SERVER['REQUEST_METHOD'], $pageLayout);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  
  doLoseRegister($_POST, $pageLayout);
  
}
?>

<div class="<?= $pageLayout['showLoseFormOrNot'] ?>">
    <form action="lose.php" method="post">
      <h1>中传放心传</h1>
      <div class="form-group">
        <div class="form-group <?= $pageLayout['userName-has-warning'] ?>">
          <label for="iUserName">用户名</label>
          <input onkeyup="value=value.replace(/[^\w\u4E00-\u9FA5]/g,'')"  class="form-control" id="iUserName" name="userName" placeholder="请输入用户名" autofocus required maxlength="128" value="<?= $_SESSION['userName'] ?>">
          <p class="help-block"><?= $pageLayout['userNameMsg'] ?></p>
        </div>
        <div id="iPasswordDiv" class="form-group <?= $pageLayout['password-has-warning'] ?>">
          <label for="iPassword">输入新口令</label>
          <input type="password" class="form-control" id="iPassword" name="password" placeholder="请输入新口令" oninput="checkLoseRegister()">
          <p class="help-block"><?= $pageLayout['userPasswordMsg'] ?></p>
        </div>
        <div id="re-iPasswordDiv" class="form-group <?= $pageLayout['password-has-warning'] ?>">
          <label for="re-iPassword">确认新口令</label>
          <input type="password" class="form-control" id="re-iPassword" name="password2" placeholder="请确认新口令" onblur="checkLoseRegister()" oninput="checkLoseRegister()">
          <p class="help-block" id="password2prompt"></p>
          <div class="form-group <?= $pageLayout['pin-has-warning'] ?>">
          <label for="iPin">PIN码</label>
          <input type="password" class="form-control" id="ipin" name="pin" placeholder="请输入您的PIN码"  oninput="if(value.length>100)value=value.slice(0,10)" required>
          <p class="help-block"><?= $pageLayout['userPinMsg'] ?></p>
        </div>

        <div class="form-group <?= $pageLayout['code-has-warning'] ?>">
          <p><label for="iCode">验证码&emsp;&emsp;</label>
          <img  id="captcha_img" border="1" src="./captcha.php?r=<?php echo rand(); ?>" alt="" width="100" height="50">
				
				<a href="javascript:void(0)" onclick="document.getElementById('captcha_img').src='./captcha.php?r='+Math.random() " style="float:right ;font-size:20px">点击更换验证码</a>
			</p>
          <input type="text" class="form-control" id="icode"  name="code" placeholder="验证码" minlength="4" oninput="if(value.length>4)value=value.slice(0,4)" required>
          <p class="help-block"><?= $pageLayout['userCodeMsg'] ?></p>
        </div>
          <script language='javascript' type='text/javascript'>
        function checkLoseRegister() {
        if ($('#iPassword').val() != $('#re-iPassword').val()) {
            $('#password2prompt').text('<?=Prompt::$msg["password_not_same"]?>');
            $('#iPasswordDiv').addClass('has-warning');
            $('#re-iPasswordDiv').addClass('has-warning');
            $('#loseBtn').prop('disabled', true);;
        } else {
            // input is valid -- reset the error message
            $('#password2prompt').text('');
            $('#iPasswordDiv').removeClass('has-warning');
            $('#re-iPasswordDiv').removeClass('has-warning');
            $('#loseBtn').prop('disabled', false);;
        }
    }
    </script>
        </div>
        <button type="submit" class="btn btn-primary btn-lg" id="loseBtn" name="lose" disabled>修改密码</button>
      </div>
    </form>
  </div>

  <div class="<?= $pageLayout['showLoseMsgOrNot'] ?>">

    <?php if($pageLayout['has-warning'] === false) { ?>
    <a class="btn btn-link btn-lg" href="index.html" role="button"><?= $pageLayout['loseMsg'] ?>登录</a>
    <?php } else {?>
    <a class="btn btn-link btn-lg" href="lose.php" role="button"><?= $pageLayout['loseMsg'] ?>找回密码</a>
    <?php } ?>
  </div>

  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
</body>
</html>
<?php 
ob_end_flush();
?>