<?php
	session_start();
	require_once '../handlers/_payroll.php';
	require_once '../handlers/_generics.php';
	
	$mydb = new _init;
	
	switch($_POST['mod']) {
		case "deleteEmployee":
			$mydb->dbquery("UPDATE ignore emp_masterfile SET FILE_STATUS = 'DELETED', DELETED_BY = '$_SESSION[userid]', DELETED_ON = NOW() WHERE record_id = '$_POST[rid]';");
		break;
		case "getCutoff":
			echo json_encode($mydb->getArray("SELECT period_id AS rid, DATE_FORMAT(period_start,'%m/%d/%Y') AS dtf, DATE_FORMAT(period_end,'%m/%d/%Y') AS dt2, reportingMonth, reportingYear, weekOfMonth, remarks FROM pay_periods WHERE period_id = '$_POST[id]';"));
		break;
		case "updateCutoff":
			$mydb->dbquery("update ignore pay_periods set period_start='".$mydb->formatDate($_POST[dtf])."', period_end='".$mydb->formatDate($_POST[dt2])."', reportingMonth = '$_POST[month]', reportingYear = '$_POST[year]', weekOfMonth = '$_POST[week]', remarks = '".$mydb->escapeString($_POST[remarks])."' where period_id = '$_POST[rid]';");
		break;
		case "newCutoff":
			$mydb->dbquery("insert ignore into pay_periods (period_start,period_end,reportingMonth,reportingYear,weekOfMonth,remarks) values ('".$mydb->formatDate($_POST[dtf])."','".$mydb->formatDate($_POST[dt2])."','$_POST[month]','$_POST[year]','$_POST[week]','".$mydb->escapeString($_POST[remarks])."');");
		break;
		
		case "popEmployees":
			$f1 = '';
			$option = "<option value=''>- All Employees -</option>";
			if($_POST['area'] != '') { $f1 = "and `AREA` = '$_POST[area]' "; }
			if($_POST['dept'] != '') { $f1 .= "and `DEPT` = '$_POST[dept]' "; }
		
			$eQuery = $mydb->dbquery("select EMP_ID, CONCAT(LNAME,', ',FNAME,' ',LEFT(MNAME,1),'.') from emp_masterfile where EMP_TYPE = '$_SESSION[payclass]' and FILE_STATUS != 'DELETED' AND EMPLOYMENT_STATUS NOT IN (7,8,9,10) $f1 $f2 ORDER BY LNAME, FNAME;");
			while(list($eid,$ename) = $eQuery->fetch_array()) {
				$option .= "<option value='$eid'>$ename</option>";
			}
			
			echo $option;
			
		break;
		
		case "popDepartments":
			$option = "<option value=''>- All Departments -</option>";
			$dQuery = $mydb->dbquery("select id, dept_name from options_dept where branch_id = '$_POST[bid]' ORDER BY dept_name;");
			while(list($did,$dname) = $dQuery->fetch_array()) {
				$option .= "<option value='$did'>$dname</option>";
			}
			
			echo $option;
			
		break;
		
		case "deleteCutoff":
			$mydb->dbquery("delete from pay_periods where period_id = '$_POST[rid]';");
		break;
		case "getNatHoliday":
			echo json_encode($mydb->getArray("SELECT id, `type` as xtype, DATE_FORMAT(`date`,'%m/%d/%Y') AS xdate, occasion FROM pay_holiday_nat WHERE id = '$_POST[id]';"));
		break;
		case "updateNatHoliday":
			$mydb->dbquery("UPDATE pay_holiday_nat SET `type` = '$_POST[type]', `date` = '".$mydb->formatDate($_POST['date'])."', occasion = '".$mydb->escapeString(htmlentities($_POST['occasion']))."' WHERE id = '$_POST[rid]';");
		break;
		case "newNatHoliday":
			$mydb->dbquery("INSERT IGNORE INTO pay_holiday_nat (`type`,`date`,occasion) VALUES ('$_POST[type]','".$mydb->formatDate($_POST['date'])."','".$mydb->escapeString(htmlentities($_POST['occasion']))."');");
		break;
		case "deleteNatHoliday":
			$mydb->dbquery("delete from pay_holiday_nat where id = '$_POST[rid]';");
		break;
		
		case "getLocHoliday":
			echo json_encode($mydb->getArray("SELECT id, `area`, DATE_FORMAT(`date`,'%m/%d/%Y') AS xdate, occasion FROM pay_holiday_local WHERE id = '$_POST[id]';"));
		break;
		case "updateLocHoliday":
			$mydb->dbquery("UPDATE pay_holiday_local SET `area` = '$_POST[area]', `date` = '".$mydb->formatDate($_POST['date'])."', occasion = '".$mydb->escapeString(htmlentities($_POST['occasion']))."' WHERE id = '$_POST[rid]';");
		break;
		case "newLocHoliday":
			$mydb->dbquery("INSERT IGNORE INTO pay_holiday_local (`area`,`date`,occasion) VALUES ('$_POST[area]','".$mydb->formatDate($_POST['date'])."','".$mydb->escapeString(htmlentities($_POST['occasion']))."');");
		break;
		case "deleteLocHoliday":
			$mydb->dbquery("delete from pay_holiday_local where id = '$_POST[rid]';");
		break;
		
		
		case "getLeave":
			echo json_encode($mydb->getArray("SELECT *, date_format(`date`,'%m/%d/%Y') as tdate, if(date_from != '0000-00-00',date_format(date_from,'%m/%d/%Y'),'') as dtf, if(date_to != '0000-00-00',date_format(date_to,'%m/%d/%Y'),'') as dt2 FROM pay_loa WHERE trans_id = '$_POST[id]';"));
		break;
		case "updateLeave":
			$mydb->dbquery("UPDATE IGNORE pay_loa set emp_id = '$_POST[emp_id]', emp_name='".$mydb->escapeString($_POST['emp_name'])."', `date` = '".$mydb->formatDate($_POST['date'])."', `length` = '$_POST[length]', date_from = '". $mydb->formatDate($_POST['dateFrom']) ."', date_to = '". $mydb->formatDate($_POST['dateTo']) ."',  leave_type = '$_POST[type]', reasons = '".$mydb->escapeString($_POST['reasons'])."', address_on_leave = '".$mydb->escapeString($_POST['address'])."', w_pay = '$_POST[w_pay]', updated_by = '$_SESSION[userid]', updated_on = NOW() WHERE trans_id = '$_POST[rid]';");
		break;
		case "newLeave":
			$mydb->dbquery("INSERT IGNORE INTO pay_loa (emp_id,emp_name,`date`,date_from,date_to,`length`,leave_type,reasons,address_on_leave,w_pay) values ('$_POST[emp_id]','".$mydb->escapeString($_POST['emp_name'])."','".$mydb->formatDate($_POST['date'])."','". $mydb->formatDate($_POST['dateFrom']) ."','". $mydb->formatDate($_POST['dateTo']) ."','$_POST[length]','$_POST[type]','".$mydb->escapeString($_POST['reasons'])."','".$mydb->escapeString($_POST['address'])."','$_POST[w_pay]');");
		break;
		case "deleteLeave":
			$mydb->dbquery("update ignore pay_loa set file_status = 'Deleted', deleted_by = '$_SESSION[userid]', deleted_on = now() where trans_id = '$_POST[rid]';");
		break;
		case "getDeduction":
			echo json_encode($mydb->getArray("select *, format(amount,2) as amt, date_format(doc_date,'%m/%d/%Y') as dd8 from emp_otherdeductions where record_id = '$_POST[id]';"));
		break;
		case "updateDeduction":
			$mydb->dbquery("UPDATE ignore emp_otherdeductions set emp_id = '$_POST[emp_id]', deduction_type = '$_POST[type]', amount = '".$mydb->formatDigit($_POST['amount'])."', period_id = '$_POST[period]', remarks = '".$mydb->escapeString($_POST['remarks'])."', doc_no = '$_POST[doc_no]', doc_date = '".$mydb->formatDate($_POST['doc_date'])."', doc_type = '$_POST[doc_type]', updated_by = '$_SESSION[userid]', updated_on = NOW() WHERE record_id = '$_POST[rid]';");
		break;
		case "newDeduction":
			$mydb->dbquery("insert ignore into emp_otherdeductions (emp_id, deduction_type, amount, period_id, remarks, doc_no, doc_date, doc_type, created_by, created_on) values ('$_POST[emp_id]','$_POST[type]','".$mydb->formatDigit($_POST['amount'])."','$_POST[period]','".$mydb->escapeString($_POST['remarks'])."','$_POST[doc_no]','".$mydb->formatDate($_POST['doc_date'])."','$_POST[doc_type]','$_SESSION[userid]',now());");
		break;
		case "deleteDeduction":
			$mydb->dbquery("update ignore emp_otherdeductions set file_status = 'Deleted', deleted_by = '$_SESSION[userid]', deleted_on = now() where record_id = '$_POST[rid]';");
		break;
		case "getIncentive":
			echo json_encode($mydb->getArray("select *, format(amount,2) as amt from emp_incentives where record_id = '$_POST[id]';"));
		break;
		case "updateIncentive":
			$mydb->dbquery("UPDATE ignore emp_incentives set pay_type = '$_POST[ptype]', emp_id = '$_POST[emp_id]', incentive_type = '$_POST[type]', amount = '".$mydb->formatDigit($_POST['amount'])."', period_id = '$_POST[period]', remarks = '".$mydb->escapeString($_POST['remarks'])."', updated_by = '$_SESSION[userid]', updated_on = NOW() WHERE record_id = '$_POST[rid]';");
		break;
		case "newIncentive":
			$mydb->dbquery("insert ignore into emp_incentives (pay_type,emp_id, incentive_type, amount, period_id, remarks, created_by, created_on) values ('$_POST[ptype]','$_POST[emp_id]','$_POST[type]','".$mydb->formatDigit($_POST['amount'])."','$_POST[period]','".$mydb->escapeString($_POST['remarks'])."','$_SESSION[userid]',now());");
		break;
		case "deleteIncentive":
			$mydb->dbquery("update ignore emp_incentives set file_status = 'Deleted', deleted_by = '$_SESSION[userid]', deleted_on = now() where record_id = '$_POST[rid]';");
		break;
		case "getLoan":
			echo json_encode($mydb->getArray("SELECT *, DATE_FORMAT(date_loan,'%m/%d/%Y') AS date_loan, DATE_FORMAT(effective_date,'%m/%d/%Y') AS eff, FORMAT(loan_amt,2) AS gamt, FORMAT(semi_amrtz,2) AS amrtz, date_format(doc_date,'%m/%d/%Y') as dd8 FROM emp_loanmasterfile WHERE record_id = '$_POST[id]';"));
		break;
		case "updateLoan":
			$mydb->dbquery("UPDATE ignore emp_loanmasterfile SET emp_id = '$_POST[emp_id]', loan_type = '$_POST[type]', date_loan = '".$mydb->formatDate($_POST['loan_date'])."', loan_amt = '".$mydb->formatDigit($_POST['amount'])."', loan_terms = '$_POST[terms]', effective_date = '".$mydb->formatDate($_POST['eff'])."', semi_amrtz = '".$mydb->formatDigit($_POST['amrtz'])."', monthly_amrtz = '" . $mydb->formatDigit($_POST[monthly_amrtz]) . "', dedu_type = '$_POST[dedu_type]', active = '$_POST[active]', doc_no = '$_POST[doc_no]', doc_date = '" . $mydb->formatDate($_POST['doc_date']) . "', doc_type = '$_POST[doc_type]', remarks = '".$mydb->escapeString($_POST['remarks'])."', updated_by = '$_SESSION[userid]', updated_on = NOW() WHERE record_id = '$_POST[rid]';");
		break;
		case "newLoan":
			$mydb->dbquery("insert into emp_loanmasterfile (emp_id,loan_type,date_loan,loan_amt,loan_terms,effective_date,semi_amrtz,monthly_amrtz,dedu_type,active,doc_no,doc_date,doc_type,remarks,created_by,created_on) values ('$_POST[emp_id]','$_POST[type]','".$mydb->formatDate($_POST['loan_date'])."','".$mydb->formatDigit($_POST['amount'])."','$_POST[terms]','".$mydb->formatDate($_POST['eff'])."','".$mydb->formatDigit($_POST['amrtz'])."','" . $mydb->formatDigit($_POST['monthly_amrtz']) . "','$_POST[dedu_type]','$_POST[active]','$_POST[doc_no]','" . $mydb->formatDate($_POST['doc_date']) . "','$_POST[doc_type]','".$mydb->escapeString($_POST['remarks'])."','$_SESSION[userid]',now());");
		break;
		case "deleteLoan":
			$mydb->dbquery("update emp_loanmasterfile set file_status = 'Deleted', deleted_by = '$_SESSION[userid]', deleted_on = now() where record_id = '$_POST[rid]';");
		break;
		case "getAdjustment":
			echo json_encode($mydb->getArray("SELECT *, format(amount,2) as amt FROM emp_adjustments WHERE record_id = '$_POST[id]';"));
		break;
		case "updateAdjustment":
			$mydb->dbquery("UPDATE emp_adjustments SET emp_id = '$_POST[emp_id]', adjustment_type = '$_POST[type]', `taxable` = '$_POST[taxable]', amount = '".$mydb->formatDigit($_POST['amount'])."', period_id = '$_POST[period]', remarks = '".$mydb->escapeString($_POST[remarks])."', updated_by = '$_SESSION[userid]', updated_on = NOW() WHERE record_id = '$_POST[rid]';");
		break;
		case "newAdjustment":
			$mydb->dbquery("insert into emp_adjustments (emp_id,adjustment_type,`taxable`,amount,period_id,remarks,created_by,created_on) values ('$_POST[emp_id]','$_POST[type]','$_POST[taxable]','".$mydb->formatDigit($_POST[amount])."','$_POST[period]','".$mydb->escapeString($_POST[remarks])."','$_SESSION[userid]',now());");
		break;
		case "deleteAdjustment":
			$mydb->dbquery("update emp_adjustments set file_status = 'Deleted', deleted_by = '$_SESSION[userid]', deleted_on = now() where record_id = '$_POST[rid]';");
		break;
		case "getBasic2":
			echo json_encode($mydb->getArray("SELECT *, format(amount,2) as amt FROM emp_basic2 WHERE record_id = '$_POST[id]';"));
		break;
		case "updateBasic2":
			$mydb->dbquery("UPDATE emp_basic2 SET pay_type='$_POST[ptype]', emp_id = '$_POST[emp_id]', `taxable` = '$_POST[taxable]', amount = '".$mydb->formatDigit($_POST['amount'])."', period_id = '$_POST[period]', remarks = '".$mydb->escapeString($_POST[remarks])."', updated_by = '$_SESSION[userid]', updated_on = NOW() WHERE record_id = '$_POST[rid]';");
		break;
		case "newBasic2":
			$mydb->dbquery("insert into emp_basic2 (pay_type,emp_id,`taxable`,amount,period_id,remarks,created_by,created_on) values ('$_POST[ptype]','$_POST[emp_id]','$_POST[taxable]','".$mydb->formatDigit($_POST[amount])."','$_POST[period]','".$mydb->escapeString($_POST[remarks])."','$_SESSION[userid]',now());");
		break;
		case "deleteBasic2":
			$mydb->dbquery("update emp_basic2 set file_status = 'Deleted', deleted_by = '$_SESSION[userid]', deleted_on = now() where record_id = '$_POST[rid]';");
		break;
		case "populateEmp":
			$qemp = $mydb->dbquery("select emp_id, concat(lname,', ',fname,' ',left(mname,1),'.') as name from emp_masterfile where file_status != 'DELETED' and payroll_type = '$_POST[type]' and employment_status not in (7,8,9,10) order by lname, fname;");
			while($emprow = $qemp->fetch_array(MYSQLI_BOTH)) {
				print "<option value='$emprow[0]'>".strtoupper($emprow[1])."</option>\n";
			}
		break;
		case "getEmpName":
			echo json_encode($mydb->getArray("SELECT CONCAT('(',emp_id,') ',lname,', ',fname,' ',mname) FROM emp_masterfile WHERE EMP_ID = '$_POST[eid]';"));
		break;
		case "saveEDTR":
		
			list($autoNoon,$area) = $mydb->getArray("select `AUTO_NOON` from emp_masterfile where EMP_ID = '$_POST[eid]';");
			list($isExist) = $mydb->getArray("select count(*) from emp_dtrfinal where `DATE` = '$_POST[date]' and EMP_ID = '$_POST[eid]';");	
			if($isExist > 0) {
				$mydb->dbquery("update ignore emp_dtrfinal set `$_POST[type]` = '$_POST[val]:00', DEPT = '$_POST[dept]', updated_by = '$_SESSION[userid]', updated_on = now(), remote_ip = '$_SERVER[REMOTE_ADDR]' where `DATE` = '$_POST[date]' and EMP_ID = '$_POST[eid]';");
			} else {
				$mydb->dbquery("insert ignore into emp_dtrfinal (PERIOD_ID,EMP_ID,EMP_TYPE,DEPT,`DATE`,`$_POST[type]`,ENCODED_BY,ENCODED_ON) values ('$_POST[period]','$_POST[eid]','$_POST[etype]','". STR_PAD($_POST['dept'],2,'0',STR_PAD_LEFT) . "','$_POST[date]','$_POST[val]:00','$_SESSION[userid]',now());");
			}

			list($ins,$oas,$ips,$ops) = $mydb->getArray("select time_to_sec(IN_AM),time_to_sec(OUT_AM),time_to_sec(IN_PM),time_to_sec(OUT_PM) from emp_dtrfinal where EMP_ID = '$_POST[eid]' and `DATE` = '$_POST[date]';");
			$mypay = new payroll($_POST['period']);
			$mypay->computeTimeSheets($_POST['eid'],$_POST['date'],$_POST['day'],$_POST['sched'],$ins,$oas,$ips,$ops,$autoNoon);
			$mypay->checkHoliday($_POST['date'],$area);
			$mydb->dbquery("update ignore emp_dtrfinal set tot_work='".$mypay->twork."',tot_late='".$mypay->late."', tot_ut='".$mypay->ut."',reg_ot = '".$mypay->overtime."',sun_ot = '".$mypay->restday."',prem_ot='".$mypay->premium."',hd_type='".$mypay->htype."', updated_by = '$_SESSION[userid]', updated_on = now(), remote_ip = '$_SERVER[REMOTE_ADDR]' where emp_id='$_POST[eid]' and date='$_POST[date]';");
				
		break;
		
		case "changeSched":
		
			$hdType = "NA";
			list($autoNoon,$area) = $mydb->getArray("select `AUTO_NOON`,`AREA` from kredoithris.emp_masterfile where EMP_ID = '$_POST[eid]';");
			list($ins,$oas,$ips,$ops) = $mydb->getArray("select time_to_sec(IN_AM),time_to_sec(OUT_AM),time_to_sec(IN_PM),time_to_sec(OUT_PM) from emp_dtrfinal where EMP_ID = '$_POST[eid]' and `DATE` = '$_POST[date]';");
			
			if($autoNoon == 'Y') {			
				
				if($ins > 0 || $oas > 0 || $ips > 0 || $ops > 0) {
					
					if($ins > 0 && $ops > 0) {
						$mypay = new payroll($_POST['period']);
						$mypay->computeTimeSheets($_POST['date'],$_POST['day'],$_POST['sched'],$ins,$oas,$ips,$ops,$autoNoon);
						$mydb->dbquery("update ignore emp_dtrfinal set tot_work='".$mypay->twork."',tot_late='".$mypay->late."', tot_ut='".$mypay->ut."', reg_ot = '".$mypay->overtime."', sun_ot = '".$mypay->restday."', prem_ot='".$mypay->premium."',hd_type='".$mypay->htype."', shift='$_POST[sched]', updated_by = '$_SESSION[userid]', updated_on = now(), remote_ip = '$_SERVER[REMOTE_ADDR]' where emp_id='$_POST[eid]' and date = '$_POST[date]';");
					}
				
				} else {
					$mydb->dbquery("update ignore emp_dtrfinal set shift = '$_POST[sched]', tot_work = 0, tot_late = 0, tot_ut = 0, reg_ot = 0, sun_ot = 0, prem_ot = 0, updated_by = '$_SESSION[userid]', updated_on = now(), remote_ip = '$_SERVER[REMOTE_ADDR]' where emp_id = '$_POST[eid]' and `date` = '$_POST[date]';"); 
				}
			
			} else {
				
				if(($ins > 0 && $oas > 0) || ($ips > 0 && $ops > 0)) {
					$mypay = new payroll($_POST['period']);
					$mypay->computeTimeSheets($_POST['eid'],$_POST['date'],$_POST['day'],$_POST['sched'],$ins,$oas,$ips,$ops,$autoNoon);
					$mypay->checkHoliday($_POST['date'],$area);
					
					$mydb->dbquery("update ignore emp_dtrfinal set tot_work='".$mypay->twork."',tot_late='".$mypay->late."', tot_ut='".$mypay->ut."', reg_ot = '".$mypay->overtime."', sun_ot = '".$mypay->restday."', prem_ot='".$mypay->premium."',hd_type='". $mypay->htype . "', shift = '$_POST[sched]', updated_by = '$_SESSION[userid]', updated_on = now(), remote_ip = '$_SERVER[REMOTE_ADDR]' where emp_id='$_POST[eid]' and date='$_POST[date]';");
				} else { 
					$mydb->dbquery("update ignore emp_dtrfinal set shift='$_POST[sched]', tot_work = 0, tot_late = 0, tot_ut = 0, reg_ot = 0, sun_ot = 0, prem_ot = 0, updated_by = '$_SESSION[userid]', updated_on = now(), remote_ip = '$_SERVER[REMOTE_ADDR]' where emp_id = '$_POST[eid]' and `date` = '$_POST[date]';"); 
						
				}
			}
			
			
		break;
		
		case "otApprove":
			$mydb->dbquery("update ignore emp_dtrfinal set OT_APPROVE = 'Y' where record_id = '$_POST[rid]';");
		break;
		case "otDisApprove":
			$mydb->dbquery("update ignore emp_dtrfinal set OT_APPROVE = 'N' where record_id = '$_POST[rid]';");
		break;	
		case "npApprove":
			$mydb->dbquery("update ignore emp_dtrfinal set NP_APPROVE = 'Y' where record_id = '$_POST[rid]';");
		break;
		case "npDisApprove":
			$mydb->dbquery("update ignore emp_dtrfinal set NP_APPROVE = 'N' where record_id = '$_POST[rid]';");
		break;
	}
	
	@mysql_close($con);
?>