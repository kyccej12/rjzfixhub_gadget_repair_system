<?php
	include("includes/dbUSE.php");
	session_start();

	unset($my_arr);
	unset($my_arr_row);

	$term = trim(strip_tags($_GET['term'])); 
	$r = mysql_query("SELECT CONCAT('(',LPAD(file_id,6,0),') ',tradename) AS label, LPAD(file_id,6,0) AS cid, tradename, a.address, a.brgy, a.city, a.province, a.terms, a.tin_no FROM contact_info a WHERE LOCATE('$term',tradename) > 0;");
	
	$my_arr = array();
	$my_arr_row = array();

	if($r) {
		while($row = mysql_fetch_array($r)) {
	
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
	
			$cname = html_entity_decode($row['tradename']);
			$addr = html_entity_decode($myaddress);
			$label = html_entity_decode($row['label']);

			$my_arr_row['tin_no'] = $row['tin_no'];
			$my_arr_row['cid'] = $row['cid'];
			$my_arr_row['cname'] = $cname;
			$my_arr_row['addr'] = $addr;
			$my_arr_row['terms'] = $row['terms'];
			$my_arr_row['label'] = $label;
			$my_arr_row['value'] = $row['cid'];

			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);
	mysql_close($con);
?>