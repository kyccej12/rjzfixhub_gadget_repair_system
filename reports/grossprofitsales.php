<?php
	session_start();
	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);
	include("../handlers/_generics.php");
	
	$con = new _init();

	$now = date("m/d/Y h:i a");

	if($_GET['group'] != '') { $fs2 = " and c.category = '$_GET[group]' "; list($myGroup) = $con->getArray("select concat('<br/>',mgroup,' Products <br/>') from options_mgroup where `mid` = '$_GET[group]';"); } else { $fs3 = ''; $myGroup = '<br>All Products</br>'; }
	$query = $con->dbquery("select lpad(a.doc_no,9,'0') as doc_no, date_format(invoice_date,'%m/%d/%Y') as id8, b.item_code, b.description, b.unit, b.qty, (b.unit_price-b.discount) as unit_price, ROUND(qty * (b.unit_price-b.discount),2) as amount, c.unit_cost, ROUND(c.unit_cost * b.qty,2) as total_cost from invoice_header a LEFT JOIN invoice_details b on a.trace_no = b.trace_no LEFT JOIN products_master c on b.item_code = c.item_code where a.invoice_date between '".$con->formatDate($_GET['dtf'])."' and '".$con->formatDate($_GET['dt2'])."' and a.status = 'Finalized' $fs1 $fs2 order by a.doc_no asc, b.description;");
	

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
				<span style="font-weight: bold; font-size: 9pt; color: #000000;">Gross Profit Sales Report</span><br/><span style="font-size: 6pt; font-style: italic;">'.$myGroup.'<br/>Date Covered ' . $_GET['dtf'] . ' - ' . $_GET['dt2'] .'</span>
			</td>
		</tr>
	</table>';
	?>
	<table cellspacing=0 cellpadding=0 border=0 width=100% cellspacing=5>	
		<tr bgcolor="#887e6e">
			<td align=center class="gridHead"><b>TRANS #</b></td>
			<td align=center class="gridHead"><b>DATE</b></td>
			<td width="10%" align=center class="gridHead"><b>ITEM CODE</b></td>
			<td width="25%" align=left class="gridHead"><b>DESCRIPTION</b></td>
			<td width="5%" align=center class="gridHead"><b>UNIT</b></td>
			<td align=right class="gridHead"><b>QTY</b></td>
			<td align=right class="gridHead"><b>PRICE</b></td>
			<td align=right class="gridHead"><b>AMOUNT</b></td>
			<td align=right class="gridHead"><b>UNIT COST</b></td>
			<td align=right class="gridHead"><b>TOTAL COST</b></td>
			<td align=right class="gridHead"><b>GROSS PROFIT</b></td>
		</tr>
		<?php
			$i = 0;
			while($row = $query->fetch_array()) {

				$directProfit = $row['amount'] - $row['total_cost'];

				echo '<tr bgcolor="'.$con->initBackground($i).'">
					<td align=center class="grid">'. $row['doc_no'] . '</td>
					<td align=center class="grid">' . $row['id8'] . '</td>
					<td align=center class="grid">' . $row['item_code'] . '</td>
					<td align=left class="grid">' . $row['description'] . '</td>
					<td align=center class="grid">' . $row['unit'] . '</td>
					<td align=right class="grid">' . number_format($row['qty'],2) . '</td>
					<td align=right class="grid">' . number_format($row['unit_price'],2) . '</td>
					<td align=right class="grid">' . number_format($row['amount'],2) . '</td>
					<td align=right class="grid">' . number_format($row['unit_cost'],2) . '</td>
					<td align=right class="grid">' . number_format($row['total_cost'],2) . '</td>
					<td align=right class="grid">' . number_format($directProfit,2) . '</td>
				</tr>'; $amtGT+=$row['amount']; $costTotal+=$row['total_cost']; $profit+=$directProfit; $i++;
			}
			echo '<tr>
					<td align=left valign=top class="grid" colspan=7 style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;"><b>GRAND TOTAL &raquo;</b></td>
					<td align=right  valign=top class="grid" style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;"><b>' . number_format($amtGT,2) . '</b></td>
					<td style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;">&nbsp;</td>
					<td align=right  valign=top class="grid" style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;"><b>' . number_format($costTotal,2) . '</b></td>
					<td align=right  valign=top class="grid" style="border-top: 1px solid #4a4a4a; border-bottom: 1px solid #4a4a4a;"><b>' . number_format($profit,2) . '</b></td>
				</tr>';
		?>
	</table>
</body>
</html>