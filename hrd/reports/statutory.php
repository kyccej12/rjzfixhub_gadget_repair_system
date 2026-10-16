<?php
session_start();

require_once '../../handlers/initDB.php';
require_once '../../lib/mpdf6/mpdf.php';
ini_set("memory_limit","1024M");
ini_set("max_execution_time",0);

$pay = new myDB;

$mpdf=new mPDF('win-1252','LEGAL-L','','',8,8,25,25,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetTitle("Payroll Summary");
$mpdf->SetAuthor("PORT80 Solutions");
$mpdf->SetDisplayMode(75);

	/* MYSQL QUERIES SECTION */
		$cutoff = $_GET['cutoff'];
		
		if($_GET[emp_type] != "") { $fs = " and emp_type = '$_GET[emp_type]' "; }
		
		$now = date("m/d/Y h:i a");
		$co = $pay->getArray("select * from companies where company_id = '$_SESSION[company]';");
		list($xmonth) = $pay->getArray("SELECT DATE_FORMAT('$_GET[year]-$_GET[month]-01','%M %Y');");
		$_ih = $pay->dbquery("SELECT distinct id, dept_name FROM emp_payslip a INNER JOIN options_dept b ON a.dept = b.id WHERE period_id in (SELECT period_id FROM pay_periods WHERE reportingMonth = '$_GET[month]' AND reportingYear = '$_GET[year]') $fs GROUP BY dept");
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

.items td {
	border: 0.1em solid black;
}
</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%">
	<tr>
		<td width="50%" style="color:#000000;" align=left><span style="font-size: 10pt;"><b>'.strtoupper($co[company_name]).'</b><br><span style="font-size: 10px;">'.strtoupper($co['company_address']).'</span></td>
		<td align=right><span style="font-size: 9pt;"><b>SUMMARY OF STATUTORY DEDUCTIONS</b><br />Covering the Period of '. $xmonth . '</span></td>
</tr></table>
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
<table class="items" width="100%" style="font-size: 8pt; border-collapse: collapse;" cellpadding="3">
<thead>
	<tr>
		<td width="25%" align=left><b>EMPLOYEE</b></td>
		<td align=center><b>SSS ID</b></td>
		<td align=center><b>HDMF ID</b></td>
		<td align=center><b>PHILHEALTH ID</b></td>
		<td align=center><b>SSS (EE)</b></td>
		<td align=center><b>SSS (ER+EC)</b></td>
		<td align=center><b>HDMF (EE)</b></td>
		<td align=center><b>HDMF (ER)</b></td>
		<td align=center><b>PHILHEALTH (EE)</b></td>
		<td align=center><b>PHILHEALTH (ER)</b></td>
	</tr>
</thead>
<tbody>';

	while($main = $_ih->fetch_array(MYSQLI_BOTH)) {
		$a = $pay->dbquery("select a.emp_id, concat(b.lname, ', ',b.fname, ' ',left(mname,1),'.') as emp_name, b.sss_no, b.hdmf_no, b.phealth_no, sum(a.sss_premium) as sss_premium, sum(a.sss_premium_er) as sss_premium_er, sum(philhealth_premium) as philhealth_premium, sum(philhealth_premium_er) as philhealth_premium_er, sum(a.pagibig_premium) as pagibig_premium, sum(pagibig_premium_er) as pagibig_premium_er from emp_payslip a left join emp_masterfile b on a.emp_id = b.emp_id where period_id in (SELECT period_id FROM pay_periods WHERE reportingMonth = '$_GET[month]' AND reportingYear = '$_GET[year]') and a.dept = '$main[id]' $fs group by a.emp_id order by emp_name;");
		$html .= '<tr><td colspan=10 align=center style="background-color: #ededed;"><b>'.strtoupper($main[1]).'</b></td></tr>';
		$sssGT = 0; $sssERGT = 0;  $hdmfGT = 0; $hdmfERGT = 0; $phGT = 0; $phERGT = 0;
		while($row = $a->fetch_array(MYSQLI_BOTH)) {
			$otOthers = $row[ot_sunday]+$row[ot_legalholiday]+$row[ot_specialholiday];
			
			$html = $html . '<tr>
				<td align="left">('.$row[emp_id].') ' . $row[emp_name] . '</td>
				<td align="center">'. $row[sss_no] . '</td>
				<td align="center">' . $row[hdmf_no] . '</td>
				<td align="center">' . $row[phealth_no] . '</td>
				<td align="right">' . number_format($row[sss_premium],2) . '</td>
				<td align="right">' . number_format($row[sss_premium_er],2) . '</td>
				<td align="right">' . number_format($row[pagibig_premium],2) . '</td>
				<td align="right">' . number_format($row[pagibig_premium_er],2) . '</td>
				<td align="right">' . number_format($row[philhealth_premium],2) . '</td>
				<td align="right">' . number_format($row[philhealth_premium_er],2) . '</td>
			</tr>';
			$sssGT+=$row[sss_premium]; $sssERGT+=$row[sss_premium_er]; $hdmfGT+=$row[pagibig_premium]; $hdmfERGT+=$row[pagibig_premium_er]; $phGT += $row[philhealth_premium]; $phERGT += $row[philhealth_premium_er];
		}
		$html .= '<tr>
			<td align="left" colspan=4><b>DEPARTMENT TOTAL</b></td>
			<td align="right"><b>' . number_format($sssGT,2) . '</b></td>
			<td align="right"><b>' . number_format($sssERGT,2) . '</b></td>
			<td align="right"><b>' . number_format($hdmfGT,2) . '</b></td>
			<td align="right"><b>' . number_format($hdmfERGT,2) . '</b></td>
			<td align="right"><b>' . number_format($phGT,2) . '</b></td>
			<td align="right"><b>' . number_format($phERGT,2) . '</b></td>
		</tr>';
	}
	
	$gt = $pay->getArray("SELECT SUM(sss_premium) AS sssGT, SUM(sss_premium_er) as sssERGT, SUM(pagibig_premium) AS hdmfGT, SUM(pagibig_premium_er) as hdmfERGT, SUM(philhealth_premium) AS phGT, SUM(philhealth_premium_er) as phERGT FROM emp_payslip WHERE period_id in (SELECT period_id FROM pay_periods WHERE reportingMonth = '$_GET[month]' AND reportingYear = '$_GET[year]') $fs");
	$html .= '<tr style="background-color: #ededed;">
				<td align="left" colspan=4><b>GRAND TOTAL</b></td>
				<td align="right"><b>' . number_format($gt[sssGT],2) . '</b></td>
				<td align="right"><b>' . number_format($gt[sssERGT],2) . '</b></td>
				<td align="right"><b>' . number_format($gt[hdmfGT],2) . '</b></td>
				<td align="right"><b>' . number_format($gt[hdmfERGT],2) . '</b></td>
				<td align="right"><b>' . number_format($gt[phGT],2) . '</b></td>
				<td align="right"><b>' . number_format($gt[phERGT],2) . '</b></td>
			</tr>';
$html = $html . '
</tbody>
</table>
</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>