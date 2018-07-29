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

function getKeyPairs($salt, $password){
    $out_len = SODIUM_CRYPTO_SIGN_SEEDBYTES; //32
    $seed = sodium_crypto_pwhash(
        $out_len,
        $password,
        $salt,
        SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, //4
        SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE //33554432
    );
    
    $encrypt_kp = sodium_crypto_box_seed_keypair($seed);
    $sign_kp = sodium_crypto_sign_seed_keypair($seed);

    return array('box' => $encrypt_kp, 'sign' => $sign_kp);
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
$privkey = openssl_pkey_new($pk_config); //新的私钥

// 查看生成的server.key的内容
// openssl x509 -in server.key -text -noout
// 给服务器安装使用的证书一般不使用口令保护，避免每次重启服务器时需要人工输入口令
openssl_pkey_export($privkey, $pkeyout, $password); // 用口令加密私钥并导出到$pkeyout

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
  'pubkey' => $certout, //字符串形式的证书
  'privkey' => $pkeyout //字符串形式的私钥
);

}

// https://stackoverflow.com/questions/39904999/converting-string-in-php-to-be-used-as-file-name-without-stripping-special-cha#
function base64_safe_encode($input, $stripPadding = false)
{
    $encoded = strtr(base64_encode($input), '+/=', '-_~');

    return ($stripPadding) ? str_replace("~","",$encoded) : $encoded;
}

function base64_safe_decode($input)
{
    return base64_decode(strtr($input, '-_~', '+/='));
}

function encryptFile($input_file, $secret_key, $filename, $nonce){
    // 判断是否是文件
    try{
        $re = @is_file($input_file);
        if($re){
            $plaintext = '';
            $chunk_size = 4096;
            $handle = fopen($input_file, "rb") or die("Couldn't get handle");
            if($handle){
                while(!feof($handle)){
                    $buffer = fread($handle, $chunk_size);
                    $plaintext .= $buffer;
                }
            }
            fclose($handle);
            if (sodium_crypto_aead_aes256gcm_is_available()) {
                $ciphertext = sodium_crypto_aead_aes256gcm_encrypt(
                    $plaintext,
                    $filename,
                    $nonce,
                    $secret_key
                );
            }
        }
        else{
            if (sodium_crypto_aead_aes256gcm_is_available()) {
                 $ciphertext = sodium_crypto_aead_aes256gcm_encrypt(
                    $input_file,
                    $filename,
                    $nonce,
                    $secret_key
                );
            }
        }
    } catch(Exception $e){
        return "error";
    }
   
    return $ciphertext;
}

function decryptFile($filename, $nonce, $encrypted_file, $secret_key) {
    if (sodium_crypto_aead_aes256gcm_is_available()) {
        $decrypted = sodium_crypto_aead_aes256gcm_decrypt(
            $encrypted_file, // ciphertext
            $filename, // ad
            $nonce, // nonce
            $secret_key
        );
        if ($decrypted === false) {
            throw new Exception("Bad ciphertext");
        }
    }

    return $decrypted;
}

function hashFileSodium($input_file){
    if(!is_file($input_file)) {
        return false;
    } else {
        $content = file_get_contents($input_file);
        $h = sodium_crypto_generichash($content);

        return $h;
    }  
}

function getPagination($number, $pageSize) {

    $start = ($number - 1) * $pageSize;

    return $start;
}

function getUploadFilePath($uid, $sodium_hash, $create_time) {
    $date = date_format(date_create($create_time), 'Y/m/d');

    $uploaddir = sprintf("%s/%s/%s", Config::$uploadRoot, $uid, $date);
    $uploadfile = sprintf("%s/%s.enc", $uploaddir, $sodium_hash);

    return $uploadfile;
}

function getMasterKey(&$masterKey) {
    $masterKey = getenv('AC_SHARE_MASTER_KEY');
    if(empty($masterKey)) {
        return false;
    }

    return true;
}

function getShareFilePath($uid, $sodium_hash) {
    $datetime = date('Y-m-d H:i:s');
    $date = date_format(date_create(), 'Y/m/d/H/i/s');
    $shareDir = sprintf("%s/%s/%s", Config::$shareRoot, $uid, $date);
    $shareFile = sprintf("%s/%s.enc", $shareDir, $sodium_hash);

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