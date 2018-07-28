<?php
require_once 'register.bo.php';

	$result = doRegister($_POST);
	if($result == true){
		echo "YES";
	}
	else{
		echo var_dump($result);
	}
