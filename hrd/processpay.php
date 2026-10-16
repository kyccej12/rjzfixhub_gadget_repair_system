<?php
	
	session_start();
	ini_set("max_execution_time",-1);
	require_once "../handlers/_payroll.php";
	
	if($_POST['area'] != '') { $f1 = " and `area` = '$_POST[area]' "; $lk1 = "&area=$_POST[area]"; } else { $f1 = ""; $lk1 = ""; }
	if($_POST['dept'] != '') { $f2 = " and dept = '$_POST[dept]' "; $lk2 = "&dept=$_POST[dept]"; } else { $f2 = ""; $lk2 = ""; }
	
	$pay = new payroll($_REQUEST['cutoff']);
	
		
	/* Delete Similar Records Previously Processed */
	$pay->dbquery("delete from emp_dtrfinal where emp_type = '$_SESSION[payclass]' and period_id = '". $pay->cutoff ."' and IN_AM = '00:00:00' and OUT_AM = '00:00:00' and IN_PM = '00:00:00' and OUT_PM = '00:00:00';");
	$pay->dbquery("delete from emp_payslip where emp_type = '$_SESSION[payclass]' and period_id = '". $pay->cutoff ."' $f1 $f2;");
	$pay->dbquery("delete from emp_deductionmaster where emp_type = '$_SESSION[payclass]' and period_id = '". $pay->cutoff ."' $f1 $f2;");
	
	$mainQuery = $pay->dbquery("SELECT emp_id, emp_type, payroll_type as pay_type, CONCAT(LNAME,', ',FNAME,' ',LEFT(MNAME,1),'.') AS emp_name, dept, `area`, acct_no, sl_credit, vl_credit, basic_rate, cola, allowance, allowance_type, ROUND(nontax_allowance/2,2) AS ntx, HDMF_PREMIUM AS hdmf, w_tax, coop_premium, retirement_plan, W_SSS, W_PHILHEALTH, W_HDMF, FLEX_TIME, UMEMBER, UNION_DUES, EXEMPT_PAYREG FROM emp_masterfile WHERE FILE_STATUS != 'DELETED' AND EMPLOYMENT_STATUS NOT IN (7,8,9,10) and EMP_TYPE = '$_SESSION[payclass]' $f1 $f2;");
	while($mainRow = $mainQuery->fetch_array(MYSQLI_BOTH)) {
	
		$q = $pay->dbquery("SELECT ROUND(SUM(tot_work)/8,2) AS twork, SUM(tot_late) AS late, SUM(tot_ut) AS ut, SUM(reg_ot) AS reg_ot, SUM(prem_ot) AS prem_ot, ROUND(sum(sun_ot),2) as sun_ot, ROUND(SUM(tot_late+tot_ut) / 8,2) AS lut FROM emp_dtrfinal a WHERE a.date between '" . $pay->dtf . "' and '" . $pay->dt2 . "' and a.emp_id = '$mainRow[emp_id]' and HD_TYPE = 'NA' GROUP BY emp_id;");
		$row = $q->fetch_array(MYSQLI_BOTH);


			
		$base = 0;
		$vl = 0;
		$sl = 0;
		$adj = 0;
		$cola = 0;
		$basic2 = 0;
		$basicDeductions = 0;
		$incentives = 0;
		$netpay = 0;
		$taxable = 0;
		$taxable = 0;
		$wtax = 0;
		$allowance = 0;
		$transpo = 0;
		$meal = 0;
		$gross = 0;
		$basic_pay = 0;
		$basic_day = 0;
		$s_rate = 0;
		$d_rate = 0;
		$lt = 0;
		$ott = 0;
		$sil_pay = 0;
		$vl_pay = 0;
		$sl_pay = 0;
		$lholiday_pay = 0;
		$sholiday_pay = 0;
		$rdholiday_pay = 0;
		$late = 0;
		$ut = 0;
		$absences = 0;
		$udues = 0;
		$coop = 0;
		$npHours = 0;
	
		$pay->checkSL($mainRow['emp_id'],$mainRow['sl_credit']);
		$pay->checkVL($mainRow['emp_id'],$mainRow['vl_credit']);
		$pay->checkSIL($mainRow['emp_id']);
		$pay->countHolidays($mainRow['area']);
		$pay->getRates($mainRow['pay_type'],$mainRow['basic_rate']);
		$pay->getAbsences($mainRow['emp_id'],$mainRow['area']);
		
		
		if($pay->dtrCount > 0) {
			if($mainRow['pay_type'] == 2) {
				
				$base = $row['twork'];
				$basic_pay = ROUND($row['twork'] * $mainRow['basic_rate'],2);
				$pay->hrate = ROUND($mainRow['basic_rate']/8,2);
				$pay->lholiday = 0; /* $pay->sholiday = 0; $pay->sl = 0; $pay->vl = 0; $pay->sil = 0; $pay->absences = 0; */
			
			} else {	
				
				$late = ROUND(($row['late']/8) * $pay->dailyRate,2);
				$ut = ROUND(($row['ut']/8) * $pay->dailyRate,2);
				$lholiday_pay = ROUND($pay->lholiday * $pay->dailyRate,2); 
				$sholiday_pay = ROUND($pay->sholiday * $pay->dailyRate,2);
				$rdholiday_pay = ROUND($pay->onRestDayHoliday * $pay->dailyRate,2);
				$sl_pay = ROUND($pay->sl * $pay->dailyRate,2);
				$vl_pay = ROUND($pay->vl * $pay->dailyRate,2);
				$other_leaves = ROUND($pay->sil * $pay->dailyRate,2);
				$absences = ROUND($pay->absences * $pay->dailyRate,2);
				
				$basicDeductions = $late + $ut + $lholiday_pay + $sholiday_pay + $sl_pay + $vl_pay + $other_leaves + $absences;
				$base = $pay->baseDays - $row['lut'] - $pay->holidayCount - $pay->sl - $pay->vl - $pay->sil - $pay->absences;
				$basic_pay = $pay->semiRate - $basicDeductions; 
			
			}
		
			/* COLA Computation */
			if($mainRow['cola'] > 0) {
				$cocaCola = 13 - $row['lut'] - $pay->absences;
				$cola = ROUND($cocaCola * $mainRow['cola'],2);
			}
			
			if($row['prem_ot'] > 0) {
				list($npOrdinary,$np1) = $pay->getArray("select ROUND((". $pay->hrate . " * sum(prem_ot)) * 0.20,2),sum(PREM_OT) from emp_dtrfinal where  `date` between '". $pay->dtf ."' and '" . $pay->dt2 . "' and emp_id = '$mainRow[emp_id]' and hd_type = 'NA' and NP_APPROVE = 'Y';");
				list($npRegular,$np2) = $pay->getArray("select ROUND(((". $pay->hrate . " * 2) * sum(prem_ot)) * 0.20,2),sum(PREM_OT) from emp_dtrfinal where  `date` between '". $pay->dtf ."' and '" . $pay->dt2 . "' and emp_id = '$mainRow[emp_id]' and hd_type = 'LH' and NP_APPROVE = 'Y';");
				list($npSpecial,$np3) = $pay->getArray("select ROUND(((". $pay->hrate . " * 1.3) * sum(prem_ot)) * 0.20,2),sum(PREM_OT) from emp_dtrfinal where  `date` between '". $pay->dtf ."' and '" . $pay->dt2 . "' and emp_id = '$mainRow[emp_id]' and hd_type = 'SH' and NP_APPROVE = 'Y';");
				list($npSunday,$np4) = $pay->getArray("select ROUND(((". $pay->hrate . " * 1.3) * sum(prem_ot)) * 0.20,2),sum(PREM_OT) from emp_dtrfinal where  `date` between '". $pay->dtf ."' and '" . $pay->dt2 . "' and emp_id = '$mainRow[emp_id]' and hd_type = 'RD' and NP_APPROVE = 'Y';");
				$npremium_pay = $npOrdinary+$npRegular+$npSpecial+$npSunday;
				$npHours = $np1 + $np2 + $np3 + $np4;
			} else { $npremium_pay = 0; }
		
			/* Regular Overtime */
			$rot_pay = 0; $rot_pay_hrs = 0;
			list($rot_pay,$rot_pay_hrs) = $pay->getArray("select ifnull(ROUND((sum(reg_ot) * ". $pay->hrate .") * 1.25,2),0), ifnull(sum(reg_ot),0) from emp_dtrfinal where `date` between '". $pay->dtf ."' and '" . $pay->dt2 . "' and emp_id = '$mainRow[emp_id]' and hd_type = 'NA' and OT_APPROVE = 'Y';");
			
			/* Legal Holiday Overtime Computation */
			$lh_ot_hrs = 0; $lh_ot = 0; $lh_ot_ex_hrs = 0; $lh_ot_ex = 0;  
			$lhQuery = $pay->dbquery("SELECT DISTINCT `date`,date_format(`date`,'%a') FROM pay_holiday_nat WHERE `date` between '". $pay->dtf ."' and '" . $pay->dt2 . "' and type = '1';");		
			if(mysqli_num_rows($lhQuery) > 0) {
				while(list($lhDate,$lhDay) = $lhQuery->fetch_array()) {
					if($lhDay == 'Sun') { $factor = 1.30; } else { $factor = 1; }
					$lhRow = $pay->getArray("select ROUND($factor * (tot_work * " . $pay->hrate . "),2), ROUND(1.30 * (reg_ot * " . $pay->hrate. "),2), sum(tot_work), sum(reg_ot) from emp_dtrfinal where `date` = '$lhDate' and emp_id = '$mainRow[emp_id]';");
					$lh_ot += $lhRow[0]; $lh_ot_ex += $lhRow[1]; $lh_ot_hrs += $lhRow[2]; $lh_ot_ex_hrs += $lhRow[3];
				}
				unset($lhRow);
			}
			
			$sh_ot_hrs = 0; $sh_ot = 0; $sh_ot_ex_hrs = 0; $sh_ot_ex = 0;
			$shQuery = $pay->dbquery("SELECT DISTINCT `date`,DATE_FORMAT(`date`,'%a') FROM pay_holiday_nat WHERE `date` BETWEEN '". $pay->dtf ."' AND '" . $pay->dt2 . "' AND `type` = '2' UNION SELECT DISTINCT `date`,DATE_FORMAT(`date`,'%a') FROM pay_holiday_local WHERE `date` BETWEEN '". $pay->dtf ."' AND '" . $pay->dt2 . "' AND `area` = '$mainRow[area]';");
			if(mysqli_num_rows($shQuery) > 0) {
				while(list($shDate,$shDay) = $shQuery->fetch_array()) {
					if($shDay == 'Sun') { $factor = 0.50; } else { $factor = 0.30; }
					$shRow = $pay->getArray("select ROUND($factor * (tot_work * " . $pay->hrate . "),2), ROUND(1.30 * (reg_ot * " . $pay->hrate. "),2),sum(tot_work), sum(reg_ot) from emp_dtrfinal where `date` = '$shDate' and emp_id = '$mainRow[emp_id]';");
					$sh_ot += $shRow[0]; $sh_ot_ex += $shRow[1]; $sh_ot_hrs += $shRow[2]; $sh_ot_ex_hrs += $shRow[3];
				}	
				unset($shRow);
			}
				
			/* Sunday Overtime */
			$sun_ot_hrs = 0; $sun_ot = 0; $sun_otex_hrs = 0; $sun_otex = 0;
			list($sun_ot_hrs,$sun_ot,$sun_otex_hrs,$sun_otex) = $pay->getArray("select ifnull(sum(tot_work),0), ifnull(ROUND((sum(tot_work) * ". $pay->hrate .") * 1.30,2),0), ifnull(ROUND(sum(reg_ot) * ". $pay->hrate ." * 1.69,2),0), sum(reg_ot) from emp_dtrfinal where  `date` between '". $pay->dtf ."' and '" . $pay->dt2 . "' and emp_id = '$mainRow[emp_id]' and hd_type='RD' and OT_APPROVE = 'Y';");
		
			/* Allowance-Taxable Computation */
			if($mainRow['allowance'] > 0 && $base > 0) {	
				if($mainRow['allowance_type'] == "D") {	
					$allowance = ROUND($base * $mainRow['allowance'],2); 
				} else {
					$dailyAllowance = ROUND(($mainRow['allowance'] * 12 / 314),2);
					$allowance = ($mainRow['allowance']/2) - ROUND($pay->absences * $dailyAllowance,2);
				}
			}
		} else {
			
			$lholiday_pay = ROUND($pay->lholiday * $pay->dailyRate,2); 
			$sholiday_pay = ROUND($pay->sholiday * $pay->dailyRate,2);
			$rdholiday_pay = ROUND($pay->onRestDayHoliday * $pay->dailyRate,2);
			$sl_pay = ROUND($pay->sl * $pay->dailyRate,2);
			$vl_pay = ROUND($pay->vl * $pay->dailyRate,2);
			$other_leaves = ROUND($pay->sil * $pay->dailyRate,2);
			$basic_pay = 0; $base = 0;
		
		}
		
		/* Incentives */
		$incQ = $pay->dbquery("SELECT SUM(amount) FROM emp_incentives WHERE emp_id = '$mainRow[emp_id]' AND period_id = '". $pay->cutoff ."' and file_status != 'Deleted';");
		if(mysqli_num_rows($incQ)) { list($incentives) = $incQ->fetch_array(); } 
		
		/* Adjustments */
		$adjQ = $pay->dbquery("SELECT SUM(IF(adjustment_type='CR',(amount*-1),amount)) AS adjustment FROM emp_adjustments WHERE emp_id = '$mainRow[emp_id]' AND period_id = '". $pay->cutoff ."' and file_status != 'Deleted' GROUP BY emp_id;");
		if(mysqli_num_rows($adjQ)) {	list($adj) = $adjQ->fetch_array(); }
		
		/* Compute Total Gross */
		$gross = $basic_pay + $allowance + $mainRow['ntx'] + $rot_pay + $npremium_pay + $sl_pay + $vl_pay + $other_leaves + $lholiday_pay + $sholiday_pay + $rdholiday_pay + $lh_ot + $lh_ot_ex + $sh_ot + +$sh_ot_ex + $sun_ot + $sun_otex + $adj + $incentives;
	
		/* Loans & Other Deductions */
		if($gross > 500) {
			
			/* Premium Contributions */
			$pay->myPremiums($mainRow['basic_rate'],$mainRow['emp_id'],$mainRow['emp_type'],$mainRow['hdmf'],$mainRow['W_SSS'],$mainRow['W_PHILHEALTH'],$mainRow['W_HDMF']);
			
			$s = $pay->dbquery("select record_id, deduction_type, amount, created_on from emp_otherdeductions where emp_id = '$mainRow[emp_id]' and period_id = '". $pay->cutoff ."' and file_status != 'Deleted';");
			if(mysqli_num_rows($s)) {
				while(list($did,$dtype,$damt,$dd8) = $s->fetch_array(MYSQLI_BOTH)) {
					$pay->dbquery("insert ignore into emp_deductionmaster (period_id,emp_type,emp_id,area,dept,type,ref_id,ref_date,ref_type,amount,posted_by,posted_on) values ('". $pay->cutoff ."','$mainRow[emp_type]','$mainRow[emp_id]','$mainRow[area]','$mainRow[dept]','O','$did','$dd8','$dtype','$damt','$_SESSION[userid]',now());");
				}
			}
			$pay->loadLoans($mainRow['emp_id'],$mainRow['emp_type'],$mainRow['area'],$mainRow['dept']);
		}
		/* End of Loans & Other Deductions */
	
		
		if($pay->wom == 2) {
			
			/* UNION DUES */
			$udues = $mainRow['UNION_DUES'];
			
			/* Withholding Tax */
			if($mainRow['w_tax'] > 0) {
				$wtax = $mainRow['w_tax'];
			} else {
				$pay->getPreviousPays($mainRow['emp_id']);
				list($nonTaxable) = $pay->getArray("SELECT IFNULL(SUM(amount),0) FROM emp_adjustments WHERE emp_id = '$mainRow[emp_id]' AND period_id = '".$pay->cutoff."' AND taxable = 'N' and file_status != 'Deleted';");
				$currentTaxable = $gross - $incentives - $nonTaxable;
				
				$taxable = $currentTaxable + $pay->previousTaxable;
				if($taxable > 20832) {
					list($wtax) = $pay->getArray("SELECT ROUND(((0$taxable - base) * ex_factor)+base_tax,2) FROM pay_newtax WHERE 0$taxable >= base AND 0$taxable <= top;");
				}
				
			}
		}
		
		/* Loans Total */
		$ltQ = $pay->dbquery("select sum(amount) from emp_deductionmaster where period_id = '". $pay->cutoff . "' and emp_id = '$mainRow[emp_id]' and type = 'L';");
		$ottQ = $pay->dbquery("select sum(amount) from emp_deductionmaster where period_id = '". $pay->cutoff . "' and emp_id = '$mainRow[emp_id]' and type = 'O';");
		
		if($ltQ) { list($lt) = $ltQ->fetch_array(); }
		if($ottQ) {	list($ott) = $ottQ->fetch_array(); }
		
		/* Coop Premium & Retirement Plan */	
		$netpay = ROUND(($gross - $pay->sss_premium - $pay->ph_premium - $pay->pg_premium - $wtax - $lt - $ott - $mainRow['coop_premium'] - $udues),2);
		
		if($netpay > 0) {
			$pay->dbquery("insert ignore into emp_payslip (period_id,pay_type,emp_type,`area`,dept,acct_no,emp_id,emp_name,monthly_rate,semi_rate,daily_rate,basic_day,basic_pay,absences,late,undertime,cola,vacation_leave,
					   sick_leave,other_leaves,legal_holiday,special_holiday,restday_holiday,ot_regular_hrs,ot_regular,night_premium_hrs,night_premium,ot_sunday_hrs,ot_sunday,ot_sundayex_hrs,ot_sundayex,ot_legalholiday_hrs,ot_legalholiday,ot_legalholidayex_hrs,ot_legalholidayex,
					   ot_specialholiday_hrs,ot_specialholiday,ot_specialholidayex_hrs,ot_specialholidayex,allowance,nontax_allowance,incentives,gross_pay,sss_premium,
					   sss_premium_er,pagibig_premium,pagibig_premium_er,philhealth_premium,philhealth_premium_er,taxable_income,wtax,coop_premium,union_dues,loans_total,others_total,adjustments,net_pay,
					   processed_on,processed_by,register_exclude) values ('". $pay->cutoff ."','$mainRow[pay_type]','$mainRow[emp_type]','$mainRow[area]','$mainRow[dept]','$mainRow[acct_no]',
					   '$mainRow[emp_id]','$mainRow[emp_name]','$mainRow[basic_rate]','" . $pay->semiRate . "','". $pay->dailyRate . "','$base','$basic_pay','$absences','$late','$ut','$cola','$vl_pay','$sl_pay','$other_leaves',
					   '$lholiday_pay','$sholiday_pay','$rdholiday_pay','$rot_pay_hrs','$rot_pay','$npHours','$npremium_pay','$sun_ot_hrs','$sun_ot','$sun_otex_hrs','$sun_otex','$lh_ot_hrs','$lh_ot','$lh_ot_ex_hrs','$lh_ot_ex','$sh_ot_hrs','$sh_ot',
					   '$sh_ot_ex_hrs','$sh_ot_ex','$allowance','$mainRow[ntx]','$incentives','$gross','". $pay->sss_premium ."','". $pay->sss_premium_er ."','" . $pay->pg_premium ."','" . $pay->pg_premium_er ."',
					   '". $pay->ph_premium ."','". $pay->ph_premium_er ."','$taxable','$wtax','$mainRow[coop_premium]','$udues','$lt','$ott','$adj','$netpay',now(),'".$_SESSION['userid']."','$mainRow[EXEMPT_PAYREG]');");
		
		}
	}
	//header("Location: payslip.php?cutoff=".$pay->cutoff."$lk1"."$lk2");
?>