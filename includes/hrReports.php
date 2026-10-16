<?php
	echo '<div id="managePayroll2" style="display: none;">';
			
		list($isA) = $o->getArray("select count(*) from (SELECT menu_title, jfunct, ifnull(icon_name,'reports256.png') as icon FROM menu_sub a LEFT JOIN user_rights b ON a.submenu_id=b.MENU_ID WHERE b.UID='$_SESSION[userid]' and a.subAsset = 'Y' and a.parentAsset = '124') a;");
		
		if($isA > 0) {
			echo '<div style="padding: 10px;">
					<div>Payroll Data</div>
					<div><hr width=100% height=1></hr></div>
					<div style="height: 10px;"></div>
			';
			
			$msx2 = $o->dbquery("SELECT menu_title, jfunct, ifnull(icon_name,'reports256.png') as icon FROM menu_sub a LEFT JOIN user_rights b ON a.submenu_id=b.MENU_ID WHERE b.UID='$_SESSION[userid]' and a.subAsset = 'Y' and a.parentAsset = '124' ORDER BY sort;");	
			while($menux = $msx2->fetch_array(MYSQLI_BOTH)) {
				echo "\t\t\t\t<div class=\"fileObjects\"><a href=\"#\" onClick=\"$menux[jfunct]\"><img src=\"images/icons/$menux[icon]\" width=45 height=45 align=absmiddle border=0 /><br/><br/>$menux[menu_title]</a></div>\n";
			}
				echo '</div>';
		}
		
		list($isB) = $o->getArray("select count(*) from (SELECT menu_title, jfunct, ifnull(icon_name,'reports256.png') as icon FROM menu_sub a LEFT JOIN user_rights b ON a.submenu_id=b.MENU_ID WHERE b.UID='$_SESSION[userid]' and a.subAsset = 'Y' and a.parentAsset = '999') a;");
		if($isB > 0) {
			echo '
				<div style="clear: both;"></div>
				<div style="padding: 20px;">
					<div>Payroll Reports</div>
					<div><hr width=100% height=1></hr></div>
					<div style="height: 10px;"></div>
			';
			
			$msx2 = $o->dbquery("SELECT menu_title, jfunct, ifnull(icon_name,'reports256.png') as icon FROM menu_sub a LEFT JOIN user_rights b ON a.submenu_id=b.MENU_ID WHERE b.UID='$_SESSION[userid]' and a.subAsset = 'Y' and a.parentAsset = '999' ORDER BY sort;");	
			while($menux = $msx2->fetch_array(MYSQLI_BOTH)) {
				echo "\t\t\t\t<div class=\"fileObjects\"><a href=\"#\" onClick=\"$menux[jfunct]\"><img src=\"images/icons/$menux[icon]\" width=45 height=45 align=absmiddle border=0 /><br/><br/>$menux[menu_title]</a></div>\n";
			}
				echo '</div>';
		}
		
	echo '</div>';		

?>