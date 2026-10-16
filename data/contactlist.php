<?php
	ini_set("memory_limit",-1);
	include("../includes/dbUSE.php");
	
	switch($_GET['mod']) {
		case "1": $fs = ""; break;
		case "2": $fs = " and a.type in ('CSUPPLIER','CUSTOMER','CUSTOMER2','CUSTOMER3','EMPLOYEE') "; break;
		case "3": $fs = $fs = " and a.type in ('CSUPPLIER','SUPPLIER','FSUPPLIER') "; break;
	}
	
	
	$datares = dbquery("SELECT LPAD(file_id,6,0) AS cid, `type` AS ctype, tradename AS cname, a.address, a.brgy, a.city, a.province, a.type, '' as caddress, a.billing_address, tel_no AS ctelno, cperson FROM contact_info a WHERE record_status != 'Deleted' $fs;");
	while($row = mysql_fetch_array($datares)){
		
		$myaddress = "";
		if($row['type'] != "FSUPPLIER") {
			list($brgy) = getArray("SELECT brgyDesc FROM options_brgy WHERE brgyCode = '$row[brgy]';");
			list($ct) = getArray("SELECT ctiymunDesc FROM options_cities WHERE cityMunCode = '$row[city]';");
			list($prov) = getArray("SELECT provDesc FROM options_provinces WHERE provCode = '$row[province]';");
		
			if($row['address'] != '') { $myaddress.=$row['address'].", "; }
			if($brgy != "") { $myaddress.=$brgy.", "; }
			if($ct != "") { $myaddress.=$ct.", "; }
			if($prov != "")  { $myaddress.=$prov.", "; }
			$myaddress = substr($myaddress,0,-2);
		} else {
			$myaddress = $row['billing_address'];
		}	
		
		$row['caddress'] = strtoupper($myaddress);
		$data[] = array_map('utf8_encode',$row);
	}
	
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
	@mysqli_close($con);
?>