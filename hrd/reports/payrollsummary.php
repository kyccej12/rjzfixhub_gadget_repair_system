<?php
	require_once '../../handlers/initDB.php';
	$con = new myDB;
	
	list($wom) = $con->getArray("select weekOfMonth from pay_periods where period_id = '$_GET[cutoff]';");
	
	echo $wom;
	
	if($wom == 1) {
		header("Location: payrollsummary-15.php?cutoff=".$_GET['cutoff']."&area=".$_GET['area']."&dept=".$_GET['dept']);
	} else {
		header("Location: payrollsummary-30.php?cutoff=".$_GET['cutoff']."&area=".$_GET['area']."&dept=".$_GET['dept']);
	}
	

?>