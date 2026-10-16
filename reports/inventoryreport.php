<?php
	include("../lib/mpdflib/mpdf.php");
	include("../includes/dbUSE.php");
	
	$mpdf=new mPDF('win-1252','folio-l','','',15,15,28,15,10,10);
	$mpdf->use_embeddedfonts_1252 = true;    // false is default
	$mpdf->SetProtection(array('print'));
	$mpdf->SetAuthor("PORT80 Business Solutions");
	$mpdf->SetDisplayMode(75);

	/* MYSQL QUERIES SECTION */	
		$now = date("m/d/Y h:i a");
		if($_REQUEST['category'] != '') { $fs1 = " and b.category = '$_REQUEST[category]' "; }
		$query = mysql_query("select distinct a.item_code from (select distinct item_code from rr_details union all select distinct item_code from pos_details union all select distinct item_code from dr_details) a left join products_master b on a.item_code=b.item_code where a.item_code != '--' $fs1;");
	
	
	
		$co = getArray("select * from companies where company_id = '$_SESSION[company]';");
		$bit = getArray("select lpad(branch_code,2,0), branch_name from options_branches where branch_code = '$_SESSION[branchid]' and company = '$_SESSION[company]';");
	/* END OF SQL QUERIES */

$html = '
<html>
<head>
<style>
body {font-family: sans-serif;
    font-size: 10pt;
}
p {    margin: 0pt;
}
td { vertical-align: top; }

table thead td {
    text-align: center;
    border: 0.1mm solid #000000;
	background-color:#ededed;
}

.items td {
    border: 0.1mm solid #000000;
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
			<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'<br/>VAT REG. TIN: '.$co['tin_no'].'</span>
		</td>
		<td width="40%" align=right><span style="font-weight: bold; font-size: 8pt;">INVENTORY REPORT<br />Covered Period : ' . $_REQUEST['irep_dtf'] . ' - '. $_REQUEST['irep_dt2'] .'</span></td>
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
<table class="items" width="100%" align=center style="font-size: 8pt; border-collapse: collapse;" cellpadding="8">
<thead>
	<tr>
		<td width="15%"><b>ITEM CODE</b></td>
		<td width="30%"><b>DESCRIPTION</b></td>
		<td width="5%"><b>UNIT</b></td>
		<td width="10%"><b>UNIT COST</b></td>
		<td width="10%"><b>QTY BEG.</b></td>
		<td width="10%"><b>QTY RECEIVED</b></td>
		<td width="10%"><b>QTY SOLD</b></td>
		<td width="10%"><b>QTY DELIVERED</b></td>
		<td width="10%"><b>QTY END</b></td>
	</tr>
</thead>
<tbody>';

while($x = mysql_fetch_array($query)) {
	list($desc,$unit,$ucost) = getArray("select description, unit, unit_cost from products_master where item_code = '$x[item_code]';");
	/* Beginning */
	/* On Period */
	list($beg_in) = getArray("select sum(qty) from rr_header a left join rr_details b on a.rr_no=b.rr_no where b.item_code = '$x[item_code]' and a.rr_date < '".formatDate($_REQUEST['irep_dtf'])."' and a.status = 'Finalized';");
	list($beg_out) = getArray("select sum(qty) from dr_header a left join dr_details b on a.dr_no=b.dr_no where b.item_code = '$x[item_code]' and a.dr_date < '".formatDate($_REQUEST['irep_dtf'])."' and a.status = 'Finalized';");
	list($beg_sold) = getArray("select sum(qty) from pos_header a left join pos_details b on a.tmpfileid = b.tmpfileid where b.item_code = '$x[item_code]' and a.trans_date < '".formatDate($_REQUEST['irep_dtf'])."' and a.status = 'Finalized';");

	$qty_beg = $beg_in - $beg_out - $beg_sold;

	/* On Period */
	list($in) = getArray("select sum(qty) from rr_header a left join rr_details b on a.rr_no=b.rr_no where b.item_code = '$x[item_code]' and a.rr_date between '".formatDate($_REQUEST['irep_dtf'])."' and '".formatDate($_REQUEST['irep_dt2'])."' and a.status = 'Finalized';");
	list($out) = getArray("select sum(qty) from dr_header a left join dr_details b on a.dr_no=b.dr_no where b.item_code = '$x[item_code]' and a.dr_date between '".formatDate($_REQUEST['irep_dtf'])."' and '".formatDate($_REQUEST['irep_dt2'])."' and a.status = 'Finalized';");
	list($sold) = getArray("select sum(qty) from pos_header a left join pos_details b on a.tmpfileid = b.tmpfileid where b.item_code = '$x[item_code]' and a.trans_date between '".formatDate($_REQUEST['irep_dtf'])."' and '".formatDate($_REQUEST['irep_dt2'])."' and a.status = 'Finalized';");

	$qty_end = ($qty_beg + $in) - $sold - $out;

	$html = $html . '<tr>
		<td align=left>'.$x['item_code'].'</td>
		<td align=left>'.$desc.'</td>
		<td align=center>'.$unit.'</td>
		<td align=center>'.number_format($ucost,2).'</td>
		<td align=center>'.number_format($qty_beg,2).'</td>
		<td align=center><a href="rritemlist.php?item_code='.$x['item_code'].'&dtf='.$_REQUEST['irep_dtf'].'&dt2='.$_REQUEST['irep_dt2'].'" target="_blank" style="text-decoration: none; color: blue;">'.number_format($in,2).'</a></td>
		<td align=center><a href="salesitemlist.php?item_code='.$x['item_code'].'&dtf='.$_REQUEST['irep_dtf'].'&dt2='.$_REQUEST['irep_dt2'].'" target="_blank" style="text-decoration: none; color: blue;">'.number_format($sold,2).'</a></td>
		<td align=center><a href="dritemlist.php?item_code='.$x['item_code'].'&dtf='.$_REQUEST['irep_dtf'].'&dt2='.$_REQUEST['irep_dt2'].'" target="_blank" style="text-decoration: none; color: blue;">'.number_format($out,2).'</td>
		<td align=center>'.number_format($qty_end,2).'</td>
	</tr>'; 
}
$html = $html . '
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