<?php

require_once ("../../lib/mpdf6/mpdf.php");
require_once("../../includes/dbUSE.php");
ini_set("memory_limit",-1);
ini_set("max_execution_time",-1);
session_start();

$dtf = "$_GET[year]-$_GET[month]-01";
list($dt2,$plbl) = mysql_fetch_array(mysql_query("select last_day('$dtf'),date_format('$dtf','%M %Y');"));

//$f1 = " and b.EMP_TYPE = '$_GET[emp_type]' ";
//if($_GET['emp_type'] == '1') { $border = "32700"; } else { $border = "29100"; }

if($_GET['dept'] != '') { $f1 = " and a.dept = '$_GET[dept]' "; } else { $f1 = ""; }
$co = getArray("select * from companies where company_id = '$_SESSION[company]';");

$mpdf=new mPDF('win-1252','letter','','',15,15,34,25,10,10);
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
		<td style="color:#000000;" width=150><img src="../../images/'.$co['headerlogo'].'" height=70 /></td>
		<td style="color:#000000; padding-top: 15px;">
			<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'<br/>VAT REG. TIN: '.$co['tin_no'].'</span>
		</td>
		<td width="40%" align=right>
			<span style="font-weight: bold; font-size: 9pt; color: #000000;">Tardiness Report</span><br /><span style="font-size: 6pt; font-style: italic;">For the month ' . $plbl .'</span>
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
<td width="15%" align=center><b>DEPT</b></td>
<td width="15%" align=center><b>DATE</b></td>
<td width="15%" align=center><b>TIME IN</b></td>
<td width="15%" align=center><b>LATE (HRS)</b></td>
</tr>
</thead>
<tbody>';

	$_i = mysql_query("SELECT distinct a.EMP_ID, c.dept_abbrv AS dept, CONCAT(LNAME,', ',FNAME,' ',LEFT(MNAME,1),'.') AS emp_name, DATE_FORMAT(DATE, '%m/%d/%Y %a') AS deyt, DATE, CONCAT(LEFT(IN_AM,5),'/',LEFT(IN_PM,5)) AS TIME_IN,TOT_LATE FROM emp_dtrfinal a LEFT JOIN emp_masterfile b ON a.EMP_ID = b.EMP_ID LEFT JOIN options_dept c ON a.dept = c.id WHERE `date` BETWEEN '".formatDate($_GET['dtf'])."' AND '".formatDate($_GET['dt2'])."' AND TOT_LATE > 0 $f1 ORDER BY b.lname,a.date ASC;");
	while($row = mysql_fetch_array($_i)) {
			$html = $html . '<tr>
				<td align=left width=15%>'.$row['EMP_ID'].'</td>
				<td align=left width=25%>'.$row['emp_name'].'</td>
				<td align=center>' . $row['dept'] . '</td>
				<td align=center>' . $row['deyt'] . '</td>
				<td align=center>' . $row['TIME_IN'] . '</td>
				<td align=center>' . $row['TOT_LATE'] . '</td>
			</tr>';
			//$oid = $row['emp_id']; 
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
mysql_close($con);
?>