<?php
	session_start();
	ini_set('display_errors','On');
	require_once '../lib/mpdf6/mpdf.php';
	require_once '../handlers/_generics.php';

	$mydb = new _init;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $mydb->getArray("select * from companies where company_id = '$_SESSION[company]';");
	$_ihead = $mydb->getArray("select srr_no, lpad(srr_no,2,0) as rr, date_format(srr_date,'%m/%d/%Y') as d8, received_from, received_by, ref_type, ref_no, if(ref_date!='0000-00-00',date_format(ref_date,'%m/%d/%Y'),'') as rd8, amount, remarks from srr_header where srr_no = '$_REQUEST[srr_no]';");
	$_idetails = $mydb->dbquery("select item_code, description, qty, unit, cost, amount from srr_details where srr_no = '$_REQUEST[srr_no]';");
	$bcode = STR_PAD($_REQUEST['user'],2,'0',STR_PAD_LEFT)."-SRR".$_ihead['srr_no']."-".date('Ymd');
	
	list($dCount) = $mydb->getArray("select count(*) from srr_details where srr_no = '$_REQUEST[srr_no]';");
	if($dCount > 5) {
		$paper = "letter";
	} else { $paper = "HALF-FOLIO"; }
	
/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252',$paper,'','',15,15,65,30,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");

if($_REQUEST['rePrint'] == 'Y') {
	$mpdf->SetWatermarkText('Reprinted Copy');
	$mpdf->showWatermarkText = true;
}

$mpdf->SetDisplayMode(60);

$html = '
<html>
<head>
<style>
body {font-family: sans-serif; font-size: 9pt; }
td { vertical-align: top; }

table thead td { 
	border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
	background-color: #EEEEEE;
    text-align: center;
}

.td-l { border-left: 0.1mm solid #000000; }
.td-r { border-right: 0.1mm solid #000000; }
.empty { border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; }

.items td.blanktotal {
    background-color: #FFFFFF;
    border: 0.1mm solid #000000;
}
.items td.totals-l-top {
    text-align: right; font-weight: bold;
    border-left: 0.1mm solid #000000;
	border-top: 0.1mm solid #000000;
}
.items td.totals-r-top {
    text-align: right; font-weight: bold;
    border-right: 0.1mm solid #000000;
	border-top: 0.1mm solid #000000;
}
.items td.totals-l {
    text-align: right; font-weight: bold;
    border-left: 0.1mm solid #000000;
}
.items td.totals-r {
    text-align: right; font-weight: bold;
    border-right: 0.1mm solid #000000;
}

.items td.tdTotals-l {
    text-align: left; font-weight: bold;
    border-left: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;  background-color: #EEEEEE;
}
.items td.tdTotals-r {
    text-align: right; font-weight: bold;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000; background-color: #EEEEEE;
}

.items td.tdTotals-l-1 {
    text-align: left;
    border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}
.items td.tdTotals-r-1 {
    text-align: right;
    border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}

.td-l-top { 	
		background-color: #EEEEEE; padding: 3px;
		text-align: left; font-weight: bold;
		border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000;
		border-top: 0.1mm solid #000000;
	}
.td-r-top { 
	text-align: right; font-weight: bold; padding: 3px;
    border-right: 0.1mm solid #000000;
	border-top: 0.1mm solid #000000;
}

.td-l-head {
	text-align: left; font-weight: bold; padding: 3px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; background-color: #EEEEEE;
}

.td-r-head {
	text-align: right; font-weight: bold; padding: 3px;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000;
}
.td-l-head-bottom {
	text-align: left; font-weight: bold; padding: 3px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; background-color: #EEEEEE; border-bottom: 0.1mm solid #000000;
}

.td-r-head-bottom {
	text-align: right; font-weight: bold; padding: 3px;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}

.billto {
	font-size: 12px; vertical-align: top; padding: 3px;
}
</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%" cellpadding=0 cellspaing=0><tr>
<td style="color:#000000;" width=80><img src="../images/'.$co['headerlogo'].'" height=70 /></td>
<td style="color:#000000; padding-top: 15px;">
	<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'<br/>VAT REG. TIN: '.$bit['tin_no'].'</span>
</td>
<td width="40%" align=right>
	<span style="font-weight: bold; font-size: 13pt; color: #000000;">STOCKS RECEIVING RECEIPT&nbsp;&nbsp;</span><br />
	<barcode size=0.8 code="'.$bcode.'" type="C128A">
</td>
</tr>
</table>

<table width="100%" cellspacing=0 cellpadding=0>
<tr>
<td class="billto" width=60% rowspan="4">
<b>Received From :</b><br /><br /><i>'.$_ihead['received_from'].'</i></b><br/><br/><b>Received By:</b><br/><br/><i>'.$_ihead['received_by'].'</i></td>
<td class="td-l-top"><b>Doc No</b></td>
<td class="td-r-top"><b>' . $_REQUEST['srr_no'] . '</b></td>
</tr>
<tr>
<td class="td-l-head"><b>Doc Date</b></td>
<td class="td-r-head"><b>' . $_ihead['d8'] . '</b></td>
</tr>
<tr>
<td class="td-l-head"><b>Reference #</b></td>
<td class="td-r-head"><b>' . $_ihead['ref_no'] . '</b></td>
</tr>
<tr>
<td class="td-l-head-bottom"><b>Reference Date</b></td>
<td class="td-r-head-bottom"><b>' . $_ihead['rd8'] . '</b></td>
</tr>
</table>
</htmlpageheader>
<htmlpagefooter name="myfooter">
<table width=100% cellpadding=5>
	<tr><td width=12%><b>Remarks :</b></td><td align=left>'.$_ihead['remarks'].'</td></tr>
</table>
<table width=100% cellpadding=5 style="border: 1px solid #000000;">
<tr>
	<td width=33% align=center><b>PREPARED BY:</b><br><br>'.$mydb->getUname($_REQUEST['user']).'<br></td>
	<td width=33% align=center><b>CHECKED BY:</b><br><br>_________________________________<br><font size=3>Signature over Printed Name</font></td>
	<td width=34% align=center><b>APPROVED BY:</b><br><br>_________________________________<br><font size=3>Signature over Printed Name</font></td>
</tr>
</table>
<table width=100%>
	<tr><td align=left>Page {PAGENO} of {nb}</td><td align=right>Run Date: '.date('m/d/Y h:i:s a').'</td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="3">
<thead>
<tr>
	<td width="75%" align=left><b>(ITEM CODE) DESCRIPTION</b></td>
	<td width="10%"><b>UoM</b></td>
	<td width="15%" align=right><b>QTY</b></td>
</tr>
</thead>
<tbody>';
	$i = 0;
	while($row = $_idetails->fetch_array(MYSQLI_BOTH)) {
		
		$html = $html . '<tr>
		<td align=left>(<b>'.$row['item_code'].'</b>) ' . $row['description'] . '</td>
		<td align="center">' . $mydb->identUnit($row['unit']) . '</td>
		<td align="right">' . number_format($row['qty'],2) . '</td>
		</tr>'; $i++;
	}

	for($i; $i < 16; $i++) { $html = $html . "<tr><td colspan=4></td></tr>"; }
	
$html = $html .  '
</tbody>
</table>

</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>