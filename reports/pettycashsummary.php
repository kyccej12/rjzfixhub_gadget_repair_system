<?php
	session_start();
	ini_set("max_execution_time",0);
	require_once "../lib/mpdf6/mpdf.php";
	require_once "../handlers/_generics.php";
	
	$con = new _init;
	
	$mpdf=new mPDF('win-1252','folio-l','','',10,10,32,25,10,10);
	$mpdf->use_embeddedfonts_1252 = true;
	$mpdf->SetProtection(array('print'));
	$mpdf->SetAuthor("PORT80 Business Solutions");
	$mpdf->SetDisplayMode(75);

	/* MYSQL QUERIES SECTION */
		$now = date("m/d/Y h:i a");
		
		$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
		if($_GET['payee'] != "") { $fs1 = " and payeeName = '$_GET[payee]' "; $lbl = $_GET['payee']; } else { $fs1 = ""; $lbl = "All Payees"; }
		if($_GET['type'] != "") { $fs2 = " and isLiquidated = '$_GET[type]' "; } else { $fs2 = ""; }

		$query = $con->dbquery("SELECT LPAD(pcvNo,6,0) AS pcvNo, DATE_FORMAT(pcvDate,'%m/%d/%Y') AS pcvDate, payeeName, particulars, pcvAccount, approvedBy, amount, IF(isLiquidated='Y','Liquidated','Unliquidated') AS `status`, IF(liquidatedOn!='0000-00-00',DATE_FORMAT(liquidatedOn,'%m/%d/%y'),'') AS liquidatedOn, amountLiquidated FROM pcv WHERE `status` in ('Posted','Finalized') and pcvDate between '".$con->formatDate($_GET['dtf'])."' and '".$con->formatDate($_GET['dt2'])."' $fs1 $fs2 $fs3 ORDER BY pcvNo;");
	/* END OF SQL QUERIES */

$html = '
<html>
<head>
<style>
body {
	font-family: arial;
    font-size: 9px;
}
p {    margin: 0pt;
}
td { vertical-align: top; }

table thead td {
    text-align: center;
    border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
}
.items td.blanktotal {
    background-color: #FFFFFF;
    border: 0mm none #000000;
    border-top: 0.1mm solid #000000;
    border-right: 0.1mm solid #000000;
}

.items td.totals {
    text-align: right;
    border: 0.1mm solid #000000;
}

.items td.lowertotals {
	border: 0mm none #000000;
    border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
}

</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%">
	<tr>
		<td style="color:#000000;" width=80><img src="../images/'.$co['headerlogo'].'" height=70 /></td>
		<td style="color:#000000; padding-top: 15px;">
			<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'</span>
		</td>
		<td width="40%" align=right>
			<span style="font-weight: bold; font-size: 9pt; color: #000000;">PETTY CASH VOUCHER SUMMARY</span><br/><span style="font-size: 6pt; font-style: italic;">Date Covered ' . $_GET['dtf'] . ' - ' . $_GET['dt2'] .'</span>
		</td>
	</tr>
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
<table style="border-top: 1px solid #000000; font-size: 7pt; width: 100%">
<tr>
<td width="50%" align="left">Page {PAGENO} of {nb}</td>
<td width="50%" align="right" style="font-size:7pt; font-color: #cdcdcd;">Run Date: ' . $now . '</td>
</tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<table class="items" width="100%" align=center style="font-size: 9px; border-collapse: collapse;" cellpadding="3">
<thead>
	<tr>
		<td align=left><b>PCV #</b></td>
		<td align=center><b>DATE</b></td>
		<td width="15%" align=left><b>PAYEE</b></td>
		<td width="15%" align=left><b>ADDRESS</b></td>
		<td align=left><b>TIN #</b></td>
		<td align=left><b>PARTICULARS</b></td>
		<td align=left><b>REF #</b></td>
		<td align=center><b>REF DATE</b></td>
		<td align=right><b>AMOUNT</b></td>
		<td align=right><b>VAT</b></td>
		<td align=right><b>NET OF VAT</b></td>

	</tr>
</thead>
<tbody>
<tr><td colspan=4></td></tr>';
$i=1;
while($row = $query->fetch_array()) {

	list($lCount) = $con->getArray("select count(*) from pcv_liquidation where pcv_no = '$row[pcvNo]';");
	if($lCount> 0) {
		list($payee,$payaddr,$paytin,$refno,$refdate,$amount,$vat,$nov) = $con->getArray("select payee_name, payee_address, payee_tin, invoice_no, date_format(invoice_date,'%m/%d/%Y') as idate, amount, ROUND((amount/1.12) * 0.12,2) as vat, ROUND(amount/1.12,2) as nov from pcv_liquidation where pcv_no = '$row[pcvNo]';");
		$vatGT+=$vat; $novGT+=$nov;
		
		$vat = number_format($vat,2);
		$nov = number_format($nov,2);
		
	} else {
		$payee = $row['payeeName'];
		$payaddr = '';
		$paytin = '';
		$refno = '';
		$refdate = '';
		$amount = $row['amount'];
		$vat = '';
		$nov = '';
	}
	$html = $html . '<tr bgcolor="'.$con->initBackground($i).'">
		<td align=left>' . $row['pcvNo'] . '</td>
		<td align=center>' . $row['pcvDate'] . '</td>
		<td align=left>' . $payee . '</td>
		<td align=left>' . $payaddr . '</td>
		<td align=left>' . $paytin . '</td>
		<td align=left>'. $row['particulars'].'</td>
		<td align=left>' . $refno . '</td>
		<td align=center>' . $refdate . '</td>
		<td align=right>'. number_format($row['amount'],2).'</td>
		<td align=right>' . $vat . '</td>
		<td align=right>' . $nov . '</td>
	</tr>'; $i++; $amtGT+=$row['amount'];
}
$html = $html . "<tr>
					<td colspan=8 align=right><br/><b>TOTAL &raquo;</b></td>
					<td align=right>=======<br/>".number_format($amtGT,2)."<br/>----------------</td>
					<td align=right>=======<br/>".number_format($vatGT,2)."<br/>----------------</td>
					<td align=right>=======<br/>".number_format($novGT,2)."<br/>----------------</td>
				</tr>";
$html = $html . '</tbody></table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>