<?php

require 'login.bo.php';  #一引用某个php文件

session_start();

checkLogin($_POST);
