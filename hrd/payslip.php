<?php
	//ini_set("display_errors","On");
	session_start();
	require_once("../lib/mpdf6/mpdf.php");
	require_once '../handlers/initDB.php';
	
	require_once "../handlers/_payroll.php";
	$pay = new payroll($_REQUEST['cutoff']);
	
	ini_set("max_execution_time",-1);
	ini_set("memory_limit",-1);
	
	$cutoff = $_REQUEST['cutoff'];
	$area = $_REQUEST['area'];
	$dept = $_REQUEST['dept'];
	$eid = $_REQUEST['eid'];
	
	$whereString = '';
	
	if($area != '') { $whereString .= "and `area` = '$area' "; } 
	if($dept != '') { $whereString .= "and dept = '$dept' "; }
	if($eid != '') { $whereString .= "and emp_id = '$eid' "; }
	$q = $pay->dbquery("select * from emp_payslip a where period_id = '$cutoff' and emp_type = '$_SESSION[payclass]' $whereString order by emp_name asc;");
	list($dtf,$dt2,$paydate) = $pay->getArray("select date_format(period_start,'%m/%d/%Y'), date_format(period_end,'%m/%d/%Y'), date_format(date_add(period_end,INTERVAL 5 DAY),'%m/%d/%Y') from pay_periods where period_id = '$cutoff';");
	
	$mpdf=new mPDF('win-1252','FOLIO','','',10,10,12,10,10,10);
	$mpdf->use_embeddedfonts_1252 = true;    // false s default
	$mpdf->SetProtection(array('print'));
	$mpdf->SetAuthor("PORT80 Solutions");
	$mpdf->useSubstitutions = false; 
	$mpdf->SetDisplayMode(60);

