<?php

	
	function showDetails($trans_no) {
		
		global $con;
		$i = 0;
		
		list($cid,$status) = $con->getArray("select customer,`status` from cr_header where trans_no = '$trans_no';");
		
		if($status == 'Active') {
			$details = $con->dbquery("SELECT 'SI' AS ref_type, doc_no, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS id8, invoice_date, IF(invoice_no=0,'',invoice_no) AS ref_no, b.description AS xterms, DATE_FORMAT(DATE_ADD(invoice_date, INTERVAL b.no_days DAY),'%m/%d/%Y') AS due_date, a.balance FROM invoice_header a LEFT JOIN options_terms b ON a.terms = b.terms_id WHERE a.customer = '$cid' AND a.status = 'Finalized' AND a.balance > 0;");
		} else {
			$details = $con->dbquery("select doc_no, date_format(invoice_date,'%m/%d/%Y') as id8, invoice_no as ref_no, terms as xterms, date_format(due_date,'%m/%d/%Y') as due_date, balance_due as balance, amount_paid from cr_details where trans_no = '$trans_no';");
			
		}
		
		echo '<table width=100% cellspacing=0 cellpadding=0 onMouseOut="javascript:highlightTableRowVersionA(0);">';
		while($x = $details->fetch_array()) {
			
			
			echo '<tr bgcolor="'. $con->initBackground($i) . '" onmouseover="highlightTableRowVersionA(this, \'#3399ff\');" id="obj_'.$x['line_id'].'" onclick="selectLine(this,\''.$x['line_id'].'\');">
					<td align=center class="grid" width="10%">'.$x['doc_no'].'</td>
					<td align=center class="grid" width="15%">'.$x['id8'].'</td>
					<td align=center class="grid" width="15%">'.$x['ref_no'].'</td>
					<td align=center class="grid" width="15%" style="padding-left: 10px;">'.$x['xterms'].'</td>
					<td align=center class="grid" width="15%">'.$x['due_date'].'</td>
					<td class="grid" width="15%" align=center>'.number_format($x['balance'],2).'</td>
					<td class="grid" align=center>';
					
					if($status == 'Active') {

						list($val) = $con->getArray("select amount_paid from cr_details where trans_no = '$trans_no' and doc_no = '$x[doc_no]' and invoice_no = '$x[ref_no]' and ref_type = '$x[ref_type]';");
						echo "<input type = 'text' name = paid[$i] id = paid[$i] class = 'gridInput' style='width: 95%; text-align: right; font-weight: bold;' value = '" . number_format($val,2) . "' onfocus = \"javascript: carryMyAmount(this.id,'$x[balance]','".$val."','$x[doc_no]','$x[so_date]','$x[ref_no]','$x[ref_type]','$x[xterms]','$x[due_date]');\" onChange = \"javascript: applyMe(this.value,'$x[doc_no]','$x[so_date]','$x[ref_no]','$x[ref_type]','$x[xterms]','$x[due_date]','$x[balance]');\">";
						
						
					} else {
					
						echo number_format($x['amount_paid'],2);
					}
					
				echo '</td>
				</tr>'; $paidGT+=$x['amount_paid']; $i++;
		
		}

		if($i < 9) { for($i; $i <= 8; $i++) {
			echo '<tr bgcolor = "'.$con->initBackground($i) . '">
						<td align=left class="grid" width="100%" colspan=7>&nbsp;</td>
				</tr>';
			}
		}
		echo "</table>";
	}

?>