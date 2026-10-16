<?php
	session_start();
	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);
	include("../handlers/_generics.php");
	
	$con = new _init();
	$now = date("m/d/Y h:i a");

	$f = '';

	if($_GET['group'] != '') { $f = " and c.group = '$_GET[group]' "; list($myGroup) = $con->getArray("select concat('<br/>',group_description,' Products <br/>') from options_igroup where `group` = '$_GET[group]';"); } else { $f = ''; $myGroup = '<br>All Products</br>'; }
	if($_GET['desc'] != '') {
		$f .= " and b.description like '%$_GET[desc]%' ";	
	}

	$queryString = "SELECT LPAD(a.doc_no,9,'0') AS doc_no, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS id8, IF(customer='0','Walk-in Customer',a.customer_name) AS cname, b.item_code, b.description, b.unit, b.qty AS qty_cash, '' AS qty_terms, (b.unit_price-b.discount) AS cost_cash, '' AS cost_terms, ROUND(qty * (b.unit_price-b.discount),2) AS amount_cash, '' AS amount_terms, d.sales_rep, e.description AS termdesc, a.terms FROM invoice_header a LEFT JOIN invoice_details b ON a.trace_no = b.trace_no LEFT JOIN products_master c ON b.item_code = c.item_code LEFT JOIN options_salesrep d ON a.sales_rep = d.record_id LEFT JOIN options_terms e ON a.terms = e.terms_id WHERE a.invoice_date BETWEEN '".$con->formatDate($_GET['dtf'])."' and '".$con->formatDate($_GET['dt2'])."' AND a.status = 'Finalized' AND terms = 0 $f UNION ALL SELECT LPAD(a.doc_no,9,'0') AS doc_no, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS id8, IF(customer='0','Walk-in Customer',a.customer_name) AS cname, b.item_code, b.description, b.unit, '' AS qty_cash, b.qty AS qty_terms, '' AS cost_cash, (b.unit_price-b.discount) AS cost_terms, '' AS amount_cash, ROUND(qty * (b.unit_price-b.discount),2) AS amount_terms, d.sales_rep, e.description AS termdesc, a.terms FROM invoice_header a LEFT JOIN invoice_details b ON a.trace_no = b.trace_no LEFT JOIN products_master c ON b.item_code = c.item_code LEFT JOIN options_salesrep d ON a.sales_rep = d.record_id LEFT JOIN options_terms e ON a.terms = e.terms_id WHERE a.invoice_date BETWEEN '".$con->formatDate($_GET['dtf'])."' and '".$con->formatDate($_GET['dt2'])."' AND a.status = 'Finalized' AND terms != 0 $f ORDER BY doc_no ASC;";
	$query = $con->dbquery($queryString);
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Bata Esensyal</title>
	<link href="../src/themes/redmond/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="../style/style.css" rel="stylesheet" type="text/css" />