$html = '
<html>
<head>
<style>
body {font-family: sans-serif; font-size: 6.5pt; }
td { vertical-align: top; font-size: 6.5pt; }
.e_info { border-top: 0.05mm solid #000000; }
</style>
</head>
<body>

<!--mpdf
<htmlpageheader name="myheader">

</htmlpageheader>

<sethtmlpageheader name="myheader" value="off" show-this-page="1" />
<sethtmlpagefooter name="myfooter" value="off" />
mpdf-->';

while($res = $q->fetch_array(MYSQLI_BOTH)) {
	list($desg,$dhired,$dept,$sl_credit,$vl_credit,$brate,$ptype,$area) = $pay->getArray("select ucase(desg) as desg,date_format(date_hired,'%m/%d/%Y'),b.dept_abbrv,sl_credit,vl_credit,a.basic_rate,a.payroll_type,a.area from emp_masterfile a left join options_dept b on a.dept=b.id where a.emp_id='$res[emp_id]' and a.file_status != 'DELETED';");
	list($lates,$ut) = $pay->getArray("SELECT IFNULL(SUM(TOT_LATE),0), IFNULL(SUM(TOT_UT),0) FROM emp_dtrfinal WHERE emp_id = '$res[emp_id]' AND period_id = '$cutoff' and HD_TYPE = 'NA';");
	
	$pay->getRates($ptype,$brate);
	$pay->getAbsences($res['emp_id'],$area);
	$pay->checkSL($res['emp_id'],$sl_credit);
	$pay->checkVL($res['emp_id'],$vl_credit);
	$pay->checkSIL($res['emp_id']);
	$pay->countHolidays($res['area']);

	if($res['wtax'] > 0) { $taxLbl = "[".number_format($res['taxable_income'],2)."]"; } else { $taxLbl = ''; }

	if($res['net_pay'] != 0) {

		$html = $html . '<table width="100%">
		<tr>
			<td width="50%" style="font-size: 6pt;"><b>SUPERIOR GAS & EQUIPMENT CO. OF CEBU, INC.</b><br/><i>Highway Labogon, Basak, Mandaue City</i></td>
			<td width="50%" align=right>
				<span style="font-weight: bold; font-size: 12pt; color: #000000;">PAY SLIP
			</td>
		</tr>
	</table>
	<table width="100%" cellspacing=0 cellpadding=0 class=e_info>
		<tr><td colspan=4 class=e_info>&nbsp;</td>
		<tr>
			<td width=15%>ID NUMBER</td>
			<td width=35% style="padding-left: 5px;">: '.$res['emp_id']. '</td>
			<td width=50% colspan=2></td>
		</tr>
		<tr>
			<td width=15%>NAME</td>
			<td width=35% style="padding-left: 5px;">: '. strtoupper(iconv("UTF-8", "ISO-8859-1//IGNORE", $res['emp_name'])). '</td>
			<td width=50% colspan=2></td>
		</tr>
		<tr>
			<td width=15%>DESIGNATION</td>
			<td width=35% style="padding-left: 5px;">: '.$desg.'</td>
			<td width=20%>PAYROLL CUT OFF DATE</td>
			<td width=30% style="padding-left: 5px;">: '. $dtf . ' - ' . $dt2 .'</td>
		</tr>
		<tr>
			<td width=15%>DEPARMENT</td>
			<td width=35% style="padding-left: 5px;">: '.$dept.'</td>';
			
			if($pay_type == 2) {
				$html .= '<td width=20%>CREDITED HOURS</td>
						  <td width=30% style="padding-left: 5px;">: '. $base .'</td>';
			} else {
				$html .= '<td width=20%>ABSENCES (IN DAYS)</td>
						  <td width=30% style="padding-left: 5px;">: '.number_format($pay->absences,2) . '</td>';
			}
			
		$html .= '</tr>
		<tr>
			<td width=15%>DATE HIRED</td><
			<td width=35% style="padding-left: 5px;">: '.$dhired.'</td>';

			if($pay_type == 2) {
				$html .= '<td width=50% colspan=2></td>';
			} else {
				$html .= '<td width=20%>LATE (IN HRS)</td>
						  <td width=30% style="padding-left: 5px;">: '. ROUND($lates,2) . '</td>';
			}
		$html .= '</tr>
		<tr>
			<td width=15%>BASIC RATE</td>
			<td width=35% style="padding-left: 5px;">: ' . number_format($brate,2) . '</td>';
			
			if($pay_type == 2) {
				$html .= '<td width=50% colspan=2></td>';
			} else {
			    $html .= '<td width=20%>UNDERTIME (IN HRS)</td>
						  <td width=30% style="padding-left: 5px;">: '.number_format($ut,2) . '</td>';
			}
	$html .= '</tr>
		</table>
		<table width=100% cellpadding=0 cellspacing=0 style="font-size: 10px; margin-top: 5px;">
			<tr>
				<td width=35% style="border: 0.1em solid black; border-collapse: collapse;">
					<table width=100% cellpadding=0 cellspacing=0>
						<tr>
							<td width="70%" style="border-bottom: 0.1mm solid black; padding-left: 10px;"><b>EARNINGS</b></td>
							<td width="30%" style="border-bottom: 0.1mm solid black; text-align: right; padding-right: 5px;"><b>AMOUNT</b></td>
						</tr>	
						<tr>
							<td width="100%" colspan=2 style="padding-left: 10px;">Basic Income</td>
						</tr>
						
						<tr>
							<td width="70%" style="padding-left: 40px;">Basic Pay</td>
							<td width="30%" style="padding-right: 5px; text-align: right;"><b>'.number_format(($res['basic_pay'] + $res['absences'] + $res['late'] + $res['undertime']),2).'</b></td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 50px; font-style: italic; font-size: 8px;">*Less: Absences</td>
							<td width="30%" style="padding-right: 5px; text-align: right; font-size: 8px;">('.number_format($res['absences'],2).')</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 50px; font-style: italic; font-size: 8px;">*Less: Late</td>
							<td width="30%" style="padding-right: 5px; text-align: right; font-size: 8px;">('.number_format($res['late'],2).')</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 50px; font-style: italic; font-size: 8px;">*Less: Undertime</td>
							<td width="30%" style="padding-right: 5px; text-align: right; font-size: 8px;">('.number_format($res['undertime'],2).')</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 40px;">('. $pay->lholiday .') Legal Holiday</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['legal_holiday'],2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 40px;">('. $pay->sholiday .') Special Holiday</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['special_holiday'],2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 40px;">('. $pay->onRestDayHoliday .') Holiday on Rest Day</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['restday_holiday'],2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 40px;">('. $pay->vl .') Vacation Leave</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['vacation_leave'],2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 40px;">('. $pay->sl .') Sick Leave</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['sick_leave'],2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 40px;">('. $pay->sil .') Other Paid Leaves (Maternity,Emergency,Bereavement,etc.)</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['other_leaves'],2).'</td>
						</tr>
						
						<tr>
							<td width="70%" style="padding-left: 10px;">COLA</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['cola'],2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 10px;">Overtime</td>
							<td width="30%" style="padding-right: 5px; text-align: right;"></td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 40px;">Regular Overtime</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format(($res['ot_regular']+$res['ot_regular_ex']),2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 40px;">Legal Holiday Overtime</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format(($res['ot_legalholiday']+$res['ot_legalholidayex']),2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 40px;">Special Holiday Overtime</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format(($res['ot_specialholiday']+$res['ot_specialholidayex']),2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 40px;">Rest Day Overtime</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format(($res['ot_sunday']+$res['ot_sundayex']),2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 40px;">Night Differentials</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['night_premium'],2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 10px;">Performance Bonuses/Incentives</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['incentives'],2).'</td>
						</tr>

						<tr>
							<td width="70%" style="padding-left: 10px;">Taxable Allowances</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['allowance'],2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 10px;">Non-taxable Allowances</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['nontax_allowance'],2).'</td>
						</tr>
						<tr>
							<td width="70%" style="padding-left: 10px;">Adjustments Total</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['adjustments'],2).'</td>
						</tr>
						<tr>
							<td width="70%" style="text-align: left; padding-left: 20px;"><br/><b>G R O S S &nbsp;&nbsp; P A Y &raquo;</b></td>
							<td width="30%" style="padding: 5px; text-align: right;"><b>'.number_format($res['gross_pay'],2).'<br/>========</b></td>
						</tr>	
					</table>
				</td>
				<td width=35% style="border: 0.1em solid black; border-collapse: collapse;">
					<table width=100% cellpadding=0 cellspacing=0>
						<tr>
							<td width="70%" style="border-bottom: 0.1mm solid black; padding-left: 10px;"><b>DEDUCTIONS</b></td>
							<td width="30%" style="border-bottom: 0.1mm solid black; text-align: right; padding-right: 5px;"><b>AMOUNT</b></td>
						</tr>
						<tr>
							<td width="100%" colspan=2 style="padding-left: 10px;">Premium Contributions</td>
						</tr>
						<tr>
							<td style="padding-left: 40px;">Philhealth</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format($res['philhealth_premium'],2).'</td>
						</tr>
						<tr>
							<td style="padding-left: 40px;">SSS Premium</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format($res['sss_premium'],2).'</td>
						</tr>
						<tr>
							<td style="padding-left: 40px;">Pagibig/HDMF Premium</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format($res['pagibig_premium'],2).'</td>
						</tr>
						<tr>
							<td style="padding-left: 10px;">Withholding Tax '.$taxLbl.'</td>
							<td style="padding-right: 5px; text-align: right;">'.number_format($res['wtax'],2).'</td>
						</tr>';
						
						if($res['coop_premium'] > 0) {
							$html .= '<tr>
										<td style="padding-left: 10px;">Coop Premium</td>
										<td style="padding-right: 5px; text-align: right;">'.number_format($res['coop_premium'],2).'</td>
									</tr>';
						}
						
						if($res['union_dues'] > 0) {
							$html .= '<tr>
										<td style="padding-left: 10px;">Union Dues</td>
										<td style="padding-right: 5px; text-align: right;">'.number_format($res['union_dues'],2).'</td>
									</tr>';
						}
						
						$z = 0;
						$loanQuery = $pay->dbquery("SELECT c.loan_type, a.amount FROM emp_deductionmaster a LEFT JOIN emp_loanmasterfile b ON a.ref_id = b.record_id LEFT JOIN option_loantype c ON b.loan_type = c.id WHERE a.emp_id = '$res[emp_id]' AND a.period_id = '$cutoff' and a.type = 'L';");
						if(mysqli_num_rows($loanQuery) > 0) {
							$html .= '<tr>
										<td width="100%" colspan=2 style="padding-left: 10px;">Loans & Other Long Term Deductions</td>
									  </tr>';
							
							
							while($loanRows = $loanQuery->fetch_array()) {
								$html .= '<tr>
											<td style="padding-left: 40px;">'.$loanRows[0].'</td>
											<td style="padding-right: 5px; text-align: right;">'.number_format($loanRows[1],2).'</td>
										</tr>';
								$z++;
							}
						}
						
						$othersQuery = $pay->dbquery("SELECT c.remarks AS others, SUM(a.amount) AS amount FROM emp_deductionmaster a LEFT JOIN option_deductiontype b ON a.ref_type = b.id LEFT JOIN emp_otherdeductions c ON a.ref_id = c.record_id WHERE a.emp_id = '$res[emp_id]' AND a.type = 'O' AND a.period_id = '$cutoff' GROUP BY a.ref_type;");
						if(mysqli_num_rows($othersQuery) > 0) {
							$html .= '<tr>
										<td width="100%" colspan=2 style="padding-left: 10px;">Other Deductions</td>
									  </tr>';
							
							while($othersRow = $othersQuery->fetch_array()) {
								$html .= '<tr>
										<td style="padding-left: 40px;">'.$othersRow[0].'</td>
										<td style="padding-right: 5px; text-align: right;">'.number_format($othersRow[1],2).'</td>
									</tr>'; $z++;
							} 
						}
						
						if($z < 5) {
							for($z; $z < 8; $z++) {
								$html .= '<tr><td width="100%" colspan=2 style="padding-left: 10px;">&nbsp;</td></tr>';
							}
						}
						
						$html .= '<tr>
							<td style="text-align: left; padding-left:10px;"><br/><b>D E D U C T I O N S &nbsp;&nbsp; T O T A L &raquo;</b></td>
							<td style="padding: 5px; text-align: right;"><b>'.number_format(($res['sss_premium']+$res['pagibig_premium']+$res['philhealth_premium']+$res['coop_premium']+$res['union_dues']+$res['wtax']+$res['loans_total']+$res['others_total']),2).'<br/>========</b></td>
						</tr>
						<tr>
							<td width="100%" style="border-top: 0.1mm solid black; padding: 5px;" colspan=2><b>NET PAY</b></td>
						</tr>
						<tr><td width=100% style="padding-left: 5px; font-size: 10pt;" align=center colspan=2><b>&#8369; '.number_format($res['net_pay'],2).'</b></td></tr>
					</table>
				</td>
				<td width=30% style="border: 0.1em solid black; border-collapse: collapse;">
					<table width=100% cellpadding=0 cellspacing=0>
						<tr>
							<td width="70%" colspan=2 style="border-bottom: 0.1mm solid black; padding-left: 10px;"><b>OVERTIME DETAILS</b></td>
							<td width="30%" style="border-bottom: 0.1mm solid black; text-align: right; padding-right: 5px;"><b>AMOUNT</b></td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">REG OT</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_regular_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_regular'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">RD OT</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_sunday_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_sunday'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">RD OT EX</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_sundayex_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_sundayex'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">SP HOL OT</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_specialholiday_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_specialholiday'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">SP HOL EX</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_specialholidayex_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_specialholidayex'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">REG HOL OT</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_legalholiday_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_legalholiday'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">REG HOL EX</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['ot_regularholidayex_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['ot_regularholidayex'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;">NIGHT PREM</td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;">'.number_format($res['night_premium_hrs'],2).'</td>
							<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($res['night_premium'],2).'</td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;"></td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;"></td>
							<td width="30%" style="padding-right: 5px; text-align: right;"></td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;"></td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;"></td>
							<td width="30%" style="padding-right: 5px; text-align: right;"></td>
						</tr>
						<tr>
							<td width="50%" style="padding-left: 5px;"></td>
							<td width="20%" style="border-left: 0.1mm solid black; border-right: 0.1mm solid black; padding-right: 5px; text-align: right;"></td>
							<td width="30%" style="padding-right: 5px; text-align: right;"></td>
						</tr>
						<tr>
							<td width="100%" style="border-bottom: 0.1mm solid black; border-top: 0.1mm solid black; padding: 2px;" colspan=3 align=center><b>SALARY ADJUSTMENT DETAILS</b></td>
						</tr>';
						
						$adjQuery = $pay->dbquery("SELECT remarks, IF(adjustment_type='CR',amount*-1,amount) AS amount FROM emp_adjustments WHERE emp_id = '$res[emp_id]' AND period_id = '$cutoff' AND file_status = 'Active';");
						if($adjQuery) {
							while($adjRow = $adjQuery->fetch_array()) {
								$html .= '<tr>
												<td width="70%" style="padding-left: 5px;" colspan=2>'.$adjRow[0].'</td>
												<td width="30%" style="padding-right: 5px; text-align: right;">'.number_format($adjRow[1],2).'</td>
										   </tr>
								';
							}
						} else {
							$html .= '<tr><td width="100%" style="padding-left: 5px;" colspan=3>N-O-N-E</td></tr>';
						}
						
					$html .= '</table>
				</td>
			</tr>
		</table>
		<table><tr><td height=15 valign=middle>&#9986;----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------</td></tr></table>';
	}

}
$html = $html . '</body>
</html>
';

$mpdf->WriteHTML($html);
$mpdf->Output(); exit;
exit;
?>