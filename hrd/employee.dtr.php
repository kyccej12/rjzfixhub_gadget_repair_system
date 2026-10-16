<?php
	//ini_set("display_errors","On");
	require_once "../handlers/initDB.php";
	$con = new myDB;
	
	list($dtf,$dt2,$looper) = $con->getArray("SELECT period_start, period_end, DATEDIFF(period_end,period_start) AS looper FROM pay_periods WHERE period_id = '$_REQUEST[period]';");
	list($sched,$emptype,$dept,$area) = $con->getArray("select SHIFT,EMP_TYPE,DEPT,AREA from emp_masterfile where EMP_ID = '$_REQUEST[eid]';");

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Prime Care Cebu Payroll System Ver. 1.0b</title>
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
				$.post("misc-data.php", { mod: "saveEDTR", rid: rid, date: date, period: <?php echo $_REQUEST['period']; ?>, day: day, type: type, sched: sched, eid: id_no, val: val, dept: <?php echo $dept; ?>, etype: <?php echo $emptype; ?>, sid: Math.random()});
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
		
		function npApprove(rid,val) {
			if(val == "Y") {
				if(confirm("Are you sure you want to approve employee's Nigh Premium Overtime?") == true) {
					$.post("misc-data.php", { mod: "npApprove", rid: rid, sid: Math.random() });
				}
			} else {
				$.post("misc-data.php", { mod: "npDisApprove", rid: rid, sid: Math.random() });	
			}
		}
		
		function changeSched(shift,eid,date,day) {
			if(confirm("Are you sure you want to change employee's shift for the selected day") == true) {
				$.post("misc-data.php", { mod: "changeSched", sched: shift, eid: eid, date: date, day: day, sid: Math.random() }, function() {
					//location.reload();
				});
			}
		}
	</script>
