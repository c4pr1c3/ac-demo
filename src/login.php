<?php

require 'login.bo.php';

session_start();

checkLogin($_POST);
