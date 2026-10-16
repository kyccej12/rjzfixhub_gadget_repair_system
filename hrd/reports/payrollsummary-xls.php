<?php
	session_start();
	//ini_set("display_errors","On");
	ini_set("memory_limit","1024M");
	ini_set("max_execution_time",0);

	require_once "../../handlers/initDB.php";
	$sql = new myDB;
	
	/* MYSQL QUERY */
		$cutoff = $_GET['cutoff'];
		if($_GET['ptype'] != "") { $fs = " and pay_type = '$_GET[ptype]' "; $fs1 = " and payroll_type = '$_GET[ptype]' "; }
		
		
		$now = date("m/d/Y h:i a");
		$co = $sql->getArray("select * from companies where company_id = '$_SESSION[company]';");
		$fDates = $sql->getArray("select date_format(period_start,'%m/%d/%Y') as dtf, date_format(period_end,'%m/%d/%Y') as dt2, period_start, period_end from pay_periods where period_id = '$_GET[cutoff]';");
		$_ih = $sql->dbquery("SELECT id,dept_name FROM options_dept ORDER BY dept_name");
	/* END OF MYSQL */
		
	include("../../lib/PHPExcel/PHPExcel.php");
	date_default_timezone_set('Asia/Manila');
	set_time_limit(0);
		
	$headerStyle = array(
		'font' => array('bold' => true),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
		'borders' => array('outline' => array('style' =>PHPExcel_Style_Border::BORDER_THIN)),
	);
	
	$contentStyle = array(
		'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	);
	
	$totalStyle = array(
		'font' => array('bold' => true),
		'borders' => array('outline' => array('style' => PHPExcel_Style_Border::BORDER_THIN))
	);
	
	$objPHPExcel = new PHPExcel();
	$objPHPExcel->getDefaultStyle()->getFont()->setSize(9);
	$objPHPExcel->getProperties()->setCreator("Payroll Master")
								 ->setLastModifiedBy("Payroll Master")
								 ->setTitle("$co[company_name] - PAYROLL SUMMARY")
								 ->setSubject("$co[company_name] - PAYROLL SUMMARY")
								 ->setDescription("$co[company_name] - PAYROLL SUMMARY")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Test result file");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1","$co[company_name]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2","$co[company_address]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3","$co[tel_no]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","PAYROLL REGISTER");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","ID NO.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B6","DEPT.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","EMPLOYEE NAME");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","MONTHLY RATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","SEMI-MONTHLY RATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","DAILY RATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","DAYS WORKED");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","BASIC PAY SALARY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","ADD: BASIC #2");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","SL PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","VL PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L6","LEGAL HOL.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M6","SP. HOLIDAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N6","OT REG");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("O6","OT (SUN,LEGAL,SP.)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("P6","NIGHT PREMIUM");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Q6","ALLOWANCE (TAXABLE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("R6","INCENTIVES/OTHER ALLOWANCES (NON-TAXABLE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("S6","SALARY ADJUSTMENTS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("T6","GROSS PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("U6","SSS (ER)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("V6","SSS (EE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("W6","HDMF (EE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("X6","HDMF (ER)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Y6","PHILHEALTH (EE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Z6","PHILHEALTH (ER)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AA6","WITHHOLDING TAX");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AB6","LOANS (SSS,HDMF,BANK, ETC.)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AC6","OTHER DEDUCTIONS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AD6","NET AFTER DEDUCTIONS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AE6","ADD: 13TH MONTH");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("AF6","NET PAY");
	
	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(16);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("L")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("M")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("N")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("O")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("P")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("Q")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("R")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("S")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("T")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("U")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("V")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("W")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("X")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("Y")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("Z")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AA")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AB")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AC")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AD")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AE")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("AF")->setAutoSize(true);

	$objPHPExcel->getActiveSheet()->getStyle('A6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('B6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('C6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('D6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('E6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('F6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('G6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('H6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('I6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('J6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('K6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('L6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('M6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('N6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('O6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('P6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('Q6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('R6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('S6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('T6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('U6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('V6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('W6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('X6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('Y6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('Z6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AA6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AB6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AC6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AD6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AE6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('AF6')->applyFromArray($headerStyle);
	
	$row = 7;
	while($data = $_ih->fetch_array(MYSQLI_BOTH)) {
		
		
		//$a = $sql->dbquery("select * from emp_payslip where period_id = '$cutoff' and dept = '$data[id]' $fs order by emp_name;");
		
		if($cutoff == '23' || $cutoff == '24') {
			$a = $sql->dbquery("SELECT emp_id, pay_type, emp_type, emp_name, dept, SUM(monthly_rate) AS monthly_rate, SUM(semi_rate) AS semi_rate, SUM(daily_rate) AS daily_rate, SUM(basic_day) AS basic_day, SUM(basic_pay) AS basic_pay, SUM(sick_leave) AS sick_leave, vacation_leave, other_leaves, legal_holiday, special_holiday, ot_regular, night_premium, ot_sunday, ot_legalholiday, ot_specialholiday, allowance, SUM(incentives) AS incentives, SUM(nontax_allowance) AS nontax_allowance, SUM(gross_pay) AS gross_pay, SUM(sss_premium) AS sss_premium, SUM(sss_premium_er) AS sss_premium_er, SUM(pagibig_premium) AS pagibig_premium, SUM(pagibig_premium_er) AS pagibig_premium_er, SUM(philhealth_premium) AS philhealth_premium, SUM(philhealth_premium_er) AS philhealth_premium_er, SUM(wtax) AS wtax, SUM(sss_loan) AS sss_loan, SUM(hdmf_loan) AS hdmf_loan, SUM(health_ins) AS health_ins, SUM(other_loans) AS other_loans, SUM(loans_total) AS loans_total, SUM(others_total) AS others_total, SUM(adjustments) AS adjustments, SUM(net_pay) AS net_pay, SUM(bonus) AS bonus FROM (SELECT 'A' AS source, emp_id, pay_type, emp_type, emp_name, dept, monthly_rate, semi_rate, daily_rate, basic_day, basic_pay, sick_leave, vacation_leave, other_leaves, legal_holiday, special_holiday, ot_regular, night_premium, ot_sunday, ot_legalholiday, ot_specialholiday, allowance, incentives, nontax_allowance, gross_pay, sss_premium, sss_premium_er, pagibig_premium, pagibig_premium_er, philhealth_premium, philhealth_premium_er, wtax, sss_loan, hdmf_loan, health_ins, other_loans, loans_total, others_total, adjustments, net_pay, 0 AS bonus FROM emp_payslip WHERE period_id = '$cutoff' and dept = '$data[id]' $fs UNION ALL SELECT 'B' AS source, a.emp_id, b.payroll_type AS pay_type, b.emp_type, a.emp_name, b.dept, 0 AS monthly_rate, 0 AS semi_rate, 0 AS daily_rate, 0 AS basic_day, 0 AS basic_pay, 0 AS sick_leave, 0 AS vacation_leave, 0 AS other_leaves, 0 AS legal_holiday, 0 AS special_holiday, 0 AS ot_regular, 0 AS night_premium, 0 AS ot_sunday, 0 AS ot_legalholiday, 0 AS ot_specialholiday, 0 AS allowance, 0 AS incentives, 0 AS nontax_allowance, 0 AS gross_pay, 0 AS sss_premium, 0 AS sss_premium_er, 0 AS pagibig_premium, 0 AS pagibig_premium_er, 0 AS philhealth_premium, 0 AS philhealth_premium_er, 0 AS wtax, 0 AS sss_loan, 0 AS hdmf_loan, 0 AS health_ins, 0 AS other_loans, 0 AS loans_total, 0 AS others_total, 0 AS adjustments, 0 AS net_pay, amount AS bonus FROM 13th_month a LEFT JOIN emp_masterfile b ON a.emp_id = b.emp_id WHERE a.file_id = '1' and b.dept = '$data[id]' $fs1 ORDER BY emp_name, emp_id) a GROUP BY a.emp_id;");
		} else {
			$a = $sql->dbquery("select * from emp_payslip a where period_id = '$cutoff' and dept = '$data[id]' $fs order by emp_name asc;");
		}
		
		$basicGT = 0; $slGT = 0; $vlGT = 0; $lgGT = 0; $spGT = 0; $otGT = 0; $otOthersGT = 0; $npGT = 0; $altGT = 0; $alntGT = 0; $grossGT = 0; $sssGT = 0;
		$hdmfGT = 0; $phGT = 0; $wtaxGT = 0; $loansGT = 0; $otherGT = 0; $adjGT = 0; $netGT = 0; $sssERGT = 0;
		
		while($data2 = $a->fetch_array(MYSQLI_BOTH)) {
			$otOthers = $data2['ot_sunday']+$data2['ot_legalholiday']+$data2['ot_specialholiday'];
			
			if($data2['pay_type'] == 1 || $data2['pay_type'] == 3) {
				if($data2['monthly_rate'] == 0) {
					list($monthlyRate) = $sql->getArray("select BASIC_RATE from emp_masterfile where EMP_ID = '$data2[emp_id]';");
					$semiRate = ROUND($monthlyRate / 2,2);
					$dailyRate = ROUND($monthlyRate / 23,2);
				} else { $monthlyRate = $data2['monthly_rate']; $semiRate = $data2['semi_rate']; $dailyRate = $data2['daily_rate']; }
			}
			
			/* Covid19 */
			/* if($data2['basic_pay'] == 0) {
				
				list($vl) = $sql->getArray("SELECT ifnull(SUM(`length`),0) FROM pay_loa WHERE date_from >= '" . $fDates['period_start'] . "' AND date_to <= '" . $fDates['period_end'] . "' AND w_pay = 'Y' AND emp_id = '$data2[emp_id]' AND leave_type = '1';");
				list($sl) = $sql->getArray("SELECT ifnull(SUM(`length`),0) FROM pay_loa WHERE date_from >= '" . $fDates['period_start'] . "' AND date_to <= '" . $fDates['period_end'] . "' AND w_pay = 'Y' AND emp_id = '$data2[emp_id]' AND leave_type = '2';");
				list($sil) = $sql->getArray("SELECT ifnull(SUM(`length`),0) FROM pay_loa WHERE date_from >= '" . $fDates['period_start'] . "' AND date_to <= '" . $fDates['period_end'] . "' AND w_pay = 'Y' AND emp_id = '$data2[emp_id]' AND leave_type not in ('1','2');");
				list($incentives) = $sql->getArray("select sum(amount) from emp_incentives where emp_id = '$data2[emp_id]' and period_id = '$cutoff';");
				list($adjustments) = $sql->getArray("SELECT IFNULL(SUM(IF(adjustment_type='DB',amount,(amount*-1))),0) FROM emp_adjustments WHERE emp_id = '$data2[emp_id]' AND period_id = '$cutoff';");
				
				$data2['vacation_leave'] = ROUND($vl * $dailyRate,2);
				$data2['sick_leave'] = ROUND($sl * $dailyRate,2);
				$data2['other_leaves'] = ROUND($sil * $dailyRate,2);
				$data2['incentives'] = $incentives;
				$data2['adjustments'] = $adjustments;
			
				$data2['net_pay'] = $data2['vacation_leave'] + $data2['sick_leave'] + $data2['other_leaves'] + $data2['incentives'] + $data2['adjustments'];
				$data2['gross_pay'] = $data2['net_pay'];
			} */

			/* Added for 13th Month */
			if($cutoff == 23 || $cutoff == 24) {
				list($bonus) = $sql->getArray("select amount from 13th_month where emp_id = '$data2[emp_id]' and file_id = '1';");		
			}
					
			 
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data2['emp_id']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['dept_name']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data2['emp_name']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$monthlyRate);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$semiRate);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$dailyRate);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data2['basic_day']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data2['basic_pay']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data2['basic2_pay']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data2['sick_leave']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data2['vacation_leave']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$data2['legal_holiday']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$data2['special_holiday']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$data2['ot_regular']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$otOthers);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15,$row,$data2['night_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(16,$row,$data2['allowance']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(17,$row,($data2['nontaxable_allowance']+$data2['incentives']));
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(18,$row,$data2['adjustments']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(19,$row,$data2['gross_pay']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(20,$row,$data2['sss_premium_er']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(21,$row,$data2['sss_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(22,$row,$data2['pagibig_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(23,$row,$data2['pagibig_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(24,$row,$data2['philhealth_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(25,$row,$data2['philhealth_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(26,$row,$data2['wtax']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(27,$row,$data2['loans_total']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(28,$row,$data2['others_total']);
			
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(29,$row,$data2['net_pay']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(30,$row,$bonus);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(31,$row,ROUND($data2['net_pay']+$bonus));
			
			for($y = 0; $y <= 31; $y++) {
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($y,$row)->applyFromArray($contentStyle);
			}
			
			for($z = 3; $z <= 31; $z++) {
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($z,$row)->getNumberFormat()->setFormatCode('#,##0.00');
			}
			$row++; $monthlyRate = 0; $semiRate = 0; $dailyRate = 0;
		}
	
	}
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("Payroll Register");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="payregister.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>