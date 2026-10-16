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
		
		if($_GET['group'] != '') { $f = " and c.group = '$_GET[group]' "; list($myGroup) = getArray("select concat('<br/>',group_description,' Products <br/>') from options_igroup where `group` = '$_GET[group]';"); } else { $f = ''; $myGroup = '<br>All Products</br>'; }
		$queryString = "SELECT LPAD(a.doc_no,9,'0') AS doc_no, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS id8, IF(customer='0','Walk-in Customer',a.customer_name) AS cname, b.item_code, b.description, b.unit, b.qty AS qty_cash, '' AS qty_terms, (b.unit_price-b.discount) AS cost_cash, '' AS cost_terms, ROUND(qty * (b.unit_price-b.discount),2) AS amount_cash, '' AS amount_terms, d.sales_rep, e.description AS termdesc, a.terms FROM invoice_header a LEFT JOIN invoice_details b ON a.trace_no = b.trace_no LEFT JOIN products_master c ON b.item_code = c.item_code LEFT JOIN options_salesrep d ON a.sales_rep = d.record_id LEFT JOIN options_terms e ON a.terms = e.terms_id WHERE a.invoice_date BETWEEN '".formatDate($_GET['dtf'])."' and '".formatDate($_GET['dt2'])."' AND a.status = 'Finalized' AND terms = 0 $f UNION ALL SELECT LPAD(a.doc_no,9,'0') AS doc_no, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS id8, IF(customer='0','Walk-in Customer',a.customer_name) AS cname, b.item_code, b.description, b.unit, '' AS qty_cash, b.qty AS qty_terms, '' AS cost_cash, (b.unit_price-b.discount) AS cost_terms, '' AS amount_cash, ROUND(qty * (b.unit_price-b.discount),2) AS amount_terms, d.sales_rep, e.description AS termdesc, a.terms FROM invoice_header a LEFT JOIN invoice_details b ON a.trace_no = b.trace_no LEFT JOIN products_master c ON b.item_code = c.item_code LEFT JOIN options_salesrep d ON a.sales_rep = d.record_id LEFT JOIN options_terms e ON a.terms = e.terms_id WHERE a.invoice_date BETWEEN '".formatDate($_GET['dtf'])."' and '".formatDate($_GET['dt2'])."' AND a.status = 'Finalized' AND terms != 0 $f ORDER BY doc_no ASC;";
		$query = mysql_query($queryString);

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
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Sales Report Covering the Period $_GET[dtf] to $_GET[dt2]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A5","");
	
	
	$objPHPExcel->getActiveSheet()->mergeCells('F6:H6');
	$objPHPExcel->getActiveSheet()->getStyle('F6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('G6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('H6')->applyFromArray($headerStyle);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F6","CASH SALES");
	
	$objPHPExcel->getActiveSheet()->mergeCells('I6:K6');
	$objPHPExcel->getActiveSheet()->getStyle('I6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('J6')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('K6')->applyFromArray($headerStyle);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I6","CHARGE SALES");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","TRANS #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B7","DATE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C7","ITEM CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D7","DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E7","UNIT");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F7","QTY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G7","UNIT PRICE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H7","AMOUNT");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I7","QTY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J7","UNIT PRICE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K7","AMOUNT");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L7","QTY TOTAL");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M7","AMOUNT TOTAL");
	
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
	$objPHPExcel->getActiveSheet()->getStyle('L7')->applyFromArray($headerStyle);
	$objPHPExcel->getActiveSheet()->getStyle('M7')->applyFromArray($headerStyle);

	$row = 8;
				
	while($data = mysql_fetch_array($query)) {
		
		$qtyTotal = $data['qty_cash'] + $data['qty_terms'];
		$amountTotal = $data['amount_cash'] + $data['amount_terms'];
					
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['doc_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['id8']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['item_code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['description']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,identUnit($data['unit']));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['qty_cash']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$data['cost_cash']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$data['amount_cash']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$data['qty_terms']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$data['cost_terms']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$data['amount_terms']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$qtyTotal);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$amountTotal);
		
		for($j = 0; $j<=12; $j++) {
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($j,$row)->applyFromArray($contentStyle);
			if($j>5) {
				$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($j,$row)->getNumberFormat()->setFormatCode('#,##0.00');
			}
		}

		$row++; $cashGT+=$data['amount_cash']; $termsGT+=$data['amount_terms']; 
	}
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$cashGT);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(7,$row)->applyFromArray($totalStyle);
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$cashGT);
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(10,$row)->applyFromArray($totalStyle);
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,($cashGT+$termsGT));
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12,$row)->getNumberFormat()->setFormatCode('#,##0.00');
	$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(12,$row)->applyFromArray($totalStyle);
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("SALES REPORT");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="salesreport.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>