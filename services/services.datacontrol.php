<?php
	session_start();
	//ini_set("display_errors","On");
	require_once "../handlers/_generics.php";
	
	$con = new _init;
	$date = date('Y-m-d');
	$bid = $_SESSION['branchid'];
	$uid = $_SESSION['userid'];

	if($_SESSION['authkey']) {
		$con->updateTstamp($_SESSION['authkey']);
	}

	switch($_POST['mod']) {

        case "saveSInfo":
			if($_POST['rid'] != '') {
				$con->dbquery("update ignore services_master set 
										item_code = '$_POST[item_code]', 
                                        item_category = '$_POST[item_category]', 
                                        short_description = '".$con->escapeString(htmlentities($_POST['short_description']))."', 
                                        subcategory = '$_POST[subcategory]', 
                                        abbreviation = '$_POST[abbreviation]', 
                                        barcode = '$_POST[barcode]', 
                                        fulldescription = '".$con->escapeString(htmlentities($_POST['fulldescription']))."',
                                        unit_price = '".$con->formatDigit($_POST['unit_price'])."', 
                                        item_service_type = '$_POST[item_service_type]', 
                                        pricing_method = '$_POST[pricing_method]', 
                                        estimated_time = '".$con->formatDigit($_POST['estimated_time'])."', 
                                        minimum_charge = '$_POST[minimum_charge]',
                                        warranty_days = '$_POST[warranty_days]',
                                        requires_diagnosis = '$_POST[requires_diagnosis]',
                                        requires_approval = '$_POST[requires_approval]',
                                        service_instructions = '".$con->escapeString(htmlentities($_POST['service_instructions']))."',
                                        updated_by = '$uid', 
                                        updated_on = now() where id = '$_POST[rid]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO services_master (item_code,item_category,short_description,subcategory,abbreviation,barcode,fulldescription,unit_price,item_service_type,pricing_method,estimated_time,minimum_charge,warranty_days,requires_diagnosis,requires_approval,service_instructions,created_by,created_on) VALUES ('$_POST[item_code]','$_POST[item_category]','".$con->escapeString(htmlentities($_POST['short_description']))."','$_POST[subcategory]','$_POST[abbreviation]','$_POST[barcode]','".$con->escapeString(htmlentities($_POST['fulldescription']))."','".$con->formatDigit($_POST['unit_price'])."','$_POST[item_service_type]','".$con->formatDigit($_POST['pricing_method'])."','".$con->formatDigit($_POST['estimated_time'])."','$_POST[minimum_charge]','$_POST[warranty_days]','$_POST[requires_diagnosis]','$_POST[requires_approval]','".$con->escapeString(htmlentities($_POST['service_instructions']))."','$uid',NOW());");
			}
		break;

        case "checkServicesTransaction":
			list($e) = $con->getArray("select count(*) from so_details where code = '$_POST[code]';");
			if($e > 0) { echo "notOk"; } else { echo "Ok"; }	
		break;
		
		case "getGroups":
			$o = $con->dbquery("select `group`,`group_description` from options_igroup where mid = '$_POST[type]' order by `group_description` asc;");
			echo "<option value=''>- Not Applicable -</option>\n";
			while($oo = $o->fetch_array()) {
				echo "<option value='$oo[0]'>$oo[1]</option>\n";
			}
		break;

        
		case "deleteService":
			$con->dbquery("delete from services_master where id = '$_POST[rid]';");
		break;

       
		case "checkServiceDupCode":

			$rid = isset($_POST['rid']) ? intval($_POST['rid']) : 0;

			$item_code = isset($_POST['item_code'])
				? $con->escapeString(trim($_POST['item_code']))
				: '';

			if($item_code == '') {
				echo "INVALID";
				break;
			}

			$f = "";

			if($rid > 0) {
				$f = " AND id != '$rid'";
			}

			$sql = "
				SELECT COUNT(*)
				FROM services_master
				WHERE item_code = '$item_code'
				$f
			";

			list($isExist) = $con->getArray($sql);

			if($isExist == 0) {
				echo "NODUPLICATE";
			} else {
				echo "DUPLICATE";
			}

		break;

		
		
        case "getServiceCode":

            $scode = "SVC-";

            list($series) = $con->getArray("
                SELECT LPAD(
                    IFNULL(MAX(CAST(SUBSTRING(item_code, 6) AS UNSIGNED)), 0) + 1,
                    3,
                    '0'
                )
                FROM services_master
                WHERE item_code LIKE 'SVC-%';
            ");

            echo $scode . $series;

        break;


		case "getServiceSubgroup":
			$a = $con->dbquery("select id, subcategory from options_servicesubcat where parent_id = '$_POST[parent]';");
			$optDrops = '';


			if($_POST['includeAll'] == 'Y') {
				$optDrops .= "<option value=''>- All -</option>";
			} else {
				$optDrops .= "<option value='0'>- Not Applicable -</option>";
			}
			
			while(list($y,$z) = $a->fetch_array()) {
				$optDrops .= "<option value = '$y'>$z</option>";
			}

			echo $optDrops;

		break;
    }

?>