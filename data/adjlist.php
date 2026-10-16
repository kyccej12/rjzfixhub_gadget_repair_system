<?php
	session_start();
	include("../includes/dbUSEi.php");
	$datares = $con->query("select lpad(doc_no,6,0) as docno, date_format(doc_date,'%m/%d/%Y') as pdate, cname, remarks, amount, status from adj_header where company = '$_SESSION[company]' and branch = '$_SESSION[branchid]';");
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
	@mysqli_close($con);
?>