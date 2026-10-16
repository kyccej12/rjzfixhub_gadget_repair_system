<?php
	session_start();
	require_once 'handlers/initDB.php';
	$db = new myDB;

	unset($my_arr);
	unset($my_arr_row);
	
	$term = trim(strip_tags($_GET['term'])); 
	$r = $db->dbquery("SELECT file_id, tradename FROM contact_info where LOCATE('$term', tradename) > 0 LIMIT 10");
	$my_arr = array();
	$my_arr_row = array();
	if($r) {
		while($row = $r->fetch_array(MYSQLI_ASSOC)) {
			$my_arr_row['fid'] = $row['file_id'];
			$my_arr_row['value'] = $row['tradename'];
			$my_arr_row['label'] = $row['tradename'];
			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);
?>