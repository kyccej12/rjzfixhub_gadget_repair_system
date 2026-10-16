<?php
	
	//ini_set("display_errors","On");
	session_start();
	require_once "../handlers/initDB.php";
	$mydb = new myDB;
	$emp_idno = $_GET['emp_idno'];
	
	if($emp_idno) {
		$myQuery = $mydb->dbquery("select *, date_format(BIRTHDATE,'%m/%d/%Y') as BDAY, date_format(DATE_HIRED,'%m/%d/%Y') as DHIRED, if(DATE_RET!='',date_format(DATE_RET,'%m/%d/%Y'),'') as DATE_RETIRED, RELIGION from emp_masterfile where emp_id='$emp_idno';");
		$_xres = $myQuery->fetch_array();
		$rsNext = $mydb->getArray("select emp_id from emp_masterfile where record_id > $_xres[record_id] AND FILE_STATUS != 'DELETED' and emp_type = '$_SESSION[payclass]' limit 1;");
		$rsPrev = $mydb->getArray("select emp_id from emp_masterfile where record_id < $_xres[record_id] AND FILE_STATUS != 'DELETED' and emp_type = '$_SESSION[payclass]' order by record_id desc limit 1;");
	}
	
	
	if(!$rsNext[0]) {
		$rsNext = $mydb->getArray("select emp_id from emp_masterfile where FILE_STATUS != 'DELETED' and emp_type = '$_SESSION[payclass]' order by record_id asc limit 1;");
	}
	if(!$rsPrev[0]) {
		$rsPrev = $mydb->getArray("select emp_id from emp_masterfile where FILE_STATUS != 'DELETED' and emp_type = '$_SESSION[payclass]' order by record_id desc limit 1;");
	}
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>SUPERIOR GAS & EQUIPMENT CO. OF CEBU INC. PAYROLL SYSTEM VER. 1.0b</title>
	<link href="../ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="../style/style.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="../ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="../ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="../js/hrd-new.js"></script>
	<script language="javascript" src="../js/jquery.dialogextend.js"></script>
	<style>INPUT[type="text"] { padding: 5px; }</style>
	<script language="javascript">
		function moveNext() {
			document.rsNext.submit();
		}
		function movePrev() {
			document.rsPrev.submit();
		}
		
		$(document).ready(function($){
			$("#date_hired").datepicker(); $("#emp_bday").datepicker(); $("#date_ret").datepicker();
		});
	</script>
</head>

