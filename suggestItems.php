<?php
	session_start();
	include("handlers/initDB.php");
	$con = new myDB();

	$term = trim(strip_tags($_GET['term'])); 
	$r = $con->dbquery("select concat('(',item_code,') ',description) as item, item_code,description,srp as unit_price,unit, '' as on_hand from products_master where (locate('$term',description) > 0 or locate('$term',barcode) or locate('$term',item_code)) and file_status = 'Active' and `active` = 'Y' limit 10");

	$my_arr = array();
	$my_arr_row = array();

	while($row = $r->fetch_array()) {

		list($end) = $con->getArray("select sum(purchases+inbound-outbound-pullouts-sold) as currentbalance from ibook where item_code = '$row[item_code]' and doc_date between '2023-03-01' and '" . date('Y-m-d') . "';");

		$my_arr_row['item_code'] = $row['item_code'];
		$my_arr_row['description'] = $row['description'];
		$my_arr_row['unit'] = $row['unit'];
		$my_arr_row['unit_price'] = $row['unit_price'];
		$my_arr_row['on_hand'] = $end;
		


		array_push($my_arr,$my_arr_row);
	}
	
	echo json_encode($my_arr);

?>