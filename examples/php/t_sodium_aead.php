<?php

$plaintext = 'HelloCUCPlaintextIsVeryGoooooooooood';

switch($argc) {
case 3:
    $key = hex2bin($argv[1]);
    $nonce = hex2bin($argv[2]); 
    $ad = 'authenticated but unencrypted data';
case 4:
    $ad = $argv[3];
    break;
default:
    $key = random_bytes(SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES); // 随机产生加密秘钥，应 **妥善** 保管
    $nonce = random_bytes(SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES); // 随密文一起公开的 IV
    break;
}

var_dump($key);
var_dump(sodium_bin2hex($key));

if (sodium_crypto_aead_aes256gcm_is_available()) {
    var_dump($nonce);
    var_dump(sodium_bin2hex($nonce));
    $ciphertext = sodium_crypto_aead_aes256gcm_encrypt(
        $plaintext,
        $ad,
        $nonce,
        $key
    );
    var_dump($ciphertext);
    var_dump(sodium_bin2hex($ciphertext));
}

if (sodium_crypto_aead_aes256gcm_is_available()) {
    $decrypted = sodium_crypto_aead_aes256gcm_decrypt(
        $ciphertext,
        $ad,
        $nonce,
        $key
    );
    if ($decrypted === false) {
        throw new Exception("Bad ciphertext");
    } else {
        var_dump($decrypted);
    }
}


