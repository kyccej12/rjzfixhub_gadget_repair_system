<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../handlers/_rrfunct.php");
	$p = new myRR;

/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = $p->getArray("select * from companies where company_id = '$_SESSION[company]';");
	$_ihead = $p->getArray("select lpad(rr_no,6,0) as rr, date_format(rr_date,'%m/%d/%Y') as d8, supplier, supplier_name, supplier_addr, invoice_no, if(invoice_date!='',if(invoice_date='0000-00-00','',date_format(invoice_date,'%m/%d/%Y')),date_format(invoice_date,'%m/%d/%Y')) as id8, received_by, amount, remarks from rr_header where rr_no='$_REQUEST[rr_no]' and branch = '$_SESSION[branchid]';");
	$_idetails = $p->dbquery("select if(po_no='','--',lpad(po_no,6,0)) as po_no, if(po_date='0000-00-00','--',date_format(po_date,'%m/%d/%y')) as po_date, item_code, description, qty, unit, cost, amount from rr_details where rr_no = '$_REQUEST[rr_no]' and branch = '$_SESSION[branchid]';");
	list($icount) = $p->getArray("select count(*) from rr_details where rr_no = '$_REQUEST[rr_no]' and branch = '1';");
	if($icount > 7) { $paper = "letter"; } else { $paper = "HALF-FOLIO"; }
	$bcode = STR_PAD($_REQUEST['user'],2,'0',STR_PAD_LEFT)."-RR".$_ihead['rr_no']."-".date('Ymd');
	list($nos, $stin) = $p->getArray("select tel_no, tin_no from contact_info where file_id = '$_ihead[supplier]';");
/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252',$paper,'','',10,10,30,30,10,10);
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
body {
	font-family: arial;
	font-size: 10pt;
 }
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
<table width="100%">
	<tr>
		<td style="color:#000000;" width=72><img src="../images/'.$co['headerlogo'].'" height=70 /></td>
		<td style="color:#000000; padding-top: 15px;">
			<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'<br/>VAT REG. TIN: '.$bit['tin_no'].'</span>
		</td>
		<td width="40%" align=right>
			<span style="font-weight: bold; font-size: 13pt; color: #000000;">RECEIVING REPORT&nbsp;&nbsp;</span><br />
			<barcode size=0.8 code="'.$bcode.'" type="C128A">
		</td>
	</tr>
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
<table width=100% cellpadding=5>
	<tr><td align=left width=15%><b>REMARKS :</b><td width=85% style="text-align: justify;">'.$_ihead['remarks'].'</td></tr>
</table>
<table width=100% cellpadding=5 style="border: 1px solid #000000;">
<tr>
	<td width=25% align=center><b>PREPARED BY:</b><br><br>'.$p->getUname($_REQUEST[user]).'<br></td>
	<td width=25% align=center><b>RECEIVED BY:</b><br><br>'.$_ihead['received_by'].'<br></td>
	<td width=25%  align=center><b>CHECKED BY:</b><br><br>_________________________<br><font size=3>Print Name Over Signature</font></td>
	<td width=25% align=center><b>APPROVED BY:</b><br><br>_________________________<br><font size=3>Print Name Over Signature</font></td>
</tr>
</table>
<table width=100%>
	<tr><td align=left>Page {PAGENO} of {nb}</td><td align=right>Run Date: '.date('m/d/Y h:i:s a').'</td></tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<table width="100%" cellspacing=0 cellpadding=0>
<tr>
<td class="billto" width=60% rowspan="5">
<b>RECEIVED FROM :</b><br /><br /><b>('.$_ihead['supplier'].') '.$_ihead['supplier_name'].'</b><br /><i>'.$_ihead['supplier_addr'].'<br/><b>Contact Nos: </b>'.$nos.'<br/><b>T-I-N #: </b>'.$stin.'</i></td>
<td class="td-l-top"><b>Doc No.</b></td>
<td class="td-r-top"><b>' . $_ihead['rr'] . '</b></td>
</tr>
<tr>
<td class="td-l-head"><b>Doc Date</b></td>
<td class="td-r-head"><b>' . $_ihead['d8'] . '</b></td>
</tr>
<tr>
<td class="td-l-head"><b>Invoice No.</b></td>
<td class="td-r-head"><b>' . $_ihead['invoice_no'] . '</b></td>
</tr>
<tr>
<td class="td-l-head"><b>Invoice Date</b></td>
<td class="td-r-head"><b>' . $_ihead['id8'] . '</b></td>
</tr>
<tr>
<td class="td-l-head-bottom"><b>Amount</b></td>
<td class="td-r-head-bottom"><b>&#8369;' . number_format($_ihead['amount'],2) . '</b></td>
</tr>
</table>
<table><tr><td height=15></td></tr></table>
<table class="items" width="100%" style="font-size: 9pt; border-collapse: collapse;" cellpadding="2">
<thead>
<tr>
<td width="10%" align=center><b>PO #</b></td>
<td width="12%" align=center><b>PO DATE</b></td>
<td width="38%" align=left><b>PARTICULARS</b></td>
<td width="10%" align=right><b>QTY</b></td>
<td width="6%"><b>UoM</b></td>
<td width="12%" align=right><b>UNIT COST</b></td>
<td width="12%" align=right><b>AMOUNT</b></td>
</tr>
</thead>
<tbody>';
	$i = 0;
	while($row = $_idetails->fetch_array(MYSQLI_ASSOC)) {
		if($old_po != $row['po_no']) { $po_no = $row['po_no']; $po_date = $row['po_date']; } else { $po_no = ""; $po_date = ""; }
		$html = $html . '<tr>
		<td align="right" style="padding-right:15px;"><b>' . $po_no . '</b></td>
		<td align="center"><b>' . $po_date . '</b></td>
		<td align=left>(' . $row['item_code'] . ') ' . $row['description'] . '</td>
		<td align="right">' . number_format($row['qty'],2) . '</td>
		<td align="center">' . $row['unit'] . '</td>
		<td align="right">' . number_format($row['cost'],2) . '</td>
		<td align="right">' . number_format($row['amount'],2) . '</td>
		</tr>'; $i++; $old_po = $row['po_no'];
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

mysql_close($con);
?>