</head>
<body bgcolor="#7f7f7f" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
 <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
	<tr>
		<td  style="padding:0px;" valign=top colspan=2>
			<table border="0" cellpadding="0" cellspacing="0" width=100%>
				<tr bgcolor="#cdcdcd">
					<td align=left class="spandix-l" style="border-bottom: 1px solid black; padding-left: 20px;"><b>DATE</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>SHIFT</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>IN (AM)</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>OUT (AM)</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>IN (PM)</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>OUT (PM)</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>LATE (MINS.)</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>REG. HRS</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>EXCESS/OT HRS</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>PREM. HRS</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>OT APPROVED?</b></td>
					<td align=center class="spandix-l" style="border-bottom: 1px solid black; padding: 4px;"><b>PREM APPROVED?</b></td>
				</tr>
				<?php
					
					for($x=0; $x <= $looper; $x++) {
						
						list($date,$xd8,$day) = $con->getArray("select date_add('$dtf', INTERVAL $x DAY),date_format(date_add('$dtf', INTERVAL $x DAY),'%m/%d/%y %a'), date_format(date_add('$dtf', INTERVAL $x DAY),'%a');");
						list($ain,$aout,$pin,$pout) = $con->getArray("SELECT LEFT(in_am,5),LEFT(out_am,5),LEFT(in_pm,5),LEFT(out_pm,5) FROM emp_shifts WHERE shift_id = '$sched' AND `day` = '$day';");
						if($ain == '') { list($ain,$aout,$pin,$pout) = $con->getArray("SELECT LEFT(in_am,5),LEFT(out_am,5),LEFT(in_pm,5),LEFT(out_pm,5) FROM emp_shifts WHERE shift_id = '$sched' AND `day` = 'Mon';"); }
						
						list($rid,$am_in,$am_out,$pm_in,$pm_out,$hrs,$late,$ot,$pot,$sot,$tot,$ota,$pota,$hd,$dtrShift) = $con->getArray("SELECT record_id, IF(IN_AM!='00:00:00',TIME_FORMAT(IN_AM,'%H:%i'),'') AS `am_in`, IF(OUT_AM!='00:00:00',TIME_FORMAT(OUT_AM,'%H:%i'),'') AS `am_out`, IF(IN_PM!='00:00:00',TIME_FORMAT(IN_PM,'%H:%i'),'') AS `pm_in`, IF(OUT_PM!='00:00:00',TIME_FORMAT(OUT_PM,'%H:%i'),'') AS `pm_out`, if(TOT_WORK > 0,TOT_WORK,'') AS hrs, if(TOT_LATE > 0,ROUND(TOT_LATE*60),'') AS late, if(REG_OT > 0,REG_OT,'') AS ot, if(PREM_OT > 0,PREM_OT,'') AS pot, if(SUN_OT > 0,SUN_OT,'') AS sot, sum(reg_ot+sun_ot+prem_ot) as tot, ot_approve, np_approve, hd_type,shift FROM emp_dtrfinal WHERE EMP_ID = '$_GET[eid]' AND `date` = '$date';");
						
						list($onleave) = $con->getArray("select count(*) from pay_loa where '$date' >= date_from and '$date' <= date_to and emp_id = '$_GET[eid]' and file_status != 'Deleted';");
						if($onleave > 0) {
							$bgC = '#faef42';
						} else {
							list($hcount,$bgC,$occasion) = $con->getArray("select count(*), bgC,occasion from (SELECT IF(`type`=1,'#00aed9','#ffd08e') AS bgC, occasion FROM pay_holiday_nat WHERE `date` = '$date' UNION SELECT '#ffd08e' AS bGC, occasion FROM pay_holiday_local WHERE `date` = '$date' and `area` = '$area') a;");
							if($hcount==0) {
								if($x%2==0){ $bgC = "#f5f5f5"; } else { $bgC = "#ffffff"; }
							}
						}
						

						echo "<tr bgcolor=\"$bgC\" title=\"$occasion\">
								<td class='dgridbox'>$xd8</td>
								<td class='dgridbox' align=center>
								";
								
								
								if($dtrShift == 0) { $myShift = $sched; } else { $myShift = $dtrShift; }
								if($rid != '') {
									echo "<select id=\"emp_shift\" name=\"emp_shift\" style=\"width : 50px\" onchange=\"javascript: changeSched(this.value,'$_GET[eid]','$date','$day');\">";
											$_sq = $con->dbquery("select distinct shift_id, remarks from emp_shifts order by shift_id");
											while($sqrow = $_sq->fetch_array()) {
												echo "<option value='$sqrow[0]' title='$sqrow[1]' ";
												if($myShift == $sqrow[0]) { echo "selected"; }
												echo ">S$sqrow[0]</option>";
											}
									echo "</select>";
								}
								
								
						  echo "</td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; text-align: center;background-color: $bgC;' value='$am_in' onchange=\"javascript: saveDTR('$rid','$date','$day','$myShift','$_REQUEST[eid]','IN_AM',this.value);\" onfocus=\"javascript: if(this.value == '') { this.value = '" . $ain . "'; saveDTR('$rid','$date','$day','$myShift','$_REQUEST[eid]','IN_AM',this.value); } \" ></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; text-align: center;background-color: $bgC;' value='$am_out' onchange=\"javascript: saveDTR('$rid','$date','$day','$myShift','$_REQUEST[eid]','OUT_AM',this.value);\" onfocus=\"javascript: if(this.value == '') { this.value = '" . $aout . "'; saveDTR('$rid','$date','$day','$myShift','$_REQUEST[eid]','OUT_AM',this.value); } \"></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; text-align: center;background-color: $bgC;' value='$pm_in' onchange=\"javascript: saveDTR('$rid','$date','$day','$myShift','$_REQUEST[eid]','IN_PM',this.value);\" onfocus=\"javascript: if(this.value == '') { this.value = '" . $pin . "'; saveDTR('$rid','$date','$day','$myShift','$_REQUEST[eid]','IN_PM',this.value); } \"></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; text-align: center;background-color: $bgC;' value='$pm_out' onchange=\"javascript: saveDTR('$rid','$date','$day','$myShift','$_REQUEST[eid]','OUT_PM',this.value);\" onfocus=\"javascript: if(this.value == '') { this.value = '" . $pout . "'; saveDTR('$rid','$date','$day','$myShift','$_REQUEST[eid]','OUT_PM',this.value); } \"></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' id=late[$rid] value='$late' readonly></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' id=hrs[$rid] value='$hrs' readonly></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' id=ot[$rid] value='$ot' readonly></td>
								<td class='dgridbox' align=center><input type='text' style='border-bottom: 1px solid #dcdcdc; border-left: none; border-right: none; border-top: none; width: 50px; background-color: $bgC; text-align: center;' id=ot[$rid] value='$pot' readonly></td>
								<td class='dgridbox' align=center>";
								
								if($rid != '' && ($tot > 0.30 || $hd != 'NA')) {
									if($ota == "Y") { $selected = "selected"; } else { $selected = ""; }
									echo "<select id=ota[$rid] onchange=\"otApprove('$rid',this.value);\"><option value='N'>N</option><option value='Y' $selected>Y</option></select>";
								} else { echo "&nbsp;"; }
						
						  echo "</td>
							    <td class='dgridbox' align=center>";
								
								if($rid != '' && ($pot > 0)) {
									if($pota == "Y") { $selected = "selected"; } else { $selected = ""; }
									echo "<select id=ota[$rid] onchange=\"npApprove('$rid',this.value);\"><option value='N'>N</option><option value='Y' $selected>Y</option></select>";
								} else { echo "&nbsp;"; }

						
						  echo "</td>
						
						
						
						</tr>";
					}
				?>
			</table>
		</td>
	</tr>
	<tr>
		<td style="padding: 5px; width: 90%">
			<button type="button" class="buttonding" style="font-size: 11px;" onclick="parent.refreshDTR('<?php echo $_GET['eid']; ?>','<?php echo $_GET['period']; ?>','<?php echo $_GET['dept']; ?>');"><img src="../images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Recalculate Changes to the DTR</b></button>
		</td>
		<td align=left>
			<fieldset style="width: 400px;">
				<legend style="font-size:11px;">COLOR INFORMATION</legend>
				<table width=100%>
					<tr>
						<td style="font-size:11px;" valign=middle width=33%><img src="../images/sh.jpg" align=absmiddle width=10 height=10/> SPECIAL HOLIDAY</td>
						<td style="font-size:11px;" valign=middle><img src="../images/lh.jpg" align=absmiddle width=10 height=10/> REGULAR HOLIDAY</td>
						<td style="font-size:11px;" valign=middle width=33%><img src="../images/ol.jpg" align=absmiddle width=10 height=10 /> ON-LEAVE</td>
					</tr>
				</table>
			</fieldset>
	</tr>
 </table>
</body>
</html>