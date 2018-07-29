<?php

require 'auth.php';
require 'download.bo.php';
require 'share.bo.php';

$expire_hours = empty($_POST['expire_hours']) ? Config::$dftDldExpHours : (int)$_POST['expire_hours'];
$allowed_download_count = empty($_POST['allowed_download_count']) ? Config::$dftAllowedDldCount : (int)$_POST['allowed_download_count'];
$fid = empty($_POST['fid']) ? '' : (int)$_POST['fid'];
$sha256 = empty($_POST['fkey']) ? '' : $_POST['fkey'];

// TODO param validation

$uid = $_SESSION['uid'];

// validate authorization
if(validateUserFileOwnership($uid, $fid, $sha256)) {
  // encrypt shared file
  list($err, $filename, $filesize, $saved_c) = download_c_file($fid, $uid);
	download_c_file($fid,$uid);
  $shareKey = bin2hex(openssl_random_pseudo_bytes(Config::$shareKeyLen)); // 对称加密秘钥，不进行持久化保存，仅返回给前端用户一次
  $shareKeyHash = password_hash($shareKey, PASSWORD_DEFAULT); // TODO 用户下载该加密文件时需要获得明文分享码并在数据库中校验一致才可进行文件加密秘钥解密

  $nonce = generateRandomString(Config::$nonceLen); // 区分相同分享文件的随机值
  $enc_key = base64_encode(openssl_random_pseudo_bytes(Config::$symmetricEncKeyLen)); // 对称加密秘钥，应妥善保存
  $enc_key_in_db = encryptFile($enc_key, $shareKey, 'enckey'); // TODO 保存到数据库中的已加密的分享文件加密秘钥
  $encryptedFile = encryptFile($saved_c, $enc_key, $filename);

  $shareFilePath = getShareFilePath($uid, $sha256);

  $shareFileRoot = dirname($shareFilePath);

  if(!is_dir($shareFileRoot)) {
    mkdir($shareFileRoot, 0755, true);
  }

  if(file_put_contents($shareFilePath, $encryptedFile) !== false) { // 分享的文件单独加密存储在区别于用户上传目录的另一个目录
    $ret = saveShareFileInfo($fid, $shareKeyHash, $enc_key_in_db, $shareFilePath, $nonce); // 文件分享信息保存到数据库
    debug_log($ret, __FILE__, __LINE__);
    if(empty($err)) {
      $params = generateShareLink($fid, $sha256, $expire_hours, $allowed_download_count, $nonce);
      $ret = array('error' => '', 'url' => getUriRoot() . '/get.php' . $params, 'access_code' => $shareKey);
    } else {
      $ret = array('error' => $err);
    }
  } else {
    $ret = array('error' => Prompt::$msg['share_file_failed_in_create_file']);
  }
} else {
  $ret = array('error' => Prompt::$msg['file_ownership_mismatch']);
}

echo json_encode($ret);

