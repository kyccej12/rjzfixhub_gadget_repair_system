<?php
	session_start();
	require_once "handlers/initDB.php";

	//ini_set("display_errors","On");
	$mydb = new myDB;


?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>RJZ Fix Hub System Ver. 1.0a</title>
<link href="style/style.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script>

	function insertRights(e, val) {
		var uid = "<?php echo $_GET['uid']; ?>"
		var push;
		if(document.getElementById(e).checked == true) { push = "Y"; } else { push = "N"; }
		$.post("src/sjerp.php", { mod: "insertRights", val: val, push: push, uid: uid, sid: Math.random() });
	}
	
</script>
</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
	<table width=100% class="td_content">
		<?php
			$mtabs = $mydb->dbquery("select parentID, parentName from menu_main order by parentID;"); $i = 0;
			while(list($mid,$mname) = $mtabs->fetch_array(MYSQLI_BOTH)) {
				echo "<tr><td class=spandix width=30% align=\"right\" valign=top style=\"padding-top: 5px;\"><b>$mname :</b></td><td valign=middle>";
				$stabs = $mydb->dbquery("select submenu_id, menu_title from menu_sub where parentAsset = '$mid';");
				echo "<table width=100%>";
				$tloop = 1; 
				while(list($sid,$sname) = $stabs->fetch_array(MYSQLI_BOTH)) {
					if($tloop > 2 ) { echo "<tr>"; $tloop = 1; }
						echo "<td width=33% valign=top class=spandix><input type=checkbox id=\"cbox[$i]\" value=\"SUBMENU|$sid\" onclick=\"javascript: insertRights(this.id,this.value,$_REQUEST[uid]);\" ";
						$isExistSub = $mydb->getArray("select count(*) as found from user_rights where MENU_MODULE='SUBMENU' and MENU_ID='$sid' and UID='$_REQUEST[uid]';");
						if($isExistSub[0] > 0) { echo "checked"; }
							echo ">&nbsp;$sname</td>";
							if($tloop > 2 )  { echo "</tr>"; }
								$tloop++; $i++;
				}
				echo "</table>";
				echo "</td><td width=\"2%\"></td></tr>";
				echo "<tr><td class=\"spandix\" align=\"right\"></td><td colspan=2><hr width=80% align=left style=\"border: 1px solid #195977;\" /></td></tr>";	
			}
		?>
	</table>
</body>
</html>