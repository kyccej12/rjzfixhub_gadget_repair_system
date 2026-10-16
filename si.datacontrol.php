<?php
	session_start();
	ini_set("max_execution_time",0);
	include("handlers/_generics.php");

	$con = new _init;

	include("functions/si.displayDetails.fnc.php");
	
	function updateHeaderAmt($traceno) {

		global $con;

		list($terms) = $con->getArray("select terms from invoice_header where trace_no = '$_POST[trace_no]';");
		list($gross,$discount) = $con->getArray("SELECT IFNULL(SUM(ROUND(qty*unit_price,2)),0) AS gross, IFNULL(SUM(ROUND(discount*qty,2)),0) AS discount FROM invoice_details WHERE trace_no = '$traceno';");	

		$bal = ROUND(($gross-$discount),2);
		$con->dbquery("update ignore invoice_header set amount=(0$gross-0$discount), discount=0$discount, balance=0$bal where trace_no = '$traceno';");
	}
	
	function postToGL($traceno) {
		
		global $con;
	
		/* Sweep to avoid duplication */	
		list($docNo) = $con->getArray("select doc_no from invoice_header where trace_no = '$traceno';");
		$con->dbquery("DELETE FROM ibook where doc_no = '$docNo' and doc_type = 'SI';");	
		$con->dbquery("INSERT IGNORE INTO ibook (doc_no,doc_date,doc_type,ccode,cname,item_code,uom,sold,posted_by,posted_on) SELECT a.doc_no, a.invoice_date AS doc_date, 'SI' AS doc_type, a.customer AS ccode, if(a.customer='0','CASH WALKIN SALES',a.customer_name) AS cname, b.item_code, b.unit AS uom, b.qty AS sold, '$_SESSION[userid]' AS posted_by, NOW() AS posted_on FROM invoice_header a LEFT JOIN invoice_details b ON a.trace_no = b.trace_no WHERE a.trace_no = '$traceno';");
		
	}

	switch($_REQUEST['mod']) {
		case "saveHeader":
			list($isE) = $con->getArray("select count(*) from invoice_header where trace_no = '$_POST[trace_no]';");
			if($isE > 0) {
				$s = "update ignore invoice_header set customer = '$_POST[cid]', customer_name = '".$con->escapeString(htmlentities($_POST['cname']))."', customer_addr = '".$con->escapeString(htmlentities($_POST['addr']))."', sales_rep = '$_POST[srep]', terms = '$_POST[terms]', invoice_date = '".$con->formatDate($_POST['invoice_date'])."', posting_date = '".$con->formatDate($_POST['postDate'])."', remarks = '".$con->escapeString($_POST['remarks'])."', updated_by = '$_SESSION[userid]', updated_on = now() where trace_no = '$_POST[trace_no]';";
				$docno = $_POST['doc_no'];
				
				/* Audit Trail */
				$con->dbquery("insert ignore into traillog (branch,user_id,`timestamp`,ipaddress,module,`action`,doc_no) values ('$_SESSION[branchid]','$_SESSION[userid]',now(),'$_SERVER[REMOTE_ADDR]','SI','UPDATED SALES INVOICE # $docno -> CUSTOMER = $_POST[cid] -> CNAME = ".$con->escapeString(htmlentities($_POST['cname']))." -> INV DATE = $_POST[invoice_date] -> TERMS = $_POST[terms] -> DISCOUNT = $_POST[discount]','$ino');");
				updateHeaderAmt($_POST['trace_no']);
		
			} else {
				
				$s = "insert ignore into invoice_header (doc_no, invoice_date, posting_date, customer, customer_name, customer_addr, sales_rep, discount, terms, remarks, created_by, created_on,trace_no) values ('$_POST[doc_no]','".$con->formatDate($_POST['invoice_date'])."','".$con->formatDate($_POST['postDate'])."','$_POST[cid]','".$con->escapeString(htmlentities($_POST['cname']))."','".$con->escapeString(htmlentities($_POST['addr']))."','$_POST[srep]','$_POST[discount]','$_POST[terms]','".$con->escapeString($_POST['remarks'])."','$_SESSION[userid]',now(),'$_POST[trace_no]');";
			
				/* Audit Trail */
				$con->dbquery("insert ignore into traillog (user_id,`timestamp`,ipaddress,module,`action`,doc_no) values ('$_SESSION[userid]',now(),'$_SERVER[REMOTE_ADDR]','SI','CREATED DOC # $_POST[doc_no] -> CUSTOMER = $_POST[cid] -> CNAME = ".$con->escapeString(htmlentities($_POST['cname']))." -> INV DATE = $_POST[invoice_date] -> TERMS = $_POST[terms] -> DISCOUNT = $_POST[discount]','$ino');");
			
			}
			$con->dbquery($s);
			echo json_encode(array('docno'=>$docno));
		break;
		case "insertDetail":
			$con->dbquery("insert ignore into invoice_details (doc_no,item_code,description,qty,unit,unit_price,amount,trace_no) values ('$_POST[doc_no]','$_POST[icode]','".$con->escapeString($_POST['desc'])."','".$con->formatDigit($_POST['qty'])."','$_POST[unit]','".$con->formatDigit($_POST['price'])."','".$con->formatDigit($_POST['amount'])."','$_POST[trace_no]');");
			
			/* AUDIT TRAIL PURPOSES */
			$con->dbquery("insert ignore into traillog (user_id,`timestamp`,ipaddress,module,`action`,doc_no) values ('$_SESSION[userid]',now(),'$_SERVER[REMOTE_ADDR]','SI','PRODUCT MANUALLY ADDED TO SALES INVOICE # $_POST[invoice_no] -> ITEM = $_POST[icode] -> QTY = $_POST[qty] -> PRICE = $_POST[price]','$_POST[invoice_no]');");
			
			updateHeaderAmt($_POST['trace_no']);
			showDetails($_POST['trace_no'],$status = 'Active', $lock = 'N',$urights = 'admin');
		break;

		case "addItemByCode":

			$item = $con->getArray("SELECT item_code, description, unit, srp FROM products_master WHERE (item_code = '$_POST[bcode]' OR barcode = '$_POST[bcode]') LIMIT 1;");
			list($isAdded) = $con->getArray("SELECT COUNT(*) from invoice_details where trace_no = '$_POST[trace_no]' and item_code = '$item[item_code]';");
			if($isAdded > 0) {
				list($qty) = $con->getArray("SELECT qty+1 from invoice_details where trace_no = '$_POST[trace_no]' and item_code = '$item[item_code]';");
				$amount = ROUND($qty * $item['srp'],2);
				$con->dbquery("UPDATE IGNORE invoice_details set qty = '$qty', amount = '$amount' WHERE trace_no = '$_POST[trace_no]' AND item_code = '$item[item_code]';");
			} else {
				$con->dbquery("INSERT IGNORE INTO invoice_details (doc_no,item_code,description,qty,unit,unit_price,amount,trace_no) VALUES ('$_POST[doc_no]','$item[item_code]','".$con->escapeString($item['description'])."','1','$item[qty]','$item[srp]','$item[srp]','$_POST[trace_no]');");
			}

			updateHeaderAmt($_POST['trace_no']);
			showDetails($_POST['trace_no'],$status = 'Active', $lock = 'N',$urights = 'admin');
		break;

		case "getTotals":
			list($terms) = $con->getArray("select terms from invoice_header where trace_no = '$_POST[trace_no]';");
			list($gross,$net,$discount) = $con->getArray("SELECT SUM(ROUND(qty*unit_price,2)) AS gross, SUM(ROUND(qty*(unit_price-discount),2)) as net, SUM(ROUND(discount*qty,2)) AS discount FROM invoice_details WHERE trace_no = '$_POST[trace_no]';");	
			if($gross=="") { $gross="0.00"; $discount="0.00"; $net="0.00"; }
			echo json_encode(array("gross"=>$gross, "net"=>$net, "discount" => $discount));
		break;
	

		case "deleteLine":
			$det = $con->getArray("select * from invoice_details where line_id = '$_POST[lid]';");
			
			/* AUDIT TRAIL PURPOSES */
			$con->dbquery("insert ignore into traillog (user_id,`timestamp`,ipaddress,module,`action`,doc_no) values ('$_SESSION[userid]',now(),'$_SERVER[REMOTE_ADDR]','SI','PRODUCT DELETED FROM SALES INVOICE WITH DOC # $det[doc_no] -> SO # = $det[so_no] -> ITEM = $det[item_code] -> QTY = $det[qty] -> PRICE = $det[unit_price]','$det[invoice_no]');");
						
			$con->dbquery("delete from invoice_details where line_id = '$_POST[lid]';");
			updateHeaderAmt($_POST['trace_no']);
			showDetails($_POST['trace_no'],$status = 'Active',$lock = 'N',$urights = 'admin');
		break;

		case "getQty":
			list($iqty) = $con->getArray("select qty from invoice_details where line_id = '$_POST[lineid]';");
			echo $iqty;
		break;
		
		case "usabQty":
			$t = $con->getArray("select * from invoice_details where line_id = '$_POST[lid]';");

			$discount = ROUND($_POST['qty'] * $t['discount'],2);
			$gross = ROUND($t['unit_price'] * $_POST['qty'],2);
			$due = $gross - $discount;

			$con->dbquery("update ignore invoice_details set qty = '$_POST[qty]', amount = '$due' where line_id = '$_POST[lid]';");
			updateHeaderAmt($_POST['trace_no']);
			showDetails($_POST['trace_no'],$status = 'Active',$lock = 'N',$urights = 'admin');
		break;
		
		case "usabPrice":
			$amt = ROUND($con->formatDigit($_POST['price']) * $con->formatDigit($_POST['qty']),2);
			list($disc) = $con->getArray("select ROUND(".$con->formatDigit($_POST['price'])." * (discount_percent/100),2) from invoice_details where line_id = '$_POST[lid]';");
			$con->dbquery("update ignore invoice_details set unit_price = '".$con->formatDigit($_POST['price'])."', discount = 0$disc, amount = ROUND(0$amt - (".$con->formatDigit($_POST['qty'])." * 0$disc),2) where line_id = '$_POST[lid]';");
			updateHeaderAmt($_POST['trace_no']);
			showDetails($_POST['trace_no'],$status = 'Active',$lock = 'N',$urights = 'admin');
		break;
		
		case "check4print":
			list($a) = $con->getArray("select count(*) from invoice_header where trace_no = '$_POST[trace_no]';");
			list($b) = $con->getArray("select count(*) from invoice_details where trace_no = '$_POST[trace_no]';");
			
			if($a == 0 && $b > 0) { echo "head"; }
			if($b == 0 && $a > 0) { echo "det"; }
			if($a == 0 && $b == 0) { echo "both"; }
			if($a > 0 && $b > 0) { echo "noerror"; }
		break;
		
		case "finalize":
			$con->dbquery("update ignore invoice_header set `status` = 'Finalized', updated_by = '$_SESSION[userid]', updated_on = now() where trace_no ='$_POST[trace_no]';");
			
			/* Perform Sweeping for Stary Records with same Doc No */
			$con->dbquery("DELETE FROM invoice_details where doc_no = '$_POST[doc_no]' and trace_no != '$_POST[trace_no]';");


			updateHeaderAmt($_POST['trace_no']);
			postToGL($_POST['trace_no']);
		break;
		
		case "flushTempGL":
			$con->dbquery("delete from tmp_salesgl where trace_no = '$_POST[trace_no]';");
		break;
		
		case "reopen":
			$con->dbquery("update ignore invoice_header set `status` = 'Active',applied_amount=0, updated_by = '$_SESSION[userid]', updated_on = now() where trace_no = '$_POST[trace_no]';");
			$con->dbquery("delete from ibook where doc_no = '$_POST[doc_no]' and doc_type = 'SI';");
	
			/* AUDIT TRAIL PURPOSES */
			$con->dbquery("insert ignore into traillog (user_id,`timestamp`,ipaddress,module,`action`,doc_no) values ('$_SESSION[userid]',now(),'$_SERVER[REMOTE_ADDR]','SI','SALES INVOICE # $_POST[invoice_no] RE-OPENED BY USER','$_POST[invoice_no]');");

		break;
		
		case "cancel":
			$con->dbquery("update ignore invoice_header set `status` = 'Cancelled', updated_by = '$_SESSION[userid]', updated_on = now() where trace_no = '$_POST[trace_no]';");
			
			/* AUDIT TRAIL PURPOSES */
			$con->dbquery("insert ignore into traillog (user_id,`timestamp`,ipaddress,module,`action`,doc_no) values ('$_SESSION[branchid]','$_SESSION[userid]',now(),'$_SERVER[REMOTE_ADDR]','SI','SALES INVOICE # $_POST[invoice_no] CANCELLED BY USER','$_POST[invoice_no]');");
		
		break;

		case 'applyDiscount':
			list($price,$qty,$gross) = $con->getArray("select unit_price, qty, ROUND(qty*unit_price,2) as gross from invoice_details where line_id = '$_POST[lineid]';");
			
			if($_POST['type'] == "PCT") {
				$pctW = $_POST['discount'];
				$pct = ROUND($_POST[discount]/100,2);
				$dUoM = ROUND($price * $pct,2);
			} else {
				$pctW = 0;
				$dUoM = $_POST['discount'];
			}
			
			$tDisc = ROUND($dUoM * $qty,2);
			$netOfDisc = $gross - $tDisc;

			$con->dbquery("UPDATE ignore invoice_details SET discount = 0$dUoM, amount = 0$netOfDisc, discount_percent = '$pctW' WHERE line_id = '$_POST[lineid]';");
			updateHeaderAmt($_POST['trace_no']);
			showDetails($_POST['trace_no'],$status = 'Active',$lock = 'N',$urights = 'admin');
		break;

		case "getAmountDue":
			list($adue) = $con->getArray("select balance from invoice_header where trace_no = '$_POST[tmpfileid]';");
			echo number_format($adue,2);
		break;

		case 'finalizePOScash':
			$con->dbquery("UPDATE ignore invoice_header a SET a.balance = '0', applied_amount = '".$con->formatDigit($_POST['due'],2)."', amount_tendered = '".$con->formatDigit($_POST['tendered'])."', change_due = '".$con->formatDigit($_POST['change'])."', `status` = 'Finalized', pay_type='cash'  WHERE a.trace_no = '$_POST[trace_no]';");
			updateHeaderAmt($_POST['trace_no']);
			postToGL($_POST['trace_no']);
		break;
		
		case "finalizePOScard":
			$con->dbquery("UPDATE ignore invoice_header a SET applied_amount = balance,a.balance = '0',pay_type = 'ccard',card_type='$_POST[cc_type]',issue_bank='$_POST[bank]',card_holder='".$con->escapeString($_POST['cc_name'])."',card_no='$_POST[cc_no]',card_expiry='$_POST[cc_expiry]',trans_apprv='$_POST[approvalno]',`status` = 'Finalized'  WHERE a.trace_no = '$_POST[trace_no]';");
			updateHeaderAmt($_POST['trace_no']);
			postToGL($_POST['trace_no']);
		break;
		
		case "finalizeCheqCheckOut":
			$con->dbquery("UPDATE ignore invoice_header a SET applied_amount = balance, a.balance = '0', pay_type = 'check',issue_bank='$_POST[bank]',cheq_no='$_POST[cheq_no]' ,cheq_date='$_POST[cheq_date]',`status` = 'Finalized'  WHERE a.trace_no = '$_POST[trace_no]';");
			postToGL($_POST['trace_no']);
		break;

		case "clearItems":
			$con->dbquery("delete from invoice_details where trace_no = '$_POST[trace_no]';");
			$con->dbquery("update ignore invoice_heder set amount = 0, discount = 0, balance = 0, applied_amount = 0 where trace_no = '$_POST[trace_no]';");
			updateHeaderAmt($_POST['trace_no']);
			showDetails($_POST['trace_no'],$status = 'Active',$lock = 'N',$urights = 'admin');
		break;

		case "retrieve":
			$data = array();
			$srrd = $con->dbquery("SELECT line_id AS id, if(so_no='0','',so_no) as so_no, if(so_date='0000-00-00','',date_format(so_date,'%m/%d/%Y')) as sodate, item_code AS `code`, IF(custom_description!='',custom_description,description) AS description, unit, qty, unit_price AS unit_price, ROUND(qty*unit_price,2) AS amount, ROUND(qty*discount,2) as discount, amount AS amount_due FROM invoice_details WHERE trace_no = '$_REQUEST[trace_no]';");
			while($row = $srrd->fetch_array()) {
				$data[] = array_map('utf8_encode',$row);
			}
			$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
			echo json_encode($results);	
		break;
		
		case "checkActive":
		
			list($aCount) = $con->getArray("select count(*) from invoice_header where `status` = 'Active' and created_by = '$_SESSION[userid]';");
			if($aCount > 0) {
				
				$actQuery = $con->dbquery("select doc_no, trace_no from invoice_header where `status` = 'Active' and created_by = '$_SESSION[userid]';");
				while($actRow = $actQuery->fetch_array()) {
					
					list($dCount) = $con->getArray("select count(*) from invoice_details where trace_no = '$actRow[trace_no]';");
					
					if($dCount == 0) {
						echo "<a href=\"#\" onclick=\"parent.viewSI(" . $actRow['doc_no'] . ");\">DOC No.: " . STR_PAD($actRow['doc_no'],6,0,STR_PAD_LEFT) . "</a><br/>";
					}
				}
			}
		
		break;
		
		case "checkDetailCount":
			list($dCount) = $con->getArray("select count(*) from invoice_details where trace_no = '$_POST[trace_no]';");
			echo $dCount;
		break;
		
		case "checkDuplicateInvoice":
			list($dCount) = $con->getArray("select count(*) from invoice_header where invoice_no = '$_POST[inv_no]' and trace_no != '$_POST[trace_no]';");
			if($dCount > 0) { echo "notok"; }
		break;

	}
?>
