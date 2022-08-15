<?php
ob_flush();

require 'auth.php';
require 'checkget.bo.php';
$fid = empty($_GET['fid']) ? '' : $_GET['fid'];

$result=checkGetSha256($fid);

?>


<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>中传放心传 - 检验</title>

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
<div>
    <?php if($result==='ok') { ?>
<h2>核验成功，文件完整！</h2>
<br>
<a class="btn btn-link btn-lg" href="register.php" role="button">如果没有账号，您可以注册</a>
<br>
<a class="btn btn-link btn-lg" href="index.html" role="button">如果已有账号，您可以登录</a>
<?php session_destroy();?>
<?php } else{?>
    <h2>核验失败，可能是文件未下载或者文件传输时损坏！</h2>
    <?php } ?>
</div>
</body>

</html>

<?php 

ob_end_flush();
?>

