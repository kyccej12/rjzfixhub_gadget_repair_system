<?php
	session_start();
	include("../handlers/initDB.php");
	$con = new myDB;
	$data = array();

	switch($_GET['mod']) {
        case "pendingPO":
            $sql = "SELECT a.po_no, LPAD(a.po_no,6,0) AS mypo, DATE_FORMAT(a.po_date,'%m/%d/%Y') AS pd8, a.supplier_name, remarks, sum(b.qty - b.qty_dld) AS pending_qty FROM po_header a LEFT JOIN po_details b ON a.po_no = b.po_no AND a.branch = b.branch WHERE a.status = 'Finalized' AND b.item_code = '$_REQUEST[item]' AND b.qty_dld < b.qty group by a.po_no,b.item_code";
        break;
        case "rrlist":
            $sql = "SELECT a.rr_no, LPAD(a.rr_no,6,0) AS myrr, DATE_FORMAT(a.rr_date,'%m/%d/%Y') AS rd8, a.supplier_name, remarks, invoice_no, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS idate FROM rr_header a LEFT JOIN rr_details b ON a.rr_no AND a.branch = b.branch WHERE a.status = 'Finalized' AND b.item_code = '$_REQUEST[item]' GROUP BY a.rr_no";
        break;
	}
	
	
	$datares = $con->dbquery($sql);
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
?>