<?php
	session_start();
	require_once "handlers/initDB.php";	
	$con = new myDB;
	$con->dbquery("delete from active_sessions where userid = '$_SESSION[userid]';");
	unset($_SESSION['userid']);
	unset($_SESSION['authkey']);
	unset($_SESSION['branchid']);
	unset($_SESSION['company']);
	session_destroy();
	
	$URL = "index.php";
	header ("Location: $URL");
	exit();

?>