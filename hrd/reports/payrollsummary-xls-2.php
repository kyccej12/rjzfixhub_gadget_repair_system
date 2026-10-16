<?php
	session_start();
	ini_set("memory_limit","1024M");
	ini_set("max_execution_time",0);

	//include("../../includes/dbUSE.php");
	
	require_once "../../handlers/_payroll.php";
	
	
	/* MYSQL QUERY */
		
		$cutoff = $_GET['cutoff'];
		if($_GET[emp_type] != "") { $fs = " and emp_type = '$_GET[emp_type]' "; }
		$pay = new payroll($_REQUEST['cutoff']);
		
		$now = date("m/d/Y h:i a");
		$co = $pay->getArray("select * from companies where company_id = '$_SESSION[company]';");
		$fDates = $pay->getArray("select date_format(period_start,'%m/%d/%Y') as dtf, date_format(period_end,'%m/%d/%Y') as dt2 from pay_periods where period_id = '$_GET[cutoff]';");
		$_ih = $pay->dbquery("SELECT id,dept_name FROM emp_payslip a INNER JOIN options_dept b ON a.dept = b.id WHERE period_id = '$cutoff' $fs GROUP BY dept");
	
	
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
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C6","EMPLOYEE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","BASIC PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D6","DAILY RATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E6","SL PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","VL PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G6","LEGAL HOL.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H6","SP. HOLIDAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","OT REG");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J6","OT (SUN,LEGAL,SP.)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K6","NIGHT PREMIUM");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L6","ALLOWANCE (TAXABLE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M6","INCENTIVES/OTHER ALLOWANCES (NON-TAXABLE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N6","SALARY ADJUSTMENTS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("O6","GROSS PAY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("P6","SSS (ER)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Q6","SSS (EE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("R6","HDMF (EE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("S6","HDMF (ER)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("T6","PHILHEALTH (EE)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("U6","PHILHEALTH (ER)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("V6","WITHHOLDING TAX");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("W6","LOANS (SSS,HDMF,BANK, ETC.)");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("X6","OTHER DEDUCTIONS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Y6","ADD: 13th MONTH");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("Z6","NET PAY");
	
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
	
	$row = 7;
	while($data = $_ih->fetch_array(MYSQLI_BOTH)) {
		$a = $pay->dbquery("select * from emp_payslip where period_id = '$cutoff' and dept = '$data[id]' $fs order by emp_name;");
		$basicGT = 0; $slGT = 0; $vlGT = 0; $lgGT = 0; $spGT = 0; $otGT = 0; $otOthersGT = 0; $npGT = 0; $altGT = 0; $alntGT = 0; $grossGT = 0; $sssGT = 0;
		$hdmfGT = 0; $phGT = 0; $wtaxGT = 0; $loansGT = 0; $otherGT = 0; $adjGT = 0; $netGT = 0; $sssERGT = 0;
		
		while($data2 = $a->fetch_array(MYSQLI_BOTH)) {
			$otOthers = $row['ot_sunday']+$row['ot_legalholiday']+$row['ot_specialholiday'];
			$pay->getRates($pay->ptype,$row['basic_rate']);
			
			/* Added for 13th Month */
			if($cutoff == 23 || $cutoff == 24) {
				list($bonus) = $pay->getArray("select amount from 13th_month where emp_id = '$data2[emp_id]' and file_id = '1';");		
			}
			
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data2['emp_id']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['dept_name']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data2['emp_name']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data2['basic_pay']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data2['sick_leave']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data2['vacation_leave']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data2['legal_holiday']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data2['special_holiday']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data2['ot_regular']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$otOthers);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data2['night_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$data2['allowance']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,($data2['nontaxable_allowance']+$data2['incentives']));
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$data2['adjustments']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(14,$row,$data2['gross_pay']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(15,$row,$data2['sss_premium_er']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(16,$row,$data2['sss_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(17,$row,$data2['pagibig_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(18,$row,$data2['pagibig_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(19,$row,$data2['philhealth_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(20,$row,$data2['philhealth_premium']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(21,$row,$data2['wtax']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(22,$row,$data2['loans_total']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(23,$row,$data2['others_total']);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(24,$row,$bonus);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(25,$row,($data2['net_pay']+$bonus));
			
			for($y = 0; $y <= 25; $y++) {
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($y,$row)->applyFromArray($contentStyle);
			}
			
			for($z = 3; $z <= 25; $z++) {
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($z,$row)->getNumberFormat()->setFormatCode('#,##0.00');
			}
			$row++; $bonus = 0;
		}
	
	}
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("Payroll Register");
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="payregister.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>