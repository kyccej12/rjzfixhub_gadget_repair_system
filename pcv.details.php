<?php	
	//ini_set("display_errors","On");
	session_start();
	require_once 'handlers/_pcvfunct.php';
	$mydb = new myPCV;
	
	$uid = $_SESSION['userid'];
	if($_REQUEST['pcv_no'] != '') { 
		$pcv_no = $_REQUEST['pcv_no']; 
		$res = $mydb->getArray("select *, date_format(pcvDate,'%m/%d/%Y') as d8, if(approvedDate='0000-00-00','',date_format(approvedDate,'%m/%d/%Y')) as ad8, if(liquidatedOn!='0000-00-00',date_format(liquidatedOn,'%m/%d/%Y'),'') as liqOn from pcv where pcvNo='$pcv_no' and branch = '1';");
		$cSelected = "Y"; $status = $res['status']; $lock = $res['lock'];
	} else {  
		$status = "Active"; $dS = "1"; $cSelected = "N"; $lock = "N";
		list($pcv_no) = $mydb->getArray("select max(pcvNo)+1 from pcv;");
	}
	
	if($res['status'] != "Active") { $isReadOnly = "readonly"; $isDisabled = "disabled"; }
?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/tautocomplete.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="js/jquery.dialogextend.js"></script>
	<script language="javascript" src="js/pcv.js"></script>
	<script>
		
		$(document).ready(function($) { 
		
			$('#supplier').autocomplete({
				source:'suggestContactNoCode.php', 
				minLength:3,
				select: function(event,ui) {
					$("#supplier").val(ui.item.tin_no);
					$("#address").val(decodeURIComponent(ui.item.addr));
					$("#tin").val(ui.item.tin_no);
				}
			});
			 $('#invoice_date').datepicker(); $('#liquidation_date').datepicker();
			
			<?php if($status == 'Posted' || $status == 'Cancelled' || $status == 'Finalized') {
				
				echo "$(\"#xform :input\").prop(\"disabled\",true);";
				
			} else { ?>
				//$("#xform :input").prop("disabled", true);
				$('#qty').bind('keypress', function(e) { if(e.keyCode ==13){ addDetails(); } });
				$('#pcv_date').datepicker(); $('#date_liquidated').datepicker(); $('#approved_date').datepicker(); 
				$('#proj_code').autocomplete({
					source:'suggestProject.php', 
					minLength:3,
					select: function(event,ui) {
						$("#proj_code").val(ui.item.project_code);
						$("#project_title").html(ui.item.project_title);
					}
				});
				/* $('#approved_by').autocomplete({
					source:'suggestEmployee.php', 
					minLength:3,
					select: function(event,ui) {
						$("#approved_by").val(ui.item.label);
					}
				}); */
				
				$('#payee_name').autocomplete({
					source:'suggestPayee.php', 
					minLength:3,
					select: function(event,ui) {
						$("#payee_name").val(ui.item.label);
						$("#payee_code").val(ui.item.fid);
					}
				});
				
			<?php } ?>
		});
		
		
	</script>
