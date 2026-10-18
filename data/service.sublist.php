<?php
	session_start();
	include("../handlers/initDB.php");
	$con = new myDB;
	$data = array();

	switch($_GET['mod']) {
        case "sprice":
            $sql = "SELECT b.tradename, FORMAT(special_price,2) AS sp, FORMAT(previous_price,2) AS pp, with_validity AS isValid, IF(valid_until!='0000-00-00',DATE_FORMAT(valid_until,'%m/%d/%Y'),'') AS validUntil, DATE_FORMAT(a.created_on,'%m/%d/%Y %h:%i %p') AS creon, IF(a.updated_on IS NOT NULL,DATE_FORMAT(a.updated_on,'%m/%d/%Y %h:%i %p'),'') AS uon FROM contact_sprice a LEFT JOIN contact_info b ON a.contact_id = b.file_id WHERE a.code = '$_REQUEST[item]';";
        break;
        case "bom":
           $sql = "SELECT record_id as id,item_code, description, unit, FORMAT(unit_cost,2) as unit_cost, FORMAT(qty,2) as qty, FORMAT(amount,2) as amount, remarks FROM services_bom WHERE `code` = '$_REQUEST[item]';";
        break;
		case "subtest":
			$sql = "SELECT a.record_id AS id, a.code, a.description, c.category,d.subcategory,f.sample_type,b.result_type FROM services_subtests a LEFT JOIN  services_master b ON a.parent = b.code LEFT JOIN options_servicecat c ON b.category = c.id LEFT JOIN options_servicesubcat d ON b.subcategory = d.id LEFT JOIN options_containers e ON b.container_type = e.id LEFT JOIN options_sampletype f ON b.sample_type = f.id WHERE a.parent = '$_REQUEST[item]';";
		break;
		case "testvalues":
			$sql = "SELECT record_id AS id, attribute_type, attribute, unit, if(`min_value`!='',`min_value`,'--') as min_value, if(`max_value`!='',`max_value`,'--') AS max_value, if(critical_low_value!='',critical_low_value,'--') as low_critical, if(critical_high_value!='',critical_high_value,'--') as high_critical, descriptive_value FROM lab_testvalues WHERE `code` = '$_REQUEST[item]';";
		break;
	}
	
	
	$datares = $con->dbquery($sql);
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
?>