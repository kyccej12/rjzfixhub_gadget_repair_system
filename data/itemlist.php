<?php
	session_start();
	ini_set('max_execution_time',0);
	ini_set('memory_limit',-1);
	
	include("../handlers/initDB.php");
	$con = new myDB;

	$datares = $con->dbquery("SELECT record_id,item_code,a.description,brand,d.description AS unit,unit_cost,b.mgroup,c.sgroup, '' as qty_onhand FROM products_master a LEFT JOIN options_mgroup b ON a.category = b.mid LEFT JOIN options_sgroup c ON a.subgroup = c.sid LEFT JOIN options_units d ON a.unit = d.unit WHERE `active` = 'Y' ORDER BY item_code ASC;");
	while($row = $datares->fetch_array()){
	  
		$pi = $con->getArray("select ifnull(sum(b.qty),0) from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch = b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$row[item_code]' and a.status = 'Finalized' and a.posting_date = '2022-02-09' GROUP BY b.item_code;");				
		$cur = $con->getArray("select sum(purchases+inbound-outbound-pullouts-sold) as currentbalance from ibook where item_code = '$row[item_code]' and doc_date between '2022-02-09' and '".date('Y-m-d')."' and doc_branch = '$_SESSION[branchid]';");
		$row['qty_onhand'] = ROUND($pi[0]+$cur['currentbalance'],2);
		
		
		$data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);

?>