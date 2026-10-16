<?php

require_once '../../lib/mpdf6/mpdf.php';
require_once '../../handlers/_payroll.php';

ini_set("memory_limit",-1);
ini_set("max_execution_time",-1);
session_start();

$pay = new payroll($_GET['cutoff']);


if($_GET['ptype'] != "") { $f1 = " and b.PAYROLL_TYPE = '$_GET[ptype]' "; } else { $f1 = ""; }
if($_GET['dept'] != "") { $f2 = " and a.DEPT = '$_GET[dept]' "; } else { $f2 = ""; }
$co = $pay->getArray("select * from kredoit.companies where company_id = '$_SESSION[company]';");

$mpdf=new mPDF('win-1252','letter','','',15,15,35,25,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetAuthor("PORT80 Solutions");
$mpdf->SetDisplayMode(50);

$html = '
<html>
<head>
<style>
body {
	font-family: sans-serif;
    font-size: 10px;
}
p {    margin: 0pt;
}
td { vertical-align: top; }
.items td { }
table thead td { 
    text-align: center;
    border-top: 0.1mm solid #000000;
	border-bottom: 0.1mm solid #000000;
}
.eheader { border: 0.1mm solid #000000; }
</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%">
	<tr>
		<td style="color:#000000;" width=85><img src="../../images/'.$co['headerlogo'].'" width=80 height=80 /></td>
		<td style="color:#000000; padding-top: 15px;">
			<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'<br/>REG. TIN: '.$co['tin_no'].'</span>
		</td>
		<td width="40%" align=right>
			<span style="font-weight: bold; font-size: 9pt; color: #000000;">Summary of Approved Overtime</span><br /><span style="font-size: 6pt; font-style: italic;">For the Period ' . $pay->dtf . ' to ' . $pay->dt2 .'</span>
		</td>
	</tr>
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
<table style="border-top: 1px solid #000000; font-size: 7pt; width: 100%">
<tr>
<td width="50%" align="left">Page {PAGENO} of {nb}</td>
<td width="50%" align="right" style="font-size:7pt;">Run Date: ' . date('m/d/Y h:s a') . '</td>
</tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<table class="items" width="100%" style="font-size: 10px; border-collapse: collapse;" cellpadding="5">
<thead>
<tr>
<td width="40%" align=left colspan=2><b>EMPLOYEE</b></td>
<td width="15%" align=left><b>DEPT</b></td>
<td width="15%" align=center><b>DATE</b></td>
<td width="15%" align=center><b>TIME OUT</b></td>
<td width="15%" align=center><b>OVERTIME (HRS)</b></td>
</tr>
</thead>
<tbody>';

	$_i = $pay->dbquery("SELECT a.emp_id, c.dept_abbrv AS dept, CONCAT(LNAME,', ',FNAME,' ',LEFT(MNAME,1),'.') AS emp_name, DATE_FORMAT(DATE, '%m/%d/%Y %a') AS deyt, DATE, IF(out_pm!='00:00:00',TIME_FORMAT(out_pm, '%h:%i'),'') AS out_pm,(REG_OT+SUN_OT) AS tot_ot FROM emp_dtrfinal a LEFT JOIN emp_masterfile b ON a.emp_id=b.emp_id LEFT JOIN options_dept c ON a.dept = c.id WHERE PERIOD_ID = '$_GET[cutoff]' AND OT_APPROVE = 'Y' $f1 $f2 ORDER BY b.lname,a.date ASC;");
	while($row = $_i->fetch_array(MYSQLI_BOTH)) {
			if($oid != $row['emp_id']) { $ename = $row['emp_name']; $eid = $row['emp_id']; $dept = $row['dept']; } else { $ename = ""; $eid = ""; $dept = ""; }
			$html = $html . '<tr>
				<td align=left width=15%>'.$eid.'</td>
				<td align=left width=25%>'.$ename.'</td>
				<td align=left>' . $row['dept'] . '</td>
				<td align=center>' . $row['deyt'] . '</td>
				<td align=center>' . $row['out_pm'] . '</td>
				<td align=center>' . $row['tot_ot'] . '</td>
			</tr>';
			$oid = $row['emp_id']; 
	}
	
$html = $html . '
<tr><td class="blanktotal" colspan=6></td></tr>
</tbody>
</table>
</body>
</html>
';

$html = iconv("UTF-8", "ISO-8859-1//IGNORE", $html);
$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>