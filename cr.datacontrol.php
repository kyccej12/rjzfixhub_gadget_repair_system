<?php
	session_start();
	include("handlers/_generics.php");
	include("functions/cr.displayDetails.fnc.php");
	
	$con = new _init();

	function updateHeadAmount($trans_no) {

		global $con;

		list($disc) = $con->getArray("select discount from cr_header where trans_no = '$trans_no';");
		list($amt) = $con->getArray("select sum(amount_paid) from cr_details where trans_no = '$trans_no';");
		list($ewt) = $con->getArray("select sum(amount) from cr_ewt where trans_no = '$trans_no';");
		$con->dbquery("update ignore cr_header set amount = 0$amt, net = ROUND(0$amt-0$disc-0$ewt,2), ewt = 0$ewt where trans_no = '$trans_no';");
	}

	function ideductAngPayment($ino,$refno,$type,$amount,$customer) {
		
		global $con;
		$con->dbquery("update ignore invoice_header set balance = balance - $amount, applied_amount = applied_amount + $amount where doc_no = '$ino';");
	
	}

	function iuliAngPayment($ino,$refno,$type,$amount,$customer) {
		
		global $con;
		$con->dbquery("update ignore invoice_header set balance = balance + $amount, applied_amount = applied_amount - $amount where doc_no = '$ino';");

	}

	mysql_query("START TRANSACTION;");
	switch($_POST['mod']) {
		case "saveHeader":
			list($a,$cid) = $con->getArray("select `status`,lpad(customer,6,0) from cr_header where trans_no = '$_POST[trans_no]';");
			if($a != "") {
				if($a != 'Posted' || $a != 'Cancelled') {
					
					if($cid != $_POST['ccode']) {
						$con->dbquery("delete from cr_details where trans_no = '$_POST[trans_no]';");
						updateHeadAmount($_POST['trans_no']);
					}
					
					
					$con->dbquery("update ignore cr_header set cr_no='$_POST[cr_no]', trans_ref_no = '$_POST[trans_ref_no]', cr_date='".$con->formatDate($_POST['cr_date'])."', customer='$_POST[ccode]', customer_name='".$con->escapeString(htmlentities($_POST['cname']))."', customer_addr = '".$con->escapeString(htmlentities($_POST['address']))."', pay_type = '$_POST[pay_type]', bank='$_POST[bank]', check_no='$_POST[check_no]', check_date='".$con->formatDate($_POST['check_date'])."', deposited_to = '$_POST[acct]', amount_received = '".$con->formatDigit($_POST['amt_paid'])."', remarks='".$con->escapeString(htmlentities($_POST['remarks']))."',updated_by='$_SESSION[userid]', updated_on=now() where trans_no='$_POST[trans_no]';");
				}
			} else {
				$con->dbquery("insert ignore into cr_header (trans_no,cr_no,trans_ref_no,cr_date,customer,customer_name,customer_addr,pay_type,bank,check_no,check_date,amount_received,remarks,created_by,created_on) values ('$_POST[trans_no]','$_POST[cr_no]','$_POST[trans_ref_no]','".$con->formatDate($_POST['cr_date'])."','$_POST[ccode]','".$con->escapeString(htmlentities($_POST['cname']))."','".$con->escapeString(htmlentities($_POST['address']))."','$_POST[pay_type]','$_POST[bank]','$_POST[check_no]','".$con->formatDate($_POST['check_date'])."','".$con->formatDigit($_POST['amt_paid'])."','".$con->escapeString(htmlentities($_POST['remarks']))."','$_SESSION[userid]',now());");
			}
			
			
			showDetails($_POST['trans_no']);
		break;
		
		case "applySelected":
			
			list($isE) = $con->getArray("select count(*) from cr_details where doc_no = '$_POST[doc_no]' and trans_no = '$_POST[trans_no]' and invoice_no = '$_POST[ref_no]' and ref_type = '$_POST[ref_type]';");
			if($isE > 0) {
				
				if($con->formatDigit($_POST['appliedamount']) == 0) {
					$con->dbquery("delete from cr_details where doc_no = '$_POST[doc_no]' and trans_no = '$_POST[trans_no]' and invoice_no = '$_POST[ref_no]' and ref_type = '$_POST[ref_type]';");
				} else {
					$con->dbquery("update ignore cr_details set amount_paid = '".$con->formatDigit($_POST['appliedamount'])."' where  doc_no = '$_POST[doc_no]' and trans_no = '$_POST[trans_no]' and invoice_no = '$_POST[ref_no]' and ref_type = '$_POST[ref_type]';");
				}
			} else {
				$con->dbquery("insert ignore into cr_details (trans_no,doc_no,invoice_no,invoice_date,ref_type,terms,due_date,balance_due,amount_paid) values ('$_POST[trans_no]','$_POST[doc_no]','$_POST[ref_no]','$_POST[doc_date]','$_POST[ref_type]','$_POST[terms]','".$con->formatDate($_POST['duedate'])."','".$con->formatDigit($_POST['balancedue'])."','".$con->formatDigit($_POST['appliedamount'])."');");
				
			}
			
			updateHeadAmount($_POST['trans_no']);
			
		break;
		case "applyRebates":
			if(mysql_num_rows(mysql_query("select trans_no from cr_header where trans_no = '$_POST[trans_no]';"))) {
				$con->dbquery("update ignore cr_header set discount = '$_POST[disc]', net = '$_POST[net]', updated_by = '$_SESSION[userid]', updated_on = now() where trans_no = '$_POST[trans_no]';");
			} else { echo "error"; }
		break;
		case "getTotals":
			list($gross) = $con->getArray("select sum(amount_paid) from cr_details where trans_no = '$_POST[trans_no]';");
			list($ewt) = $con->getArray("select sum(amount) from cr_ewt where trans_no = '$_POST[trans_no]';");
			if($gross == "") { $gross = "0.00"; }
			if($ewt == "") { $ewt = "0.00"; }
			echo json_encode(array("gross"=>$gross,"ewt"=>$ewt));
		break;

		case "check4print":
			updateHeadAmount($_POST['trans_no']);
			list($aaa) = $con->getArray("select count(*) from cr_details where trans_no = '$_POST[trans_no]';");
			if($aaa > 0) {

					//list($disc,$net,$ewt,$dbAcct) = $con->getArray("select discount, net, ewt, deposited_to from cr_header where trans_no = '$_POST[trans_no]';");
					$con->dbquery("update ignore cr_header set status = 'Posted', updated_by = '$_SESSION[userid]', updated_on = now() where trans_no = '$_POST[trans_no]';");
					
					$dQuery = $con->dbquery("select doc_no, invoice_no, ref_type, amount_paid from cr_details where trans_no = '$_POST[trans_no]';");
					while($dRow = $dQuery->fetch_array()) { ideductAngPayment($dRow['doc_no'],$dRow['invoice_no'],$dRow['ref_type'],$dRow['amount_paid'],$_POST['cid']); }
					echo "noerror";
			} else { echo "waySulod"; }
		break;

		case "reopenCR":
			$con->dbquery("delete from acctg_gl where doc_no = '$_POST[trans_no]' and doc_type = 'CR';");
			$con->dbquery("update ignore cr_header set status = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where trans_no = '$_POST[trans_no]';");
			list($cust) = $con->getArray("select customer from cr_header where trans_no = '$_POST[trans_no]';");
			$dQuery = $con->dbquery("select doc_no, invoice_no, ref_type, amount_paid from cr_details where trans_no = '$_POST[trans_no]';");
			while($dRow = $dQuery->fetch_array()) { iuliAngPayment($dRow['doc_no'],$dRow['invoice_no'],$dRow['ref_type'],$dRow['amount_paid'],$cust);	}
		break;

		case "reuse":
			$con->dbquery("delete from acctg_gl where doc_no = '$_POST[trans_no]' and doc_type = 'CR';");
			$con->dbquery("update ignore cr_header set status = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where trans_no = '$_POST[trans_no]';");
		break;
		
		case "cancel":
			$con->dbquery("update ignore cr_header set status='Cancelled', cancelled_by='$_SESSION[userid]', cancelled_on = now() where trans_no ='$_POST[trans_no]';");
		break;
		
		case "fetchInvoiceDetails":
			echo json_encode($con->getArray("SELECT doc_no, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS dd8, amount_paid as amt FROM cr_details WHERE line_id = '$_POST[lid]';"));
		break;
		
		case "computeEWT":
			list($ewt) = $con->getArray("SELECT ROUND(('$_POST[amount]' / 1.12) * (rate/100),2) FROM options_atc WHERE atc_code = '$_POST[atc]';");
			echo $ewt;
		break;
		
		case "applyCreditableTax":
			$con->dbquery("INSERT IGNORE INTO cr_ewt (trans_no, si_docno, si_docdate, si_amount, cert_date, atc_code, amount) VALUES ('$_POST[trans_no]','$_POST[doc_no]','".$con->formatDate($_POST['docdate'])."','$_POST[docamt]','".$con->formatDate($_POST['certdate'])."','$_POST[atc]','$_POST[ewt]');");
			updateHeadAmount($_POST['trans_no']);
		break;
		case "viewCreditableTaxes":
			$a = $con->dbquery("select * from cr_ewt where trans_no = '$_POST[trans_no]';");
			echo "<table width=100% cellpadding=0 cellspacing=0>";
			$i = 0;
			while($b = mysql_fetch_array($a)) {
				if($i%2==0){ $bgC = "#ededed"; } else { $bgC = "#ffffff"; }
				echo "<tr bgcolor=\"$bgC\">
					<td width=20% class=grid align=center>$b[si_docno]</td>
					<td width=20% class=grid align=center>$b[si_docdate]</td>
					<td width=20% class=grid align=right style=\"padding-right: 20px;\">".number_format($b[si_amount],2)."</td>
					<td width=20% class=grid align=center>$b[atc_code]</td>
					<td width=20% class=grid align=right style=\"padding-right: 5px;\">".number_format($b[amount],2)."&nbsp;&nbsp;<a href=\"#\" onclick=\"removeCreditableTax($b[record_id]);\"><img src=\"images/icons/bin.png\" width=14 height=14 align=absmiddle /></a></td>
				</tr>"; $i++;
			}
			
			if($i < 10) {
				for($i; $i <= 10; $i++) {
					if($i%2==0){ $bgC = "#ededed"; } else { $bgC = "#ffffff"; }
					echo "<tr bgcolor=\"$bgC\"><td colspan=5 width=100% class=grid>&nbsp;</td></tr>";
				}
			}	
			echo "</table>";
		break;
		case "removeCreditableTax":
			$con->dbquery("delete from cr_ewt where record_id = '$_POST[lid]';");
			$a = $con->dbquery("select * from cr_ewt where trans_no = '$_POST[trans_no]';");
			echo "<table width=100% cellpadding=0 cellspacing=0>";
			$i = 0;
			while($b = mysql_fetch_array($a)) {
				if($i%2==0){ $bgC = "#ededed"; } else { $bgC = "#ffffff"; }
				echo "<tr bgcolor=\"$bgC\">
					<td width=20% class=grid align=center>$b[si_docno]</td>
					<td width=20% class=grid align=center>$b[si_docdate]</td>
					<td width=20% class=grid align=right style=\"padding-right: 20px;\">".number_format($b[si_amount],2)."</td>
					<td width=20% class=grid align=center>$b[atc_code]</td>
					<td width=20% class=grid align=right style=\"padding-right: 5px;\">".number_format($b[amount],2)."&nbsp;&nbsp;<a href=\"#\" onclick=\"removeCreditableTax($b[line_id]);\"><img src=\"images/icons/bin.png\" width=14 height=14 align=absmiddle /></a></td>
				</tr>"; $i++;
			}
			
			if($i < 10) {
				for($i; $i <= 10; $i++) {
					if($i%2==0){ $bgC = "#ededed"; } else { $bgC = "#ffffff"; }
					echo "<tr bgcolor=\"$bgC\"><td colspan=5 width=100% class=grid>&nbsp;</td></tr>";
				}
			}	
			echo "</table>";
			updateHeadAmount($_POST['trans_no']);
		break;
	}
	mysql_query("COMMIT;");
?>
