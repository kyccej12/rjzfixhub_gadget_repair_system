<?php
	session_start();
	include("includes/dbUSE.php");
	include("functions/srr.displayDetails.fnc.php");

	function updateHeaderAmt($srr_no) {
		list($amt) = getArray("select sum(amount) from srr_details where srr_no = '$srr_no' and branch = '$_SESSION[branchid]';");
		dbquery("update ignore srr_header set amount='$amt' where srr_no = '$srr_no' and branch = '$_SESSION[branchid]';");
	}

	switch($_REQUEST['mod']) {
		case "saveHeader":
			list($isE) = getArray("select count(*) from srr_header where srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]';");
			if($isE > 0) {
				$s = "update ignore srr_header set received_by = '".mysql_real_escape_string(htmlentities($_POST['by']))."', received_from = '".mysql_real_escape_string(htmlentities($_POST['from']))."', srr_date = '".formatDate($_POST['srr_date'])."', ref_no = '$_POST[ref_no]', ref_date='".formatDate($_POST['ref_date'])."', ref_type='$_POST[ref_type]', remarks = '".mysql_real_escape_string(htmlentities($_POST['remarks']))."', updated_by = '$_SESSION[userid]', updated_on = now() where srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]';";
			} else {
				$s = "insert ignore into srr_header (srr_no,branch,srr_date,received_from,received_by,ref_type,ref_no,ref_date,remarks,created_by,created_on) values ('$_POST[srr_no]','$_SESSION[branchid]','".formatDate($_POST['srr_date'])."','".mysql_real_escape_string(htmlentities($_POST['from']))."','".mysql_real_escape_string(htmlentities($_POST['by']))."','$_POST[ref_type]','$_POST[ref_no]','".formatDate($_POST['ref_date'])."','".mysql_real_escape_string(htmlentities($_POST['remarks']))."','$_SESSION[userid]',now());";
			}
			dbquery($s);
		break;

		case "insertDetail":
			list($isE) = getArray("select count(*) from srr_details where srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]' and item_code = '$_POST[icode]';");
			if($isE > 0) {
				$s = "update ignore srr_details set qty = qty + ".formatDigit($_POST['qty'])." where srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]' and item_code = '$_POST[icode]';";
			} else {
				$s = "insert ignore into srr_details (srr_no,branch,item_code,description,qty,unit) values ('$_POST[srr_no]','$_SESSION[branchid]','$_POST[icode]','".mysql_real_escape_string(htmlentities($_POST['desc']))."','".formatDigit($_POST['qty'])."','$_POST[unit]');";
			}
			dbquery($s);
			showDetails($_POST['srr_no'],$status='Active',$lock='N');
		break;
		case "deleteDetails":
			dbquery("delete from srr_details where line_id = '$_POST[lid]';");
			showDetails($_POST['srr_no'],$status='Active',$lock='N');
		break;
		case "usabQty":
			dbquery("update srr_details set qty = '".formatDigit($_POST['val'])."' where line_id = '$_POST[lid]';");
		break;
		case "check4print":
			list($a) = getArray("select count(*) from srr_header where srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]';");
			list($b) = getArray("select count(*) from srr_details where srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]';");
			
			if($a == 0 && $b > 0) { echo "head"; }
			if($b == 0 && $a > 0) { echo "det"; }
			if($a == 0 && $b == 0) { echo "both"; }
			if($a > 0 && $b > 0) { echo "noerror"; }
		break;
		
		case "finalizeSRR":
			dbquery("update srr_header set status = 'Finalized', updated_by = '$_SESSION[userid]', updated_on = now() where srr_no ='$_POST[srr_no]';");
				
			$iquery = dbquery("SELECT a.branch, a.srr_no AS doc_no, a.srr_date AS doc_date, received_from AS customer, b.item_code, b.unit, b.qty FROM srr_header a INNER JOIN srr_details b ON a.srr_no = b.srr_no WHERE a.srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]';");
			while($ibook = mysql_fetch_array($iquery)) {
				dbquery("INSERT IGNORE INTO ibook (doc_no,doc_branch,doc_date,doc_type,cname,item_code,uom,inbound,posted_by,posted_on) VALUES ('$ibook[doc_no]','$_SESSION[branchid]','$ibook[doc_date]','SRR','".mysql_real_escape_string($ibook['customer'])."','$ibook[item_code]','$ibook[unit]','$ibook[qty]','$_SESSION[userid]',now());");
			}
			
			/* Check if Ref # is from STR */
			list($t) = getArray("select ref_no from srr_header where srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]';");
			if($t) {
				list($type,$branch,$strno) = explode("-",$t);
				if($type == "STR") {
					dbquery("update ignore str_header set received = 'Y', srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]' where str_no = '$strno';");
				}
			}
			

		break;
		
		case "reopenSRR":
			dbquery("update srr_header set status = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]';");
			dbquery("delete from ibook where doc_no = '$_POST[srr_no]' and doc_type = 'SRR' and doc_branch = '$_SESSION[brancid]';");

			/* Check if Ref # is from STR */
			/*list($t) = getArray("select ref_no from srr_header where srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]';");
			if($t) {
				list($type,$branch,$strno) = explode("-",$t);
				if($type == "STR") {
					dbquery("update ignore str_header set received = 'N', srr_no = NULL where str_no = '$strno';");
				}
			}*/
		
		break;
		
		case "cancel":
			dbquery("update srr_header set status = 'Cancelled', updated_by = '$_SESSION[userid]', updated_on = now() where srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]';");
		break;
		
		case "getTotals":
			list($amt) = getArray("select sum(ROUND(qty*cost,2)) as amount from srr_details where srr_no = '$_POST[srr_no]' and branch = '$_SESSION[branchid]';");
			echo json_encode(array("total"=>number_format($amt,2)));
		break;
		
	}

	mysql_close($con);

?>