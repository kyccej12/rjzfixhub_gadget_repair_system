<?php
	include("includes/dbUSE.php");
	
	$term = trim(strip_tags($_GET['search'])); 
	$r = dbquery("select record_id, item_code,description,unit_price,unit from products_master where (locate('$term',description) > 0 or locate('$term',barcode) or locate('$term',item_code)) limit 10");
	$my_arr = array();
	$my_arr_row = array();

	if($r) {
		while($row = mysql_fetch_array($r)) {
			$my_arr_row['id'] = $row['record_id'];
			$my_arr_row['code'] = $row['item_code'];
			$my_arr_row['description'] = $row['description'];
			$my_arr_row['price'] = $row['unit_price'];
			array_push($my_arr,$my_arr_row);
		}
	}
	
	echo json_encode($my_arr);
?>