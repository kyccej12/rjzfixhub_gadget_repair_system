<?php
	session_start();
	require_once("../includes/dbUSE.php");
	mysql_select_db("kredoithris",$con);
	@mysql_query("update emp_masterfile set file_status = 'Deleted', deleted_by = '$_SESSION[userid]', deleted_on=now() where record_id = '$_REQUEST[rid]';");
	mysql_close($con);
?>