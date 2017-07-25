<?php

require_once 'lang.php';

class Config {
    public static $password = array(
        'rules' => array(
            'length' => array(
                '/[\s\S]{6,36}/', '口令长度限制为6-36位'
            ),
            'number' => array(
                '/[0-9]+/', '口令需要至少一位数字'
            ),
            'chars' => array(
                '/[a-zA-Z]+/', '口令需要至少一个英文字母'
            )
        )
    );
    public static $uploadRoot = "/upload";
}
