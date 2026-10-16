<?php
	session_start();
	require_once "../lib/mpdf6/mpdf.php";
	
	ini_set("display_errors","On");
	require_once "../handlers/_generics.php";
	
	$db = new _init;

/* MYSQL QUERIES SECTION */

	$now = date("m/d/Y h:i a");
	$co = $db->getArray("select * from companies where company_id = '$_SESSION[company]';");
	$_ihead = $db->getArray("select sw_no, lpad(sw_no,2,0) as rr, date_format(sw_date,'%m/%d/%Y') as d8, withdrawn_by, requested_by, ref_type, if(request_date!='0000-00-00',date_format(request_date,'%m/%d/%Y'),'') as rd8, amount, remarks from sw_header where sw_no = '$_REQUEST[sw_no]' and branch = '$_SESSION[branchid]';");
	$_idetails = $db->dbquery("select item_code, description, qty, unit, cost, amount from sw_details where sw_no = '$_REQUEST[sw_no]' and branch = '$_SESSION[branchid]';");
	$bcode = STR_PAD($_REQUEST['user'],2,'0',STR_PAD_LEFT)."-SW".$_ihead['sw_no']."-".date('Ymd');
	switch($_ihead['ref_type']) { case "PU": $ref = "Personal Use"; break; case "MS": $ref = "General Maintenance"; break; case "SP": $ref = "Stocks Pullout"; break; case "CI": $ref = "Change Item"; break; }

/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','HALF-FOLIO','','',15,15,65,30,10,10);
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
    border-left: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
}
.items td.tdTotals-r {
    text-align: right; font-weight: bold;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
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
		padding: 3px;
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
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000;
}

.td-r-head {
	text-align: right; font-weight: bold; padding: 3px;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000;
}
.td-l-head-bottom {
	text-align: left; font-weight: bold; padding: 3px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; border-bottom: 0.1mm solid #000000;
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
	<span style="font-weight: bold; font-size: 13pt; color: #000000;">STOCKS WITHDRAWAL SLIP&nbsp;&nbsp;</span><br />
	<barcode size=0.8 code="'.$bcode.'" type="C128A">
</td>
</tr>
</table>
<table width="100%" cellspacing=0 cellpadding=0>
<tr>
<td class="billto" width=60% rowspan="5">
<br/><br/><b>Withdrawn By : &nbsp;&nbsp;&nbsp;&nbsp;</b><i>'.$_ihead['withdrawn_by'].'</i><br/></br><b>Requested By : &nbsp;&nbsp;&nbsp;&nbsp;</b><i>'.$_ihead['requested_by'].'</i><br/><br/><b>Purpose :</b><br/><br/><i>'.$ref.'</i></td>
<td class="td-l-top"><b>PAGE</b></td>
<td class="td-r-top"><b>{PAGENO} of {nb}</b></td>
</tr>
<tr>
<td class="td-l-head"><b>Doc No</b></td>
<td class="td-r-head"><b>' . $_REQUEST['sw_no'] . '</b></td>
</tr>
<tr>
<td class="td-l-head"><b>Doc Date</b></td>
<td class="td-r-head"><b>' . $_ihead['d8'] . '</b></td>
</tr>
<tr>
<td class="td-l-head-bottom"><b>Date Requested</b></td>
<td class="td-r-head-bottom"><b>' . $_ihead['rd8'] . '</b></td>
</tr>
</table>
</htmlpageheader>
<htmlpagefooter name="myfooter">
<table width=100% cellspacing=0 cellpadding=0>
	<tr><td width=150><b>Transaction Remarks :</b></td><td style="padding-left: 5px;" align=left>'.$_ihead['remarks'].'</td></tr>
</table>
<table width=100% cellpadding=5 style="border: 1px solid #000000; margin-top: 10px;">
	<tr>
		<td width=33% align=center><b>PREPARED BY:</b><br><br>'.$db->getUname($_REQUEST['user']).'<br></td>
		<td width=33% align=center><b>CHECKED BY:</b><br><br>_________________________________<br><font size=3>Signature over Printed Name</font></td>
		<td width=34% align=center><b>RECEIVED BY:</b><br><br>_________________________________<br><font size=3>Signature over Printed Name</font></td>
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
<td width="55%" align=left><b>(ITEM CODE) DESCRIPTION</b></td>
<td width="15%" align=right><b>QTY</b></td>
<td width="10%"><b>UNIT</b></td>
<td width="25%" align=left><b>ASSET/VEH. BODY #</b></td>
</tr>
</thead>
<tbody>';

	while($row = $_idetails->fetch_array(MYSQLI_BOTH)) {
		
		$html = $html . '<tr>
		<td align=left>(<b>'.$row['item_code'].'</b>) ' . $row['description'] . '</td>
		<td align="right">' . number_format($row['qty'],2) . '</td>
		<td align="center">' . $row['unit'] . '</td>
		<td align="left">' . $row['asset_no'] . '</td>
		</tr>';
	}
	
$html = $html .  '
</tbody>
</table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

mysql_close($con);
?>