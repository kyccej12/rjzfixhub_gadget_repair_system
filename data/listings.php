<?php
	session_start();
	require_once("../handlers/_generics.php");

	$con = new _init;
	$data = array();
	$searchString = '';
	
	switch($_REQUEST['mod']) {
		case "contactlist":
			$txt = "SELECT LPAD(file_id,5,0) AS cid, tradename as cname, concat(address,', ',b.city,', ',c.province) as caddress, tel_no, cperson FROM contact_info a left join options_cities b on a.city = b.city_id left join options_provinces c on a.province = c.province_id WHERE record_status != 'Deleted' and company in ('0','$_SESSION[company]') order by tradename";
		break;
		case "projectlist":
			$txt = "SELECT proj_id, proj_code, if(proj_type != '',proj_type,'INTERNAL') as proj_type, proj_name, proj_address, 
					CASE 
						WHEN proj_duration = 1 THEN '0 to 3 Months'
						WHEN proj_duration = 2 THEN '3 to 6 Months'
						WHEN proj_duration = 3 THEN '6 to 12 Months'
						WHEN proj_duration = 4 THEN 'Over 1 Year'
						ELSE '0 to 3 Months'
					END AS proj_duration,
				 archived FROM options_project;";
		break;
		case "itemlist":
			$txt = "SELECT record_id, item_code, a.description, unit, barcode, brand, unit_cost, b.description AS igroup FROM products_master a LEFT JOIN igroup b ON a.group = b.line_id WHERE a.file_status = 'Active'";
		break;
		case "userlist":
			$txt = "SELECT emp_id AS id, LPAD(emp_id,3,'0') AS uid, username AS uname, fullname, IF(STATUS='A','Active','Disabled') AS stat, IF(user_type='admin','Super User','Limited') AS utype, DATE_FORMAT(last_logged_in,'%m/%d/%Y %r') AS lastlogged FROM user_info";
		break;
		case "branchlist":
			$txt = "SELECT record_id as id, LPAD(branch_code,3,0) AS bcode, branch_name, CONCAT(address,', ',b.city,', ',c.province) AS address, a.tel_no FROM options_branches a LEFT JOIN options_cities b ON a.city=b.city_id LEFT JOIN options_provinces c ON a.province = c.province_id WHERE company = '$_SESSION[company]'";
		break;
		case "igroup":
			$txt = "SELECT line_id as id, grpcode, description FROM igroup where file_status = 'Active';";
		break;
		case "sgroup":
			$txt = "SELECT a.sid AS id, a.code, a.sgroup, b.mgroup FROM options_sgroup a LEFT JOIN options_mgroup b ON a.mid = b.mid WHERE a.file_status = 'Active' ORDER BY a.sgroup ASC;";
		break;
		case "falist":
			if($_GET['search'] == 'Y') {
				if($_GET['item'] != '') { $searchString .= "and (a.asset_code = '$_GET[item]' or a.description like '%$_GET[item]%') "; }
				if($_GET['cat'] != '') { $searchString .= "and a.category = '$_GET[cat]' "; }
				if($_GET['dtf'] != '' && $_GET['dt2'] != '') {
					$searchString .= "and date_acquired between '".$con->formatDate($_GET['dtf'])."' and '".$con->formatDate($_GET['dt2'])."' "; 
				} else {
					if($_GET['dtf'] != '') { 
						$searchString .= "and date_acquired = '".$con->formatDate($_GET['dtf'])."' "; 
					} elseif ($_GET['dt2'] != '') { 
						$searchString .= "and date_acquired = '".$con->formatDate($_GET['dt2'])."' "; 
					} else { }
				}
				if($_GET['proj'] != '') { $searchString .= "and a.asset_costcenter = '$_GET[proj]' "; }
			}
		
			$txt = "SELECT fid,asset_code,`name`,description,b.category,date_format(date_acquired,'%m/%d/%Y') date_acquired,c.proj_name as ccenter,unit_value,`user` from fa_master a LEFT JOIN fa_category b ON a.category = b.id left join options_project c on a.asset_costcenter = c.proj_id where 1=1 $searchString";
		break;
		case "srrlist":
			$txt = "SELECT srr_no, LPAD(srr_no,6,0) AS srr, DATE_FORMAT(srr_date,'%m/%d/%Y') AS sdate, branch_name received_from, remarks,`status` FROM srr_header a INNER JOIN options_branches b ON a.received_from = b.branch_code WHERE a.company = '$_SESSION[company]' and branch = '$_SESSION[branchid]'";
		break;
		case "swlist":
			$txt = "SELECT sw_no, LPAD(sw_no,6,0) AS swno, DATE_FORMAT(sw_date,'%m/%d/%Y') AS sdate, withdrawn_by, remarks, `status` FROM sw_header WHERE company = '$_SESSION[company]' AND branch = '$_SESSION[branchid]'";
		break;
		case "strlist":
			$txt = "SELECT str_no, LPAD(str_no,6,0) AS str, DATE_FORMAT(str_date,'%m/%d/%Y') AS sdate, b.branch_name, a.remarks, a.status FROM str_header a LEFT JOIN options_branches b ON a.transferred_to = b.branch_code AND a.company=b.company WHERE a.company = '$_SESSION[company]' AND a.branch = '$_SESSION[branchid]'";
		break;
		case "phy":
			if(isset($_GET['dtf']) && $_GET['dtf']!="") { $fs2 = " and doc_date between '$_GET[dtf]' and '$_GET[dt2]' "; }
			if(isset($_GET['doc_date']) && $_GET['doc_date'] != "") { $fs3 = " and doc_date = '$_GET[doc_date]' "; }
			if(isset($_GET['idesc']) && $_GET['idesc'] != '') { $fs4 = " and doc_no in (select distinct doc_no from phy_details where (description like '%$_GET[idesc]%' || item_code = '$_GET[idesc]')) "; }
			$txt = "SELECT LPAD(doc_no,6,'0') AS docno, DATE_FORMAT(doc_date,'%m/%d/%Y') AS dd8, conducted_by, remarks, `status` FROM phy_header WHERE company = '$_SESSION[company]' and branch = '$_SESSION[branchid]' $fs2 $fs3 $fs4;";
		break;
		
	}
	
	$datares = $con->dbquery($txt);
	while($row = $datares->fetch_array(MYSQLI_ASSOC)){
	  $data[] = array_map('utf8_encode',$row);
	}
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
?>