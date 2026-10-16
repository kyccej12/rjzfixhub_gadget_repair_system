<?php
	session_start();
	require_once '../lib/PHPExcel/PHPExcel.php';
	require_once '../handlers/_generics.php';
	
	$con = new _init;
	
	$now = date("m/d/Y h:i a");
	$co = $con->getArray("select * from companies where company_id = '1';");
	if($_GET['payee'] != "") { $fs1 = " and payeeName = '$_GET[payee]' "; $lbl = $_GET['payee']; } else { $fs1 = ""; $lbl = "All Payees"; }
	if($_GET['type'] != "") { $fs2 = " and isLiquidated = '$_GET[type]' "; } else { $fs2 = ""; }
	
	$query = $con->dbquery("SELECT pcvNo as myPCV, LPAD(pcvNo,6,0) AS pcvNo, DATE_FORMAT(pcvDate,'%m/%d/%Y') AS pcvDate, payeeName, particulars, pcvAccount, approvedBy, amount, IF(isLiquidated='Y','Liquidated','Unliquidated') AS `status`, IF(liquidatedOn!='0000-00-00',DATE_FORMAT(liquidatedOn,'%m/%d/%y'),'') AS liquidatedOn, amountLiquidated FROM pcv WHERE `status` in ('Posted','Finalized') and pcvDate between '".$con->formatDate($_GET['dtf'])."' and '".$con->formatDate($_GET['dt2'])."' $fs1 $fs2 $fs3 order by pcvDate asc;");
	
	$headerStyle = array(
		'font' => array('bold' => true),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER),
		'borders' => array('outline' => array('style' =>PHPExcel_Style_Border::BORDER_THIN)),
	);

	$totalStyle = array(
		'font' => array('bold' => true),
		'alignment' => array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT),
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
	$objPHPExcel->getProperties()->setCreator("Root Admin")
								 ->setLastModifiedBy("CGAP System")
								 ->setTitle("Bata Esensyal - Petty Cash Summary")
								 ->setSubject("Bata Esensyal - Petty Cash Summary")
								 ->setDescription("Bata Esensyal - Petty Cash Summary")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3",$co['tel_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A5","Petty Cash Voucher Summary");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A6","Covered Period: $_GET[dtf] to $_GET[dt2]");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","PCV #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B7","DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C7","PAYEE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D7","ADDRESS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E7","TIN #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F7","PARTICULARS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G7","REF #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H7","REF DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I7","AMOUNT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J7","VAT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K7","NET OF VAT");

	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(16);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("C")->setWidth(60);
	$objPHPExcel->getActiveSheet()->getColumnDimension("D")->setWidth(60);
	$objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("F")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("G")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("H")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("I")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("J")->setAutoSize(true);
	$objPHPExcel->getActiveSheet()->getColumnDimension("K")->setAutoSize(true);

	$objPHPExcel->getActiveSheet()->getStyle('A7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('B7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('C7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('D7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('E7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('F7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('G7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('H7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('I7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('J7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('K7')->applyFromArray($headerStyle);
	
	$row = 8;
	while($data = $query->fetch_array(MYSQLI_BOTH)) {
		
		list($lCount) = $con->getArray("select count(*) from pcv_liquidation where pcv_no = '$data[pcvNo]';");
		if($lCount> 0) {
			list($payee,$payaddr,$paytin,$refno,$refdate,$amount,$vat,$nov) = $con->getArray("select payee_name, payee_address, payee_tin, invoice_no, date_format(invoice_date,'%m/%d/%Y') as idate, amount, ROUND((amount/1.12) * 0.12,2) as vat, ROUND(amount/1.12,2) as nov from pcv_liquidation where pcv_no = '$data[pcvNo]';");
			$vatGT+=$vat; $novGT+=$nov;
			
		} else {
			$payee = $data['payeeName'];
			$payaddr = '';
			$paytin = '';
			$refno = '';
			$refdate = '';
			$amount = $data['amount'];
			$vat = '';
			$nov = '';
		}


		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['pcvNo']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['pcvDate']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$payee);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$payaddr);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$paytin);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['particulars']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$refno);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$refdate);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['amount']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$vat);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$nov);
		
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(0,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(1,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(2,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(4,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(5,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(6,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->applyFromArray($contentStyle);
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->applyFromArray($contentStyle);

		
		/* NUMBER FORMAT */
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->getNumberFormat()->setFormatCode('#,##0.00');

		$row++; $amtGT+=$data['amount'];
	}


	/* TOTAL */
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$amtGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$vatGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$novGT);

	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->applyFromArray($totalStyle);

	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(8,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(9,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->getNumberFormat()->setFormatCode('#,##0.00');

	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("PCV Summary");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="pcvsummary.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>