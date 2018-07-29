<?php

require_once 'config.php';

function debug_log($msg, $file = '', $line = '', $output = '') {
    if(!is_file($output)) {
        $output = Config::$debugLogFile;
    }

    $formatedMsg = date('Y-m-d H:i:s');
    if(!empty($file)) {
        $formatedMsg  = sprintf("%s %s", $formatedMsg, $file);
    }
    if(!empty($line)) {
        $formatedMsg  = sprintf("%s %d", $formatedMsg, $line);
    }
    $formatedMsg = sprintf("%s - %s\n", $formatedMsg, $msg);

    file_put_contents($output, $formatedMsg, FILE_APPEND);
}

function getPubAndPrivKeys($userName, $password) {
  // 以下是最终使用的公钥证书中可以被查看的Distinguished Name（简称：DN）信息
$dn = array(
    "countryName" => "CN",
    "stateOrProvinceName" => "Beijing",
    "localityName" => "Chaoyang",
    "organizationName" => "CUC",
    "organizationalUnitName" => "CS",
    "commonName" => "$userName",  // https应用务必确保和你要使用的站点域名匹配
    "emailAddress" => "$userName"
);

$pk_config = array(
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
    'digest_alg' => 'sha256',
);

// 产生公私钥对一套
// 等价openssl命令
// openssl genrsa -out server.key 2048
$privkey = openssl_pkey_new($pk_config);

// 查看生成的server.key的内容
// openssl x509 -in server.key -text -noout
// 给服务器安装使用的证书一般不使用口令保护，避免每次重启服务器时需要人工输入口令
openssl_pkey_export($privkey, $pkeyout, $password);  //将privatekey 用 password 存储以后  输出到 pkout

// 制作CSR文件：Certificate Signing Request
// 等价openssl命令
// openssl req -new -key server.key -out server.csr
// 查看CSR文件内容
// openssl req -text -noout -in server.csr
$csr = openssl_csr_new($dn, $privkey, $pk_config);

// 对CSR文件进行自签名（第2个参数设置为null，否则可以设置为CA的证书路径），设置证书有效期：365天
// FIXME 证书有效期根据产品设计需要，可能需要保存到数据库中，定期更换用户的公私钥对
// TODO 如果用户的公私钥对支持定期更换，则历史加密文件的对称加密秘钥在每次用户个人公私钥更换时需要先用历史秘钥解密一次
// 再用新秘钥重新加密后保存
$sscert = openssl_csr_sign($csr, null, $privkey, 365, $pk_config);
// 以上所有代码的等价单行openssl命令
// openssl req -x509 -newkey rsa:2048 -keyout server.key -out server.crt -days 365

// 如果不再需要使用，应尽快释放私钥资源，防止针对服务器内存明文私钥数据的直接非法访问

file_put_contents('debug.log', "user first register's privatekey ".$privkey."\n", FILE_APPEND);
openssl_pkey_free($privkey);

/*
 * ref: https://www.sslshopper.com/ssl-converter.html
 * PEM格式是CA颁发机构最常使用的证书格式。PEM证书文件的常用扩展名包括：.pem, .crt, .cer和.key。
 * PEM格式文件内容采用Base64编码为ASCII文本，并使用
 * "-----BEGIN CERTIFICATE-----" 和 "-----END CERTIFICATE-----"包围编码之后的文本内容。
 * 服务器证书、中间CA证书、私钥都可以使用PEM格式存储。
 */
openssl_x509_export($sscert, $certout);

return array(
  'pubkey' => $certout,
  'privkey' => $pkeyout
);

}


function encryptFile($input_file, $enc_key, $filename) {
    if(!@is_file($input_file)) {
        $plaintext = $input_file;
    } else {
        $plaintext = file_get_contents($input_file); // FIXME 对于大文件的加密，这种一次性读取明文的方式对内存的压力太大，应分片读取
    }

    $method = "aes-256-cbc"; // print_r(openssl_get_cipher_methods());
    $enc_options = 0;
    $iv_length = openssl_cipher_iv_length($method);
    $iv = openssl_random_pseudo_bytes($iv_length);

    $ciphertext = openssl_encrypt($plaintext, $method, $enc_key, $enc_options, $iv);   // 加密从文件中读取的内容

    // 定义我们“私有”的密文结构
    $saved_ciphertext = sprintf('%s$%d$%s$%s$%s', $method, $enc_options, bin2hex($iv), $filename, $ciphertext);

    return $saved_ciphertext;
}

function decryptFile($saved_ciphertext, $enc_key) {

    if(@is_file($saved_ciphertext)) {
        $saved_ciphertext = file_get_contents($saved_ciphertext);
    }
    // 检查密文格式是否正确、符合我们的定义
    if(preg_match('/.*$.*$.*$.*$.*/', $saved_ciphertext) !== 1) {
        return false;
    }

    // 解析密文结构，提取解密所需各个字段
    //list($extracted_method, $extracted_enc_options, $extracted_iv, $extracted_filename, $extracted_ciphertext) = explode('$', $saved_ciphertext); 
    $decryptedArr = explode('$', $saved_ciphertext); 

    return openssl_decrypt($decryptedArr[4], $decryptedArr[0], $enc_key, $decryptedArr[1], hex2bin($decryptedArr[2]));

}

function getEncryptFileForShare_sign ($saved_ciphertext) {

    if(@is_file($saved_ciphertext)) {
        $saved_ciphertext = file_get_contents($saved_ciphertext);
    }
    // 检查密文格式是否正确、符合我们的定义
    if(preg_match('/.*$.*$.*$.*$.*/', $saved_ciphertext) !== 1) {
        return false;
    }

    return $saved_ciphertext;
}

function getPagination($number, $pageSize) {

    $start = ($number - 1) * $pageSize;

    return $start;
}

function getUploadFilePath($uid, $sha256, $create_time) {
    $date = date_format(date_create($create_time), 'Y/m/d');
    $uploaddir = sprintf("%s/%s/%s", Config::$uploadRoot, $uid, $date);
    $uploadfile = sprintf("%s/%s.enc", $uploaddir, $sha256);

    return $uploadfile;
}

function getMasterKey(&$masterKey) {
    $masterKey = getenv('AC_SHARE_MASTER_KEY');
    if(empty($masterKey)) {
        return false;
    }

    return true;
}

function getShareFilePath($uid, $sha256) {
    $datetime = date('Y-m-d H:i:s');
    $date = date_format(date_create(), 'Y/m/d/H/i/s');
    $shareDir = sprintf("%s/%s/%s", Config::$shareRoot, $uid, $date);
    $shareFile = sprintf("%s/%s.enc", $shareDir, $sha256);

    return $shareFile;
}

function generateRandomString($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getUriRoot() {
    $server_name = $_SERVER['SERVER_NAME'];
    $port = '';
    switch($_SERVER['SERVER_PORT']) {
    case '80':
        $protocol = 'http';
        break;
    case '443':
        $protocol = 'https';
        break;
    default:
        $protocol = 'http';
        $port = $_SERVER['SERVER_PORT'];
        break;
    } 
    if(empty($port)) {
        $uriRoot = sprintf('%s://%s', $protocol, $server_name);
    } else {
        $uriRoot = sprintf('%s://%s:%s', $protocol, $server_name, $port);
    }

    return $uriRoot;
}

