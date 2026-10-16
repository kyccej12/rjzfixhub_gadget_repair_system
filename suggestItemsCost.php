<?php
	session_start();
	include("handlers/initDB.php");
	$con = new myDB;

	unset($my_arr);
	unset($my_arr_row);

	$term = trim(strip_tags($_GET['term'])); 
	$r = $con->dbquery("SELECT CONCAT('(',item_code,') ',brand, model, `description`,' [UOM = ',unit,' ^ Current Available = ') AS item, item_code,description,srp,unit FROM products_master WHERE (LOCATE('$term',description) > 0 OR LOCATE('$term',item_code)) AND file_status = 'Active' LIMIT 30;");
	$my_arr = array();
	$my_arr_row = array();

	if($r) {
		while($row = $r->fetch_array()) {

			$pi = $con->getArray("select ifnull(sum(b.qty),0) from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch = b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$row[item_code]' and a.status = 'Finalized' and a.posting_date = '2026-10-11' GROUP BY b.item_code;");				
			$cur = $con->getArray("select sum(purchases+inbound-outbound-pullouts-sold) as currentbalance from ibook where item_code = '$row[item_code]' and doc_date between '2026-10-11' and '".date('Y-m-d')."' and doc_branch = '$_SESSION[branchid]';");
		    $qoh = ROUND($pi[0]+$cur['currentbalance'],2);

			$my_arr_row['item_code'] = $row['item_code'];
			$my_arr_row['value'] = $row['description'];
			$my_arr_row['brand'] = $row['brand'];
			$my_arr_row['model'] = $row['model'];
			$my_arr_row['srp'] = $row['srp'];
			$my_arr_row['label'] = $row['item'] . $qoh . ']';

			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);

?>