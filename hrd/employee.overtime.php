<?php
	require_once '../handlers/_generics.php';
	$db = new _init;
	list($dtf,$dt2,$looper) = $db->getArray("SELECT period_start, period_end, DATEDIFF(period_end,period_start) AS looper FROM pay_periods WHERE period_id = '$_REQUEST[period]';");
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Red Global Land Properties Corp.</title>
	<link href="../style/style.css" rel="stylesheet" type="text/css" />
	<link href="../ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="../ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="../ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="../js/tableH.js"></script>
	<script>
		function saveDTR(rid,date,day,sched,id_no,type,val) {
			if(val == "") {
				parent.sendErrorMessage("Invalid Value specified!")
			} else {
				$.post("misc-data.php", { mod: "saveEDTR", rid: rid, date: date, period: <?php echo $_REQUEST['period']; ?>, day: day, type: type, sched: sched, eid: id_no, val: val, dept: <?php echo $_GET['dept']; ?>, etype: <?php echo $_GET['pay_type']; ?>, sid: Math.random()});
			}
		}

		function printDTR(eid,period) {
			window.open("reports/dtr.php?period="+period+"&eid="+eid+"&sid="+Math.random()+"","Daily Time Record","location=1,status=1,scrollbars=1,width=640,height=720");
		}

		function otApprove(rid,val) {
			if(val == "Y") {
				if(confirm("Are you sure you want to approve employee's overtime?") == true) {
					$.post("misc-data.php", { mod: "otApprove", rid: rid, sid: Math.random() });
				}
			} else {
				$.post("misc-data.php", { mod: "otDisApprove", rid: rid, sid: Math.random() });	
			}
		}
	</script>
</head>
<body bgcolor="#7f7f7f" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
 <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
	<tr>
		<td  style="padding:0px;" valign=top>
			<table border="0" cellpadding="0" cellspacing="0" width=100%>
			
				<tr bgcolor="#cdcdcd">
					<td align=left class="spandix-l" style="border-bottom: 1px solid black; padding-left: 20px;"><b>EMPLOYEE</b></td>
					<td align=left class="spandix-l" style="border-bottom: 1px solid black; padding-left: 20px;"><b>DATE</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>IN (AM)</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>OUT (AM)</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>IN (PM)</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>OUT (PM)</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>LATE</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>REG. HRS</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>O.T</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>R.D OT</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>PREM. OT</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>OT APPROVED?</b></td>
				</tr>
				
				<?php
					$i = 0;
					$a = $db->dbquery("SELECT a.record_id, a.EMP_ID as eid, CONCAT(b.LNAME,', ',b.FNAME) AS ename, b.SHIFT as shift, `DATE` as xdate, DATE_FORMAT(`DATE`,'%m/%d/%y %a') AS d8, DATE_FORMAT(`DATE`,'%a') as xday, IF(IN_AM!='00:00:00',TIME_FORMAT(IN_AM,'%H:%i'),'') AS `am_in`, IF(OUT_AM!='00:00:00',TIME_FORMAT(OUT_AM,'%H:%i'),'') AS `am_out`, IF(IN_PM!='00:00:00',TIME_FORMAT(IN_PM,'%H:%i'),'') AS `pm_in`, IF(OUT_PM!='00:00:00',TIME_FORMAT(OUT_PM,'%H:%i'),'') AS `pm_out`, IF(TOT_WORK > 0,TOT_WORK,'') AS hrs, IF(TOT_LATE > 0,TOT_LATE,'') AS late, IF(REG_OT > 0,REG_OT,'') AS ot, IF(PREM_OT > 0,PREM_OT,'') AS pot, IF(SUN_OT > 0,SUN_OT,'') AS sot, (reg_ot+sun_ot+prem_ot) AS tot, ot_approve, hd_type FROM emp_dtrfinal a LEFT JOIN emp_masterfile b ON a.EMP_ID = b.EMP_ID WHERE b.PAYROLL_TYPE = '$_GET[pay_type]' AND `DATE` BETWEEN '$dtf' AND '$dt2' AND (REG_OT+SUN_OT) >= 0.30 ORDER BY B.LNAME, A.DATE;");
					while($b = $a->fetch_array(MYSQLI_BOTH)) {
						$bgC = $db->initBackground($i);
						echo "<tr bgcolor=\"$bgC\" >
								<td class='dgridbox'>".$b['ename']."</td>
								<td class='dgridbox'>".$b['d8']."</td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' value='$b[am_in]' onchange=\"javascript: saveDTR('$b[record_id]','$b[xdate]','$b[xday]','$b[shift]','$b[eid]','IN_AM',this.value);\" onfocus=\"javascript: if(this.value == '') { this.value = '08:50'; saveDTR('$b[record_id]','$b[xdate]','$b[xday]','$b[shift]','$b[eid]','IN_AM',this.value); } \" ></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' value='$b[am_out]' onchange=\"javascript: saveDTR('$b[record_id]','$b[xdate]','$b[xday]','$b[shift]','$b[eid]','OUT_AM',this.value);\" onfocus=\"javascript: if(this.value == '') { this.value = '12:50'; saveDTR('$b[record_id]','$b[xdate]','$b[xday]','$b[shift]','$b[eid]','OUT_AM',this.value); } \"></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' value='$b[pm_in]' onchange=\"javascript: saveDTR('$b[record_id]','$b[xdate]','$b[xday]','$b[shift]','$b[eid]','IN_PM',this.value);\" onfocus=\"javascript: if(this.value == '') { this.value = '13:50'; saveDTR('$b[record_id]','$b[xdate]','$b[xday]','$b[shift]','$b[eid]','IN_PM',this.value); } \"></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' value='$b[pm_out]' onchange=\"javascript: saveDTR('$b[record_id]','$b[xdate]','$b[xday]','$b[shift]','$b[eid]','OUT_PM',this.value);\" onfocus=\"javascript: if(this.value == '') { this.value = '17:50'; saveDTR('$b[record_id]','$b[xdate]','$b[xday]','$b[shift]','$b[eid]','OUT_PM',this.value); } \"></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' id=late[".$b['record_id']."] value='$b[late]' readonly></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' id=hrs[".$b['record_id'] . "] value='$b[hrs]' readonly></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' id=ot[".$b['record_id'] . "] value='$b[ot]' readonly></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' id=sot[".$b['record_id'] . "] value='$b[sot]' readonly></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' id=pot[".$b['record_id'] . "] value='$b[pot]' readonly></td>
								<td class='dgridbox' align=center>";
								if($b['tot'] > 0 || $b['hd_type'] != 'NA') {
									if($b['ot_approve'] == "Y") { $selected = "selected"; } else { $selected = ""; }
									echo "<select id=ota[" . $b['record_id'] . "] onchange=\"otApprove('$b[record_id]',this.value);\"><option value='N'>N</option><option value='Y' $selected>Y</option></select>";
								}
						echo "</td>
						</tr>";
						$i++;
					}
				
				
				?>
				
			</table>
		</td>
	</tr>
 </table>
</body>
</html>