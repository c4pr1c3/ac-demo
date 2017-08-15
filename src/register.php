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
</head>

<body>

<?php
session_start();
require_once 'register.bo.php';

// 根据客户端请求类型是GET还是POST，分别设置页面中不同div是否可见
setupPageLayout($_SERVER['REQUEST_METHOD'], $pageLayout);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    doRegister($_POST, $pageLayout);
}

?>

  <div class="<?= $pageLayout['showRegFormOrNot'] ?>">
    <form action="register.php" method="post">
      <h1>中传放心传</h1>
      <div class="form-group">
        <div class="form-group <?= $pageLayout['userName-has-warning'] ?>">
          <label for="iUserName">用户名</label>
          <input type="email" class="form-control" id="iUserName" name="userName" placeholder="请输入用户名" autofocus required maxlength="128" value="<?= $_SESSION['userName'] ?>">
          <p class="help-block"><?= $pageLayout['userNameMsg'] ?></p>
        </div>
        <div id="iPasswordDiv" class="form-group <?= $pageLayout['password-has-warning'] ?>">
          <label for="iPassword">口令</label>
          <input type="password" class="form-control" id="iPassword" name="password" placeholder="请输入登录口令" oninput="checkRegister()">
          <p class="help-block"><?= $pageLayout['userPasswordMsg'] ?></p>
        </div>
        <div id="re-iPasswordDiv" class="form-group <?= $pageLayout['password-has-warning'] ?>">
          <label for="re-iPassword">确认口令</label>
          <input type="password" class="form-control" id="re-iPassword" name="password2" placeholder="请再次输入登录口令" onblur="checkRegister()" oninput="checkRegister()">
          <p class="help-block" id="password2prompt"></p>
          <script language='javascript' type='text/javascript'>
          // FIXME Bad Coding Style
    function checkRegister() {
        if ($('#iPassword').val() != $('#re-iPassword').val()) {
            $('#password2prompt').text('<?=Prompt::$msg["password_not_same"]?>');
            $('#iPasswordDiv').addClass('has-warning');
            $('#re-iPasswordDiv').addClass('has-warning');
            $('#registerBtn').prop('disabled', true);;
        } else {
            // input is valid -- reset the error message
            $('#password2prompt').text('');
            $('#iPasswordDiv').removeClass('has-warning');
            $('#re-iPasswordDiv').removeClass('has-warning');
            $('#registerBtn').prop('disabled', false);;
        }
    }
</script>
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
