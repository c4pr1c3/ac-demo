<?php

session_start();
setcookie('loggedInUser', NULL, -1);
setcookie('userName', NULL, -1);
session_destroy();
header('Location: index-new.html');
