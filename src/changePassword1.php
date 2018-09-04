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
</head>

<body>

<?php
require_once 'changePassword.bo.php';

// 根据客户端请求类型是GET还是POST，分别设置页面中不同div是否可见
setupPageLayout($_SERVER['REQUEST_METHOD'], $pageLayout);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //doRegister($_POST, $pageLayout);
    //doResetPassword($_POST,$pageLayout);
    test($_POST);
}

?>

<div class="<?= $pageLayout['showRegFormOrNot'] ?>">
    <form action="changePassword1.php" method="post">
        <h1>中传放心传</h1>

        <div class="form-group" id="iuserNameDiv" >
            <div class="form-group <?= $pageLayout['userName-has-warning'] ?>">
                <label for="iuserName">用户名</label>
                <input type="text" class="form-control" id="iuserName" name="userName"  onblur="checkUsername()"  oninput="checkUsername()" placeholder="请输入用户名" autofocus required maxlength="128" value="<?= $_SESSION['emailName'] ?>">
                <p class="help-block" id ="name1"><?= $pageLayout['userNameMsg'] ?></p>

                <script language='javascript' type='text/javascript'>  //前端检查用户名中是否有不合法字符
                    // FIXME Bad Coding Style
                    function checkUsername()
                    {
                        //正则表达式
                        let reg = new RegExp("^[A-Za-z0-9\u4e00-\u9fa5]+$");//获取输入框中的值
                        let username = document.getElementById("iuserName").value.trim();//判断输入框中有内容
                        if(!reg.test(username))
                        {
                            $('#name1').text('用户名输入不合法');
                            $('#iuserNameDiv').addClass('has-warning');
                            $('#registerBtn').prop('disabled', true);
                            $("#iuserName").val("");
                        }
                        else {
                            $('#name1').text('');
                            $('#iuserNameDiv').removeClass('has-warning');
                            $('#registerBtn').prop('disabled', false);
                        }
                    }
                </script>

            </div>

            <div class="form-group">
                <div class="form-group <?= $pageLayout['emailName-has-warning'] ?>">
                    <label for="iemailName">邮箱</label>
                    <input type="email" class="form-control" id="iemailName" name="emailName" placeholder="请输入有效邮箱" autofocus required maxlength="128" value="<?= $_SESSION['emailName'] ?>">
                    <p class="help-block"><?= $pageLayout['emailNameMsg'] ?></p>

                </div>





                <button type="submit" class="btn btn-primary btn-lg" id="registerBtn" name="register" disabled>提交</button>
            </div>
    </form>
</div>


<div class="<?= $pageLayout['showRegMsgOrNot'] ?>">
    <?php

    if($pageLayout['has-warning'] === false) {
        ?>
        <a class="btn btn-link btn-lg" href="index.html" role="button"><?= $pageLayout['retMsg'] ?>登录</a>
        <?php
    }
    else {
        ?>
        <a class="btn btn-link btn-lg" href="changePassword.php" role="button"><?= $pageLayout['retMsg'] ?>注册</a>
        <?php
    } ?>
</div>

<script src="node_modules/jquery/dist/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
</body>

</html>
