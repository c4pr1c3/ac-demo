<?php
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
require_once 'find.bo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ret = doCheck($_POST);
    echo $ret;
    return;
}

?>

  <div class="<?= $pageLayout['showRegFormOrNot'] ?>">
    <form action="find.php" method="post">
      <h1>找回密码</h1>
      <div class="form-group">
        <div class="form-group <?= $pageLayout['userEmail-has-warning'] ?>">
          <label for="iUserEmail">电子邮箱</label>
          <input type="email" class="form-control" id="iEmail" name="Email" placeholder="请输入已注册电子邮箱" autofocus required maxlength="128" value="<?= $_SESSION['Email'] ?>">
          <p class="help-block"><?= $pageLayout['EmailMsg'] ?></p>
        </div>
        <button type="submit" class="btn btn-primary btn-lg" id="findBtn" name="find">找回密码</button>
      </div>
    </form>
  </div>
  
  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <!-- Include all compiled plugins (below), or include individual files as needed -->
  <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
</body>

</html>