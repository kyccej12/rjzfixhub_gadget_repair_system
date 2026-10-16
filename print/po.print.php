<?php
	session_start();
	
	ini_set("display_errors","On");
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_generics.php");
	
	$p = new _init;


/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $p->getArray("select * from companies where company_id = '$_SESSION[company]';");
	$_ihead = $p->getArray("select po_no, po_no as rr, date_format(po_date,'%m/%d/%Y') as d8, if(date_needed!='0000-00-00',date_format(date_needed,'%m/%d/%Y'),'') as nd8, delivery_address, supplier, supplier_name, supplier_addr, requested_by, amount, discount, net, remarks, mrs_no from po_header where po_no='$_REQUEST[po_no]' and branch = '$_SESSION[branchid]';");
	$_idetails = $p->dbquery("SELECT a.item_code,a.description as xdesc, a.description, qty, a.unit, a.cost, ROUND(discount*qty) AS discount, a.amount, if(a.discount_percent!=0,concat(round(a.discount_percent),'%'),'') as pct, a.custom_description as custDesc FROM po_details a WHERE po_no = '$_REQUEST[po_no]' and branch = '$_SESSION[branchid]';");
	list($icount) = $p->getArray("select count(*) from po_details where po_no = '$_REQUEST[po_no]' and branch = '$_SESSION[branchid]';");
	if($icount > 7) { $paper = "letter"; } else { $paper = "HALF-FOLIO"; }
	$bcode = STR_PAD($_REQUEST['user'],2,'0',STR_PAD_LEFT)."-PO".$_ihead['po_no']."-".date('Ymd');
	list($nos, $stin) = $p->getArray("select tel_no, tin_no from contact_info where file_id = '$_ihead[supplier]';");
	
	
	//list($isVat,$vatType) = $p->getArray("select vatable, vat_type from contact_info where file_id = '$_ihead[supplier]';");
	
/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252',$paper,'','',15,15,65,45,9,3);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");
$mpdf->SetDisplayMode(40);

if($_REQUEST['rePrint'] == 'Y') {
	$mpdf->SetWatermarkText('Reprinted Copy');
	$mpdf->showWatermarkText = true;
}

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
		<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'</span>
	</td>
	<td width="40%" align=right>
		<span style="font-weight: bold; font-size: 13pt; color: #000000;">PURCHASE ORDER&nbsp;&nbsp;</span><br />
		<barcode size=0.8 code="'.$bcode.'" type="C128A">
	</td>
	</tr>
	</table>
	<table width="100%" cellspacing=0 cellpadding=0>
	<tr>
	<td class="billto" width=60% rowspan="5">
	<b>SUPPLIER :</b><br /><br /><b>('.$_ihead['supplier'].') '.$_ihead['supplier_name'].'</b><br /><i>'.$_ihead['supplier_addr'].'<br/><b>Contact Nos: </b>'.$nos.'</i><br/><br><i>'.$daddr.'</i></td>
	<td class="td-l-top"><b>Page</b></td>
	<td class="td-r-top"><b>{PAGENO} of {nb}</b></td>
	</tr>
	<tr>
	<td class="td-l-head"><b>P.O No.</b></td>
	<td class="td-r-head"><b>' . $_REQUEST['po_no'] . '</b></td>
	</tr>
	<tr>
	<td class="td-l-head"><b>P.O Date</b></td>
	<td class="td-r-head"><b>' . $_ihead['d8'] . '</b></td>
	</tr>
	<tr>
	<td class="td-l-head"><b>Date Needed</b></td>
	<td class="td-r-head"><b>' . $_ihead['nd8'] . '</b></td>
	</tr>
	<tr>
	<td class="td-l-head-bottom"><b>Amount</b></td>
	<td class="td-r-head-bottom"><b>&#8369;' . number_format($_ihead['net'],2) . '</b></td>
	</tr>
	</table>
	</htmlpageheader>

<htmlpagefooter name="myfooter">
	<table width=100% cellpadding=5 style="border-top: 1px solid black;">
		<tr><td width=20%><b>GENERAL REMARKS :</b></td><td align=left>'.$_ihead['remarks'].'</td></tr>
	</table>
	<table width=100% cellpadding=5 style="border: 1px solid #000000;">
	<tr>
		<td width=33% align=center><b>PREPARED BY:</b><br><br>'.$p->getUname($_REQUEST['user']).'<br></td>
		<td width=33% align=center><b>CHECKED BY:<br/><br/>_________________________________________</b></td>
		<td width=33% align=center><b>APPROVED BY:<br/><br/>_________________________________________</b></td>
	</tr>
	</table>
	<table>
		<tr><td height=20></td></tr>
		<tr><td>Date & Time Sent: ______________________________</td></tr>
		<tr><td>P.O Received By: &nbsp;&nbsp;______________________________</td></tr>
	</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />

<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->

<table class="items" width="100%" style="font-size: 8pt; border-collapse: collapse;" cellpadding="3">
<thead>
<tr>
	<td align=left><b>PARTICULARS</b></td>
	<td width="10%" align=right><b>QTY</b></td>
	<td width="10%"><b>UoM</b></td>
	<td width="10%" align=right><b>UNIT COST</b></td>
	<td width="10%" align=right><b>AMOUNT</b></td>
</tr>
</thead>
<tbody>';
	$i = 0;
	while($row = $_idetails->fetch_array()) {
		$html = $html . '<tr>
		<td align=left>'. $row['xdesc'] . '</td>
		<td align="right">' . number_format($row['qty'],2) . '</td>
		<td align="center">' . $p->identUnit($row['unit']) . '</td>
		<td align="right">' . number_format($row['cost'],2) . '</td>
		<td align="right">' . number_format($row['amount'],2) . '</td>
		</tr>'; $i++;
	}
	
	if($isVat == 'Y') {
		if($vatType == '2') {
			$html .= '<tr>
					<td colspan=4 align=right><b>ADD: VAT &raquo;</b></td>
					<td align="right"><b>' . number_format($_ihead['vat'],2) . '</b></td>
			  </tr>';
		}
	}
	
	if($_ihead['discount'] > 0) {
		$html .= '<tr>
					<td colspan=6 align=right><b>LESS: DISCOUNT &raquo;</b></td>
					<td align="right"><b>-' . number_format($_ihead['discount'],2) . '</b></td>
			  </tr>';
	}
	
$html = $html .  '
</tbody>
</table>
</body>
</html>
';

$html = html_entity_decode($html);
$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>