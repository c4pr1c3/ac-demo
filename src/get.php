<?php

require 'get.bo.php';

$fid    = empty($_GET['id']) ? '' : $_GET['id'];
$key    = empty($_GET['key']) ? '' : $_GET['key'];
$expire = empty($_GET['expire']) ? '' : $_GET['expire'];
$count  = empty($_GET['count']) ? '' : $_GET['count'];
$nonce  = empty($_GET['nonce']) ? '' : $_GET['nonce'];
$token  = empty($_GET['token']) ? '' : $_GET['token'];

if(empty($_POST['access_code'])) {
} else {
    $access_code = $_POST['access_code'];
}

if(validateShareLink($fid, $key, $expire, $count, $token, $nonce)) {
    // 检查是否过期
    $now = time();
    if($now > $expire) {
        $ret = Prompt::$msg['share_file_expired'];
        echo $ret;
        return;
    }

    $fileShareInfo = getFileShareInfo($fid, $nonce);
    if(!empty($fileShareInfo)) {
        if($fileShareInfo['dcount'] >= $count) { // 检查下载次数是否超出分享限制
            $ret = Prompt::$msg['share_file_exceed_down_limit'];
            echo $ret;
            return;
        }

        $fileInfoHtml = sprintf('%s 分享的 %s', $fileShareInfo['uname'], htmlspecialchars($fileShareInfo['fname']));

        if(empty($_POST['access_code'])) {
            $ret = <<<HTML
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
</head>

<body>
  <div id="shareFormDiv" class="container">
    <form id="shareForm" method="post">
      <h1>中传放心传</h1>
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">文件基本信息</h3>
            </div>
            <div class="panel-body" id="fileinfo">$fileInfoHtml</div>
        </div>
        <div class="form-group">
          <label for="access_code">请输入验证码</label>
          <input type="text" class="form-control" id="access_code" name="access_code" placeholder="请输入验证码" autofocus required maxlength="128">
        </div>
        <button type="submit" class="btn btn-primary btn-lg" name="login">确认下载</button>
      </div>
    </form>
  </div>

  <script src="node_modules/jquery/dist/jquery.min.js"></script>
  <script src="node_modules/bootstrap/dist/js/bootstrap.min.js"></script>
</body>

</html>
HTML;
            echo $ret;
            exit();
        }

        if(sodium_crypto_pwhash_str_verify($fileShareInfo['sharekey'], sodium_hex2bin($access_code))) {
            // 用户提供的access_code是正确的
            $enc_key = decryptFile('enckey', sodium_hex2bin($fileShareInfo['nonce']), sodium_hex2bin($fileShareInfo['enckey']),sodium_hex2bin($access_code));
            $decrypted_content = decryptFile($fileShareInfo['fname'], sodium_hex2bin($fileShareInfo['nonce']), file_get_contents($fileShareInfo['filepath']), $enc_key);

            if($decrypted_content === false) {
                $error = Prompt::$msg['decrypt_oops'];
            }

            if(empty($error)) {
                updateDownloadCount($fid, $nonce);
                $filename = $fileShareInfo['fname'];
                $filesize = $fileShareInfo['size'];
                header('Content-Description: Decrypted File Download');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Transfer-Encoding: binary');
                header('Expires: 0');
                header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                header('Pragma: private');
                header('Content-Length: ' . $filesize); 
                ob_clean();
                flush();

                echo $decrypted_content;
                exit();
            } else {
                $ret = Prompt::$msg['share_file_invalid_access_code']; 
            }
        } else {
            $ret = Prompt::$msg['share_file_invalid_access_code']; 
        }
    } else {
        $ret = Prompt::$msg['share_file_not_found']; 
    }


} else {
    $ret = <<<EOF
<script type="text/javascript">
window.top.location.href = "/index.html";
</script>
EOF;
}

echo $ret;

