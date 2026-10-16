<?php
	
	function initBackground($i) {
		if($i%2==0){ $bgC = "#ededed"; } else { $bgC = "#ffffff"; }
		return $bgC;
	}
	
	
	function showDetails($srr_no,$status,$lock) {
		$i = 1;
		$details = dbquery("select line_id, srr_no, item_code, description, qty, unit from srr_details where srr_no = '$srr_no' and branch = '$_SESSION[branchid]' order by line_id desc;");
		echo '<table width=100% cellspacing=0 cellpadding=0 onMouseOut="javascript:highlightTableRowVersionA(0);">';
		while(list($line_id, $srr_no, $item_code, $description, $qty, $unit) = mysql_fetch_array($details)) {
		   echo '<tr bgcolor="'.initBackground($i).'" onmouseover="highlightTableRowVersionA(this, \'#3399ff\');" id="obj_'.$lined_id.'" onclick="selectLine(this,\''.$line_id.'\');">
					<td align=left class="grid" width="15%" style="padding-left: 5px;">'.$item_code.'</td>
					<td align=left class="grid" width="55%">'.strtoupper($description).'</td>
					<td align=center class="grid" width="15%">'.identUnit($unit).'</td>
					<td align=center class="grid" width="15%">';
					if(($status == 'Active' || $status == '') && $lock != 'Y') {
						echo '<input type="text" id="qty['.$line_id.']" class="gridInput" style="width: 98%; text-align: center;" value="'.number_format($qty,2).'" onchange="updateQty(this.value,\''.$srr_no.'\',\''.$line_id.'\',\''.$qty.'\');"></td>';
					} else { echo number_format($qty,2); }
					echo '</td>
				 </tr>';	
			$i++;				
		}
		
		
		if($i < 8) { for($i; $i <= 7; $i++) {
			echo '<tr bgcolor='.initBackground($i).'>
						<td align=left class="grid" width="100%" colspan=4>&nbsp;</td>
				</tr>';
			}
		}
		echo '</table>';
	}

?>