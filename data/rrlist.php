<?php
	session_start();
	include("../includes/dbUSEi.php");
	$data = array();
	$datares = $con->query("select lpad(rr_no,6,0) as rr, date_format(rr_date,'%m/%d/%Y') as rdate, supplier_name, remarks, amount, status, if(apv_no!='',concat('<a href=\"#\" onclick=\"parent.viewAP(',apv_no,');\" style=\"text-decoration: none;\">','AP-',lpad(apv_no,6,0),'</a>'),'') as apv from rr_header where branch = '1';");
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
	@mysqli_close($con);
?>