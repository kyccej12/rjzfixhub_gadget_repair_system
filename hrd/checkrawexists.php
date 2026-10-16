<?php
	require_once("../includes/dbUSE.php");
    mysql_select_db("hris", $con);
	
	list($dtf,$dt2) = explode(":",$_POST['period']);
	
	switch($_POST['mod']) {
		case "checkBio":
			list($_i) = mysql_fetch_array(mysql_query("select count(*) from hris.biologs_raw where `date` between '$dtf' and '$dt2';"));
			if($_i > 0) { echo "shubet"; }
		break;
		case "checkFinal":
			list($_i) = mysql_fetch_array(msyql_query("select count(*) from hris.emp_dtrfinal where `date` between '$dtf' and '$dt2' and POST_ACCTG = 'Y';"));
			if($_i > 0) { echo "shubet"; }
		break;
	}
	
	@mysql_close($con);
?>