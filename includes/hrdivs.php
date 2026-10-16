<?php
	include("hrReports.php");
	
	$cutoffOption = '';
	$coQuery = $o->dbquery("select period_id,concat(date_format(period_start,'%m/%d/%Y'),' - ',date_format(period_end,'%m/%d/%Y')) from pay_periods order by period_end desc limit 20;");
	while(list($periodID,$periodRange) = $coQuery->fetch_array()) {
		$cutoffOption .= "<option value='$periodID'>$periodRange</option>";
	}
	
	$areaOption = '';
	$bQuery = $o->dbquery("select `area`,`region` from emp_areas order by `area`;");
	while(list($bid,$bname) = $bQuery->fetch_array()) {
		$areaOption .= "<option value='$bid'>$bname</option>";
	}
	
	$deptOption = '';
	$dQuery = $o->dbquery("select `id`,`dept_name` from options_dept order by `dept_name`;");
	while(list($did,$dname) = $dQuery->fetch_array()) {
		$deptOption .= "<option value='$did'>$dname</option>";
	}
	
?>
<div id="manageDTR" style="display: none;">
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Payroll Period :</span></td>
			<td>
				<select name="mdtr_cutoff" id="mdtr_cutoff" style="width: 90%; font-size: 11px;" class="gridInput" />
					<?php echo $cutoffOption; ?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Branch :</span></td>
			<td>
				<select id="mdtr_branch" name="mdtr_branch" class="gridInput" style="width : 90%; font-size: 11px;" onchange="javascript: pop_emp(this.value,'','#mdtr_emp');">
					<option value="">- Select Branch -</option>
					<?php
						echo $areaOption;
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td><span class="spandix-l">Employee :</span></td>
			<td>
				<select id="mdtr_emp" name="mdtr_emp" style="width: 90%; font-size: 11px;" class="gridInput" >
				
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="getDTR();" class="buttonding" style="font-size: 11px;"><img src="images/icons/dtr.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;View & Manage DTR</button>
			</td>
		</tr>
	</table>
</div>
<div id="manageOT" style="display: none;">
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Payroll Period :</span></td>
			<td>
				<select name="mot_cutoff" id="mot_cutoff" style="width: 90%; font-size: 11px;" class="gridInput" />
					<?php echo $cutoffOption; ?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Branch :</span></td>
			<td>
				<select id="mot_branch" name="mot_branch" class="gridInput" style="width : 90%; font-size: 11px;" onchange="javascript: pop_dept(this.value,'#mot_dept');">
					<option value="">- Select Branch -</option>
					<?php
						echo $areaOption;
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td><span class="spandix-l">Department :</span></td>
			<td>
				<select id="mot_dept" name="mot_dept" style="width: 90%; font-size: 11px;" class="gridInput">
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="getOT();" class="buttonding" style="font-size: 11px;"><img src="images/icons/dtr.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;View & Manage DTR</button>
			</td>
		</tr>
	</table>
</div>
<div id="otSummary" style="display: none;">
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Payroll Period :</span></td>
			<td>
				<select name="otCutoff" id="otCutoff" style="width: 90%; font-size: 11px;" class="gridInput" />
					<?php echo $cutoffOption; ?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td><span class="spandix-l">Branch :</span></td>
			<td>
				<select id="otBranch" name="otBranch" style="width: 90%; font-size: 11px;" class="gridInput">				
					<?php
						echo $areaOption;
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="processOT();" class="buttonding" style="font-size: 11px;"><img src="images/icons/pdf.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Generate Report</button>
			</td>
		</tr>
	</table>
</div>
<div id="printDTR" style="display: none;">
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Payroll Period :</span></td>
			<td>
				<select name="pdtr_cutoff" id="pdtr_cutoff" style="width: 90%; font-size: 11px;" class="gridInput" />
					<?php echo $cutoffOption; ?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td><span class="spandix-l">Department :</span></td>
			<td>
				<select id="pdtr_dept" name="pdtr_dept" style="width: 90%; font-size: 11px;" class="gridInput">
					<?php echo $deptOption; ?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="printDTR(1);" class="buttonding" style="font-size: 11px;"><img src="images/icons/print.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Print Daily Time Record</button>
			</td>
		</tr>
	</table>
