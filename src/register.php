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
require_once 'register.bo.php';

// 根据客户端请求类型是GET还是POST，分别设置页面中不同div是否可见
setupPageLayout($_SERVER['REQUEST_METHOD'], $pageLayout);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    doRegister($_POST, $pageLayout);
}

?>

  <div class="<?= $pageLayout['showRegFormOrNot'] ?>">
    <form action="register.php" method="post" onsubmit='document.charset='utf-8';'>
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
                        let reg = new RegExp("^[A-Za-z0-9]|[\u3400-\u4DB5\u4E00-\u9FEF\uFA0E\uFA0F\uFA11\uFA13\uFA14\uFA1F\uFA21\uFA23\uFA24\uFA27-\uFA29]|[\uD840-\uD868\uD86A-\uD86C\uD86F-\uD872\uD874-\uD879][\uDC00-\uDFFF]|\uD869[\uDC00-\uDED6\uDF00-\uDFFF]|\uD86D[\uDC00-\uDF34\uDF40-\uDFFF]|\uD86E[\uDC00-\uDC1D\uDC20-\uDFFF]|\uD873[\uDC00-\uDEA1\uDEB0-\uDFFF]|\uD87A[\uDC00-\uDFE0]+$");//获取输入框中的值
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


        <div id="iPasswordDiv" class="form-group <?= $pageLayout['password-has-warning'] ?>">
          <label for="iPassword">口令</label>
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
        </div>



        <button type="submit" class="btn btn-primary btn-lg" id="registerBtn" name="register" disabled>注册</button>
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
