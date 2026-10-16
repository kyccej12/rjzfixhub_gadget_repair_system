<?php
	session_start();
	include("handlers/initDB.php");
	
	$con = new myDB();

	$term = trim(strip_tags($_GET['term']));
	
	$datares = $con->dbquery("SELECT item_code, description, unit FROM products_master WHERE file_status = 'Active' and `active` = 'Y' and (LOCATE('$term',description) > 0 OR LOCATE('$term',full_description) > 0 OR item_code = '$term') limit 500");
	
	while($row = $datares->fetch_array()){	
	  
	  $data[] = array_map('utf8_encode',$row);
	}

	echo json_encode($data);
	
?>