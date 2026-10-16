<?php
	include("includes/dbUSE.php");
	session_start();
	if(isset($_REQUEST['queryString']) && $_REQUEST['queryString']!= "") {
		$q = mysql_real_escape_string($_REQUEST['queryString']);
		$r = mysql_query("select item_code,description,unit_price,unit from products_master where company = '$_SESSION[company]' and (locate('$_REQUEST[queryString]',description) > 0 or locate('$_REQUEST[queryString]',barcode) or locate('$_REQUEST[queryString]',item_code)) limit 10");

		$i = 0;
		echo "<table width=100% border=0 cellspacing=0 cellpadding=0 onMouseOut=\"javascript:highlightTableRowVersionA(0);\">";
		echo "<tr><td colspan=3 class=gridhead style='padding: 5px;'>Search Results for \"<b>$_REQUEST[queryString]</b>\" string</td></tr>";
		while(list($icode,$idesc,$iprice,$unit) = mysql_fetch_array($r)) {
			if($i%2==0){ $bgC = "#ededed"; } else { $bgC = "#ffffff"; }
				$xname = preg_replace('/(' . $q . ')/i', '<span style="font-weight:bold;">$1</span>', $idesc);
				echo "<tr onMouseOver=\"javascript:highlightTableRowVersionA(this,'#95f0e8');\" onclick=\"pickItem('$icode','".rawurlencode($idesc)."','$iprice','$unit');\">
					<td class=grid bgcolor='$bgC' style='padding-left: 10px;' width=15%>$icode</td>
					<td class=grid bgcolor='$bgC' style='padding-left: 10px;'>$xname</td>
					<td class=grid align=right bgcolor='$bgC' style='padding-right: 10px;'>".number_format($iprice,2)."</td>
			  </tr>";
			$i++;
		}
		echo "</table>";
	}
	mysql_close($con);
?>