<?php
	session_start();
	include("../includes/dbUSEi.php");
	$data = array();
	$datares = $con->query("select lpad(srr_no,6,0) as srr, date_format(srr_date,'%m/%d/%Y') as sdate, received_from, remarks, amount, status from srr_header;");
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
	@mysqli_close($con);
?>