<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
<table width="100%" border="0" cellspacing="0" cellpadding="0" >
 <form name="empdetails" id="empdetails">
	<input type="hidden" name="old_idno" id="old_idno" value="<?php echo $emp_idno ?>">
	<input type="hidden" name="record_id" id="record_id" value="<?php echo $_xres['record_id']; ?>">
	<tr>
		<td colspan=2 style="padding:0px;">
			<table width=99% align=center cellpadding=4 cellspacing=0>
				<tr>
					<td align=left>
						<?php if($_xres['record_id'] > 0) { ?>
							<a href="#" class="topClickers" onclick="javacript: movePrev()"><img src="../images/icons/previous.png" width=12 height=12 align=absmiddle />&nbsp;Previous Record</a>&nbsp;
						<?php } ?>
						<a href="#" class="topClickers" onclick="javascript: save_record()"><img src="../images/icons/floppy.png" width=12 height=12 align=absmiddle />&nbsp;Save Record</a>&nbsp;
						<?php if($_xres['record_id'] > 0) { ?>
							<a href="#" class="topClickers" onclick="javascript: delete_record(<?php echo $_xres['record_id']; ?>)"><img src="../images/icons/bin.png" width=12 height=12 align=absmiddle />&nbsp;Archive & Hide File</a>&nbsp;
							<a href="#" class="topClickers" onclick="javascript: moveNext()"><img src="../images/icons/forward.png" width=12 height=12 align=absmiddle />&nbsp;Next Record</a>
						<?php } ?>
					</td>
				</tr>
			</table>
			<table width=99% align=center cellpadding=0 cellspacing=0 style="border: thin solid #ccc;">
				<tr bgcolor="#f5f5f5">
					<td width=60% valign=top style="padding-left: 10px;">
						<table border="0" cellpadding="0" cellspacing="0" width=100%>
							<tr>
								<td class=baregray colspan=3><br/>Please fill up required fields indicated by asterisk (*) sign..</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin width="10%">Type :&nbsp;&nbsp;&nbsp;</td>
								<td class=bare width=30%>
									<select name="emp_type" id="emp_type" style="width:75%">
										<?php
											$etypeQuery = $mydb->dbquery("select eTypeID,eType from option_emptype;");
											while(list($eTypeID,$eType) = $etypeQuery->fetch_array(MYSQLI_BOTH)) {
												if($eTypeID == $_xres['EMP_TYPE']) { $selected = "selected"; } else { $selected = ""; }
												echo "<option value='$eTypeID' $selected>$eType</option>";
											}
										?>
									</select>
								</td>	
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin width="10%">ID No. :&nbsp;&nbsp;&nbsp;</td>
								<td class=bare width=30%><input type="text" id="emp_idno" name="emp_idno" style="width:75%" value="<?php echo $_xres['EMP_ID']; ?>">&nbsp;<font color="red"><b>*</b></font>
								</td>	
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin width="10%">Name :&nbsp;&nbsp;&nbsp;</td>
								<td class=bare width=30%><input type="text" id="emp_lname" name="emp_lname" style="width:95%" value="<?php echo $_xres['LNAME'] ?>">&nbsp;<font color="red"><b>*</b></font>
								</td>
								<td class=bare style="width : 30px"></td>
								<td class=bare><input type="text" id="emp_fname" name="emp_fname" style="width:90%" value="<?php echo $_xres['FNAME'] ?>">&nbsp;<font color="red"><b>*</b></font></td>
								<td class=bare style="width : 30px"></td>
								<td class=bare><input type="text" id="emp_mname" name="emp_mname" style="width:90%" value="<?php echo $_xres['MNAME'] ?>"></td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareGray style="width : 30px"></td>
								<td class=bareGray align="center">(Last Name)</td>
								<td class=bareGray style="width : 30px"></td>
								<td class=bareGray align="center">(First Name)</td>
								<td class=bareGray style="width : 30px"></td>
								<td class=bareGray align="center">(Full Middle Name)</td>
							</tr>		
							<tr><td height=2></td></tr>
							<tr>
								<td colspan="6">
									<table width="100%" border="0">
										<tr>
											<td class=bareThin valign="top" style="width : 120px">Present Address :</td>
											<td class=bare><textarea id="emp_add1" name="emp_add1" rows="1" style="width:98%"><?php echo $_xres['ADDRESS1'] ?></textarea>&nbsp;<font color="red"><b>*</b></font></td>
										</tr>
										<tr><td height=2></td></tr>
										<tr>
											<td class=bareThin valign="top" style="width : 50px">Home Address :</td>
											<td class=bare><textarea id="emp_add2" name="emp_add2" rows="1" style="width:98%"><?php echo $_xres['ADDRESS2'] ?></textarea>&nbsp;<font color="red"><b>*</b></font></td>
										</tr>
										<tr><td height=2></td></tr>
										<tr>
											<td class=bareThin style="width : 50px">Contact Person :</td>
											<td class=bare><input type="text" id="contact_person" name="contact_person" style="width:280px;" value="<?php echo $_xres['CONTACT_PERSON'] ?>"><span class="bareGray" style="font-style: italic;"> (In Case of Emergency)</span></td>
										</tr>
										<tr><td height=2></td></tr>
										<tr>
											<td class=bareThin style="width : 50px">Contact Nos. :</td>
											<td class=bare><input type="text" id="contact_nos" name="contact_nos" style="width:280px;" value="<?php echo $_xres['CONTACT_NOS'] ?>">
										</tr>
									</table>
								</td>
							</tr>
						</table>
					</td>
					<td  style="padding: 5px;" valign=top>
						<table border="0" cellpadding="0" cellspacing="0" width=100%>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin align="left" width=30%>Gender :&nbsp;&nbsp</td>
												<td class=bare width=80%>
									<select id="emp_sex" name="emp_sex" style="width : 140px">
										<option value=""> - Select -</option>
										<option value="M" <?php if ($_xres['GENDER'] == "M") { echo "selected"; }?>>Male</option>
										<option value="F" <?php if ($_xres['GENDER'] == "F") { echo "selected"; }?>>Female</option> 
									</select>
									&nbsp;<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin align="left">Birth Date :&nbsp;&nbsp;</td>
								<td class=bare><input type="text" id="emp_bday" name="emp_bday" style="width : 140px" align="right" value="<?php echo $_xres['BDAY'] ?>">&nbsp;&nbsp;<font color="red"><b>*</b></font></td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin align="left">Blood Type :&nbsp;&nbsp;</td>
								<td class=bare align="left">
									<select id="emp_bloodtype" name="emp_bloodtype" style="width : 140px">
									<option value=''>- Not Specified -</option>
									<?php
										$btQuery = $mydb->dbquery("select bloodType from option_bloodtypes;");
										while(list($bloodtype) = $btQuery->fetch_array()) {
											if($_xres['BLOOD_TYPE'] == $bloodtype) { $selected = "selected"; } else { $selected = ""; }
											echo "<option value = '".rawurlencode($bloodtype)."' $selected>$bloodtype</option>";
										}
										unset($btQuery);
									?>	
									</select>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin align="left">Civil Status :&nbsp;&nbsp;</td>
								<td class=bare align="left">
									<select id="emp_cstat" name="emp_cstat" style="width : 140px">
										<option value="1" <?php if ($_xres['CIVIL_STATUS'] == "1") { echo "selected"; }?>>Single</option>
										<option value="2" <?php if ($_xres['CIVIL_STATUS'] == "2") { echo "selected"; }?>>Married</option>
										<option value="3" <?php if ($_xres['CIVIL_STATUS'] == "3") { echo "selected"; }?>>Separated</option>
										<option value="4" <?php if ($_xres['CIVIL_STATUS'] == "4") { echo "selected"; }?>>Widow/Widower</option>
									</select>
									&nbsp;<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin align="left">Religion :&nbsp;&nbsp;</td>
								<td class=bare><input type="text" id="emp_religion" name="emp_religion" style="width : 140px" align="right" value="<?php echo $_xres['RELIGION'] ?>">&nbsp;&nbsp;<font color="red"></font></td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin align="left">Nationality :&nbsp;&nbsp;</td>
								<td class=bare>
									<select id="emp_nationality" name="emp_nationality" style="width : 140px">
										<?php
											$natQuery = $mydb->dbquery("select line_id, nation_desc from nationality order by nation_desc;");
											while(list($nid,$ndesc) = $natQuery->fetch_array()) {
												if($_xres['NATIONALITY'] == $nid) { $natSelect = "selected"; } else { $natSelect = ""; }
												echo "<option value = '$nid' $natSelect>$ndesc</option>";
											}
										?>
									</select>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin align="left">Email Address :&nbsp;&nbsp;</td>
								<td class=bare><input type="text" id="email_add" name="email_add" style="width : 140px;" align="right" value="<?php echo $_xres['EMAIL_ADD'] ?>"></td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin align="left">Home Phone/Mobile # :&nbsp;&nbsp;</td>
								<td class=bare><input type="text" id="tel_no" name="tel_no" style="width : 140px;" align="right" value="<?php echo $_xres['TEL_NO'] ?>"></td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class=bareThin align="left">Biometric ID :&nbsp;&nbsp;</td>
								<td class=bare><input type="text" id="bio_id" name="bio_id" style="width : 140px;" align="right" value="<?php echo $_xres['BIO_ID'] ?>">&nbsp;&nbsp;<font color="red">*</font></td>
							</tr>
						</table>
					</td>
				</tr>	
			</table>
			<table cellpadding=0 cellspacing=1 width=99% border=0 align=center>
				<tr style="background-color :#cccccc">
					<td align=center class=bareThin align="center" style="padding-top: 2px; padding-bottom: 2px;">JOB RELATED INFORMATION</td>
				</tr>
			</table>
			<table width=99% align=center cellpadding=0 cellspacing=0 style="border: thin solid #ccc;">
				<tr bgcolor="#f5f5f5">
					<td width=50% valign=top style="padding: 10px">
						<table border="0" cellpadding="0" cellspacing="0" width=100%>
							<tr>
								<td class="bareThin" width="25%">Date Hired :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<input type="text" id="date_hired" name="date_hired" style="width : 140px" value="<?php echo $_xres['DHIRED'] ?>">&nbsp;<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="25%">Position/Designation :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<input type="text" id="emp_desg" name="emp_desg" style="width : 300px" value="<?php echo $_xres['DESG'] ?>">&nbsp;<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="25%">Department :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<select id="emp_dept" name="emp_dept" style="width : 300px">
										<?php
											$dept = $mydb->dbquery("select id,dept_name from options_dept order by id;");
											while($_xrow = $dept->fetch_array(MYSQLI_BOTH))
											{
												print("<option value=\"" . $_xrow[0] . "\" ");
												if($_xres['DEPT'] == $_xrow[0]) { echo "selected"; }
												print(">" . $_xrow[1] . "</option>");
											}
											unset($dept);
										?>
									</select>
									<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="25%">Payroll Type :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<select id="emp_ptype" name="emp_ptype" style="width : 160px">
										<?php
											$ptype = $mydb->dbquery("select id,payroll_type from payroll_type order by payroll_type;");
											while($_xrow1 = $ptype->fetch_array(MYSQLI_BOTH))
											{
												print("<option value=\"" . $_xrow1[0] . "\" ");
												if ($_xres['PAYROLL_TYPE'] == $_xrow1[0]) { echo "selected"; }
												print(">" . $_xrow1[1] . "</option>");
											}
											unset($ptype);
										?>
									</select>
									<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="25%">Employment Status :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<select id="emp_stat" name="emp_stat" style="width : 160px">
										<?php
											$estatus = $mydb->dbquery("select id,emp_status from emp_status order by id;");
											while($my_row = $estatus->fetch_array(MYSQLI_BOTH))
											{
												print("<option value=\"" . $my_row[0] . "\" ");
												if($my_row[0] == $_xres['EMPLOYMENT_STATUS']) { echo "selected"; }
												print(">" . $my_row[1] . "</option>");
											}
											unset($estatus);
										?>
									</select>
									<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="25%">Date Resigned/Terminated :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<input type="text" id="date_ret" name="date_ret" style="width : 140px" value="<?php echo $_xres['DATE_RETIRED']; ?>">
								</td>
							</tr>
							<tr>
								<td></td><td class=baregray>(Indicate Date if Status is either Retired,Resigned,Terminated, End of Contract)</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="25%">Flex Time? :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<select id="emp_flex" name="emp_flex" style="width : 100px">
										<option value="N" <?php if($_xres['FLEX_TIME'] == "N") { echo "selected"; } ?>>No</option>
										<option value="Y" <?php if($_xres['FLEX_TIME'] == "Y") { echo "selected"; } ?>>Yes</option>
									</select>
									<input type="hidden" value="N" id="emp_noon_swipe" name="emp_noon_swipe">
									<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="25%">Default Payroll Batch :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<select id="emp_batch" name="emp_batch" style="width : 100px">
										<?php
											$_sq = $mydb->dbquery("select batch_id,batch_details from pay_batch order by batch_id");
											while($sqrow = $_sq->fetch_array(MYSQLI_BOTH)) {
												echo "<option value='$sqrow[0]' title='$sqrow[1]' ";
												if($_xres['BATCH'] == $sqrow[0]) { echo "selected"; }
												echo ">Batch $sqrow[0]</option>";
											}
										?>
									</select>
									<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="25%">Default Shift :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<select id="emp_shift" name="emp_shift" style="width : 100px">
										<?php
											$_sq = $mydb->dbquery("select distinct shift_id, remarks from emp_shifts order by shift_id");
											while($sqrow = $_sq->fetch_array(MYSQLI_BOTH)) {
												echo "<option value='$sqrow[0]' title='$sqrow[1]' ";
												if($_xres['SHIFT'] == $sqrow[0]) { echo "selected"; }
												echo ">SHIFT $sqrow[0]</option>";
											}
										?>
									</select>
									<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="25%">Sales Office/Branch :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<select id="emp_area" name="emp_area" style="width : 120px">
										<?php
											$_sa1 = $mydb->dbquery("select `area`, `region` from emp_areas");
											while($sa1row = $_sa1->fetch_array(MYSQLI_BOTH)) {
												echo "<option value='$sa1row[0]' ";
												if($_xres['AREA'] == $sa1row[0]) { echo "selected"; }
												echo ">$sa1row[1]</option>";
											}
										?>
									</select>
									<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="25%" align="left">TIN No :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<input type="text" id="emp_tin" name="emp_tin" style="width : 120px" value="<?php echo $_xres['TIN_NO'] ?>">
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" align="left" width="25%">SSS No.:</td>
								<td class="bare" align="left">
									<input type="text" id="emp_sss_no" name="emp_sss_no" style="width : 120px" value="<?php echo $_xres['SSS_NO'] ?>">
									<select name="emp_sss" id="emp_sss" style="width: 40px;">
										<option value="Y" <?php if($_xres['W_SSS'] == 'Y') { echo "selected"; }?>>Y</option>
										<option value="N" <?php if($_xres['W_SSS'] == 'N') { echo "selected"; }?>>N</option>
									</select>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" align="left" width="25%">Philhealth ID No.:</td>
								<td class="bare" align="left">
									<input type="text" id="emp_phealth_no" name="emp_phealth_no" style="width : 120px" value="<?php echo $_xres['PHEALTH_NO'] ?>">
									<select name="emp_ph" id="emp_ph" style="width: 40px;">
										<option value="Y" <?php if($_xres['W_PHILHEALTH'] == 'Y') { echo "selected"; }?>>Y</option>
										<option value="N" <?php if($_xres['W_PHILHEALTH'] == 'N') { echo "selected"; }?>>N</option>
									</select>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" align="left">PAG-IBIG/HDMF No.:</td>
								<td class="bare" align="left">
									<input type="text" id="emp_hdmf_no" name="emp_hdmf_no" style="width : 120px" value="<?php echo $_xres['HDMF_NO'] ?>">
									<select name="emp_hdmf" id="emp_hdmf" style="width: 40px;">
										<option value="Y" <?php if($_xres['W_HDMF'] == 'Y') { echo "selected"; }?>>Y</option>
										<option value="N" <?php if($_xres['W_HDMF'] == 'N') { echo "selected"; }?>>N</option>
									</select>
								</td>
							</tr>
						</table>
					</td>
					<td  style="padding: 10px;" valign="top">	
						<table border="0" cellpadding="0" cellspacing="0" width="100%">
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="35%" align="left">S.I.L Credits (Days) :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<input type="text" id="emp_vl" name="emp_vl" style="width :100px" value="<?php echo $_xres['VL_CREDIT'] ?>">
									<input type="hidden" id="emp_sl" name="emp_sl">
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="35%" align="left">Basic Rate :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<input type="text" id="emp_rate" name="emp_rate" style="width : 100px" value="<?php echo number_format($_xres['BASIC_RATE'],2) ?>">&nbsp;<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="35%" align="left">COLA :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<input type="text" id="emp_cola" name="emp_cola" style="width : 100px" value="<?php echo number_format($_xres['COLA'],2) ?>">&nbsp;<font color="red"><b>*</b></font>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="35%" align="left">Allowance (Taxable) :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<input type="text" id="emp_allw" name="emp_allw" style="width : 100px" value="<?php echo number_format($_xres['ALLOWANCE'],2) ?>">
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="35%" align="left">Allowance Type:&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<select name="emp_allwtype" id="emp_allwtype" style="width: 100px;">
										<option value="">- NA -</option>
										<option value="M" <?php if($_xres['ALLOWANCE_TYPE'] == 'M') { echo "selected"; }?>>Monthly</option>
										<option value="D" <?php if($_xres['ALLOWANCE_TYPE'] == 'D') { echo "selected"; }?>>Daily</option>
									</select>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="35%" align="left">Allowance (Non-taxable) :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<input type="text" id="emp_allw_ntx" name="emp_allw_ntx" style="width : 100px" value="<?php echo number_format($_xres['NONTAX_ALLOWANCE'],2) ?>">
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="35%" align="left">HDMF Contribution :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<input type="text" id="emp_hdmf_ee" name="emp_hdmf_ee" style="width : 100px" value="<?php echo number_format($_xres['HDMF_PREMIUM'],2) ?>">
									<input type="hidden" id="emp_coop" name="emp_coop">
									<input type="hidden" id="emp_union" name="emp_union">
									<input type="hidden" id="emp_uniondues" name="emp_uniondues">
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="35%" align="left">Salary Thru :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<select name="emp_atmbank" id="emp_atmbank" style="width: 100px;">
										<option value='0'>- Cash -</option>
										<?php
											$atm = $mydb->dbquery("select bank_id, bank_name from options_paybank order by bank_name");
											while(list($bid,$bname) = $atm->fetch_array()){
												echo "<option value='$bid' ";
												if($_xres['ATM_BANK'] == $bid) { echo "selected"; }
												echo ">$bname</option>";
											}
										?>
									</select>
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="35%" align="left">ATM Payroll Acct. No. :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<input type="text" id="emp_bank" name="emp_bank" style="width : 100px" value="<?php echo $_xres['ACCT_NO'] ?>">
								</td>
							</tr>
							<tr><td height=2></td></tr>
							<tr>
								<td class="bareThin" width="35%" align="left">Exclude from Pay Register :&nbsp;&nbsp;</td>
								<td class="bare" align="left">
									<select name="emp_expayreg" id="emp_expayreg" style="width: 100px;">
										<option value="N" <?php if($_xres['EXEMPT_PAYREG'] == 'N') { echo "selected"; }?>>No</option>
										<option value="Y" <?php if($_xres['EXEMPT_PAYREG'] == 'Y') { echo "selected"; }?>>Yes</option>
									</select>
								</td>
							</tr>
						</table>
					</td>
				</tr>	
			</table>
		</td>
	</tr>
	</form>
	<form name="rsNext" action="employee.details.php">
		<input type="hidden" name="emp_idno" value="<?php print $rsNext[0]; ?>">
	</form>
	<form name="rsPrev" action="employee.details.php">
		<input type="hidden" name="emp_idno" value="<?php print $rsPrev[0]; ?>">
	</form>
 </table>
</body>
</html>