</head>
<body bgcolor="#ffffff" leftmargin="10" bottommargin="100" rightmargin="20" topmargin="10" width="215">	
	<?php echo '<table width="100%">
		<tr>
			<td style="color:#000000;" width=65><img src="../images/logo-small.png" height=60  /></td>
			<td style="color:#000000; padding-top: 15px;">
				<b>Bata Esensyal</b><br/><span style="font-size: 6pt;">Surigao City<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'</span>
			</td>
			<td width="40%" align=right valign=top>
				<span style="font-weight: bold; font-size: 9pt; color: #000000;">Detailed Sales</span><br /><span style="font-weight: bold; font-size: 9pt; color: #000000;">'.$cname.'</span><br/><span style="font-size: 6pt; font-style: italic;">'.$myBranch.$myGroup.'<br/>Date Covered ' . $_GET['dtf'] . ' - ' . $_GET['dt2'] .'</span>
			</td>
		</tr>
	</table>';
	?>
	<table cellspacing=0 cellpadding=0 border=0 width=100% cellspacing=5>	
		<tr bgcolor="#887e6e">
			
			<td align=center class="gridHead" colspan=7>&nbsp;</td>
			<td align=center class="gridHead" colspan=3><b>CASH SALES</b></td>
			<td align=center class="gridHead" colspan=3><b>CHARGE SALES</b></td>

			<td align=right class="gridHead">&nbsp;</td>
			<td align=right class="gridHead">&nbsp;</td>
		</tr>
		<tr bgcolor="#887e6e">
			<td align=center class="gridHead"><b>TRANS #</b></td>
			<td align=center class="gridHead"><b>DATE</b></td>
			<td align=center class="gridHead"><b>CUSTOMER</b></td>
			<td align=center class="gridHead"><b>TERMS</b></td>
			<td width="10%" align=center class="gridHead"><b>ITEM CODE</b></td>
			<td width="25%" align=left class="gridHead"><b>DESCRIPTION</b></td>
			<td width="5%" align=center class="gridHead"><b>UNIT</b></td>
			<td align=right class="gridHead"><b>QTY</b></td>
			<td align=right class="gridHead"><b>PRICE</b></td>
			<td align=right class="gridHead"><b>AMOUNT</b></td>
			<td align=right class="gridHead"><b>QTY</b></td>
			<td align=right class="gridHead"><b>PRICE</b></td>
			<td align=right class="gridHead"><b>AMOUNT</b></td>
			<td align=right class="gridHead"><b>QTY TOTAL</b></td>
			<td align=right class="gridHead"><b>AMOUNT TOTAL</b></td>
		</tr>
		<?php
			$i = 0;
			while($row = $query->fetch_array()) {
				
				$qtyTotal = $row['qty_cash'] + $row['qty_terms'];
				$amountTotal = $row['amount_cash'] + $row['amount_terms'];
				
				echo '<tr bgcolor="'.$con->initBackground($i).'">
					<td align=center class="grid">'. $row['doc_no'] . '</td>
					<td align=center class="grid">' . $row['id8'] . '</td>
					<td align=center class="grid">' . $row['cname'] . '</td>
					<td align=center class="grid">' . $row['termdesc'] . '</td>
					<td align=center class="grid">' . $row['item_code'] . '</td>
					<td align=left class="grid">' . $row['description'] . '</td>
					<td align=center class="grid">' . $con->identUnit($row['unit']) . '</td>
					<td align=right class="grid">' . number_format($row['qty_cash'],2) . '</td>
					<td align=right class="grid">' . number_format($row['cost_cash'],2) . '</td>
					<td align=right class="grid">' . number_format($row['amount_cash'],2) . '</td>
					<td align=right class="grid">' . number_format($row['qty_terms'],2) . '</td>
					<td align=right class="grid">' . number_format($row['cost_terms'],2) . '</td>
					<td align=right class="grid">' . number_format($row['amount_terms'],2) . '</td>
					<td align=right class="grid">' . number_format($qtyTotal,2) . '</td>
					<td align=right class="grid">' . number_format($amountTotal,2) . '</td>
				</tr>'; $cashGT+=$row['amount_cash']; $termsGT+=$row['amount_terms']; $cashQtyGT+=$row['qty_cash']; $termsQtyGT+=$row['qty_terms']; $i++;
			}
			echo '<tr>
					<td align=left valign=top class="grid" colspan=7 style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;"><b>GRAND TOTAL &raquo;</b></td>
					<td align=right  valign=top class="grid" style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;"><b>' . number_format($cashQtyGT,2) . '</b></td>
					<td align=left valign=top class="grid" style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;">&nbsp;</td>
					<td align=right  valign=top class="grid" style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;"><b>' . number_format($cashGT,2) . '</b></td>
					<td align=right valign=top class="grid"  style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;">'. number_format($termsQtyGT,2) . '</td>
					<td align=left valign=top class="grid" style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;">&nbsp;</td>
					<td align=right  valign=top class="grid" style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;"><b>' . number_format($termsGT,2) . '</b></td>
					<td align=left valign=top class="grid" style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;">&nbsp;</td>
					<td align=right  valign=top class="grid" style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;"><b>' . number_format(($cashGT+$termsGT),2) . '</b></td>
				</tr>';
		?>
	</table>
</body>
</html>
