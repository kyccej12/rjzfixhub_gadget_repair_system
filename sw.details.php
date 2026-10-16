<?php	
	/* UNSET QUED FOR DELETION */
	session_start();
	
	include("functions/sw.displayDetails.fnc.php");
	include("includes/dbUSE.php");
	
	$uid = $_SESSION['userid'];
	if($_REQUEST['sw_no'] != '') { 
		$sw_no = $_REQUEST['sw_no']; 
		$res = getArray("select *, date_format(sw_date,'%m/%d/%Y') as d8, if(request_date='0000-00-00','',date_format(request_date,'%m/%d/%Y')) as rd8 from sw_header where sw_no = '$sw_no' and branch = '$_SESSION[branchid]';");
		$cSelected = "Y"; $status = $res['status']; $lock = $res['lock'];
	} else {  
		list($sw_no) = getArray("select lpad(ifnull(max(sw_no),0)+1,6,0) from sw_header where branch = '$_SESSION[branchid]';"); 
		$status = "Active"; $dS = "1"; $cSelected = "N"; $lock = "N";
	}
		
	function setHeaderControls($status,$lock,$sw_no,$uid,$dS) {
		list($urights) = getArray("select user_type from user_info where emp_id='$uid'");
		if($lock != 'Y') {
			switch($status) {
				case "Finalized":
					list($posted_by,$posted_on) = getArray("select fullname as name, date_format(updated_on,'%m/%d/%Y %p') as date_posted from sw_header a left join user_info b on a.updated_by=b.emp_id where a.sw_no='$sw_no';");
					if($urights == "admin") {
						$headerControls = "<a href=\"#\" class=\"topClickers\" onclick=\"javascript: reopenSW('$sw_no');\"><img src='images/icons/edit.png' align=absmiddle width=16 height=16 />&nbsp;Set this Document to Active Status</a>&nbsp;";
					}
					$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript:parent.printSW('$sw_no','$_SESSION[userid]','N');\"><img src=\"images/icons/print.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Print Stocks Withdrawal Slip</a>&nbsp;";
				break;
				case "Cancelled":
					if($urights == "admin") {
						$headerControls = $headerControls . "<a href=\"#\" class=\"topClickers\" onclick=\"javascript:reuseSW('$sw_no');\" style=\"padding: 5px;\"><img src=\"images/icons/refresh.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Recycle this Document</a>&nbsp;";	
					}
				break;
				case "Active": default:
					$headerControls = "<a href=\"#\" class=\"topClickers\" onClick=\"javascript:finalizeSW('$sw_no','$_SESSION[userid]');\"><img src=\"images/icons/ok.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Finalize Stocks Withdrawal Slip</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:saveSWHeader();\"><img src=\"images/save.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Save Changes</a>&nbsp;";
					if($urights == "admin" && $dS != 1) {
						$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:cancelSW('$sw_no');\"><img src=\"images/icons/cancel.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Cancel this Document</a>&nbsp;";
					}
				break;
			}
		} else {
			$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript:parent.printSW('$sw_no','$_SESSION[userid]','Y');\"><img src=\"images/icons/print.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Re-Print Stocks Withdrawal Slip</a>";
		}
		echo $headerControls;
	}
	
	function setNavButtons($sw_no) {
		list($fwd) = getArray("select sw_no from sw_header where sw_no > $sw_no and company = '$_SESSION[company]' and branch = '$_SESSION[branchid]' limit 1;");
		list($prev) = getArray("select sw_no from sw_header where sw_no < $sw_no and company = '$_SESSION[company]'and branch = '$_SESSION[branchid]' order by sw_no desc limit 1;");
		list($last) = getArray("select sw_no from sw_header where company = '$_SESSION[company]' and branch = '$_SESSION[branchid]' order by sw_no desc limit 1;");
		list($first) = getArray("select sw_no from sw_header where company = '$_SESSION[company]' and branch = '$_SESSION[branchid]' order by sw_no asc limit 1;");
		if($prev)
			$nav = $nav . "<a href=# onclick=\"parent.viewPO('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
		if($fwd) 
			$nav = $nav . "<a href=# onclick=\"parent.viewSRR('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
		echo "<a href=# onclick=\"parent.viewSRR('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.viewSRR('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
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
	<script language="javascript" src="js/tautocomplete.js"></script>
	<script language="javascript" src="js/tableH.js"></script>
	<script language="javascript" src="js/sw.js?sessi=<?php echo uniqid(); ?>"></script>
	<script>
		
		var line;
		
		function selectLine(obj,lid) {
			gObj = obj;
			$(obj).closest("tr").siblings().removeClass("diffcolor");
			$(obj).toggleClass("diffcolor");
			line = lid;
		}
		
		function deleteLine() {
			if(line == "") {
				parent.sendErrorMessage("- You have not selected any record that you wish to remove.");
			} else {
				if(confirm("Are you sure you want to remove this entry?") == true) {
					$.post("sw.datacontrol.php", { mod: "deleteDetails", lid: line, sw_no: $("#sw_no").val(), sid: Math.random() }, function(ret) {
						$("#details").html(ret);
						line = "";
					},"html");
				}
			}
		}
		
		
		$(document).ready(function($) { 
		
			$("#qty, #unit").keyup(
				function(e) {
					if(e.keyCode == 13) {
						addDetails();
					}
				}
			);
		
		
			<?php if($status == 'Finalized' || $status == 'Cancelled') {
				echo "$(\"#xform :input\").prop('disabled',true);";
			} else { ?>
			
				$('#qty').bind('keypress', function(e) { if(e.keyCode ==13){ addDetails(); } });
				$('#request_date').datepicker(); $('#sw_date').datepicker();
			
				var myProduct = $("#description").tautocomplete({
					width: "720px",
					columns: ['Item Code','Description','Unit'],
					hide: false,
					ajax: {
						url:  "suggestItemsNoCust.php",
						type: "GET",
						data:function() {var x = { term: myProduct.searchdata() }; return x; },
						success: function (data) {
							var filterData = [];
							var searchData = eval("/" + myProduct.searchdata() + "/gi");
							$.each(data, function (i,v) {
								if (v.description.search(new RegExp(searchData)) != -1) {
									filterData.push(v);
								}
							});
							return filterData;
						}
					},
					onchange: function () {
						var cellData = myProduct.all();
						$("#product_code").val(cellData['Item Code']);
						$("#description").val(cellData['Description']);
						$("#unit").val(cellData['Unit']);
						$("#qty").focus();
					}
				  });
			
			<?php } ?>
		});
		
	</script>
</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div>
	<form name="xform" id="xform">
		<input type="hidden" name="cSelected" id="cSelected" value="<?php echo $cSelected; ?>">
		<input type="hidden" name="prev_sw_date" id="prev_sw_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>">
		<table width=100% cellpadding=0 cellspacing=0 border=0 align=center>
			<tr>
				<td class="upper_menus" align=left>
					<?php setHeaderControls($status,$lock,$sw_no,$_SESSION['userid'],$dS); ?>
				</td>
				<td width=20% align=right style='padding-right: 5px;'><?php if($sw_no) { setNavButtons($sw_no); } ?></td>
			</tr>
			<tr><td height=2></td></tr>
		</table>

		<table border="0" cellpadding="0" cellspacing="1" width=100% class="td_content">
			<tr>
				<td width=50% valign=top>
					<table width=100% style="padding:0px 0px 0px 0px;">
						<tr><td height=2></td></tr>
						<tr>
							<td class="bareBold" align=right width=30% style="padding-right: 5px;">Withdrawn By :</td>
							<td align="left">
								<input class="gridInput" style="width:210px;" type=text name="withdrawn_by" id="withdrawn_by" value="<?php echo $res['withdrawn_by']; ?>" onchange='javascript: saveSWHeader();'>
							</td>
						</tr>
						<tr><td height=2></td></tr>
						<tr>
							<td align="right" width="30%" class="bareBold" style="padding-right: 5px;">Purpose of Withdrawal&nbsp;:</td>
							<td align=left>
								<select class="gridInput" style="width:210px; font-size: 11px;" name="ref_type" id="ref_type" >
									<option value="PU" <?php if($res['ref_type'] == "PU") { echo "selected"; } ?>>- For Production Of Finished Product  -</option>
									<option value="MS" <?php if($res['ref_type'] == "MS") { echo "selected"; } ?>>- General Maintenance -</option>
									<option value="SP" <?php if($res['ref_type'] == "SP") { echo "selected"; } ?>>- For Kitchen Use/Supplies -</option>
								</select>
							</td>				
						</tr>
						<tr><td height=2></td></tr>
					</table>
				</td>
				<td valign=top>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>
						<tr><td height=2></td></tr>
						<tr>
							<td align="right" width="30%" class="bareBold" style="padding-right: 5px;">Doc. No.&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:60%;" type=text name="sw_no" id="sw_no" value="<?php echo $sw_no; ?>" readonly >
							</td>				
						</tr>
						<tr>
							<td align="right" width="30%" class="bareBold" style="padding-right: 5px;">Transaction Date&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:60%;" type=text name="sw_date" id="sw_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>" onChange="javascript: checkLockDate(this.id,this.value,$('#prev_sw_date').val());" >
							</td>				
						</tr>
						<tr>
							<td align="right" width="30%" class="bareBold" style="padding-right: 5px;">Requested By&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:60%;" type=text name="requested_by" id="requested_by" value="<?php echo $res['requested_by']; ?>">
							</td>				
						</tr>
						<tr>
							<td align="right" width="30%" class="bareBold" style="padding-right: 5px;">Date Requested&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:60%;" type=text name="request_date" id="request_date" value="<?php echo $res['rd8']; ?>" >
							</td>				
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<table cellspacing=0 cellpadding=0 border=0 width=100% style="margin-top: 1px;">
			<tr>
				<td align=left class="ui-state-default" style="padding: 5px 5px 5px 25px;" width="15%">ITEM CODE</td>
				<td align=left class="ui-state-default" style="padding: 5px;" width="55%">DESCRIPTION</td>
				<td align=center class="ui-state-default" style="padding: 5px;" width="15%">UoM</td>
				<td align=center class="ui-state-default" style="padding: 5px;">QTY</td>
			</tr>
			<?php
				if($status == "Active" || $status == "") {
					echo '<tr>
							<td align=center class="grid" colspan=2><input type="hidden" id="product_code" /><input type=text class="inputSearch" style="padding-left: 22px; 100%;" id="description" /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="unit" style="width: 95%; text-align: center;" readonly /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="qty" style="width: 95%; text-align: right;"/></td>
						</tr>';
					$i++;
				}
			?>
		</table>
		<div id="details" style="height: 160px; overflow-x: auto; border-bottom: 3px solid #4297d7; scrollbar-width: none;">
			<?php showDetails($sw_no); ?>
		</div>
		<table width=100% class="td_content">
			<tr>
				<td width=50%>
				<br/><br/>
					Transaction Remarks: <br/>
					<textarea rows=2 type="text" id="remarks" style="width:83%;" onchange='javascript: saveSWHeader();'><?php echo $res['remarks']; ?></textarea>
				</td>
				<td align=right width=50% valign=top>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" width="80%" class="bareBold" style="padding-left: 40%;"></td>
							<td align=right>
								<input style="width:80%;text-align:right;" type=hidden name="grandTotal" id="grandTotal" value="<?php echo number_format($res['amount'],2); ?>" readonly>
							</td>				
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align=left colspan=2 style="padding-top: 15px;">
					<?php if($status == 'Active') { ?>
						<a href="#" class="topClickers" onClick="javascript:deleteLine();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Selected Line Entry</a>
					<?php } ?>
				</td>
			</tr>
		</table>	
	</form>	
</div>
<div class="suggestionsBox" id="suggestions" style="display: none;">
	<div class="suggestionList" id="autoSuggestionsList">&nbsp;</div>
</div>
<div id ="branchSelector" style="display:none">
	<table border=0 width="100%">
		<tr>
			<td width="30%" align=right> Branch : </td>
			<td width="70%">
			<select id="branch_slector" style="width:200px" onchange="reloadSW(this.value)">
				<option value=''> SELECT </option>
				<?php  
					$complist = dbquery("SELECT branch_code,branch_name FROM options_branches a WHERE a.company = '1' ORDER BY branch_code;");
					while($iRow = mysql_fetch_array($complist)){
						$opts .= "<option value = '".$iRow['branch_code']."'> $iRow[branch_name] </optoin>";
					}
					echo $opts;
				?>
			</select>
			</td>
		</tr>
		<tr> <td height=4> </td> <tr>
		<tr>
			<td colspan=2> 
				
				<div style ="overflow:auto;height:340px;" id="sw_list"> </div>
			</td>
		</tr>
	</table>
</div>
</body>
</html>