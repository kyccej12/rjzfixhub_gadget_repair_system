<?php

	//ini_set("display_errors","On");
	require_once '../../lib/mpdf6/mpdf.php';
	require_once '../../handlers/_payroll.php';
	
	ini_set("max_execution_time",-1);
	ini_set("memory_limit",-1);
	
	$pay = new payroll($_GET['period']);
	
	session_start();

	$mpdf=new mPDF('win-1252','LETTER','','',10,10,32,20,10,10);
	$mpdf->use_embeddedfonts_1252 = true;    // false is default
	$mpdf->SetProtection(array('print'));
	$mpdf->SetAuthor("PORT80 Business Solutions");
	$mpdf->SetDisplayMode(75);

	/* MYSQL QUERIES SECTION */
		$now = date("m/d/Y h:i a");
		$co = $pay->getArray("select * from companies where company_id = '1';");

		list($deptName) = $pay->getArray("select dept_name from options_dept where `id` = '$_GET[dept]';");
		$query = $pay->dbquery("SELECT DISTINCT CONCAT('(',a.EMP_ID,') ',lname,', ',fname,' ',left(mname,1),'.') AS employee, a.emp_id, b.area FROM emp_dtrfinal a LEFT JOIN emp_masterfile b ON a.EMP_ID = b.EMP_ID WHERE period_id = '$_GET[period]' and b.emp_type = '$_SESSION[payclass]' and a.dept = '$_GET[dept]' order by b.lname, b.fname");
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
		<td style="color:#000000;">
			<b>'.$co['company_name'].'</b><br/><span style="font-size: 9pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'</span>
		</td>
		<td width="40%" align=right>
			<span style="font-weight: bold; font-size: 8pt; color: #000000;">Daily Time Record</span><br /><span style="font-size: 7pt; font-style: italic;"><b>Cut-off Period: </b>' . $pay->dtf . ' to ' . $pay->dt2 .'<br/>'. $deptName .'</span>
		</td>
	</tr>
</table>
</htmlpageheader>

<htmlpagefooter name="myfooter">
<table style="border-top: 1px solid #000000; font-size: 9pt; width: 100%">
<tr>
<td width="50%" align="left">Page {PAGENO} of {nb}</td>
<td width="50%" align="right" style="font-size:7pt; font-color: #cdcdcd;">Run Date: ' . $now . '</td>
</tr>
</table>
</htmlpagefooter>

<sethtmlpageheader name="myheader" value="on" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="on" />
mpdf-->
<table class="items" width="100%" align=center style="font-size: 9pt; border-collapse: collapse;" cellpadding="1">
<thead>
	<tr>
		<td width="10%" align=left><b>DATE</b></td>
		<td width="8%" align=left><b>DAY</b></td>
		<td width="8%" align=center><b>IN (AM)</b></td>
		<td width="10%" align=center><b>OUT (AM)</b></td>
		<td width="8%" align=center><b>IN (PM)</b></td>
		<td width="10%" align=center><b>OUT (PM)</b></td>
		<td width=8% align=center><b>LATE<br/>(MINS)</b></td>
		<td with=8% align=center><b>REG HRS</b></td>
		<td width=12% align=center><b>OVERTIME<br/>(REG,RD,HOL)</b></td>
		<td align=left width=18% style="padding-left: 10px;"><b>REMARKS</b></td>
	</tr>
</thead>
<tbody>';

while($row = $query->fetch_array()) {
	$html .= '<tr><td colspan=9><b>'.$row['employee'].'</b></td></tr>';
	$tHrs = 0; $tLate = 0;
	for($x = 0; $x <= $pay->ndays; $x++) {
		list($date,$xd8,$day) = $pay->getArray("select date_add('".$pay->dtf."', INTERVAL $x DAY),date_format(date_add('".$pay->dtf."', INTERVAL $x DAY),'%m/%d/%y'), date_format(date_add('".$pay->dtf."', INTERVAL $x DAY),'%a');");
		list($rid,$am_in,$am_out,$pm_in,$pm_out,$hrs,$late,$tot,$ota,$hd) = $pay->getArray("SELECT record_id, IF(IN_AM!='00:00:00',TIME_FORMAT(IN_AM,'%H:%i'),'') AS `am_in`, IF(OUT_AM!='00:00:00',TIME_FORMAT(OUT_AM,'%H:%i'),'') AS `am_out`, IF(IN_PM!='00:00:00',TIME_FORMAT(IN_PM,'%H:%i'),'') AS `pm_in`, IF(OUT_PM!='00:00:00',TIME_FORMAT(OUT_PM,'%H:%i'),'') AS `pm_out`, IF(TOT_WORK > 0,TOT_WORK,'') AS hrs, IF(TOT_LATE > 0,ROUND(TOT_LATE*60),'') AS late, SUM(reg_ot+sun_ot+prem_ot) AS tot,hd_type FROM emp_dtrfinal WHERE EMP_ID = '$row[emp_id]' AND `date` = '$date';");
		if($hrs == 0 ) { $hrs = ''; }
		if($late == 0) { $late = ''; } 
		if($tot == 0) { $tot = ''; }
		
		$ol = $pay->getArray("select reasons as occasion from pay_loa where '$date' >= date_from and '$date' <= date_to and emp_id = '$row[emp_id]' and file_status != 'Deleted';");
		if(mysqli_num_rows($ol) > 0) {
			$rem = "On Leave: " . $ol[1]; 
		} else {
			list($hcount,$rem) = $pay->getArray("SELECT COUNT(*), occasion FROM (SELECT CONCAT(IF(`type`='1','Legal Holiday: ','Special Holiday: '),occasion) AS occasion FROM pay_holiday_nat WHERE `date` = '$date' UNION SELECT CONCAT('Special Holiday: ', occasion) FROM pay_holiday_local WHERE `date` = '$date' AND `area` = '$row[area]') a;");
			if($hcount==0) {
				$rem = '';
			}
		}
		
		
		$html .= '<tr>
			<td align=left>' . $xd8 . '</td>
			<td align=left>' . $day . '</td>
			<td align=center>' . $am_in . '</td>
			<td align=center>' . $am_out . '</td>
			<td align=center>' . $pm_in . '</td>
			<td align=center>' . $pm_out . '</td>
			<td align=center>'. $late . '</td>
			<td align=center>'. $hrs . '</td>
			<td align=center>' . $tot . '</td>
			<td>'.$rem.'</td>
		</tr>'; $tHrs+=$hrs; $tLate+=$late; $tOT += $tot;	
	}
		$html .= '<tr>
			<td align=center colspan=6></td>
			<td align=center>'. $tLate . '<br/>=====</td>
			<td align=center>'. number_format($tHrs,2) . '<br/>=====</td>
			<td align=center>' . number_format($tOT,2) . '<br/>=====</td>
			<td></td>
		</tr>';

}
$html .= '
	<tr><td colspan=10 style="padding-top: 50x;"><b>Checked & Approved By:</b> _____________________________________________</td></tr>
	<tr><td colspan=10 style="padding-left:220x;"><i>Print Name & Signature</i></td></tr>
	<tr><td colspan=10 style="padding-top: 10px;"><b>Date: &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</b> _____________________________________________</td></tr>
</tbody></table>
</body>
</html>
';

$html = iconv("UTF-8", "ISO-8859-1//IGNORE", $html);
$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;

?>