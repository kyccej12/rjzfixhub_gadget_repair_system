<?php
	session_start();
	include("../handlers/initDB.php");
	$con = new myDB();
	
	$data = array();
	
	$datares = $con->dbquery("SELECT LPAD(trans_no,6,'0') AS docno, DATE_FORMAT(cr_date,'%m/%d/%Y') AS cd8, CONCAT('(',customer,') ',customer_name) AS cust, remarks, amount, if(cr_no=0,'',cr_no) as cr_no, '' as trans_ref_no, `status` FROM cr_header ORDER BY cr_date DESC, trans_no DESC;");
	while($row = $datares->fetch_array()){
	 
	  $docnos = '';
	  
	  $a = $con->dbquery("SELECT doc_no FROM cr_details WHERE trans_no = '$row[docno]';");
	  while($b = $a->fetch_array()) {
		$docnos .= '<a href="#" style="text-decoration: none;" onclick="javascript: parent.viewSI(\''. $b['doc_no'] . '\');">'.$b['doc_no'] . "</a><br/>";
	  }
	  
	  $row['trans_ref_no'] = rtrim($docnos,'<br/>');
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>