</div>
<div id="printTardy" style="display: none;">
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td><span class="spandix-l">Branch :</span></td>
			<td>
				<select id="tardyBranch" name="tardyBranch" style="width: 90%; font-size: 11px;" class="gridInput">
					<option value=''>- All Branches -</option>
					<?php
						echo $areaOption;
					?>

				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<?php
			$o->_structInput('From','35%','tardyDtf','gridInput','width: 90%;',date('m/01/Y')); 
			$o->_structInput('To','35%','tardyDt2','gridInput','width: 90%;',date('m/d/Y')); 
		?>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="printTardy();" class="buttonding" style="font-size: 11px;"><img src="images/icons/print.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Print Tardiness Report</button>
			</td>
		</tr>
	</table>
</div>
<div id="processPay" style="display: none;">
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Payroll Period :</span></td>
			<td>
				<select name="payCutoff" id="payCutoff" style="width: 90%; font-size: 11px;" class="gridInput" />
					<?php echo $cutoffOption; ?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td><span class="spandix-l">Branch :</span></td>
			<td>
				<select id="payBranch" name="payBranch" style="width: 90%; font-size: 11px;" class="gridInput" onchange="javascript: pop_dept(this.value,'#payDept');">
					<option value=""> - All Branches -</option>
					<?php
						echo $areaOption;
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td><span class="spandix-l">Department :</span></td>
			<td>
				<select id="payDept" name="payDept" style="width: 90%; font-size: 11px;" class="gridInput">
					<option value=''>- All Departments -</option>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="processPay();" class="buttonding" style="font-size: 11px;"><img src="images/icons/processraw.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Process Payroll</button>
			</td>
		</tr>
	</table>
</div>
<div id="printPaySlip" style="display: none;">
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Payroll Period :</span></td>
			<td>
				<select name="payslipCutoff" id="payslipCutoff" style="width: 90%; font-size: 11px;" class="gridInput" />
					<?php echo $cutoffOption; ?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td><span class="spandix-l">Branch :</span></td>
			<td>
				<select id="payslipBranch" name="payslipBranch" style="width: 90%; font-size: 11px;" class="gridInput" onchange="javascript: pop_dept(this.value,'#payslipDept'); $('#payslipEmployee').html('<option value=\'\'>- All Employees -</option>');" />
					<option value=""> - All Branches -</option>
					<?php
						echo $areaOption;
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Department :</span></td>
			<td>
				<select name="payslipDept" id="payslipDept" style="width: 90%; font-size: 11px;" class="gridInput" onchange = "javascript: pop_emp($('#payslipBranch').val(),this.value,'#payslipEmployee');" />
					<option value=''>- All Departments -</option>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Employee :</span></td>
			<td>
				<select name="payslipEmployee" id="payslipEmployee" style="width: 90%; font-size: 11px;" class="gridInput" />
					<option value="">- All Employees -</option>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="printPaySlip();" class="buttonding" style="font-size: 11px;"><img src="images/icons/print.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Print Payslip</button>
			</td>
		</tr>
	</table>
