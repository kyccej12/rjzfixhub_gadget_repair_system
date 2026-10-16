<?php
	ini_set("max_execution_time",0);
	ini_set("memory_limit",-1);
	require_once '../../handlers/initDB.php';
	session_start();

	$pay = new myDB;
	

	/* MYSQL QUERIES SECTION */	

		$now = date("m/d/Y h:i a");
		$co = $pay->getArray("select * from companies where company_id = '$_SESSION[company]';");
		list($ee_name) = $pay->getArray("select concat('(',emp_id,') ',lname,', ',fname,' ',mname) from emp_masterfile where emp_id = '$_REQUEST[id_no]';");
	
	/* END OF SQL QUERIES */

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
<body bgcolor="#ffffff" leftmargin="10" bottommargin="50" rightmargin="10" topmargin="10" width="215">	
	<?php 
		echo '<table width="100%">
				<tr>
					<td style="color:#000000;" width=150><img src="../../images/'.$co['headerlogo'].'" height=70 /></td>
					<td style="color:#000000; padding-top: 15px;">
						<b>'.$co['company_name'].'</b><br/><span style="font-size: 6pt;">'.$co['company_address'].'<br/>Tel # '.$co['tel_no'].'<br/>'.$co['website'].'<br/>VAT REG. TIN: '.$co['tin_no'].'</span>
					</td>
					<td style="color:#000000;" align=right><span style="font-size: 9pt;"><b>Employee Loan Balance</b><br />'.$ee_name.'</br><br/>Date As Of: '. date('m/d/Y') . '</span></td>
				</tr>
			  </table>';
	?>
	<table cellspacing=0 cellpadding=0 border=0 width=100% cellspacing=3 style="font-size: 10px;">	
		<thead>
			<tr>
			<td width="10%" align=center class="gridHead"><b>RECORD ID</b></td>
			<td width="10%" align=center class="gridHead"><b>LOAN TYPE</b></td>
			<td width="10%" align=center class="gridHead"><b>LOAN DATE</b></td>
			<td width="10%" align=center class="gridHead"><b>LOAN AMOUNT</b></td>
			<td width="15%" align=center class="gridHead"><b>TERMS</b></td>
			<td width="15%" align=right class="gridHead"><b>PAYMENTS MADE</b></td>
			<td width="30%" align=left class="gridHead" style="padding-left: 10px;"><b>REFERENCE/MEMO</b></td>
			</tr>
		</thead>
		<tbody>
			<?php
				$_idetails = $pay->dbquery("SELECT record_id, LPAD(record_id,6,'0') AS rid, b.loan_type, DATE_FORMAT(date_loan,'%m/%d/%Y') AS loandate, loan_amt, CONCAT(loan_terms,' Mos.') AS terms, remarks FROM emp_loanmasterfile a LEFT JOIN option_loantype b ON a.loan_type = b.id WHERE emp_id = '$_REQUEST[id_no]' AND balance > 0 AND file_status = 'Active';");
					while($row = $_idetails->fetch_array(MYSQLI_BOTH)) {
						
						$applied = 0;
						echo '<tr>
							<td align=center>' . $row['rid'] . '</td>
							<td align=center>' . $row['loan_type'] . '</td>
							<td align=center>' . $row['loandate'] . '</td>
							<td align=center>' . number_format($row['loan_amt'],2) . '</td>
							<td align=center>' . $row['terms'] . '</td>
							<td></td><td style="padding-left: 10px;">'. $row['remarks'] . '</td>
						</tr>';
						$_xdetails = $pay->dbquery("SELECT amount AS amount_paid,CONCAT('Payroll: ',DATE_FORMAT(period_start,'%m/%d'),'-',DATE_FORMAT(period_end,'%m/%d'),', ',DATE_FORMAT(period_end,'%Y')) AS reference FROM emp_deductionmaster a LEFT JOIN pay_periods b ON a.period_id = b.period_id WHERE ref_id = '$row[record_id]' AND `type` = 'L' AND emp_id = '$_REQUEST[id_no]';");
						while($irow = $_xdetails->fetch_array(MYSQLI_BOTH)) {
							echo '<tr>
									<td colspan=5></td>
									<td align=right>(' . number_format($irow['amount_paid'],2) . ')</td>
									<td align=left style="padding-left: 10px;">' . $irow['reference'] . '</td>
								  </tr>';
							$applied+=$irow['amount_paid'];
							$appliedGT+=$irow['amount_paid'];
						}
						$balance = $row['loan_amt'] - $applied;
						echo "<tr>
									<td colspan=4></td>
									<td align=right><br><b>BALANCE &raquo;</b></td>
									<td align=right>----------------------<br/>" . number_format($balance,2) . "<br>===========</td>
									<td></td>
							   </tr>";
						$amtGT+=$row['loan_amt'];
						$balGT+=$balance;
					}
					echo "<tr><td colspan=4></td><td align=right><br><b>TOTAL DEDUCTION &raquo;</b></td><td align=right>----------------------<br/>" . number_format($appliedGT,2) . "<br>===========</td><td></td></tr>";
					echo "<tr><td colspan=4></td><td align=right><br><b>TOTAL BALANCE &raquo;</b></td><td align=right>----------------------<br/>" . number_format($balGT,2) . "<br>===========</td><td></td></tr>";
			?>
		</tbody>
	</table>
</body>
</html>