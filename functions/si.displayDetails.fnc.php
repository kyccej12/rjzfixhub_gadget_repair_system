<?php

	function showDetails($trace_no,$status,$lock,$urights) {
		
		global $con;
		
		$i = 0;
		
		$details = $con->dbquery("SELECT a.line_id,a.item_code,a.description,a.qty,a.unit,a.unit_price,ROUND(a.qty*a.unit_price,2) as amount,ROUND(a.qty*a.discount,2) as discouunt,a.discount_percent,round(a.qty*a.discount,2) as tdisc FROM invoice_details a WHERE trace_no = '$trace_no' order by a.line_id desc;");
		echo '<table width=100% cellspacing=0 cellpadding=0 onMouseOut="javascript:highlightTableRowVersionA(0);">';
		while(list($line_id, $item_code,$description,$qty,$unit,$price,$amount,$disc,$percent,$tdisc) = $details->fetch_array()) {
				
			echo '<tr bgcolor="'. $con->initBackground($i) . '" onmouseover="highlightTableRowVersionA(this, \'#3399ff\');" id="obj_'.$lined_id.'" onclick="selectSLid(this,\''.$line_id.'\');">
					<td align=left class="grid" width="10%" valign=top style="padding-left: 5px;">'. $item_code . '</td>
					<td align=left class="grid"  width="50%" valign=top>'.$description.'</td>
					<td align=center class="grid" width="10%" valign=top>'. $con->identUnit($unit) . '</td>
					<td align=center class="grid" width="10%" valign=top>' . number_format($qty,2) .'</td>
					<td align=center class="grid" width="10%" valign=top>'. number_format($price,2) . '</td>
				    <td align=right class="grid" style="padding-right: 20px;" valign=top>'.number_format($amount,2).'</td>
					
				</tr>';	
			   	$i++;		
			
			   	if($disc > 0) {

					if($percent > 0) {
						$plabel = number_format($percent) . "%";
					} else {
						$plabel = "Peso Value";
					}

					echo '<tr bgcolor="'. $con->initBackground($i) . '">
							<td align=right class="grid" width="90%" valign=top colspan=5><b>Less: Item Discount ('.$plabel.')</b></td>
							<td align=right class="grid" style="padding-right: 20px;" valign=top><b>('.number_format($disc,2).')</b></td>
					</tr>';	
					$i++;	

			   	}
			
		}
		
		
		if($i < 8) { for($i; $i <= 7; $i++) {
			echo '<tr bgcolor='.$con->initBackground($i).'>
						<td align=left class="grid" width="100%" colspan=6>&nbsp;</td>
				</tr>';
			}
		}
		echo "</table>";
	}

?>