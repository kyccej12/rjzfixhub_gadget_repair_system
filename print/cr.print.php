<?php
	session_start();
	include("../lib/mpdf6/mpdf.php");
	include("../includes/dbUSE.php");
	session_start();
	
/* MYSQL QUERIES SECTION */
	$co = getArray("select * from companies where company_id = '1';");
	$_ihead = getArray("select *, date_format(cr_date,'%m/%d/%Y') as cd8, if(check_date!='0000-00-00',date_format(check_date,'%m/%d/%Y'),'') as ckd8, b.tin_no, b.bizstyle from cr_header a left join contact_info b on a.customer = b.file_id where trans_no = '$_REQUEST[trans_no]';");
	list($digs,$fracs) = explode(".",$_ihead['net']);
	$fracs = " & $fracs/100";
	$word = inWords($digs);
/* END OF SQL QUERIES */

$mpdf=new mPDF('win-1252','LETTER-H','','',10,10,32,15,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");
$mpdf->shrink_tables_to_fit=1;
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

.subdetail { 
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
		text-align: left;
		border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000;
		border-top: 0.1mm solid #000000;
	}
.td-r-top { 
	text-align: right; padding: 3px;
    border-right: 0.1mm solid #000000;
	border-top: 0.1mm solid #000000;
}

.td-l-head {
	text-align: left; font-weight: bold; padding: 3px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; background-color: #EEEEEE;
}

.td-r-head {
	text-align: right; padding: 3px;
    border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000;
}
.td-l-head-bottom {
	text-align: left; padding: 3px;
    border-left: 0.1mm solid #000000; border-right: 0.1mm solid #000000; border-top: 0.1mm solid #000000; background-color: #EEEEEE; border-bottom: 0.1mm solid #000000;
}

.td-r-head-bottom {
	text-align: right; padding: 3px;
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
<table width="100%"><tr>
<td style="color:#000000;" width=80>
	<img src="../images/'.$co['headerlogo'].'" height=70 />
</td>
<td style="color:#000000; padding-top: 15px;">
	<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'<br/>VAT REG. TIN: '.$co['tin_no'].'</span>
</td>
<td width="40%" align=right><b>DATE :&nbsp;&nbsp;&nbsp;</b>'.$_ihead['cd8'].'</td>
</tr>
</table>
</htmlpageheader>
<htmlpagefooter name="myfooter">
</htmlpagefooter>
<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="off" />
mpdf-->
<table width="100%" style="border-collapse: collapse;" cellpadding="3" >
	<tr>
		<td width="30%">
			<table width=100% style="border: 1px solid black;" cellspacing=0 cellpadding=0 >
				<tr><td colspan=2 align=center style="border-bottom: 1px solid black; padding: 3px;"><b>IN PART/FULL SETTLEMENT OF THE FF:</b></td></tr>
				<tr>
					<td width=50% align=center style="border-right: 1px solid black; border-bottom: 1px solid black; padding: 3px;"><b>REFERENCE</b></td>
					<td width=50% align=center style="border-bottom: 1px solid black; padding: 3px;"><b>AMOUNT</b></td>
				</tr>
				';
				$i = 0;
				$ix = dbquery("select ref_type, lpad(doc_no,6,0), amount_paid from cr_details where trans_no = '$_REQUEST[trans_no]';");
				while(list($type,$ino,$amount) = mysql_fetch_array($ix)) {
					$html = $html . '<tr>
											<td align=center style="border-right: 1px solid black; padding: 2px;">'.$type."-".$ino.'</td>
											<td align=right style="padding: 2px;">'.number_format($amount,2).'</td>
									</tr>'; $i++; 
				}
				
				if($_ihead['discount'] > 0) {
					$html .= '<tr>
							<td align=center style="border-right: 1px solid black; padding: 2px;">Less: Rebates</td>
							<td align=right style="padding: 2px;">('.number_format($_ihead[discount],2).')</td>
					</tr>'; $i++; 
				}

				if($i < 12) {
				for($i; $i <= 11; $i++) {
					$html = $html . '<tr>
										<td style="border-right: 1px solid black;">&nbsp;</td>
										<td>&nbsp;</td>
									</tr>';
					}
				}

				$html = $html . '
								<tr>
									 <td style="border-right: 1px solid black; border-top: 1px solid black; padding: 4px; background-color: #EEEEEE; "><b>TOTAL</b></td>
									 <td style="border-top: 1px solid black; padding:4px; background-color: #EEEEEE; " align=right><b>'.number_format($_ihead['net'],2).'</b></td>
								</tr>';
$html = $html. '</table>
		</td>
		<td width=70%>
			<table width=100% cellpadding=0 cellspacing=0>
				<tr><td width=100% colspan=4 align=center style="font-size: 20pt; font-weight: bold;"><u><i>PAYMENT FORM</i></u></td></tr>
				<tr><td colspan=4>&nbsp;</td></tr>
				<tr>
					<td width=25%><i>Received From</i>:</td>
					<td width=75% colspan=3 style="font-size: 9pt; font-weight: bold;border-bottom: 1px solid black;">('.$_ihead['customer'].') '.$_ihead['customer_name'].'</td>
				</tr>
				<tr>
					<td width=25% style="padding-top: 10px;"><i>TIN</i> :</td>
					<td width=10% style="border-bottom: 1px solid black; padding-top: 10px;">&nbsp;'.$_ihead['tin_no'].'</td>
					<td width=25% align=right style="padding-top: 10px;"><i>Business Style:</i></td>
					<td width=40% style="border-bottom: 1px solid black; padding-top: 10px;">&nbsp;'.$_ihead['bizstyle'].'</td>
				</tr>
				<tr>
					<td width=25% style="padding-top: 10px;"><i>Address</i>:</td>
					<td width=75% colspan=3 style="padding-top: 10px; border-bottom: 1px solid black;">'.$_ihead['customer_addr'].'</td>
				</tr>
				<tr><td colspan=4></td></tr>
				<tr>
					<td width=25% style="padding-top: 10px;"><i>The Amount Of :</i></td>
					<td width=75% colspan=3 style="padding-top: 10px;"><u>'. $word . $fracs . ' <b>(P '.number_format($_ihead['net'],2).')</b></u></td>
				</tr>
				<tr>
					<td width=25% style="padding-top: 10px;"><i>Remarks :</i></td>
					<td width=75% colspan=3 style="padding-top: 10px;"><u>'.$_ihead['remarks'].'</b></u></td>
				</tr>
			</table>
			<table><tr><td height=20></td></tr></table>
			<table width=100% align=center cellspacing=0 cellpadding=5>
				<tr><td width=70% colspan=5 style="border: 1px solid black;" align=center background-color: #EEEEEE; >FORM OF PAYMENT</td></tr>
				<tr>
					<td style="border-left: 1px solid black; background-color: #EEEEEE; " align=center>FORM</td></tr>
					<td style="border-left: 1px solid black; background-color: #EEEEEE; " align=center>BANK</td>
					<td style="border-left: 1px solid black; background-color: #EEEEEE; " align=center>CHECK NO</td>
					<td style="border-left: 1px solid black; background-color: #EEEEEE; " align=center>DATE</td>
					<td style="border-left: 1px solid black;border-right: 1px solid black; background-color: #EEEEEE; " align=center>AMOUNT</td>
				</tr>
				<tr>
					<td align=center style="border-left: 1px solid black; border-bottom: 1px solid black; border-top: 1px solid black; padding: 4px;"><i>'.$_ihead['pay_type'].'</i></td></tr>
					<td align=center style="border-left: 1px solid black; border-bottom: 1px solid black; border-top: 1px solid black; padding: 4px;">'.$_ihead['bank'].'</td>
					<td align=center style="border-left: 1px solid black; border-bottom: 1px solid black; border-top: 1px solid black;padding: 4px;">'.$_ihead['check_no'].'</td>
					<td align=center style="border-left: 1px solid black; border-bottom: 1px solid black; border-top: 1px solid black;padding: 4px;">'.$_ihead['ckd8'].'</td>
					<td align=center style="border: 1px solid black;padding: 4px;" align=right>'.number_format($_ihead['net'],2).'</td>
				</tr>
			</table>
			<table width=100%>
				<tr><td width=70% style="font-size: 7pt;"></td><td align=left colspan=2 style="font-size: 8pt;"><br/>ISSUED BY:<br/><br/>_________________________________<br/>Print Name over Signature</td>
			</table>
		</td>
	</tr>
</table>
';
$html = $html . '
</body>
</html>
';

$html = html_entity_decode($html);
$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

mysql_close($con);
?>