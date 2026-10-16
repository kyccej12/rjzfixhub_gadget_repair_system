<?php
	require_once '../handlers/initDB.php';
	$con = new myDB;
	
	$data = array();
	
	$datares = $con->dbquery("SELECT pcvNo as pcv_no,LPAD(pcvNo,6,0) AS mypcv,DATE_FORMAT(pcvDate,'%m/%d/%Y') AS pcv_date, payeeName, particulars, amount, `status`, IF(isLiquidated='Y','Liquidated','Unliquidated') AS substatus FROM pcv ORDER BY pcv_no DESC");
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
	@mysqli_close($con);
?>