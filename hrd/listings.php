<?php
	session_start();
	require_once "../handlers/_generics.php";
	$mydb = new _init;
	
	switch($_REQUEST['mod']) {
		case "employees":
			$queryString = "SELECT emp_id, UCASE(lname) AS lname, UCASE(fname) AS fname, UCASE(mname) AS mname, b.dept_name, UCASE(desg) AS desg, emp_status FROM emp_masterfile a LEFT JOIN options_dept b ON a.dept=b.id LEFT JOIN emp_status c ON a.employment_status = c.id WHERE a.file_status != 'Deleted' and emp_type = '$_SESSION[payclass]';";
		break;
		case "cutoffs":
			$queryString = "select period_id, date_format(period_start, '%m/%d/%Y') as pstart, date_format(period_end,'%m/%d/%Y') as pend, remarks, period_end from pay_periods order by period_end;";
		break;
		case "natholidays":
			$queryString = "select id, if(type=1,'Regular Holiday','Special Holiday') as type, date_format(date,'%m/%d/%Y') as xdate, occasion from pay_holiday_nat order by date desc;";
		break;
		case "locholidays":
			$queryString = "select id, b.region as proj, date_format(date,'%m/%d/%Y') as xdate, occasion from pay_holiday_local a left join emp_areas b on a.area = b.area order by `date` desc;";
		break;
		case "leaves":
			$queryString = "SELECT trans_id AS id, a.emp_id, CONCAT(b.lname,', ',fname,' ',LEFT(mname,1),'.') AS `name`, DATE_FORMAT(`date`,'%m/%d/%Y') AS tdate, c.description AS `type`,`length`, CONCAT(DATE_FORMAT(date_from,'%m/%d/%Y'),' to ',DATE_FORMAT(date_to,'%m/%d/%Y')) AS `range`, reasons FROM pay_loa a LEFT JOIN emp_masterfile b ON a.emp_id=b.emp_id LEFT JOIN options_leavetype c ON a.leave_type = c.type WHERE a.file_status = 'Active' and b.file_status != 'DELETED' and b.employment_status not in (7,8,9,10) and b.emp_type = '$_SESSION[payclass]' ORDER BY `date` DESC";
		break;
		case "deductions":
			$queryString = "select a.record_id as rid, a.emp_id,concat(b.lname,', ',b.fname,' ',left(b.mname,1),'.') as emp_name, c.deduction_type as dtype, a.amount, concat(date_format(period_start,'%m/%d/%y'),' - ',date_format(period_end,'%m/%d/%y')) as period, a.remarks from emp_otherdeductions a left join emp_masterfile b on a.emp_id=b.emp_id left join option_deductiontype c on a.deduction_type = c.id left join pay_periods d on a.period_id=d.period_id where a.file_status != 'Deleted' and b.file_status != 'DELETED' and b.employment_status not in (7,8,9,10) and b.emp_type = '$_SESSION[payclass]' order by a.period_id desc, a.record_id desc";
		break;
		case "adjustments":
			$queryString = "SELECT a.record_id as rid, a.emp_id, CONCAT(b.lname,', ',b.fname,' ',LEFT(b.mname,1),'.') AS emp_name, UCASE(d.adjust_type) as adjtype, a.amount, CONCAT(DATE_FORMAT(period_start,'%m/%d/%y'),' - ',DATE_FORMAT(period_end,'%m/%d/%y')) AS period, a.remarks FROM emp_adjustments a LEFT JOIN emp_masterfile b ON a.emp_id=b.emp_id LEFT JOIN pay_periods c ON a.period_id=c.period_id left join option_adjustments d on a.adjustment_type = d.id WHERE a.file_status != 'Deleted' and a.emp_id != '' and b.file_status != 'DELETED' and b.employment_status not in (7,8,9,10) and b.emp_type = '$_SESSION[payclass]' order by a.period_id desc, a.emp_id";
		break;
		//case "addbasic":
		//	$queryString = "SELECT record_id as rid, a.emp_id, CONCAT(b.lname,', ',b.fname,' ',LEFT(b.mname,1),'.') AS emp_name, a.amount, CONCAT(DATE_FORMAT(period_start,'%m/%d/%y'),' - ',DATE_FORMAT(period_end,'%m/%d/%y')) AS period, a.remarks FROM emp_basic2 a LEFT JOIN emp_masterfile b ON a.emp_id=b.emp_id LEFT JOIN pay_periods c ON a.period_id=c.period_id WHERE a.file_status != 'Deleted' and a.emp_id != '' order by a.period_id desc, a.emp_id";
		//break;
		case "loans":
			$queryString = "SELECT a.record_id, a.emp_id, CONCAT(b.lname,', ',b.fname,' ',LEFT(b.mname,1),'.') AS emp_name, c.loan_type, DATE_FORMAT(date_loan, '%m/%d/%Y') AS deyt, loan_amt AS gross_amt, loan_terms, monthly_amrtz, '' AS balance, date_loan FROM emp_loanmasterfile a LEFT JOIN emp_masterfile b ON a.emp_id = b.emp_id LEFT JOIN option_loantype c ON a.loan_type = c.id WHERE a.file_status != 'Deleted' AND b.`FILE_STATUS` != 'DELETED' AND b.EMPLOYMENT_STATUS NOT IN (7,8,9,10) and b.emp_type = '$_SESSION[payclass]' ORDER BY a.date_loan DESC, emp_name ASC";
		break;
		case "incentives":
			$queryString = "select concat(a.record_id,'|',a.pay_type) as rid, a.emp_id,concat(b.lname,', ',b.fname,' ',left(b.mname,1),'.') as emp_name, c.incType as incType, a.amount, concat(date_format(period_start,'%m/%d/%y'),' - ',date_format(period_end,'%m/%d/%y')) as period, a.remarks from emp_incentives a left join emp_masterfile b on a.emp_id=b.emp_id left join option_incentives c on a.incentive_type = c.id left join pay_periods d on a.period_id=d.period_id where a.file_status != 'Deleted' and b.file_status != 'DELETED' and b.employment_status not in (7,8,9,10) and b.emp_type = '$_SESSION[payclass]' order by a.period_id desc, a.record_id desc";
		break;
		
	}
	
	$data = array();
	$datares = $mydb->dbquery($queryString);
	if($datares) {
		while($row = $datares->fetch_array(MYSQLI_ASSOC)){
			
			switch($_REQUEST['mod']) {
				case "loans":
					list($dGT) = $mydb->getArray("select ifnull(sum(amount),0) from emp_deductionmaster where ref_id = '$row[record_id]' and `type` = 'L';");
					$row['balance'] = $row['gross_amt'] - $dGT;
				break;
			}
			
			
		  $data[] = array_map('utf8_encode',$row);
		}
	}
	
	$results = ["sEcho" => 1,"iTotalRecords" => count($data),"iTotalDisplayRecords" => count($data),"aaData" => $data];
	echo json_encode($results);
?>