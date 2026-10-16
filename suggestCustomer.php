<?php
	session_start();
    include("handlers/initDB.php");
	$con = new myDB;

	unset($my_arr);
	unset($my_arr_row);

	$term = trim(strip_tags($_GET['term'])); 
	$r = $con->dbquery("SELECT CONCAT('[',lpad(file_id,6,0),'] ',lname,', ',fname,' ',mname) AS label,  CONCAT(lname,', ',fname,' ',mname) AS `name`, LPAD(file_id,6,'0') AS cid, brgy, city, province, gender, date_format(birthdate,'%m/%d/%Y') as bday, contact_no, email, `address` FROM customer_info WHERE (LOCATE('$term',lname) > 0 OR LOCATE('$term',fname) > 0 OR LOCATE('$term',mname) > 0);");
	
	$my_arr = array();
	$my_arr_row = array();

	if($r) {
		while($row = $r->fetch_array()) {
            $myaddress = '';
            list($brgy) = $con->getArray("SELECT brgyDesc FROM options_brgy WHERE brgyCode = '$row[brgy]';");
			list($ct) = $con->getArray("SELECT citymunDesc FROM options_cities WHERE cityMunCode = '$row[city]';");
			list($prov) = $con->getArray("SELECT provDesc FROM options_provinces WHERE provCode = '$row[province]';");
		
			if($row['address'] != '') { $myaddress.=$row['address'].", "; }
			if($brgy != "") { $myaddress.=$brgy.", "; }
			if($ct != "") { $myaddress.=$ct.", "; }
			if($prov != "")  { $myaddress.=$prov.", "; }
			$myaddress = substr($myaddress,0,-2);

            $name = html_entity_decode($row['name']);
			$label = html_entity_decode($row['label']);
            $addr = html_entity_decode($myaddress);

			$my_arr_row['contact_no'] = $row['contact_no'];
			$my_arr_row['gender'] = $row['gender'];
			$my_arr_row['bday'] = $row['bday'];
            $my_arr_row['name'] = $name;
			$my_arr_row['addr'] = $addr;
			$my_arr_row['label'] = $label;
			$my_arr_row['value'] = $row['cid'];
			$my_arr_row['email'] = $row['email'];


			array_push($my_arr,$my_arr_row);
		}
	}

	echo json_encode($my_arr);
?>