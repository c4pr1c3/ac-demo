<?php

require_once "db.php";

if(empty(checkRegisterInDb($_POST['u'])))
{
	echo "YSE";
}
else
{
	echo "NO";
}
