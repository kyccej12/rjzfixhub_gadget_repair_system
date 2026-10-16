<?php
	session_start();
	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);
	require_once '../../handlers/_generics.php';

	$pay = new _init;
	
	if($_GET[emp_type] != "") { $f1 = " and emp_type = '$_GET[emp_type]' "; }

	$now = date("m/d/Y h:i a");
	$co = $pay->getArray("select * from companies where company_id = '$_SESSION[company]';");
	$emp_list = $pay->dbquery("SELECT emp_id, lname, fname, mname, desg AS `position`, if(payroll_type=1,'SEMI-MONTHLY','DAILY') as paytype, if(payroll_type=1,round((basic_rate * 12) / 314,2),basic_rate) as daily_rate, DATE_FORMAT(date_hired,'%m/%d/%Y') AS hired_date, basic_rate, allowance AS tax_allowance, nontax_allowance, (SL_CREDIT+VL_CREDIT) as SIL FROM emp_masterfile WHERE `status` NOT IN ('RESIGNED','RETIRED') $f1 ORDER BY lname, fname, mname;");
		
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Red Global Land Properties Corp.</title>
	<link href="../../style/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="../../style/style.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="../../js/jquery.js"></script>
	<script language="javascript" src="../../js/jquery-ui.js"></script>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="10" width="215">	
	<?php echo '<table width="100%">
		<tr>
			<td style="color:#000000;" width=160><img src="../../images/'.$co['headerlogo'].'" height=70 /></td>
			<td style="color:#000000; padding-top: 15px;">
				<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'<br/>VAT REG. TIN: '.$co['tin_no'].'</span>
			</td>
			<td width="40%" align=right>
				<span style="font-weight: bold; font-size: 9pt;">Gross Compensation</br>Covering the '. $_GET['year'] . ' Calendar Year</span>
			</td>
		</tr>
	</table>';
	?>
	<table style="border-collapse: collapse;">	
		<tr>
			<td class="gridHead" align=left width=200><b>ID No.</b></td>
			<td class="gridHead" align=left width=400><b>Last Name</b></td>
			<td class="gridHead" align=left width=400><b>First Name</b></td>
			<td class="gridHead" align=left width=400><b>Midle Name</b></td>
			<td class="gridHead" align=left width=300><b>Position</b></td>
			<td class="gridHead" align=left width=300><b>Date Hired</b></td>
			<td class="gridHead" align=left width=300><b>Pay Type</b></td>
			<td class="gridHead" align=left width=300><b>Basic Rate</b></td>
			<td class="gridHead" align=left width=300><b>Taxable Allowance</b></td>
			<td class="gridHead" align=left width=300><b>Non-Taxable Allowance</b></td>
			<td class="gridHead" align=center width=200><b>JAN</b></td>
			<td class="gridHead" align=center width=200><b>FEB</b></td>
			<td class="gridHead" align=center width=200><b>MAR</b></td>
			<td class="gridHead" align=center width=200><b>APR</b></td>
			<td class="gridHead" align=center width=200><b>MAY</b></td>
			<td class="gridHead" align=center width=200><b>JUN</b></td>
			<td class="gridHead" align=center width=200><b>JUL</b></td>
			<td class="gridHead" align=center width=200><b>AUG</b></td>
			<td class="gridHead" align=center width=200><b>SEP</b></td>
			<td class="gridHead" align=center width=200><b>OCT</b></td>
			<td class="gridHead" align=center width=200><b>NOV</b></td>
			<td class="gridHead" align=center width=200><b>DEC</b></td>
			<td class="gridHead" align=center width=200><b>Unused VL & SL</b></td>
			<td class="gridHead" align=center width=200><b>Annual Gross Income</b></td>
		</tr>
		
		<?php
			$i = 0;
			while($res = $emp_list->fetch_array(MYSQLI_BOTH)) {
				$annual_pay = 0; $usedSIL = 0;
				
				list($usedSIL) = $pay->getArray("SELECT SUM(IF(`length`='WD',1,0.5)) FROM pay_loa WHERE emp_id = '$res[emp_id]' AND DATE_FORMAT(`date`,'%Y') = '$_GET[year]' AND leave_type IN (1,2);");
				if($usedSIL < $res['SIL']) { $unused = round($res['daily_rate'] * ($res['SIL'] - $usedSIL ),2); }
				
				echo '<tr bgcolor='.$pay->initBackground($i).'>
						<td class="grid2" align=left width=200>'.$res['emp_id'].'</td>
						<td class="grid2" align=left width=200>'.$res['lname'].'</td>
						<td class="grid2" align=left width=200>'.$res['fname'].'</td>
						<td class="grid2" align=left width=200>'.$res['mname'].'</td>
						<td class="grid2" align=left width=200>'.$res['position'].'</td>
						<td class="grid2" align=left width=200>'.$res['hired_date'].'</td>
						<td class="grid2" align=left width=200>'.$res['paytype'].'</td>
						<td class="grid2" width=200 align=right>'.number_format($res['basic_rate'],2).'</td>
						<td class="grid2" width=200 align=right>'.number_format($res['tax_allowance'],2).'</td>
						<td class="grid2" width=200 align=right>'.number_format($res['nontax_allowance'],2).'</td>
						
						';
						
						for($z = 1; $z <= 12; $z++) {
							$zmonth = str_pad($z,2,'0',STR_PAD_LEFT);
							list($moPay) = $pay->getArray("select sum(gross_pay) from emp_payslip where period_id in (select period_id from pay_periods where reportingMonth = '$zmonth' and reportingYear = '$_GET[year]') and emp_id = '$res[emp_id]';");
							
							echo '<td class="grid2" align=right width=200>'.number_format($moPay,2).'</td>';
							$annual_pay += $moPay;
						
						}

					echo '<td class="grid2" align=right width=200>'.number_format($unused,2).'</td>
						  <td class="grid2" align=right width=200>'.number_format(($annual_pay+$unused),2).'</td>
				</tr>';
				$i++;
				
				$annual_payGT+=$annual_pay; $unusedGT+=$unused;
			
			}
			
			echo '<tr bgcolor='.$pay->initBackground($i).'>
						<td class="grid2" align=left colspan=10><b>GRAND TOTAL &raquo;</b></td>';
						
						for($z = 1; $z <= 12; $z++) {
							$zmonth = str_pad($z,2,'0',STR_PAD_LEFT);
							list($moPayGT) = $pay->getArray("select sum(gross_pay) from emp_payslip where period_id in (select period_id from pay_periods where reportingMonth = '$zmonth' and reportingYear = '$_GET[year]') $f1;");
							
							echo '<td class="grid2" align=right width=200><b>'.number_format($moPayGT,2).'</b></td>';
						}

					echo '
					    <td class="grid2" align=right width=200><b>'.number_format($unusedGT,2).'</b></td>
						<td class="grid2" align=right width=200><b>'.number_format(($annual_payGT+$unusedGT),2).'</b></td>
				</tr>';
		?>
		
		
	</table>
</body>
</html>