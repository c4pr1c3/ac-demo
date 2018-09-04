<?php
//session_start();
//echo $_POST['userName'];
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
setupPageLayout2($_SERVER['REQUEST_METHOD'], $pageLayout);

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
     //echo 'post';
    resetPassword($_POST);  //重置密码
    setupPageLayout3($pageLayout);
    echo '密码修改成功';
  }
  if($_SERVER['REQUEST_METHOD'] ==='GET'){
     // echo 'get';
      if(isvalidReset($_GET['verify'],urldecode($_GET['name']))){

      }else{
          setupPageLayout3($pageLayout);
          echo '链接无效 ';
      }

}

?>

<div class="<?= $pageLayout['showRegFormOrNot'] ?>">
    <form action="changePassword2.php" method="post">
        <h1>中传放心传</h1>

        <div class="form-group" id="iuserNameDiv" >


                <div id="iPasswordDiv" class="form-group <?= $pageLayout['password-has-warning'] ?>">
                    <label for="iPassword">新口令</label>
                    <input type="password" class="form-control" id="iPassword" name="password" placeholder="请输入登录口令" oninput="chencklength()">
                    <p class="help-block" id ="password1"><?= $pageLayout['userPasswordMsg'] ?></p>
                    <script language='javascript' type='text/javascript'>
                        // FIXME Bad Coding Style
                        function scorePassword(pass) {
                            let score = 0;
                            if (!pass)
                                return score;

                            // award every unique letter until 5 repetitions
                            let letters = new Object();
                            for (let i=0; i<pass.length; i++) {
                                letters[pass[i]] = (letters[pass[i]] || 0) + 1;
                                score += 5.0 / letters[pass[i]];
                            }

                            // bonus points for mixing it up
                            let variations = {
                                digits: /\d/.test(pass),
                                lower: /[a-z]/.test(pass),
                                upper: /[A-Z]/.test(pass),
                                nonWords: /\W/.test(pass),
                            };

                            variationCount = 0;
                            for (let check in variations) {
                                variationCount += (variations[check] == true) ? 1 : 0;
                            }
                            score += (variationCount - 1) * 10;

                            return parseInt(score);
                        }

                        function chencklength() {
                            //if(true){
                            let pwd = document.getElementById("iPassword").value;

                            if (pwd.length >36 || pwd.length <6 ) {
                                $('#password1').text("口令长度限制为6-36位");
                                $('#iPasswordDiv').addClass('has-warning');
                                $('#registerBtn').prop('disabled', true);
                                // $("#iPassword").val("");
                            }
                            else {
                                // input is valid -- reset the error message
                                var score = scorePassword(pwd);
                                if(score <60 ){
                                    $('#password1').text("密码强度过弱");
                                    $('#iPasswordDiv').addClass('has-warning');
                                    $('#registerBtn').prop('disabled', true);
                                } else {
                                    if(score <80) {
                                        $('#password1').text('密码强度一般 可用');
                                    }else{
                                        $('#password1').text('密码强度高 可用');
                                    }
                                    $('#iPasswordDiv').removeClass('has-warning');
                                    $('#registerBtn').prop('disabled', false);

                                }
                            }
                        }

                    </script>




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
                                $('#registerBtn').prop('disabled', true);
                            } else {
                                // input is valid -- reset the error message
                                if()
                                    $('#password2prompt').text('');
                                $('#iPasswordDiv').removeClass('has-warning');
                                $('#re-iPasswordDiv').removeClass('has-warning');
                                $('#registerBtn').prop('disabled', false);
                            }
                        }

                    </script>
                    <input type="text" class="hidden"  name="verify"   value="<?= $_GET['verify'] ?>">
                    <input type="text" class="hidden"  name="username"   value="<?= urldecode($_GET['name'])?>">
                </div>



                <button type="submit" class="btn btn-primary btn-lg" id="registerBtn" name="register" disabled>确认修改</button>
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
        <a class="btn btn-link btn-lg" href="register.php" role="button"><?= $pageLayout['retMsg'] ?>注册</a>
        <?php
    } ?>
</div>

<script src="node_modules/jquery/dist/jquery.min.js"></script>
<!-- Include all compiled plugins (below), or include individual files as needed -->
<script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
</body>

</html>
