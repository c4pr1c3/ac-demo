<?php

$plaintext = 'HelloCUCPlaintextIsVeryGoooooooooood';

var_dump($plaintext);

// 随机产生加密秘钥，应 **妥善** 保管
$key = random_bytes(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

var_dump(SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

// 加密数据
$nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

var_dump(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

$ciphertext = sodium_crypto_secretbox($plaintext, $nonce, $key);

var_dump($ciphertext);

printf("%s\n", sodium_bin2hex($ciphertext));

$decrypted = sodium_crypto_secretbox_open($ciphertext, $nonce, $key);
if ($decrypted === false) {
	throw new Exception("Bad ciphertext");
} else {
	var_dump($decrypted);
}

var_dump(SODIUM_CRYPTO_AEAD_AES256GCM_NPUBBYTES);

// https://download.libsodium.org/doc/secret-key_cryptography/authenticated_encryption.html 
// 在 libsodium 内部实现中，crypto_secretbox() 调用 crypto_stream_xor() 来加密数据
// crypto_secretbox() 会对上述加密后数据

//$key = random_bytes(SODIUM_CRYPTO_STREAM_KEYBYTES);
//$nonce = random_bytes(SODIUM_CRYPTO_STREAM_NONCEBYTES);

var_dump(SODIUM_CRYPTO_STREAM_KEYBYTES);
var_dump(SODIUM_CRYPTO_STREAM_NONCEBYTES);

// This operation is reversible:
$ciphertext = sodium_crypto_stream_xor($plaintext, $nonce, $key);
$decrypted = sodium_crypto_stream_xor($ciphertext, $nonce, $key);

var_dump($ciphertext);
printf("%s\n", sodium_bin2hex($ciphertext));
var_dump($decrypted);

