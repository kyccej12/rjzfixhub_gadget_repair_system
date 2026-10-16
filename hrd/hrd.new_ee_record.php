<?php
	session_start();
	require_once "../handlers/_generics.php";
	
	$mydb = new _init;
	
	$rid = $_POST['record_id'];
	$old_idno = $_POST['old_idno'];
	$emp_idno = $_POST['emp_idno'];
	$emp_type = $_POST['emp_type'];
	$emp_lname = $_POST['emp_lname'];
	$emp_fname = $_POST['emp_fname'];
	$emp_mname = $_POST['emp_mname'];
	$contact_person = $_POST['contact_person'];
	$contact_nos = $_POST['contact_nos'];
	
	$emp_sex = $_POST['emp_sex'];
	$emp_bday = $mydb->formatDate($_POST['emp_bday']);
	$emp_bloodtype = urldecode($_POST['emp_bloodtype']);
	$emp_cstat = $_POST['emp_cstat'];
	$emp_add1 = $_POST['emp_add1'];
	$emp_add2 = $_POST['emp_add2'];
	$emp_religion = $_POST['emp_religion'];
	$nationality = $_POST['emp_nationality'];
	$email_add = $_POST['email_add'];
	$tel_no = $_POST['tel_no'];
	$bio_id = $_POST['bio_id'];
	
	$date_hired = $mydb->formatDate($_POST['date_hired']); 
	$emp_desg = $_POST['emp_desg'];
	$emp_dept = $_POST['emp_dept'];
	$emp_ptype = $_POST['emp_ptype'];
	$emp_stat = $_POST['emp_stat'];
	$emp_date_ret = $mydb->formatDate($_POST['date_ret']);
	$emp_flex = $_POST['emp_flex'];
	$emp_noon = $_POST['emp_noon_swipe'];
	$emp_shift = $_POST['emp_shift'];
	$emp_area = $_POST['emp_area'];
	$emp_tin = $_POST['emp_tin'];
	$emp_sss_no = $_POST['emp_sss_no'];
	$emp_hdmf_no = $_POST['emp_hdmf_no'];
	$emp_phealth_no = $_POST['emp_phealth_no'];
	$w_sss = $_POST['emp_sss'];
	$w_ph = $_POST['emp_ph'];
	$w_hdmf = $_POST['emp_hdmf'];
	
	$emp_vl = $_POST['emp_vl'];
	$emp_sl = $_POST['emp_sl'];
	$emp_rate = $mydb->formatDigit($_POST['emp_rate']);
	$cola = $mydb->formatDigit($_POST['emp_cola']);
	$emp_allw = $mydb->formatDigit($_POST['emp_allw']);
	$emp_allwtype = $_POST['emp_allwtype'];
	$emp_allw_ntx = $mydb->formatDigit($_POST['emp_allw_ntx']);
	$emp_hdmf_ee = $mydb->formatDigit($_POST['emp_hdmf_ee']);
	$empu = $_POST['emp_union'];
	$emp_uniondues = $_POST['emp_uniondues']; 
	$emp_atmbank = $_POST['emp_atmbank'];
	$emp_bank = $_POST['emp_bank'];
	$emp_expayreg = $_POST['emp_expayreg'];
	
	if($rid == "") {
		$_xsearch = $mydb->getArray("select count(*) from emp_masterfile where emp_id = '$emp_idno';");
		if($_xsearch[0] == 0) {
			$_namesearch = $mydb->getArray("select count(*) as found from emp_masterfile where lname = '$emp_lname' and fname = '$emp_fname' and mname = '$emp_mname';");
			if($_namesearch[0] == 0) { 
			   
			   $query = "INSERT IGNORE INTO emp_masterfile (EMP_TYPE,EMP_ID,BIO_ID,LNAME,FNAME,MNAME,ADDRESS1,ADDRESS2,GENDER,BIRTHDATE,EMAIL_ADD,TEL_NO,BLOOD_TYPE,
							CIVIL_STATUS,CONTACT_PERSON,CONTACT_NOS,RELIGION,NATIONALITY,DATE_HIRED,DATE_RET,DESG,DEPT,PAYROLL_TYPE,EMPLOYMENT_STATUS,FLEX_TIME,AUTO_NOON,BASIC_RATE,COLA,ALLOWANCE,
							ALLOWANCE_TYPE,NONTAX_ALLOWANCE,COOP_PREMIUM,HDMF_PREMIUM,UMEMBER,UNION_DUES,ACCT_NO,ATM_BANK,EXEMPT_PAYREG,W_TAX,VL_CREDIT,SL_CREDIT,TIN_NO,SSS_NO,HDMF_NO,
							PHEALTH_NO,W_PHILHEALTH,W_SSS,W_HDMF,`SHIFT`,`AREA`,CREATED_BY,CREATED_ON) 
						VALUES ('$emp_type','$emp_idno','$bio_id','".htmlentities($emp_lname)."','".htmlentities($emp_fname)."','".htmlentities($emp_mname)."','".$mydb->escapeString(htmlentities($emp_add1))."',
						    '".$mydb->escapeString(htmlentities($emp_add2))."','$emp_sex','$emp_bday','$email_add','$tel_no','$blood_type','$emp_cstat','".htmlentities($contact_person)."','$contact_nos','$emp_religion',
							'$nationality','$date_hired','$emp_date_ret','$emp_desg','$emp_dept','$emp_ptype','$emp_stat','$emp_flex','$emp_noon','$emp_rate','$cola','$emp_allw','$emp_allwtype','$emp_allw_ntx',
							'$emp_coop','$emp_hdmf_ee','$empu','$emp_uniondues','$emp_bank','$emp_atmbank','$emp_expayreg','$emp_wtax','$emp_vl','$emp_sl','$emp_tin','$emp_sss_no','$emp_hdmf_no','$emp_phealth_no',
							'$w_ph','$w_sss','$w_hdmf','$emp_shift','$emp_area','$_SESSION[userid]',now());
						";
				echo $query;
				$mydb->dbquery($query);
				echo 1;
				
			} else { echo 2; }
		} else { echo 3; }
	} else {
		//Check if ID NO. was changed
		if ($old_idno != $emp_idno) { $_xsearch =  $mydb->getArray("select count(*) from emp_masterfile where emp_id='$emp_idno';"); } else { $_xsearch[0] == 0; }
		if($_xsearch[0] == 0) {
			
			$query = "UPDATE emp_masterfile SET EMP_TYPE = '$emp_type',EMP_ID = '$emp_idno',BIO_ID = '$bio_id',LNAME = '".htmlentities($emp_lname)."',FNAME = '".htmlentities($emp_fname)."',
							MNAME = '".htmlentities($emp_mname)."',ADDRESS1 = '".$mydb->escapeString(htmlentities($emp_add1))."',ADDRESS2 = '".$mydb->escapeString(htmlentities($emp_add2))."',GENDER = '$emp_sex',
							BIRTHDATE = '$emp_bday',EMAIL_ADD = '$email_add',TEL_NO = '$tel_no',BLOOD_TYPE = '$blood_type',CIVIL_STATUS = '$emp_cstat',CONTACT_PERSON = '".htmlentities($contact_person)."',
							CONTACT_NOS = '$contact_nos',RELIGION = '$emp_religion',NATIONALITY = '$nationality',DATE_HIRED = '$date_hired',DATE_RET = '$emp_date_ret',DESG = '$emp_desg',DEPT = '$emp_dept',
							PAYROLL_TYPE = '$emp_ptype',EMPLOYMENT_STATUS = '$emp_stat',FLEX_TIME = '$emp_flex',AUTO_NOON = '$emp_noon',BASIC_RATE = '$emp_rate',COLA = '$cola',ALLOWANCE = '$emp_allw',ALLOWANCE_TYPE = '$emp_allwtype',
							NONTAX_ALLOWANCE = '$emp_allw_ntx',COOP_PREMIUM = '$emp_coop',HDMF_PREMIUM = '$emp_hdmf_ee',UMEMBER = '$empu',UNION_DUES='$emp_uniondues',
							ACCT_NO = '$emp_bank',ATM_BANK = '$emp_atmbank', EXEMPT_PAYREG = '$emp_expayreg', W_TAX = '$emp_wtax',VL_CREDIT = '$emp_vl',SL_CREDIT = '$emp_sl',TIN_NO = '$emp_tin',SSS_NO = '$emp_sss_no',HDMF_NO = '$emp_hdmf_no',
							PHEALTH_NO = '$emp_phealth_no',W_PHILHEALTH = '$w_ph',W_SSS = '$w_sss',W_HDMF = '$w_hdmf',`SHIFT` = '$emp_shift',`AREA` = '$emp_area',UPDATED_BY ='$_SESSION[userid]',UPDATED_ON = now() WHERE RECORD_ID = '$rid'
						";
			echo $query;
			$mydb->dbquery($query);
			echo 5; //Prompt User that Employee Record Successfully updated!
		} else { echo 4; } //If updated ID No. already exist, prompt user that the ID No. was already in used!
	}
?>