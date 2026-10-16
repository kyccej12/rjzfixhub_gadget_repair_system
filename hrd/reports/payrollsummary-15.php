<?php
session_start();
require_once '../../handlers/initDB.php';
require_once '../../lib/mpdf6/mpdf.php';
ini_set("memory_limit","1024M");
ini_set("max_execution_time",0);

$pay = new myDB;

$mpdf=new mPDF('win-1252','LEGAL-L','','',8,8,20,25,10,10);
$mpdf->use_embeddedfonts_1252 = true;    // false is default
$mpdf->SetProtection(array('print'));
$mpdf->SetTitle("Payroll Summary");
$mpdf->SetAuthor("PORT80 Solutions");
$mpdf->SetDisplayMode(75);

	/* MYSQL QUERIES SECTION */
		$co = $pay->getArray("select * from companies where company_id = '1';");
		$cutoff = $_GET['cutoff'];
		$whereString = '';
		
		if($_GET['dept'] != '') { $whereString .= "and `dept` = '$_GET[dept]' "; }
		if($_GET['area'] != '') { $whereString .= "and `area` = '$_GET[area]' "; }
		
		$now = date("m/d/Y h:i a");
		$co = $pay->getArray("select * from companies where company_id = '$_SESSION[company]';");
		$fDates = $pay->getArray("select date_format(period_start,'%m/%d/%Y') as dtf, date_format(period_end,'%m/%d/%Y') as dt2 from pay_periods where period_id = '$_GET[cutoff]';");
	
	/* END OF SQL QUERIES */

$html = '
<html>
<head>
<style>
body {
	font-family: sans-serif;
    font-size: 7pt;
}
p {    margin: 0pt;
}
td { vertical-align: top; }

table thead td {
    text-align: center;
}

.items td.totals {
    text-align: right;
    border: 0.1mm solid #000000;
}

.items td {
	//border: 0.1mm solid black;
}

.borderMe {
	border: 0.1mm solid black;
}

</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">
<table width="100%">
	<tr>
		<td>
			<span style="font-size: 7pt;"><b>Superior Gas & Equipment Co. of Cebu, Inc.</b><br/>Highway Labogon, Mandaue City, Cebu<br/>Tel # (032) 344-3805 to 08</span>
		</td>
		<td align=right><span style="font-size: 7pt;"><b>PAYROLL REGISTER</b><br />Cutoff Date: '. $fDates[0] . '-' . $fDates[1] . '</span></td>
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
<table class="items" width="100%" style="font-size: 7pt; border-collapse: collapse;" cellpadding="3">
<thead>
	<tr>
		<td colspan="21">&nbsp;</td>
		<td colspan=2 style="background-color: #cdcdcd;">LOANS & LONG TERM DED.</td>
		<td colspan=2 style="background-color: #cdcdcd;">OTHER DEDUCTIONS</td>
		<td>&nbsp;</td>
	</tr>
		
	<tr style="background-color: #cdcdcd;">
		<td align=center >ID #</td>
		<td align=center >EMPLOYEE</td>
		<td align=center >BASIC<br/>PAY</td>
		<td align=center >COLA</td>
		<td align=center >VL</td>
		<td align=center >SL</td>
		<td align=center >OTHER<br/>LEAVES</td>
		<td align=center >LGL HOL</td>
		<td align=center >SP HOL</td>
		<td align=center >RD HOL</td>
		<td align=center >OT<br/>REG</td>
		<td align=center >OT<br/>(RD,HOL)</td>
		<td align=center >N-PREM</td>
		<td align=center >ALLOW<br/>(TAX)</td>
		<td align=center >ALLOW<br/>(NON-TAX)</td>
		<td align=center >SAL<br/>ADJ</td>
		<td align=center >GROSS<br/>PAY</td>
		<td align=center >PHIC<br/>(EE)</td>
		<td align=center >PHIC<br/>(ER)</td>
		<td align=center >SSS<br/>(EE)</td>
		<td align=center >SSS<br/>(ER+EC)</td>
		
		<td align=center >TYPE</td>
		<td align=center >AMOUNT</td>
		
		<td align=center >TYPE</td>
		<td align=center >AMOUNT</td>

		<td align=center >NET<br/>PAY</td>
	</tr>
