<?php
	session_start();
	require_once 'handlers/initDB.php';
	$db = new myDB;

	unset($my_arr);
	unset($my_arr_row);
	
	$term = trim(strip_tags($_GET['term'])); 
	$r = $db->dbquery("select emp_id, concat(lname,', ',fname,left(mname,1),'.') as emp_name, payroll_type as ptype, dept from `emp_masterfile` where (LOCATE('$term', lname) > 0 or LOCATE('$term',fname) > 0) AND FILE_STATUS != 'DELETED' LIMIT 10");
	$my_arr = array();
	$my_arr_row = array();
	if($r) {
		while($row = $r->fetch_array(MYSQLI_ASSOC)) {
			$my_arr_row['emp_id'] = $row['emp_id'];
			$my_arr_row['dept'] = $row['dept'];
			$my_arr_row['value'] = $row['emp_name'];
			$my_arr_row['label'] = $row['emp_name'];
			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);
?>