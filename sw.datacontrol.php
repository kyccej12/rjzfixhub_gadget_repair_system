<?php
	session_start();
	//ini_set("display_errors","On");
	include("includes/dbUSE.php");
	include("functions/sw.displayDetails.fnc.php");

	function updateHeaderAmt($sw_no) {
		list($amt) = getArray("select sum(amount) from sw_details where sw_no = '$sw_no' and branch = '$_SESSION[branchid]';");
		dbquery("update ignore sw_header set amount='$amt' where sw_no = '$sw_no' and branch = '$_SESSION[branchid]';");
	}

	switch($_POST['mod']) {
		case "saveHeader":

			list($isE) = getArray("select count(*) from sw_header where sw_no = '$_POST[sw_no]' and branch = '$_SESSION[branchid]';");

			if($isE > 0) {
				$s = "update ignore sw_header set withdrawn_by = '".mysql_real_escape_string(htmlentities($_POST['wby']))."', requested_by = '".mysql_real_escape_string(htmlentities($_POST['by']))."', sw_date = '".formatDate($_POST['sw_date'])."', ref_type = '$_POST[ref_type]', request_date ='".formatDate($_POST['request_date'])."', remarks = '".mysql_real_escape_string(htmlentities($_POST['remarks']))."', updated_by = '$_SESSION[userid]', updated_on = now() where sw_no = '$_POST[sw_no]' and branch = '$_SESSION[branchid]';";
			} else {
				$s = "insert ignore into sw_header (branch,sw_no,sw_date,withdrawn_by,requested_by,request_date,ref_type,remarks,created_by,created_on) values ('$_SESSION[branchid]','$_POST[sw_no]','".formatDate($_POST['sw_date'])."','".mysql_real_escape_string(htmlentities($_POST['wby']))."','".mysql_real_escape_string(htmlentities($_POST['by']))."','".formatDate($_POST['request_date'])."','$_POST[ref_type]','".mysql_real_escape_string(htmlentities($_POST['remarks']))."','$_SESSION[userid]',now());";
			}
			
			dbquery($s);
			echo $s;
		break;

		case "insertDetail":
			list($isE) = getArray("select count(*) from sw_details where sw_no = '$_POST[sw_no]' and item_code = '$_POST[icode]' and branch = '$_SESSION[branchid]';");
			if($isE > 0) {
				$s = "update ignore sw_details set qty = qty + ".formatDigit($_POST['qty']).", amount = amount + ".formatDigit($_POST['amount'])." where sw_no = '$_POST[sw_no]' and item_code = '$_POST[icode]' and branch = '$_SESSION[branchid]' and branch = '$_SESSION[branchid]';";
			} else {
				$s = "insert ignore into sw_details (branch,sw_no,item_code,description,qty,unit,cost,amount) values ('$_SESSION[branchid]','$_POST[sw_no]','$_POST[icode]','".mysql_real_escape_string(htmlentities($_POST['desc']))."','".formatDigit($_POST['qty'])."','$_POST[unit]','".formatDigit($_POST['price'])."','".formatDigit($_POST['amount'])."');";
			}
			dbquery($s);
			showDetails($_POST['sw_no']);
		break;
		
		case "deleteDetails":
			dbquery("delete from sw_details where line_id = '$_POST[lid]';");
			showDetails($_POST['sw_no']);
		break;

		case "usabQty":
			dbquery("update sw_details set qty = '".formatDigit($_POST['val'])."' where line_id = '$_POST[lid]' and branch = '$_SESSION[branchid]';");
		break;
		case "check4print":
			list($a) = getArray("select count(*) from sw_header where sw_no = '$_POST[sw_no]' and branch = '$_SESSION[branchid]';");
			list($b) = getArray("select count(*) from sw_details where sw_no = '$_POST[sw_no]' and branch = '$_SESSION[branchid]';");
			
			if($a == 0 && $b > 0) { echo "head"; }
			if($b == 0 && $a > 0) { echo "det"; }
			if($a == 0 && $b == 0) { echo "both"; }
			if($a > 0 && $b > 0) { echo "noerror"; }
		break;
		
		case "finalizeSW":
			dbquery("update sw_header set status = 'Finalized', updated_by = '$_SESSION[userid]', updated_on = now() where sw_no ='$_POST[sw_no]' and branch = '$_SESSION[branchid]';");
			$iquery = dbquery("SELECT a.branch, a.sw_no AS doc_no, a.sw_date AS doc_date, withdrawn_by AS customer, b.item_code, b.unit, b.qty FROM sw_header a INNER JOIN sw_details b ON a.sw_no = b.sw_no AND a.branch = b.branch WHERE a.sw_no = '$_POST[sw_no]' AND a.branch = '$_SESSION[branchid]';");
			while($ibook = mysql_fetch_array($iquery)) {
				dbquery("INSERT IGNORE INTO ibook (doc_no,doc_date,doc_type,doc_branch,cname,item_code,uom,pullouts,posted_by,posted_on) VALUES ('$ibook[doc_no]','$ibook[doc_date]','SW','$ibook[branch]','".mysql_real_escape_string($ibook['customer'])."','$ibook[item_code]','$ibook[unit]','$ibook[qty]','$_SESSION[userid]',now());");
			}

		break;
		case "reopenSW":
			dbquery("update sw_header set status = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where sw_no = '$_POST[sw_no]' and branch = '$_SESSION[branchid]';");
			dbquery("delete from ibook where doc_no = '$_POST[sw_no]' and doc_branch = '$_SESSION[branchid]' and doc_type = 'SW';");
			
		break;
		case "cancel":
			dbquery("update sw_header set status = 'Cancelled', updated_by = '$_SESSION[userid]', updated_on = now() where sw_no = '$_POST[sw_no]' and branch = '$_SESSION[branchid]';");
		break;
		case "reloadSW" :
			$swlist=dbquery("SELECT sw_no,LPAD(sw_no,6,0) AS label_no,DATE_FORMAT(sw_date,'%m/%d/%Y') label_date,sw_date,left(remarks,25) remarks FROM ssjfc.sw_header a WHERE a.branch = '$_POST[branch]' AND a.company = '1' ORDER BY sw_no,sw_date;");
			$table.= "<table width=100%>
							<td width=15%> </td>
							<td width=15%> </td>
							<td width=68%> </td>
							<td> </td>
					  ";
			while($iRow = mysql_fetch_array($swlist)){
				$table.="<tr>
								<td width=15%> $iRow[label_no] </td>
								<td width=15%> $iRow[label_date] </td>
								<td width=68%> $iRow[remarks] </td>
								<td> <input type=radio name='sw' value = '$_SESSION[company]|$_POST[branch]|$iRow[sw_no]' /></td>
						 </tr>";
			}
			$table.= "</table>";
			echo $table;
		break;
		
	}

	mysql_close($con);

?>