<?php
	include("../includes/dbUSEi.php");
	
	$data = array();
	$datares = $con->query("SELECT a.id, a.item_code, short_description, b.category, c.subcategory, unit_price FROM services_master a LEFT JOIN options_servicecat b ON a.item_category = b.id LEFT JOIN options_servicesubcat c ON a.subcategory = c.id WHERE a.file_status != 'Deleted';");
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>