</div>
<div id="paySummary" style="display: none;">
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Payroll Period :</span></td>
			<td>
				<select name="paysumCutoff" id="paysumCutoff" style="width: 90%; font-size: 11px;" class="gridInput" />
					<?php echo $cutoffOption; ?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td><span class="spandix-l">Branch :</span></td>
			<td>
				<select id="paysumBranch" name="paysumBranch" style="width: 90%; font-size: 11px;" class="gridInput"  onchange="javascript: pop_dept(this.value,'#paysumDept');" />
					<option value=""> - All Branch -</option>
					<?php
						echo $areaOption;
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Department :</span></td>
			<td>
				<select name="paysumDept" id="paysumDept" style="width: 90%; font-size: 11px;" class="gridInput" />
					<option value=''>- All Departments -</option>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="processPaySummary();" class="buttonding" style="font-size: 11px;"><img src="images/icons/customer-report-icon.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Print Payroll Resgister</button>
				<!--button onClick="processPaySummaryExcel();" class="buttonding" style="font-size: 11px;"><img src="images/icons/excel.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Export to Excel</button-->
			</td>
		</tr>
	</table>
</div>
<div id="loanBalances" style="display: none;">
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td><span class="spandix-l">Employee :</span></td>
			<td>
				<select id="lb_emp" name="lb_emp" style="width: 90%; font-size: 11px;" class="gridInput" >
					<?php
						$_q = $o->dbquery("SELECT emp_id, CONCAT(lname,', ',fname,' ',LEFT(mname,1),'.') FROM emp_masterfile WHERE FILE_STATUS != 'DELETED' order by lname, fname;");
						while(list($a,$b) =$_q->fetch_array()) {
							echo "<option value='$a'>". strtoupper($b) . " [$a]</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="getEmpLoans();" class="buttonding" style="font-size: 11px;"><img src="images/icons/pdf.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Generate Report</button>
			</td>
		</tr>
	</table>
</div>
<div id="printStatutory" style="display: none;">
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Branch :</span></td>
			<td align=left>
				<select name="statBranch" id="statBranch" style="width: 90%; font-size: 11px;" class="gridInput">
					<option value=""> - All Branches -</option>
					<?php
						echo $areaOption;
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<?php
			$o->_structMonths('statMonth','width: 90%; font-size: 11px;','gridInput');
			$o->_structInput('Year','35%','statYear','gridInput','width: 90%;',date('Y')); 
		?>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="printStatutory();" class="buttonding" style="font-size: 11px;"><img src="images/icons/print.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Generate Report</button>
			</td>
		</tr>
	</table>
</div>
<div id="printGrossCompensation" style="display: none;">
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Branch :</span></td>
			<td align=left>
				<select name="grossProj" id="grossProj" style="width: 90%; font-size: 11px;" class="gridInput">
					<option value=""> - All Branches -</option>
					<?php
						echo $areaOption;
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<?php
			$o->_structInput('Year','35%','grossYear','gridInput','width: 100px;',date('Y')); 
		?>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="printGrossCompensation();" class="buttonding" style="font-size: 11px;"><img src="images/icons/print.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Generate Report</button>
			</td>
		</tr>
	</table>
</div>
<div id="userChangePass" style="display: none;">
	<form name="frmPass" id="frmPass">
		<input type="hidden" name="myUID" id="myUID" value="<?php echo $_SESSION['userid']; ?>">
		<table border="0" cellpadding="0" cellspacing="0" width=100%>
			<tr><td height=4></td></tr>
			<tr>
				<td width=35%><span class="spandix-l">New Password :</span></td>
				<td>
					<input type="password" id="pass1" class="nInput" style="width: 80%;"  />
				</td>
			</tr>
			<tr><td height=4></td></tr>
			<tr>
				<td width=35%><span class="spandix-l">Confirm New Password :</span></td>
				<td>
					<input type="password" id="pass2" class="nInput" style="width: 80%;" />
				</td>
			</tr>
			</table>
	</form>
</div>
<div id="e_list" style="display: none;"></div>
<div id="e_details" style="display: none;"></div>
<div id="payperiods" style="display: none;"></div>
<div id="holidays" style="display: none;"></div>
<div id="leaves" style="display: none;"></div>
<div id="deductions" style="display: none;"></div>
<div id="loans" style="display: none;"></div>
<div id="adjustments" style="display: none;"></div>
<div id="importdtr" style="display: none;"></div>
<div id="empdtr" style="display: none;"></div>