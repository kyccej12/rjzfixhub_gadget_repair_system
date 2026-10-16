<?php

	//ini_set("display_errors","On");
	ini_set("memory_limit","2056M");
	ini_set("max_execution_time",0);

	/* Importing Data File */
	$uploadDir = "temp/";

	$fileName = $_FILES['userfile']['name'];
	$tmpName = $_FILES['userfile']['tmp_name'];
		
	/* CHANGE UNIQUE FILENAME TO PREVENT DUPLICATION */
	$ext = substr(strrchr($fileName, "."), 1);
	$randName = md5(rand() * time());
	$newFileName = $randName . "." . $ext;
	$filePath = $uploadDir . $newFileName;
	
	$result = move_uploaded_file($tmpName, $filePath);
	if (!$result) {
		echo "Error uploading file";
		exit;
	} else {
	
		require_once '../handlers/_payroll.php';
		require_once '../handlers/_generics.php';
		
		$pay = new payroll($_POST['cutoff']);
		
		$file = "temp/$newFileName";
		$handle = fopen($file, "r");
		$read = file_get_contents($file); 
		$lines = explode("\n", $read);
		$i= 0;

		/* delete existing logs to avoid duplication */
		$pay->dbquery("delete from biologs_raw where `date` between '".$pay->dtf."' and '".$pay->dt2."';");
		

		foreach($lines as $key => $value) {
			$cols[$i] = explode("\t", $value);
		
			$empID = trim($cols[$i][0]);
			list($date,$tmpTime) = explode(' ',$cols[$i][1]);
			$time = substr_replace($tmpTime,"00",-2);
			
			if($date >= $pay->dtf && $date <= $pay->dt2) {		
				list($emp_id,$shift,$emp_type,$pay_type,$day,$timeSec) = $pay->getArray("select emp_id, shift, emp_type, payroll_type, date_format('$date','%a'), TIME_TO_SEC('$time') from emp_masterfile where EMP_ID = '$empID';");
				if($emp_id != '') {
					$pay->getTimeDefaults($shift,$day);
					if($timeSec >= $pay->def_ins_min && $timeSec <= $pay->def_ins_max) { $timePeriod = "AM"; $timeType = "I"; }
					if($timeSec > $pay->def_oas_min && $timeSec <= $pay->def_ips_max) {
						list($isE) = $pay->getArray("select count(*) from biologs_raw where emp_id = '$emp_id' and `date` = '$date' and `period` = 'AM' and `type` = 'O';");
						if($isE > 0) { $timePeriod = 'PM'; $timeType = 'I'; } else { $timePeriod = 'AM'; $timeType = 'O'; }					
					}
					if($timeSec >= $pay->def_ops_min) { $timePeriod = "PM"; $timeType = "O"; }
					$pay->dbquery("INSERT IGNORE INTO biologs_raw (emp_id,shift,emp_type,`date`,`time`,`period`,`type`) VALUES ('$emp_id','$shift','$emp_type','$date','$time','$timePeriod','$timeType');");
				}
			}
		}	

		
		for($i = 0; $i <= $pay->ndays; $i++) {			
			list($myDay,$dayOfWeek) = $pay->getArray("select date_add('". $pay->dtf . "', INTERVAL $i DAY),date_format(date_add('". $pay->dtf . "', INTERVAL $i DAY),'%a');");
			$b = $pay->dbquery("select emp_id,shift from biologs_raw where `date` = '$myDay' group by emp_id order by emp_id asc;");
			$pay->checkHoliday($myDay);
			
			while(list($emp_id,$shift) = $b->fetch_array(MYSQLI_BOTH)) {
				list($dept,$autoNoon,$area) = $pay->getArray("SELECT `DEPT`,`AUTO_NOON`,`AREA` FROM emp_masterfile where EMP_ID = '$emp_id';");
				$ins = 0; $oas = 0; $ips = 0; $ops = 0;
				list($in_am,$ins) = $pay->getArray("SELECT `time`, TIME_TO_SEC(`time`) FROM biologs_raw WHERE emp_id = '$emp_id' AND `date` = '$myDay' AND `period` = 'AM' AND `type` = 'I' LIMIT 1");
				list($out_am,$oas) = $pay->getArray("SELECT `time`, TIME_TO_SEC(`time`)  FROM biologs_raw WHERE emp_id = '$emp_id' AND `date` = '$myDay' AND `period` = 'AM' AND `type` = 'O' LIMIT 1");
				list($in_pm,$ips) = $pay->getArray("SELECT `time`, TIME_TO_SEC(`time`) FROM biologs_raw WHERE emp_id = '$emp_id' AND `date` = '$myDay' AND `period` = 'PM' AND `type` = 'I' LIMIT 1");
				list($out_pm,$ops) = $pay->getArray("SELECT `time`, TIME_TO_SEC(`time`) FROM biologs_raw WHERE emp_id = '$emp_id' AND `date` = '$myDay' AND `period` = 'PM' AND `type` = 'O' LIMIT 1");
			
				list($isE) = $pay->getArray("select count(*) from emp_dtrfinal where EMP_ID = '$emp_id' and `DATE` = '$myDay';");
				if($isE > 0) {
					$pay->dbquery("update emp_dtrfinal set IN_AM='$in_am',OUT_AM='$out_am',IN_PM='$in_pm',OUT_PM='$out_pm' where EMP_ID = '$emp_id' and `DATE` = '$myDay';");
				} else {
					$pay->dbquery("INSERT INTO emp_dtrfinal (PERIOD_ID,EMP_TYPE,EMP_ID,DEPT,`DATE`,IN_AM,OUT_AM,IN_PM,OUT_PM) VALUES ('".$pay->cutoff."','$emp_type','$emp_id','" . STR_PAD($dept,2,'0',STR_PAD_LEFT) . "','$myDay','$in_am','$out_am','$in_pm','$out_pm');");
				}
				
				$pay->checkHoliday($myDay,$area);
				$pay->computeTimeSheets($emp_id,$myDay,$dayOfWeek,$shift,$ins,$oas,$ips,$ops,$autoNoon);
				$pay->dbquery("update ignore emp_dtrfinal set tot_work='".$pay->twork."',tot_late='".$pay->late."', tot_ut='".$pay->ut."', reg_ot = '".$pay->overtime."', sun_ot = '".$pay->restday."', prem_ot='".$pay->premium."',hd_type='".$pay->htype."' where emp_id='$emp_id' and date='$myDay';");
				
				//echo "update ignore emp_dtrfinal set tot_work='".$pay->twork."',tot_late='".$pay->late."', tot_ut='".$pay->ut."', reg_ot = '".$pay->overtime."', sun_ot = '".$pay->restday."', prem_ot='".$pay->premium."',hd_type='".$pay->htype."' where emp_id='$emp_id' and date='$myDay';";
	
			}
		}
		
		
		echo "Logs Successfully Uploaded... Please close this window...";
		
	}
?>