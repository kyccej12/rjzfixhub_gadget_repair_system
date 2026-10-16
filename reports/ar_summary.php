<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	ini_set("memory_limit","512M");
	ini_set("max_execution_time","0");
	//ini_set("display_errors","On");

	require '../handlers/_generics.php';
	$con = new _init();


	$mpdf=new mPDF('win-1252','letter','','',15,15,32,20,10,10);
	$mpdf->use_embeddedfonts_1252 = true;    // false is default
	$mpdf->SetProtection(array('print'));
	$mpdf->SetAuthor("PORT80 Business Solutions");
	$mpdf->SetDisplayMode(75);

	/* MYSQL QUERIES SECTION */
		$now = date("m/d/Y h:i a");
		$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
		
		if($_GET['cid'] != '') { $cid = " and a.customer = '$_GET[cid]' "; } else { $cid = ''; } 
		
		$query = $con->dbquery("SELECT a.doc_no, DATE_FORMAT(a.invoice_date,'%m/%d/%Y') AS idate, a.customer_name, b.description AS xterms,DATE_FORMAT(DATE_ADD(invoice_date,INTERVAL b.no_days DAY),'%m/%d/%Y') AS due_date, a.amount, a.applied_amount, a.balance FROM invoice_header a LEFT JOIN options_terms b ON a.terms = b.terms_id WHERE a.status = 'Finalized' AND a.terms != 0 and a.invoice_date <= '".$con->formatDate($_GET['asof'])."' and balance > 0 $cid ORDER BY invoice_date ASC, a.doc_no ASC;");
		//echo "SELECT a.doc_no, DATE_FORMAT(a.invoice_date,'%m/%d/%Y') AS idate, a.customer_name, b.description AS xterms,DAET_FORMAT(DATE_ADD(invoice_date,INTERVAL b.no_days DAY),'%m/%d/%Y') AS due_date, a.amount, a.applied_amount, a.balance FROM invoice_header a LEFT JOIN options_terms b ON a.terms = b.terms_id WHERE a.status = 'Finalized' AND a.terms != 0 and a.invoice_date <= '".$con->formatDate($_GET['asof'])."' and balance > 0 $cid ORDER BY invoice_date ASC, a.doc_no ASC;";
	/* END OF SQL QUERIES */

$html = '
<html>
<head>
<style>
body {font-family: sans-serif;
    font-size: 8pt;
}
p {    margin: 0pt;
}
td { vertical-align: top; }

table thead td {
    text-align: center;
    border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
}

.lowerHeader {
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
		<td style="color:#000000; padding-top: 15px;">
			<b>Bata Esensyal</b><br/><span style="font-size: 6pt;">SURIGAO CITY, SURIGAO DEL NORTE</span>
		</td>
		<td width="40%" align=right>
			<span style="font-weight: bold; font-size: 9pt; color: #000000;">Accounts Receivable Summary</span><br /><span style="font-size: 6pt; font-style: italic;">As Of ' . $_GET['asof'] .'</span>
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
<table class="items" width="100%" align=center style="font-size: 8pt; border-collapse: collapse;" cellpadding="3">
<thead>
	<tr>
		<td width="30%" align=left><b>CUSTOMER</b></td>
		<td width="10%" align=center><b>DOC NO</b></td>
		<td width="10%" align=center><b>DOC DATE</b></td>
		<td width="10%" align=center><b>TERMS</b></td>
		<td width="10%" align=center><b>DUE DATE</b></td>
		<td width="10%" align=right><b>AMOUNT</b></td>
		<td width="10%" align=right><b>PAID</b></td>
		<td width="10%" align=right><b>BALANCE</b></td>
	</tr>
</thead>
<tbody>';
$i = 0;
while($row = $query->fetch_array()) {
	
	$html = $html . '<tr bgcolor="'.$con->initBackground($i).'">
		<td align=left><b>' . $row['customer_name'] . '</b></td>
		<td align=center><b>' . str_pad($row['doc_no'],6,0,STR_PAD_LEFT) . '</b></td>
		<td align=center><b>' . $row['idate'] . '</b></td>
		<td align=center>' . $row['xterms'] . '</td>
		<td align=left>' . $row['due_date'] . '</td>
		<td align=right>' . number_format($row['amount'],2) . '</td>
		<td align=right>' . number_format($row['applied_amount'],2) . '</td>
		<td align=right>' . number_format($row['balance'],2) . '</td>
	</tr>'; $amtGT+=$row['amount']; $paidGT+=$row['applied_amount']; $balGT+=$row['balance']; $i++;
}

$html = $html . '<tr>
					<td colspan=5 style="border-top: 1px solid black;border-bottom: 1px solid black; font-weight: bold;">GRAND TOTAL &raquo;</td>
					<td style="border-top: 1px solid black;border-bottom: 1px solid black; font-weight: bold;" align=right>'.number_format($amtGT,2).'</td>
					<td style="border-top: 1px solid black;border-bottom: 1px solid black; font-weight: bold;" align=right>'.number_format($paidGT,2).'</td>
					<td style="border-top: 1px solid black;border-bottom: 1px solid black; font-weight: bold;" align=right>'.number_format($balGT,2).'</td>
			</tr>
	</tbody>
</table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output();
 exit;
?>