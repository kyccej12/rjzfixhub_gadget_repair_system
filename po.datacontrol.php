<?php
	session_start();
	//include("includes/dbUSE.php");

	ini_set("display_errors","On");
	require_once "handlers/_pofunct.php";
	$p = new myPO;


	switch($_POST['mod']) {
		case "saveHeader":
			list($isE) = $p->getArray("select count(*) from po_header where po_no = '$_POST[po_no]' and branch = '1';");
			if($isE > 0) {
				$s = "update ignore po_header set supplier = '$_POST[cid]', requested_by = '".$p->escapeString(htmlentities($_POST['requested_by']))."', delivery_address = '".$p->escapeString(htmlentities($_POST['del_addr']))."', date_needed = '".$p->formatDate($_POST['date_needed'])."', supplier_name = '".$p->escapeString(htmlentities($_POST['cname']))."', supplier_addr = '".$p->escapeString(htmlentities($_POST['addr']))."', po_date = '".$p->formatDate($_POST['po_date'])."', terms='$_POST[terms]', proj='$_POST[proj]', mrs_no = '$_POST[mrs]', remarks = '".$p->escapeString($_POST['remarks'])."', updated_by = '$_SESSION[userid]', updated_on = now() where po_no = '$_POST[po_no]' and branch = '1';";
			} else {
				$s = "insert ignore into po_header (branch, po_no, po_date, terms, proj, requested_by, mrs_no, delivery_address, date_needed, supplier, supplier_name, supplier_addr, remarks, created_by, created_on) values ('1','$_POST[po_no]','".$p->formatDate($_POST['po_date'])."','$_POST[terms]','$_POST[proj]','".$p->escapeString(htmlentities($_POST['requested_by']))."','$_POST[mrs]','".$p->escapeString(htmlentities($_POST['del_addr']))."','".$p->formatDate($_POST['date_needed'])."','$_POST[cid]','".$p->escapeString(htmlentities($_POST['cname']))."','".$p->escapeString($_POST['addr'])."','".$p->escapeString(htmlentities($_POST['remarks']))."','$_SESSION[userid]',now());";
			}
			$p->dbquery($s);
		break;
		case "insertDetail":
			list($isE) = $p->getArray("select count(*) from po_details where po_no = '$_POST[po_no]' and item_code = '$_POST[icode]' and branch = '1';");
			if($isE > 0) {
				$s = "update ignore po_details set qty = qty + ".$p->formatDigit($_POST['qty']).", amount = amount + ".$p->formatDigit($_POST['amount'])." where po_no = '$_POST[po_no]' and item_code = '$_POST[icode]' and branch = '1';";
			} else {
				$s = "insert ignore into po_details (branch,po_no,item_code,description,qty,unit,cost,amount) values ('1','$_POST[po_no]','$_POST[icode]','".$p->escapeString($_POST['desc'])."','".$p->formatDigit($_POST['qty'])."','$_POST[unit]','".$p->formatDigit($_POST['cost'])."','".$p->formatDigit($_POST['amount'])."');";
			}
			$p->dbquery($s);
			$p->updateHeaderAmt($_POST['po_no']);
			$p->PODETAILS($_POST['po_no'],$status="Active",$lock="N");
		break;
		case "deleteLine":
			$p->dbquery("delete from po_details where line_id = '$_POST[lid]';");
			$p->updateHeaderAmt($_POST['po_no']);
			$p->PODETAILS($_POST['po_no'],$status="Active",$lock="N");
		break;
		case "usabQty":
			$amt = ROUND($p->formatDigit($_POST['price']) * $p->formatDigit($_POST['val']),2);
			$p->dbquery("update po_details set qty = '".$p->formatDigit($_POST['val'])."', amount = (0$amt - ROUND(".$p->formatDigit($_POST['val'])." * discount,2)) where line_id = '$_POST[lid]' and branch = '1';");
			echo json_encode($p->getArray("select format(amount,2) as amt1 from po_details where line_id = '$_POST[lid]';"));
			$p->updateHeaderAmt($_POST['po_no']);
		break;
		case "usabPrice":
			$amt = ROUND($p->formatDigit($_POST['price']) * $p->formatDigit($_POST['qty']),2);
			$p->dbquery("update po_details set cost = '".$p->formatDigit($_POST['price'])."', amount = (0$amt - ROUND(".$p->formatDigit($_POST['qty'])." * discount,2)) where line_id = '$_POST[lid]' and branch = '1';");
			echo json_encode($p->getArray("select format(amount,2) as amt1 from po_details where line_id = '$_POST[lid]';"));
			$p->updateHeaderAmt($_POST['po_no']);
		break;
		case "check4print":
			list($a) = $p->getArray("select count(*) from po_header where po_no = '$_POST[po_no]' and branch = '1';");
			list($b) = $p->getArray("select count(*) from po_details where po_no = '$_POST[po_no]' and branch = '1';");
			
			if($a == 0 && $b > 0) { echo "head"; }
			if($b == 0 && $a > 0) { echo "det"; }
			if($a == 0 && $b == 0) { echo "both"; }
			if($a > 0 && $b > 0) { echo "noerror"; }
		break;
		case "finalizePO":
			$p->dbquery("update po_header set status = 'Finalized', updated_by = '$_SESSION[userid]', updated_on = now() where po_no ='$_POST[po_no]' and branch = '1';");
			$p->updateHeaderAmt($_POST['po_no']);
		break;
		case "reopenPO":
			$p->dbquery("update po_header set status = 'Active', updated_by = '$_SESSION[userid]', updated_on = now() where po_no = '$_POST[po_no]' and branch = '1';");
		break;
		case "cancel":
			$p->dbquery("update po_header set status = 'Cancelled', updated_by = '$_SESSION[userid]', updated_on = now() where po_no = '$_POST[po_no]' and branch = '1';");
		break;
		case "getDocInfo":
			$m = $p->getArray("select a.status,if(a.status='Cancelled','Cancelled By',if(a.status='Finalized','Finalized By','Last Updated By')) as lbl, a.status,if(a.status='Cancelled','Cancelled On',if(a.status='Finalized','Finalized On','Last Updated On')) as lbl2,b.fullname as cby, date_format(a.created_on,'%m/%d/%Y %r') as con, c.fullname as uby, date_format(updated_on,'%m/%d/%Y %r') as uon from po_header a left join user_info b on a.created_by=b.emp_id left join user_info c on a.updated_by=c.emp_id where po_no = '$_POST[po_no]' and a.company = '$_SESSION[company]' and a.branch = '1';");
			$n = $p->dbquery("select a.rr_no,with_ap,if(with_ap = 'Y',concat('AP-',a.apv_no),if(with_cv='Y',concat('CV-',a.cv_no),'')) as doc_no, if(with_ap='Y',date_format(c.apv_date,'%m/%d/%y'),if(with_cv='Y',date_format(d.cv_date,'%m/%d/%y'),'')) as doc_date,  date_format(a.rr_date,'%m/%d/%y') as rd8 from rr_header a left join rr_details b on a.rr_no=b.rr_no and a.branch=b.branch and a.company=b.company left join apv_header c on a.apv_no=c.apv_no and a.branch=c.branch and a.company=c.company left join cv_header d on a.cv_no=d.cv_no and a.company=d.company and a.branch=d.branch where b.po_no = '$_POST[po_no]' and a.company = '$_SESSION[company]' and a.branch = '1' group by a.rr_no;");
			while(list($o,$p,$u,$v,$w) = $n->fetch_array(MYSQLI_BOTH)) {
				if($o != "") { $q = $q . " RR # $o Dtd. $w;"; }
				if($u != "" && $u != $ou) { $z = $z . " $u Dtd. $v;"; }

				if($p == "Y") {
					$doc = explode("-",$u);
					$f = $p->dbquery("select a.branch,a.cv_no, date_format(a.cv_date,'%m/%d/%y') as cd8 from cv_header a left join cv_details b on a.cv_no=b.cv_no and a.company=b.company and a.branch=b.branch where b.ref_no = '$doc[1]' and b.ref_type='AP' and b.acct_branch='1' and b.company='$_SESSION[company]';");
					while(list($l,$g,$h) = $f->fetch_array(MYSQLI_BOTH)) {
						$t = $t . "CV # $l-$g Dtd. $h;";					}
				}
				$ou = $u;
			}

			if($q == "") { $q = "None "; }
			if($t == "") { $t = "None "; }
			if($z == "") { $z = "None "; }

			echo "<table width=100% cellpadding=2 cellspacing=0 style='font-size: 11px;'>
					<tr>
						<td width='30%'>Created By</td>
						<td width=5>:</td>
						<td style='padding-left:10px;'>$m[cby]</td>
					</tr>
					<tr>
						<td>Created On</td>
						<td width=5>:</td>
						<td style='padding-left:10px;'>$m[con]</td>
					</tr>
					<tr>
						<td>$m[lbl]</td>
						<td width=5>:</td>
						<td style='padding-left:10px;'>$m[uby]</td>
					</tr>
					<tr>
						<td>$m[lbl2]</td>
						<td width=5>:</td>
						<td style='padding-left:10px;'>$m[uon]</td>
					</tr>
					<tr>
						<td valign=top>RR Reference</td>
						<td valign=top width=5 valign=top>:</td>
						<td style='padding-left:10px;' valign=top>".substr_replace($q, "", -1)."</td>
					</tr>
					<tr>
						<td valign=top>AP Reference</td>
						<td valign=top width=5>:</td>
						<td style='padding-left:10px;'>".substr_replace($z, "", -1)."</td>
					</tr>
					<tr>
						<td valign=top>CV Reference</td>
						<td valign=top width=5>:</td>
						<td style='padding-left:10px;'>".substr_replace($t, "", -1)."</td>
					</tr>
				  </table>";

		break;
		case "getTotals":
			list($gross,$discount,$net) = $p->getArray("SELECT SUM(ROUND(qty*cost,2)) AS gros, SUM(ROUND(qty*discount,2)) AS discount, SUM(ROUND(qty*(cost-discount),2)) AS net FROM po_details WHERE po_no = '$_POST[po_no]' AND branch = '1';");
			if($gross == "") { $gross = "0.00"; $discount = "0.00"; $net = "0.00"; }
			echo json_encode(array("gross"=>$gross,"discount"=>$discount,"net"=>$net));
		break;
		case "applyDiscount":
			list($po_no,$price,$qty,$gross) = $p->getArray("select po_no,cost, qty, ROUND(qty*cost,2) as gross from po_details where line_id = '$_POST[lineid]';");
			
			if($_POST['type'] == 'PCT') {
				$pctW = $_POST['discount'];
				$pct = ROUND($_POST[discount]/100,2);
				$dUoM = ROUND($price * $pct,2);
			} else {
				$pctW = 0; $pct = 0;
				$dUoM = $_POST['discount'];
			}
			
			
			$tDisc = ROUND($dUoM * $qty,2);
			$netOfDisc = $gross - $tDisc;
		
			$p->dbquery("UPDATE po_details SET discount_percent = '$pctW', discount = '$dUoM', amount = '$netOfDisc' WHERE line_id = '$_POST[lineid]';");
			$p->updateHeaderAmt($po_no);
			$p->PODETAILS($po_no,$status="Active",$lock="N");
		break;
		case "getCurrentDescription":
			list($idesc) = $p->getArray("select description from po_details where line_id = '$_POST[lineid]';");
			echo html_entity_decode($idesc);
		break;
		case "saveCustomDesc":
			$p->dbquery("update po_details set custom_description = '".$p->escapeString(htmlentities($_POST[desc]))."' where line_id = '$_POST[lineid]';");
			$p->PODETAILS($_POST['po_no'],$status="Active",$lock="N");
		break;
	}

	//$p->@mysqli::close();

?>