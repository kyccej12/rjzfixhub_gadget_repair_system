<?php
	include("../includes/dbUSEi.php");
	$datares = $con->query("SELECT LPAD(emp_id,3,'0') AS uid, username AS uname, fullname, IF(STATUS='A','Active','Disabled') AS stat, IF(user_type='admin','Super User','Limited') AS utype, DATE_FORMAT(last_logged_in,'%m/%d/%Y %r') AS lastlogged FROM user_info WHERE 1=1;");
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
	@mysql_close($con);
?>