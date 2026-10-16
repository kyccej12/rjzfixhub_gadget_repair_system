<?php
	session_start();
	require_once '../lib/PHPExcel/PHPExcel.php';
	include("../handlers/_generics.php");
	ini_set("max_execution_time",-1);
	
	$con = new _init;

	date_default_timezone_set('Asia/Manila');
	set_time_limit(0);

	/* MYSQL QUERIES SECTION */
		$now = date("m/d/Y h:i a");
		$co = $con->getArray("select * from companies where company_id = '$_SESSION[company]';");
	
	/* END OF SQL QUERIES */

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
								 ->setTitle("$co[company_name] - INVENTORY BOOK")
								 ->setSubject("$co[company_name] - INVENTORY BOOK")
								 ->setDescription("$co[company_name] - INVENTORY BOOK")
								 ->setKeywords("office 2007 openxml php")
								 ->setCategory("Exported File");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A1",$co['company_name']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A2",$co['company_address']);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A3","$co[tel_no]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","$co[website]");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A4","Inventory Book");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A5","Period Covered: $_GET[dtf] to $_GET[dt2]");
	
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("A7","ITEM CODE");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("B7","DESCRIPTION");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("C7","UNIT");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("D7","UNIT COST");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("E7","LOT #");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("F7","EXPIRY");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("G7","BEG.");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("H7","PURCHASES");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("I7","RETURNS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("J7","WITHDRAWALS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("K7","TRANSFERS");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("L7","SOLD");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("M7","END");
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue("N7","INVENTORY COST TOTAL");
	
	$objPHPExcel->getActiveSheet()->getColumnDimension("A")->setWidth(16);
	$objPHPExcel->getActiveSheet()->getColumnDimension("B")->setWidth(32);
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
	$objPHPExcel->getActiveSheet()->getStyle('N7')->applyFromArray($headerStyle);

	$row = 8;
	$query = $con->dbquery("SELECT a.*,b.description, b.unit, b.unit_cost FROM (SELECT item_code, lot_no, expiry, IF(expiry!='0000-00-00',DATE_FORMAT(expiry,'%m/%d/%Y'),'') AS `exp` FROM phy_details WHERE branch = '$_SESSION[branchid]' UNION  SELECT item_code, lot_no, expiry, IF(expiry!='0000-00-00',DATE_FORMAT(expiry,'%m/%d/%Y'),'') AS `exp` FROM ibook WHERE doc_branch = '$_SESSION[branchid]') a LEFT JOIN products_master b ON a.item_code = b.item_code GROUP BY item_code order by a.item_code, b.description");
	while($data = $query->fetch_array()) {
		
		/* Check Last Physical Inventory for the Item */
		list($baseD8) = $con->getArray("select posting_date from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch = b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$data[item_code]' order by posting_date desc limit 1;");
		if($baseD8 == '') { $baseD8 = '2022-02-09'; }
		
		if($dtf < $baseD8) { $dtf = $baseD8; }
		
		/* Forward Balance = From Last Date of Physical Count and Before Period Start */
		$pi = $con->getArray("select ifnull(sum(b.qty),0) from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch=b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$data[item_code]' and a.status = 'Finalized' and a.posting_date = '$baseD8' GROUP BY b.item_code;");
		$run = $con->getArray("select sum(purchases+inbound-outbound-pullouts-sold) as run from ibook where doc_date >= '$baseD8' and doc_date < '" .$dtf . "' and item_code = '$data[item_code]' and doc_branch = '$_SESSION[branchid]';");
		
		/* Inventory Net Balance for the Specified Period */
		$cur = $con->getArray("select sum(purchases) as purchases, sum(inbound) as returns, sum(pullouts) as withdrawals, sum(outbound) as transfers, sum(sold) as sold, sum(purchases+inbound-outbound-pullouts-sold) as currentbalance from ibook where item_code = '$data[item_code]' and doc_date between '" . $dtf . "' and '"  .$con->formatDate($_GET['dt2']) . "' and doc_branch = '$_SESSION[branchid]';");
		
		$end = ROUND($pi[0]+$run[0]+$cur['currentbalance'],2);			
		$invCost = ROUND($end * $data['unit_cost'],2);


		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0,$row,$data['item_code']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1,$row,$data['description']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(2,$row,$data['unit']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3,$row,$data['unit_cost']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4,$row,$data['lot_no']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5,$row,$data['exp']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6,$row,$pi[0]+$run[0]);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(7,$row,$cur['purchases']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(8,$row,$cur['returns']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(9,$row,$cur['withdrawals']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(10,$row,$cur['transfers']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(11,$row,$cur['sold']);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(12,$row,$end);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(13,$row,$invCost);
		
		
		for($j=0;$j<=13;$j++){
			$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow($j,$row)->applyFromArray($contentStyle);
		}

		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(3,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$objPHPExcel->getActiveSheet()->getStyleByColumnAndRow(13,$row)->getNumberFormat()->setFormatCode('#,##0.00');
		$row++; 
	}
	
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle("Inventory Summary");
	
	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);
			
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="inventorysummary.xlsx"');
	header('Cache-Control: max-age=0');
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
?>