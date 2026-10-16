<?php
	session_start();
	include("includes/dbUSEi.php");
	
	$term = trim(strip_tags($_GET['term']));
	$datares = $con->query("SELECT item_code, description, unit, unit_cost AS amount FROM products_master where (LOCATE('$term',description) > 0 OR LOCATE('$term',full_description)>0 OR LOCATE('$term',item_code)>0) AND file_status = 'Active' and `active` = 'Y';");
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}

	echo json_encode($data);
	@mysqli_close($con);
?>