</thead>
<tbody>';
	
	$a = $pay->dbquery("select distinct dept from emp_payslip where period_id = '$cutoff' and emp_type = '$_SESSION[payclass]' $whereString order by dept;");
	while($drow = $a->fetch_array()) {
		list($dName) = $pay->getArray("select dept_name from options_dept where `id` = '$drow[dept]';");
		$html .= '<tr><td colspan=25 align=center style="background-color: #ededed; font-weight: bold;">'.$dName.'</td></tr>';

		$b = $pay->dbquery("select * from emp_payslip where period_id = '$cutoff' and emp_type = '$_SESSION[payclass]' and dept = '$drow[dept]' and register_exclude != 'Y' order by emp_name;");
		while($row = $b->fetch_array(MYSQLI_BOTH)) {

			$otRD = $row['ot_sunday'] + $row['ot_sundayex'] + $row['ot_legalholiday'] + $row['ot_specialholiday'] + $row['ot_specialholidayex'];
			$otLH = $row['ot_legalholiday'] + $row['ot_legalholidayex'];
			$otSH = $row['ot_specialholiday'] + $row['ot_specialholidayex'];
			list($dept) = $pay->getArray("select dept_abbrv from options_dept where id = '$row[dept]';");
			
			$html = $html . '<tr>
				<td align="left">' . $row['emp_id'] . '</td>
				<td align="left">' . $row['emp_name'] . '</td>
				<td align="right">' . number_format($row['basic_pay'],2) . '</td>
				<td align="right">' . number_format($row['cola'],2) . '</td>
				<td align="right">' . number_format($row['vacation_leave'],2) . '</td>
				<td align="right">' . number_format($row['sick_leave'],2) . '</td>
				<td align="right">' . number_format($row['other_leaves'],2) . '</td>
				<td align="right">' . number_format($row['legal_holiday'],2) . '</td>
				<td align="right">' . number_format($row['special_holiday'],2) . '</td>
				<td align="right">' . number_format($row['restday_holiday'],2) . '</td>
				<td align="right">' . number_format($row['ot_regular'],2) . '</td>
				<td align="right">' . number_format($otRD,2) . '</td>
				<td align="right">' . number_format($row['night_premium'],2) . '</td>
				<td align="right">' . number_format($row['allowance'],2) . '</td>
				<td align="right">' . number_format($row['nontax_allowance'],2) . '</td>
				<td align="right">' . number_format($row['adjustments'],2) . '</td>
				<td align="right">' . number_format($row['gross_pay'],2) . '</td>
				<td align="right">' . number_format($row['philhealth_premium'],2) . '</td>
				<td align="right">' . number_format($row['philhealth_premium_er'],2) . '</td>
				<td align="right">' . number_format($row['sss_premium'],2) . '</td>
				<td align="right">' . number_format($row['sss_premium_er'],2) . '</td>
			
				<td align="left">';
					$ltype = $pay->dbquery("SELECT b.loan_type FROM emp_deductionmaster a LEFT JOIN option_loantype b ON a.ref_type = b.id WHERE a.emp_id = '$row[emp_id]' AND a.type = 'L' AND period_id = '$cutoff' ORDER BY a.record_id ASC;");
					if(mysqli_num_rows($ltype) > 0) {
						while($ltypeRow = $ltype->fetch_array()) {
							$html .= strtoupper($ltypeRow[0]) . "<br/>";
						}
					}
				$html .= '</td>
				<td align="right">';
					$lamt = $pay->dbquery("SELECT amount FROM emp_deductionmaster WHERE emp_id = '$row[emp_id]' AND type = 'L' AND period_id = '$cutoff' ORDER BY record_id ASC;");
					if(mysqli_num_rows($lamt) > 0) {
						while($lRow = $lamt->fetch_array()) {
							$html .= number_format($lRow[0],2) . "<br/>";
						}
					}
				$html .= '</td>
				<td align="left">';
					$otype = $pay->dbquery("SELECT c.deduction_type FROM emp_deductionmaster a LEFT JOIN emp_otherdeductions b ON a.ref_id = b.record_id LEFT JOIN option_deductiontype c ON b.deduction_type = c.id WHERE a.emp_id = '$row[emp_id]' AND a.type = 'O' AND a.period_id = '$cutoff' ORDER BY a.record_id ASC;;");
					if(mysqli_num_rows($otype) > 0) {
						while($oRow = $otype->fetch_array()) {
							$html .= $oRow[0] . "<br/>";
						}
					}
				$html .= '</td>';
				
				$html .= '</td>
				<td align="right">';
					$otamt = $pay->dbquery("SELECT amount FROM emp_deductionmaster  WHERE emp_id = '$row[emp_id]' AND type = 'O' AND period_id = '$cutoff' ORDER BY record_id ASC;");
					if(mysqli_num_rows($otamt) > 0) {
						while($oamtRow = $otamt->fetch_array()) {
							$html .= number_format($oamtRow[0],2) . "<br/>";
						}
					}
				$html .= '</td>
					
					<td align="right">' . number_format($row['net_pay'],2) . '</td>
			</tr>';
				
				$basicGT+=$row['basic_pay'];
				$colaGT+=$row['cola'];
				$slGT+=$row['sick_leave'];
				$vlGT+=$row['vacation_leave'];
				$rdhGT+=$row['restday_holiday'];
				$silGT+=$row['other_leaves'];
				$lgGT+=$row['legal_holiday']; 
				$spGT+=$row['special_holiday'];
				$otGT+=$row['ot_regular']; 
				$otRDGT+=$otRD;
				$otSHGT=$otSH;
				$otLHGT+=$otLH;
				$npGT+=$row['night_premium']; 
				$altGT+=$row['allowance']; 
				$alntGT+=$row['nontax_allowance'];
				$mealGT+=$row['meal_allowance'];
				$transpoGT+=$row['transpo_allowance'];
				$adjGT+=$row['adjustments'];
				$grossGT+=$row['gross_pay'];
				$phicGT+=$row['philhealth_premium'];
				$phicerGT+=$row['philhealth_premium_er'];
				$sssGT+=$row['sss_premium'];
				$ssserGT+=$row['sss_premium_er'];
				$netGT+=$row['net_pay'];
		
		}
		
		$gt = $pay->getArray("SELECT SUM(basic_pay) AS bTotal, SUM(cola) AS cTotal, SUM(sick_leave) AS slTotal, SUM(vacation_leave) AS vlTotal, sum(restday_holiday) as rdhTotal, SUM(other_leaves) AS olTotal, SUM(legal_holiday) AS lhTotal, SUM(special_holiday) AS shTotal,  SUM(ot_regular) AS otTotal, SUM(night_premium) AS npTotal, SUM(ot_sunday+ot_sundayex+ot_legalholiday+ot_legalholidayex+ot_specialholiday+ot_specialholidayex) AS otoTotal, SUM(allowance) AS allowTotal, SUM(incentives) AS incTotal, SUM(nontax_allowance) AS ntxTotal, SUM(gross_pay) AS gpTotal, SUM(sss_premium) AS sssTotal, sum(sss_premium_er) as ssserTotal, SUM(pagibig_premium) AS hdmfTotal, sum(pagibig_premium_er) as hdmferTotal, SUM(philhealth_premium) AS phicTotal, sum(philhealth_premium_er) as phicerTotal, SUM(wtax) AS wtaxTotal, SUM(union_dues) AS unionTotal, SUM(loans_total) AS loansTotal, SUM(others_total) AS othersTotal, SUM(adjustments) AS adjTotal, SUM(net_pay) AS netTotal FROM emp_payslip WHERE emp_type = '$_SESSION[payclass]' AND period_id = '$cutoff' and dept = '$drow[dept]' and register_exclude != 'Y';");
		
		$html .= '<tr style="background-color: #cdcdcd; font-weight: bold;">
			<td align="left" colspan=2 style="font-weight: bold; ">DEPARTMENT TOTAL</td>
			<td align="right" >' . number_format($gt['bTotal'],2) . '</td>
			<td align="right">' . number_format($gt['cTotal'],2) . '</td>
			<td align="right">' . number_format($gt['vlTotal'],2) . '</td>
			<td align="right">' . number_format($gt['slTotal'],2) . '</td>
			<td align="right">' . number_format($gt['olTotal'],2) . '</td>
			<td align="right">' . number_format($gt['lhTotal'],2) . '</td>
			<td align="right">' . number_format($gt['shTotal'],2) . '</td>
			<td align="right">' . number_format($gt['rdhTotal'],2) . '</td>
			<td align="right">' . number_format($gt['otTotal'],2) . '</td>
			<td align="right">' . number_format($gt['otoTotal'],2) . '</td>
			<td align="right">' . number_format($gt['npTotal'],2) . '</td>
			<td align="right">' . number_format($gt['allowTotal'],2) . '</td>
			<td align="right">' . number_format($gt['ntxTotal'],2) . '</td>
			<td align="right">' . number_format($gt['adjTotal'],2) . '</td>
			<td align="right">' . number_format($gt['gpTotal'],2) . '</td>
			<td align="right">' . number_format($gt['phicTotal'],2) . '</td>
			<td align="right">' . number_format($gt['phicerTotal'],2) . '</td>
			<td align="right">' . number_format($gt['sssTotal'],2) . '</td>
			<td align="right">' . number_format($gt['ssserTotal'],2) . '</td>
			
			<td align="right"></td>
			<td align="right">'.number_format($gt['loansTotal'],2).'</td>
			<td align="right"></td>
			<td align="right">'.number_format($gt['othersTotal'],2).'</td>
			
			<td align="right">' . number_format($gt['netTotal'],2) . '</td>
		</tr>';
		
	}
	
	$html .= '<tr style="background-color: #cdcdcd; font-weight: bold;">
			<td align="left" colspan=2 style="font-weight: bold;">GRAND TOTAL</td>
			<td align="right" >' . number_format($basicGT,2) . '</td>
			<td align="right">' . number_format($colaGT,2) . '</td>
			<td align="right">' . number_format($vlGT,2) . '</td>
			<td align="right">' . number_format($slGT,2) . '</td>
			<td align="right">' . number_format($silGT,2) . '</td>
			<td align="right">' . number_format($lgGT,2) . '</td>
			<td align="right">' . number_format($spGT,2) . '</td>
			<td align="right">' . number_format($rdhGT,2) . '</td>
			<td align="right">' . number_format($otGT,2) . '</td>
			<td align="right">' . number_format($otRDGT,2) . '</td>
			<td align="right">' . number_format($npGT,2) . '</td>
			<td align="right">' . number_format($altGT,2) . '</td>
			<td align="right">' . number_format($alntGT,2) . '</td>
			<td align="right">' . number_format($adjGT,2) . '</td>
			<td align="right">' . number_format($grossGT,2) . '</td>
			<td align="right">' . number_format($phicGT,2) . '</td>
			<td align="right">' . number_format($phicerGT,2) . '</td>
			<td align="right">' . number_format($sssGT,2) . '</td>
			<td align="right">' . number_format($ssserGT,2) . '</td>
			
			<td align="right"></td>
			<td align="right"></td>
			<td align="right"></td>
			<td align="right"></td>
			
			
			<td align="right">' . number_format($netGT,2) . '</td>
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