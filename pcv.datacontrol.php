<?php

	//ini_set("display_errors","On");

	session_start();
	require 'handlers/_generics.php';

	$mydb = new _init;
	
	switch($_POST['mod']) {
		case "saveHeader":
			list($isExist) = $mydb->getArray("select count(*) from pcv where pcvNo = '$_POST[pcv_no]';");
			if($isExist > 0) {
				$mydb->dbquery("update pcv set pcvDate = '".$mydb->formatDate($_POST['pcv_date'])."',proj_code = '$_POST[proj_code]',payeeCode = '$_POST[payee_code]',payeeName = '".$mydb->escapeString($_POST['payee_name'])."',". 
							    "particulars = '".$mydb->escapeString($_POST['particulars'])."',pcvAccount = '$_POST[pcv_type]', approvedBy = '$_POST[approved_by]', approvedDate = '".$mydb->formatDate($_POST['approved_date'])."', amount = '".$mydb->formatDigit($_POST['amount'])."'," .
								"isLiquidated = '$_POST[is_liquidated]',liquidatedOn = '".$mydb->formatDate($_POST['date_liquidated'])."', amountLiquidated = '".$mydb->formatDigit($_POST['amount_liquidated'])."', liquidationRemarks = '".$mydb->escapeString($_POST['liquidation_remarks'])."', updatedBy = '$_SESSION[userid]',updatedOn = now() where pcvNo = '$_POST[pcv_no]' and branch = '1';");
								
			} else {
				$mydb->dbquery("INSERT IGNORE INTO pcv (branch,pcvNo,pcvDate,proj_code,payeeCode,payeeName,particulars,pcvAccount,approvedBy,approvedDate,amount,isLiquidated,liquidatedOn,amountLiquidated,createdBy,createdOn) " .
								" VALUES ('1','$_POST[pcv_no]','".$mydb->formatDate($_POST['pcv_date'])."','$_POST[proj_code]','$_POST[payee_code]','".$mydb->escapeString($_POST['payee_name'])."',". 
							    "'".$mydb->escapeString($_POST['particulars'])."','$_POST[pcv_type]','$_POST[approved_by]','".$mydb->formatDate($_POST['approved_date'])."','".$mydb->formatDigit($_POST['amount'])."'," .
								"'$_POST[is_liquidated]','".$mydb->formatDate($_POST['date_liquidated'])."','".$mydb->formatDigit($_POST['amount_liquidated'])."','$_SESSION[userid]',now());");
			}
		break;
		case "saveLiquidation":
			list($isExist) = $mydb->getArray("select count(*) from pcv_liquidation where pcv_no = '$_POST[pcv_no]';");
			if($isExist > 0) {
				$mydb->dbquery("update pcv_liquidation set date_liquidated = '".$mydb->formatDate($_POST['liquidation_date'])."', payee_name = '".$mydb->escapeString($_POST[supplier])."',payee_address = '".$mydb->escapeString($_POST['address'])."', payee_tin = '$_POST[tin]', invoice_no = '$_POST[invoice]', invoice_date = '".$mydb->formatDate($_POST['invoice_date'])."', amount = '".$mydb->formatDigit($_POST['invoice_amount'])."', other_amount = '".$mydb->formatDigit($_POST['other_amount'])."', cash_return = '".$mydb->formatDigit($_POST['return_amount'])."', particulars = '".$mydb->escapeString($_POST['liquidation_particulars'])."', updatedBy = '$_SESSION[userid]', updatedOn = now() where pcv_no = '$_POST[pcv_no]';");
			} else {
				$mydb->dbquery("insert into pcv_liquidation (branch,pcv_no,date_liquidated,payee_name,payee_address,payee_tin,invoice_no,invoice_date,amount,other_amount,cash_return,particulars,dateEncoded,encodedBy) values ('1','$_POST[pcv_no]','".$mydb->formatDate($_POST['liquidation_date'])."','".$mydb->escapeString($_POST[supplier])."','".$mydb->escapeString($_POST['address'])."','$_POST[tin]','$_POST[invoice]','".$mydb->formatDate($_POST['invoice_date'])."','".$mydb->formatDigit($_POST['invoice_amount'])."','".$mydb->formatDigit($_POST['other_amount'])."','".$mydb->formatDigit($_POST['return_amount'])."','".$mydb->escapeString($_POST['liquidation_particulars'])."','$_SESSION[userid]',now());");
			}
			
			$mydb->dbquery("update pcv set isLiquidated = 'Y', amountLiquidated = 0".$mydb->formatDigit($_POST['invoice_amount'])."+0".$mydb->formatDigit($_POST['other_amount'])."+0".$mydb->formatDigit($_POST['return_amount']).", liquidatedOn = '".$mydb->formatDate($_POST['liquidation_date'])."' where pcvNo = '$_POST[pcv_no]';");
		break;
		case "clearLiquidation":
			$mydb->dbquery("delete from pcv_liquidation where pcv_no = '$_POST[pcv_no]';");
			$mydb->dbquery("update pcv set isLiquidated = 'N', amountLiquidated = 0, liquidatedOn = '0000-00-00' where pcvNo = '$_POST[pcv_no]';");
		break;
		case "retrieveLiquidation":
			echo json_encode($mydb->getArray("select *, if(invoice_date!='0000-00-00',date_format(invoice_date,'%m/%d/%Y'),'') as idate, if(date_liquidated!='0000-00-00',date_format(date_liquidated,'%m/%d/%Y'),'') as ldate, format(amount,2) as amt, format(amount,2) as amt, format(other_amount,2) as oamt, format(cash_return,2) as camt from pcv_liquidation where pcv_no = '$_POST[pcv_no]';"));
		break;
		case "finalize":
			$mydb->dbquery("update pcv set `status` = 'Finalized', finalizedBy = '$_SESSION[userid]', finalizedOn = now() where pcvNo = '$_POST[pcv_no]' and branch = '1';");
		break;
		case "post":
			$a = $mydb->getArray("select *,date_format(pcvDate,'%Y') as cy from pcv where pcvNo = '$_POST[pcvNo]';");
			$b = $mydb->getArray("select * from pcv_liquidation where pcv_no = '$_POST[pcvNo]';");
			
			$netPCV = $b['amount'] + $b['other_amount'];
			if($b['payee_tin'] != '') {
				$vat = ROUND(($b['amount'] / 1.12) * 0.12,2);
				$net = $b['amount'] - $vat;
			} else { $vat = 0; $net = $b['amount']; }
			
			if($b['other_amount'] > 0) {
				$mydb->dbquery("INSERT IGNORE INTO acctg_gl (branch,cy,doc_no,doc_date,doc_type,acct,acct_branch,debit,cost_center,doc_remarks,posted_by,posted_on) VALUES ('1','$a[cy]','$a[pcvNo]','$a[pcvDate]','PCV','50101','1','$b[other_amount]','$a[proj_code]','".$mydb->escapeString($a['particulars'])."','$_SESSION[userid]',NOW());");
			}
			$mydb->dbquery("INSERT IGNORE INTO acctg_gl (branch,cy,doc_no,doc_date,doc_type,acct,acct_branch,credit,cost_center,doc_remarks,posted_by,posted_on) VALUES ('1','$a[cy]','$a[pcvNo]','$a[pcvDate]','PCV','10101','1','$netPCV','$a[proj_code]','".$mydb->escapeString($a['particulars'])."','$_SESSION[userid]',NOW());");
			$mydb->dbquery("INSERT IGNORE INTO acctg_gl (branch,cy,doc_no,doc_date,doc_type,acct,acct_branch,debit,cost_center,doc_remarks,posted_by,posted_on) VALUES ('1','$a[cy]','$a[pcvNo]','$a[pcvDate]','PCV','$a[pcvAccount]','1','$net','$a[proj_code]','".$mydb->escapeString($a['particulars'])."','$_SESSION[userid]',NOW());");
			if($vat > 0) {
				$mydb->dbquery("INSERT IGNORE INTO acctg_gl (branch,cy,doc_no,doc_date,doc_type,acct,acct_branch,debit,cost_center,doc_remarks,posted_by,posted_on) VALUES ('1','$a[cy]','$a[pcvNo]','$a[pcvDate]','PCV','10401','1','$vat','$a[proj_code]','".$mydb->escapeString($a['particulars'])."','$_SESSION[userid]',NOW());");
			}
			
			$mydb->dbquery("update pcv set `status` = 'Posted', postedBy = '$_SESSION[userid]', postedOn = now() where pcvNo = '$_POST[pcvNo]' and branch = '1';");
		break;
		case "unpost":
			$mydb->dbquery("delete from acctg_gl where doc_no = '$_POST[pcvNo]' and doc_type = 'PCV' and branch = '1';");
			$mydb->dbquery("update pcv set `status` = 'Finalized', postedBy = '', postedOn = '' where pcvNo = '$_POST[pcvNo]' and branch = '1';");
		break;
		case "cancel":
			$mydb->dbquery("update pcv set `status` = 'Cancelled', cancelledBy = '$_SESSION[userid]', cancelledOn = now(), postedBy = '', postedOn = '' where pcvNo = '$_POST[pcv_no]' and branch = '1';");
		break;
		case "reopen":
			$mydb->dbquery("update pcv set `status` = 'Active', updatedBy = '$_SESSION[userid]', updatedOn = now(), cancelledBy = '', cancelledOn = '' where pcvNo = '$_POST[pcv_no]' and branch = '1';");
		break;
	}


?>