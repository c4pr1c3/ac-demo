
<!DOCTYPE html>
<html lang="zh">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>中传放心传 - 注册</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="node_modules/bootstrap/dist/css/bootstrap.min.css">


</head>

<body>

<?php
require_once 'active.bo.php';

// 根据客户端请求类型是GET还是POST，分别设置页面中不同div是否可见
//setupPageLayout($_SERVER['REQUEST_METHOD'], $pageLayout);

$namep = urldecode($_GET['name']);
$verifyp = urldecode($_GET['verify']);



//var_dump($_GET['name']);
//var_dump(iconv("gbk","utf-8", $_GET['name'] ));
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    doRegisterActive($_POST, $pageLayout);
    //echo $_POST['userName'];
    //  echo $_POST['verify'];
}

?>

<div  class="<?= $pageLayout['showRegFormOrNot'] ?>">
    <form action="active.php" method="post">
        <h1>中传放心传</h1>


        <div class="form-group">
            <div >
                <input type="text" class="hidden"  name="userName"   value="<?= $namep?>">  <!-- 使用input设置表单中 userName 的数值为 url链接中的 name -->
                <input type="text" class="hidden"  name="verify"   value="<?= $verifyp ?>">  <!-- 同上 -->
            </div>
        </div>


            <button type="submit" class="btn btn-primary btn-lg" >确认激活</button>

    </form>
</div>
</body>
</html>