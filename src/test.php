<?php
session_start();
if(!$_SESSION['uid']){
	echo "<meta http-equiv='refresh' content='2;url=index.html'>";
}
?>