<?php
	session_start();
	require_once '../lib/PHPExcel/PHPExcel.php';
	include("../includes/dbUSE.php");
	ini_set("max_execution_time",-1);
	

	date_default_timezone_set('Asia/Manila');
	set_time_limit(0);

	/* MYSQL QUERIES SECTION */
	$now = date("m/d/Y h:i a");
	$co = getArray("select * from companies where company_id = '1';");
	

	if($_GET['group'] != '') { $fs2 = " and c.category = '$_GET[group]' "; list($myGroup) = getArray("select concat('<br/>',mgroup,' Products <br/>') from options_mgroup where `mid` = '$_GET[group]';"); } else { $fs3 = ''; $myGroup = '<br>All Products</br>'; }
	$query = dbquery("select lpad(a.doc_no,9,'0') as doc_no, date_format(invoice_date,'%m/%d/%Y') as id8, b.item_code, b.description, b.unit, b.qty, (b.unit_price-b.discount) as unit_price, ROUND(qty * (b.unit_price-b.discount),2) as amount, c.unit_cost, ROUND(c.unit_cost * b.qty,2) as total_cost from invoice_header a LEFT JOIN invoice_details b on a.trace_no = b.trace_no LEFT JOIN products_master c on b.item_code = c.item_code where a.invoice_date between '".formatDate($_GET['dtf'])."' and '".formatDate($_GET['dt2'])."' and a.status = 'Finalized' $fs1 $fs2 order by a.doc_no asc, b.description;");
	
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
	$objPHPExcel->getProperties()->setCreator("Root Admin")
								 ->setLastModifiedBy("Root Admin")
								 ->setTitle("Bata Esensyal - INVENTORY BOOK")
								 ->setSubject("Bata Esensyal - INVENTORY BOOK")
								 ->setDescription("Bata Esensyal - INVENTORY BOOK")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Test result file");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3",$co['tel_no']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4",$co['email']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Gross Profit Sales Report Covering the Period $_GET[dtf] to $_GET[dt2]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A5","");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","TRANS #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B7","DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C7","ITEM CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D7","DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E7","UNIT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F7","QTY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G7","UNIT PRICE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H7","AMOUNT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I7","UNIT COST");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J7","TOTAL COST");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K7","GROSS PROFIT");
	
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
				
	while($data = mysql_fetch_array($query)) {
					
		$directProfit = $data['amount'] - $data['total_cost'];			
					
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['doc_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['id8']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['item_code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['description']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,identUnit($data['unit']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['qty']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['unit_price']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['amount']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['unit_cost']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['total_cost']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$directProfit);
		
		for($j = 0; $j<=10; $j++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($j,$row)->applyFromArray($contentStyle);
			if($j>5) {
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($j,$row)->getNumberFormat()->setFormatCode('#,##0.00');
			}
		}

		$row++; $amtGT+=$data['amount']; $profitTotal+=$directProfit;
	}
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$amtGT);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$profitTotal);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->applyFromArray($totalStyle);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->applyFromArray($totalStyle);
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("GROSS PROFIT SALES");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="grossprofitsales.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>