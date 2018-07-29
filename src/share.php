<?php

require 'auth.php';
require 'download.bo.php';
require 'share.bo.php';

$expire_hours = empty($_POST['expire_hours']) ? Config::$dftDldExpHours : (int)$_POST['expire_hours'];
$allowed_download_count = empty($_POST['allowed_download_count']) ? Config::$dftAllowedDldCount : (int)$_POST['allowed_download_count'];
$fid = empty($_POST['fid']) ? '' : (int)$_POST['fid'];
$sodium_hash = empty($_POST['fkey']) ? '' : $_POST['fkey'];

// TODO param validation

$uid = $_SESSION['uid'];

// validate authorization
if(validateUserFileOwnership($uid, $fid, $sodium_hash)) {
  // encrypt shared file
  list($err, $filename, $filesize, $decrypted_content) = download_file($fid, $uid, $_SESSION['sign'], $_SESSION['box'], $_SESSION['passphrase'], $_SESSION['nonce']);

  // $shareKey = sodium_bin2hex(random_bytes(Config::$shareKeyLen)); // 对称加密秘钥，不进行持久化保存，仅返回给前端用户一次
  $shareKey = random_bytes(SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES); // 32
  // $shareKeyHash = password_hash($shareKey, PASSWORD_DEFAULT); // TODO 用户下载该加密文件时需要获得明文分享码并在数据库中校验一致才可进行文件加密秘钥解密
  $shareKeyHash = sodium_crypto_pwhash_str(
            $shareKey,
            SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE,
            SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
        );
  $nonce = random_bytes(SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES); // 12，区分相同分享文件的随机值
  $enc_key = random_bytes(SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES); // 32,，对称加密秘钥，应妥善保存
  $enc_key_in_db = encryptFile($enc_key, $shareKey, 'enckey', $nonce); // TODO 保存到数据库中的已加密的分享文件加密秘钥
  $encryptedFile = encryptFile($decrypted_content, $enc_key, $filename, $nonce);
  $shareFilePath = getShareFilePath($uid, $sodium_hash);

  $shareFileRoot = dirname($shareFilePath);

  if(!is_dir($shareFileRoot)) {
    mkdir($shareFileRoot, 0755, true);
  }

  if(file_put_contents($shareFilePath, $encryptedFile) !== false) { // 分享的文件单独加密存储在区别于用户上传目录的另一个目录
    $ret = saveShareFileInfo($fid, $shareKeyHash, sodium_bin2hex($enc_key_in_db), $shareFilePath, sodium_bin2hex($nonce)); // 文件分享信息保存到数据库
    debug_log($ret, __FILE__, __LINE__);
    if(empty($err)) {
      $params = generateShareLink($fid, $sodium_hash, $expire_hours, $allowed_download_count, sodium_bin2hex($nonce));
      $ret = array('error' => '', 'url' => getUriRoot() . '/get.php' . $params, 'access_code' => sodium_bin2hex($shareKey));
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