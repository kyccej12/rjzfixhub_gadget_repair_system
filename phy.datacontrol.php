<?php
	session_start();
	include("handlers/_generics.php");
	$con = new _init;

	function updateAmount($doc_no) {
		global $con;

		list($amt) = $con->getArray("select sum(qty*cost) from phy_details where doc_no = '$doc_no' and branch = '$_SESSION[branchid]';");
		$con->dbquery("update ignore phy_header set amount = '$amt' where doc_no = '$doc_no' and branch = '$_SESSION[branchid]';");

		echo number_format($amt,2);

	}

	switch($_POST['mod']) {
		case "saveHeader":
			if($_POST['doc_no'] != "") {
				$s = "update ignore phy_header set conducted_by = '".$con->escapeString(htmlentities($_POST['conducted_by']))."', verified_by = '".$con->escapeString(htmlentities($_POST['verified_by']))."', posting_date = '".$con->formatDate($_POST['doc_date'])."', remarks = '".$con->escapeString(htmlentities($_POST['remarks']))."', updated_by = '$_SESSION[userid]', updated_on = now() where doc_no = '$_POST[doc_no]' and branch = '$_SESSION[branchid]';";
				$docno = $_POST['doc_no'];
			} else {
				list($docno) = $con->getArray("select ifnull(max(doc_no),0)+1 from phy_header where branch = '$_SESSION[branchid]';"); 
				$s = "insert ignore into phy_header (branch,doc_no,posting_date,conducted_by,verified_by,trace_no,remarks,created_by,created_on) values ('$_SESSION[branchid]','$docno','".$con->formatDate($_POST['doc_date'])."','".$con->escapeString(htmlentities($_POST['conducted_by']))."','".$con->escapeString(htmlentities($_POST['verfied_by']))."','$_POST[trace_no]','".$con->escapeString(htmlentities($_POST['remarks']))."','$_SESSION[userid]',now());";
			}

			$con->dbquery($s);
			echo str_pad($docno,6,'0',STR_PAD_LEFT);
		break;

		case "addItem":

			if($_POST['expiry'] == '') { $expiry = '0000-00-00'; } else { $expiry = $con->formatDate($_POST['expiry']); }

			list($isE) = $con->getArray("select count(*) from phy_details where trace_no = '$_POST[trace_no]' and item_code = '$_POST[item]' and lot_no = '$_POST[lot_no]' and expiry = '$expiry' and branch = '$_SESSION[branchid]';");
			if($isE > 0) {
				$s = "update ignore phy_details set qty = qty + ".$con->formatDigit($_POST['qty']).", amount = amount + ".$con->formatDigit($_POST['amount'])." where trace_no = '$_POST[trace_no]' and item_code = '$_POST[item]' and lot_no = '$_POST[lot_no]' and expiry = '$expiry' and branch = '$_SESSION[branchid]';";
			} else {
				$s = "insert ignore into phy_details (branch,doc_no,item_code,description,qty,unit,cost,amount,lot_no,expiry,trace_no) values ('$_SESSION[branchid]','$_POST[doc_no]','$_POST[item]','".$con->escapeString(htmlentities($_POST['description']))."','".$con->formatDigit($_POST['qty'])."','$_POST[unit]','".$con->formatDigit($_POST['cost'])."','".$con->formatDigit($_POST['amount'])."','$_POST[lot_no]','$expiry','$_POST[trace_no]');";
			}
		
			$con->dbquery($s);
			updateAmount($_POST['doc_no']);
		break;
		
		case "deleteLine":
			$con->dbquery("delete from phy_details where line_id = '$_POST[lid]';");
			updateAmount($_POST['doc_no']);
		break;
	
		case "retrieveLine":
			echo json_encode($con->getArray("select *,format(cost,2) as ucost, format(amount,2) as amt, if(expiry!='0000-00-00',date_format(expiry,'%m/%d/%Y'),'') as exp from phy_details where line_id = '$_POST[lid]';"));
		break;

		case "updateItem":

			if($_POST['expiry'] == '') { $expiry = '0000-00-00'; } else { $expiry = $con->formatDate($_POST['expiry']); }
			$con->dbquery("update phy_details set item_code = '$_POST[item]', description = '".$con->escapeString(htmlentities($_POST['description']))."', qty = '".$con->formatDigit($_POST['qty'])."', unit = '$_POST[unit]',cost = '".$con->formatDigit($_POST['cost'])."', amount = '".$con->formatDigit($_POST['amount'])."', lot_no = '$_POST[lot_no]', expiry = '$expiry' where line_id = '$_POST[lid]';");
			updateAmount($_POST['doc_no']);
		break;

		case "check4print":
			list($a) = $con->getArray("select count(*) from phy_header where doc_no = '$_POST[doc_no]' and branch = '$_SESSION[branchid]';");
			list($b) = $con->getArray("select count(*) from phy_details where doc_no = '$_POST[doc_no]' and branch = '$_SESSION[branchid]';");
			
			if($a == 0 && $b > 0) { echo "head"; }
			if($b == 0 && $a > 0) { echo "det"; }
			if($a == 0 && $b == 0) { echo "both"; }
			if($a > 0 && $b > 0) { echo "noerror"; }
		break;

		case "finalizePhy":
			$con->dbquery("update phy_header set status = 'Finalized', updated_by = '$_SESSION[userid]', updated_on = now() where doc_no ='$_POST[doc_no]' and branch = '$_SESSION[branchid]';");
		break;
		
		case "reopenPhy":
			$con->dbquery("update phy_header set status = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where doc_no = '$_POST[doc_no]' and branch = '$_SESSION[branchid]';");
		break;
		
		case "cancel":
			$con->dbquery("update phy_header set status = 'Cancelled', updated_by = '$_SESSION[userid]', updated_on = now() where doc_no = '$_POST[doc_no]' and branch = '$_SESSION[branchid]';");
		break;

		case "retrieve":
			$data = array();
	
			$srrd = $con->dbquery("SELECT line_id as id, description, item_code, unit, brand, model, qty, cost, amount FROM phy_details where trace_no = '$_REQUEST[trace_no]';");
			while($row = $srrd->fetch_array()) {

				$data[] = array_map('utf8_encode',$row);
			}
			
			$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
			echo json_encode($results);	
		break;
	}

?>