</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div id="main">
	<form name="xform" id="xform">
		<table width=100% cellpadding=0 cellspacing=0 border=0 align=center>
			<tr>
				<td class="upper_menus" align=left>
					<?php $mydb->setHeaderControls($status,$lock,$pcv_no,$_SESSION['userid'],$dS,$_SESSION['utype']); ?>
				</td>
				<td width=20% align=right style='padding-right: 5px;'><?php if($pcv_no) { $mydb->setNavButtons($pcv_no); } ?></td>
			</tr>
			<tr><td height=2></td></tr>
		</table>

		<table border="0" cellpadding="0" cellspacing="1" width=100% style="padding: 20px;" class="td_content">
			<tr>
				<td class="bareBold" align=right width=20% style="padding-right: 5px;">Payee :</td>
				<td align="left">
					<input class="inputSearch2" style="width:90%;padding-left: 22px;" type=text name="payee_name" id="payee_name" value="<?php echo $res['payeeName']; ?>" >
					<input type="hidden" name="payee_code" id="payee_code" value="<?php echo $res['payeeCode']; ?>">
				</td>
				<td align="right" width="15%" class="bareBold" style="padding-right: 5px;">Doc. No.&nbsp;:</td>
				<td align=left>
					<input class="gridInput" style="width:80%;" type=text name="pcv_no" id="pcv_no" value="<?php echo $pcv_no; ?>" readonly >
				</td>		
			</tr>
			<tr>
				<td align="right" width="20%" class="bareBold" style="padding-right: 5px;">Purpose of Transaction&nbsp;:</td>
				<td align=left>
					<select class="gridInput" style="width:90%;" name="pcv_type" id="pcv_type" >
						<?php
							$pt = $mydb->dbquery("SELECT `acct`,`description` FROM options_pcvtype");
							while(list($acct,$desc) = $pt->fetch_array()) {
								if($res['pcvAccount'] == $acct) { $selected = "selected"; } else { $selected = ''; }
								echo "<option value='$acct' $selected>$desc</option>";
							}
							unset($pt);
						?>
					</select>
				</td>
				<td align="right" width="15%" class="bareBold" style="padding-right: 5px;">Transaction Date&nbsp;:</td>
				<td align=left>
					<input class="gridInput" style="width:80%;" type=text name="pcv_date" id="pcv_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>" >
				</td>
			</tr>
			
			<tr>
				<td align="right" width="20%" class="bareBold" style="padding-right: 5px;">Amount Disbursed&nbsp;:</td>
				<td align=left>
					<input class="gridInput" style="width:90%;" type=text name="amount" id="amount" value="<?php echo number_format($res['amount'],2); ?>" >
				</td>
				<td align="right" width="15%" class="bareBold" style="padding-right: 5px;">Date Approved&nbsp;:</td>
				<td align=left>
					<input class="gridInput" style="width:80%;" type=text name="approved_date" id="approved_date" value="<?php echo $res['ad8']; ?>" >
				</td>
			</tr>
			<tr>
				<td class="bareBold" align=right width=20% style="padding-right: 5px; padding-top: 3px;" valign=top></td>
				<td align="left"></td>
				<td align="right" width="15%" class="bareBold" style="padding-right: 5px;padding-top: 3px;" valign=top>Approved By&nbsp;:</td>
				<td align=left valign=top>
					<input class="inputSearch2" style="width:80%; padding-left: 22px;" type=text name="approved_by" id="approved_by" value="<?php echo $res['approvedBy']; ?>" onchange='javascript: saveHeader();'>
				</td>	
			</tr>
			<tr>
				<td class="bareBold" align=right width=20% style="padding-right: 5px;" valign=top>Particulars / Memo :</td>
				<td colspan=3>
					<textarea rows=2 type="text" id="particulars" name="particulars" style="width:93%;" onchange='javascript: saveHeader();'><?php echo $res['particulars']; ?></textarea>
				</td>
			</tr>
			<tr>
				<td align="right" width="20%" class="bareBold" style="padding-right: 5px;">Status&nbsp;:</td>
				<td align=left>
					<select class="gridInput" style="width:70%;" name="is_liquidated" id="is_liquidated" >
						<option value="N" <?php if($res['isLiquidated'] == 'N') { echo "selected"; }?>>Unliquidated</option>
						<option value="Y" <?php if($res['isLiquidated'] == 'Y') { echo "selected"; }?>>Liquidated</option>
					</select>
				</td>
				<td></td><td></td>
			</tr>
			<tr>
				<td align="right" width="20%" class="bareBold" style="padding-right: 5px;">Liquidated Amount&nbsp;:</td>
				<td align=left>
					<input class="gridInput" style="width:70%;" type=text name="amount_liquidated" id="amount_liquidated" value="<?php echo number_format($res['amountLiquidated'],2); ?>" >
				</td>
				<td></td><td></td>
			</tr>
			<tr>
				<td align="right" width="20%" class="bareBold" style="padding-right: 5px;">Date Liquidated&nbsp;:</td>
				<td align=left>
					<input class="gridInput" style="width:70%;" type=text name="date_liquidated" id="date_liquidated" value="<?php echo $res['liqOn']; ?>" >
				</td>
				<td></td><td></td>
			</tr>
			<tr>
				<td class="bareBold" align=right width=20% style="padding-right: 5px;" valign=top>Liquidation Remarks :</td>
				<td colspan=3>
					<textarea rows=2 type="text" id="liquidation_remarks" name="liquidation_remarks" style="width:93%;" onchange='javascript: saveHeader();'><?php echo $res['liquidationRemarks']; ?></textarea>
				</td>
			</tr>
		</table>

	</form>	
</div>
<div id="myInvoices" style="display: none;">
	<form name = "frmLiquidation" id = "frmLiquidation">
		<table width=100% cellpadding = 0 cellspacing = 1 border = 0>
			<tr>
				<td width=40% class="spandix-l">Supplier/Payee :</td>
				<td><input type="text" name = "supplier" id = "supplier" class="inputSearch2" style="width: 80%; padding-left: 22px;"></td>
			</tr>
			<tr>
				<td width=40% class="spandix-l" valign=top>Address :</td>
				<td><textarea name="address" id="address" style="width: 80%;" rows=1></textarea></td>
			</tr>
			<tr>
				<td width=40% class="spandix-l">T-I-N :</td>
				<td><input type="text" name = "tin" id = "tin" class="gridInput" style="width: 80%;"></td>
			</tr>
			<tr>
				<td width=40% class="spandix-l">Date Liquidated :</td>
				<td><input type="text" name="liquidation_date" id="liquidation_date" class="gridInput" style="width: 80%;"></td>
			</tr>
			<tr>
				<td width=40% class="spandix-l">OR/Invoice No. :</td>
				<td><input type="text" name="invoice" id="invoice" class="gridInput" style="width: 80%;"></td>
			</tr>
			<tr>
				<td width=40% class="spandix-l">Amount :</td>
				<td><input type="text" name="invoice_amount" id="invoice_amount" class="gridInput" style="width: 80%;"></td>
			</tr>
			<tr>
				<td width=40% class="spandix-l">OR/Invoice Date :</td>
				<td><input type="text" name="invoice_date" id="invoice_date" class="gridInput" style="width: 80%;"></td>
			</tr>
			<tr>
				<td width=40% class="spandix-l">Other Expenses :</td>
				<td><input type="text" name="other_amount" id="other_amount" class="gridInput" style="width: 80%;"></td>
			</tr>
			<tr>
				<td width=40% class="spandix-l">Cash Return :</td>
				<td><input type="text" name="return_amount" id="return_amount" class="gridInput" style="width: 80%;"></td>
			</tr>
			<tr>
				<td width=40% class="spandix-l" valign=top>Particulars :</td>
				<td><textarea name="liquidation_particulars" id="liquidation_particulars" style="width: 80%;" rows=1></textarea></td>
			</tr>
		</table>
	</form>
</div>
<div class="suggestionsBox" id="suggestions" style="display: none;">
	<div class="suggestionList" id="autoSuggestionsList">&nbsp;</div>
</div>
</body>
</html>