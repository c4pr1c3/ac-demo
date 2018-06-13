<?php

$passphrase = 'admin123';

// 常量定义参考 https://paragonie.com/book/pecl-libsodium/read/01-quick-start.md#constant-index
// https://paragonie.com/book/pecl-libsodium/read/07-password-hashing.md Argon2i Key Derivation

$out_len = SODIUM_CRYPTO_AEAD_AES256GCM_KEYBYTES; // 32
$salt = isset($argv[1]) ? hex2bin($argv[1]) : random_bytes(SODIUM_CRYPTO_PWHASH_SALTBYTES); // 16
$password = sodium_crypto_pwhash(
    $out_len,
    $passphrase,
    $salt,
    SODIUM_CRYPTO_PWHASH_OPSLIMIT_INTERACTIVE, // 4
    SODIUM_CRYPTO_PWHASH_MEMLIMIT_INTERACTIVE
);

var_dump(bin2hex($salt));
var_dump(bin2hex($password));

$iterations = 1000;
$algo = "sha256";

$password = hash_pbkdf2($algo, $passphrase, $salt, $iterations, $out_len * 2);

var_dump($password);

