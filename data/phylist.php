<?php
	session_start();
	include("../handlers/_generics.php");
	$con = new _init;
	$data = array();
	$searchString = "";

	if(isset($_GET['dtf']) && $_GET['dtf']!="") { $searchString .= " and doc_date between '".$con->formatDate($_GET['dtf'])."' and '".$con->formatDate($_GET['dt2'])."' "; }
	if(isset($_GET['idesc']) && $_GET['idesc'] != '') { $searchString .= " and doc_no in (select distinct doc_no from phy_details where (description like '%$_GET[idesc]%' || item_code = '$_GET[idesc]') and branch = '$_SESSION[branchid]') "; }
	
	$datares = $con->dbquery("SELECT LPAD(doc_no,6,'0') AS docno, DATE_FORMAT(posting_date,'%m/%d/%Y') AS dd8, conducted_by, verified_by, remarks, format(amount,2) as amount, `status` FROM phy_header WHERE branch = '$_SESSION[branchid]';");
	while($row = $datares->fetch_array()){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
?>