<?php
	session_start();
	include("../includes/dbUSEi.php");
	
	$searchString = '';
	
	$data = array();
	
	if(isset($_GET['cname']) && $_GET['cname'] != '') { $searchString .= " and supplier_name like '%$_GET[cname]%' "; }
	if(isset($_GET['dtf']) && $_GET['dtf']!="") { $searchString .= " and po_date between '$_GET[dtf]' and '$_GET[dt2]' "; }
	if(isset($_GET['po_date']) && $_GET['po_date'] != "") { $searchString .= " and po_date = '$_GET[po_date]' "; }
	if(isset($_GET['idesc']) && $_GET['idesc'] != '') { $searchString .= " and po_no in (select distinct po_no from po_details where (item_code like '%$_GET[idesc]%' or description like '%$_GET[idesc]%')) "; }
	
	$datares = $con->query("SELECT LPAD(po_no,6,0) AS po,branch, DATE_FORMAT(po_date,'%m/%d/%Y') AS pdate, supplier_name, remarks, amount, `status`,'' as rr FROM po_header WHERE branch = '1' $searchString;");
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  
		$myDoc = "";
		$a = dbquery("SELECT DISTINCT CONCAT ('<a href=\"#\" onclick=\"parent.viewRR(',a.rr_no,')\" style=\"text-decoration: none;\">','RR-',LPAD(a.rr_no,6,0),'</a>') AS xdoc FROM rr_header a LEFT JOIN rr_details b ON a.rr_no = b.rr_no AND a.branch = b.branch WHERE  b.po_no =  TRIM(LEADING '0' FROM '$row[po]')  AND b.po_branch = '$row[branch]';");
		while($b = $a->fetch_array(MYSQLI_ASSOC)) {
			$myDoc.="$b[xdoc]<br/>";
		}
		$myDoc=substr($myDoc,0,-5);
		if($myDoc!="") { $row['rr'] = $myDoc; }
	  
		$data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
	@mysqli_close($con);
?>