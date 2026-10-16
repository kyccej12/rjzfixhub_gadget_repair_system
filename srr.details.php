<?php	
	session_start();
	include("functions/srr.displayDetails.fnc.php");
	include("includes/dbUSE.php");
	
	$uid = $_SESSION['userid'];
	if(isset($_REQUEST['srr_no']) && $_REQUEST['srr_no'] != '') { 
		$res = getArray("select *, lpad(srr_no,6,0) as srrno, date_format(srr_date,'%m/%d/%Y') as d8, if(ref_date='0000-00-00','',date_format(ref_date,'%m/%d/%Y')) as rd8 from srr_header where srr_no='$_REQUEST[srr_no]' and branch = '$_SESSION[branchid]';");
		$cSelected = "Y"; $srr_no = $res['srr_no']; $status = $res['status']; $lock = $res['lock'];
	} else {  
		list($srr_no) = getArray("select lpad((ifnull(max(srr_no),0)+1),6,0) from srr_header;"); 
		$status = "Active"; $dS = "1"; $cSelected = "N"; $lock = "N";
	}
	
	if($status != 'Active') { $height = "200px"; } else { $height = "160px"; }
		
	function setHeaderControls($status,$lock,$srr_no,$uid,$dS) {
		list($urights) = getArray("select user_type from user_info where emp_id='$uid'");
		if($lock != 'Y') {
			switch($status) {
				case "Finalized":
					list($posted_by,$posted_on) = getArray("select fullname as name, date_format(updated_on,'%m/%d/%Y %p') as date_posted from srr_header a left join user_info b on a.updated_by=b.emp_id where a.srr_no='$srr_no';");
					if($urights == "admin") {
						$headerControls = "<a href=\"#\" class=\"topClickers\" onclick=\"javascript: reopenSRR('$srr_no');\"><img src='images/icons/edit.png' align=absmiddle width=16 height=16 />&nbsp;Set this Document to Active Status</a>&nbsp;";
					}
					$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript: parent.printSRR('$srr_no','$_SESSION[userid]','N');\"><img src=\"images/icons/print.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Print Stocks Receiving Slip</a>&nbsp;";
				break;
				case "Cancelled":
					if($urights == "admin") {
						$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:reuseSRR('$srr_no');\" style=\"padding: 5px;\"><img src=\"images/icons/refresh.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Recycle this Document</a>&nbsp;";	
					}
				break;
				case "Active": default:
					$headerControls = "<a href=\"#\" class=\"topClickers\" onClick=\"javascript:finalizeSRR('$srr_no','$_SESSION[userid]');\"><img src=\"images/icons/ok.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Finalize Stocks Receiving Slip</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:saveSRRHeader();\"><img src=\"images/save.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Save Changes</a>&nbsp;";
					if($urights == "admin" && $dS != 1) {
						$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:cancelSRR('$srr_no');\"><img src=\"images/icons/cancel.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Cancel this Document</a>";
					}
				break;
			}
		} else {
			$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript: parent.printSRR('$srr_no','$_SESSION[userid]','Y');\"><img src=\"images/icons/print.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Re-Print Stocks Receiving Receipt</a>";
		}
		echo $headerControls;
	}
	
	function setNavButtons($srr_no) {
		list($fwd) = getArray("select srr_no from srr_header where srr_no > $srr_no limit 1;");
		list($prev) = getArray("select srr_no from srr_header where srr_no < $srr_no order by srr_no desc limit 1;");
		list($last) = getArray("select srr_no from srr_header order by srr_no desc limit 1;");
		list($first) = getArray("select srr_no from srr_header order by srr_no asc limit 1;");
		if($prev)
			$nav = $nav . "<a href=# onclick=\"parent.viewSRR('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
		if($fwd) 
			$nav = $nav . "<a href=# onclick=\"parent.viewSRR('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
		echo "<a href=# onclick=\"parent.viewSRR('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.viewSRR('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
	}
?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<link href="style/tautocomplete.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script language="javascript" src="js/tautocomplete.js?sessid=<?php echo uniqid(); ?>"></script>
	<script language="javascript" src="js/tableH.js"></script>
	<script language="javascript" src="js/srr.js?sessid=<?php echo uniqid(); ?>"></script>
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
					$.post("srr.datacontrol.php", { mod: "deleteDetails", lid: line, srr_no: $("#srr_no").val(), sid: Math.random() }, function(ret) {
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
				$("#srr_date").datepicker(); $("#ref_date").datepicker();
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
								if (v.description.search(new RegExp(searchData)) != -1)  {
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
	<style>
		::-webkit-scrollbar {
			width: 1px;
			
		}
	</style>
</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div>
	<form name="xform" id="xform">
		<input type="hidden" name="cSelected" id="cSelected" value="<?php echo $cSelected; ?>">
		<input type=hidden name="prev_srr_date" id="prev_srr_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>">
		<table width=100% cellpadding=0 cellspacing=0 border=0 align=center>
			<tr>
				<td class="upper_menus" align=left>
					<?php setHeaderControls($res['status'],$res['locked'],$srr_no,$_SESSION['userid'],$dS); ?>
				</td>
				<td width=30% align=right style='padding-right: 5px;'><?php if($srr_no) { setNavButtons($srr_no); } ?></td>
			</tr>
			<tr><td height=2></td></tr>
		</table>

		<table border="0" width=100% class="td_content">
			<tr>
				<td width=60% valign=top>
					<table width=100% style="padding:0px 0px 0px 0px;">
						<tr><td height=2></td></tr>
						<tr>
							<td class="bareBold" align=left width=25% style="padding-left: 35px;">Received From :</td>
							<td align="left">
								<input class="gridInput" style="width:80%;" type=text name="received_from" id="received_from" value="<?php echo $res['received_from']; ?>" <?php echo $isReadOnly; ?> onchange='javascript: saveSRRHeader();'>
							</td>
						</tr>
						<tr>
							<td align="left" class="bareBold" style="padding-left: 35px;">Received By :</td>
							<td align=left>
								<input class="gridInput" style="width:80%;" type=text name="received_by" id="received_by" value="<?php echo $res['received_by']; ?>" <?php echo $isReadOnly; ?> >
							</td>				
						</tr>
						<tr>
							<td align="left" width="25%" class="bareBold" style="padding-left: 35px;">Reference Type&nbsp;:</td>
							<td align=left>
								<select class="gridInput" style="width:80%;" name="ref_type" id="ref_type" <?php echo $isDisabled; ?>>
									<option value="DR" <?php if($res['ref_type'] == "DR") { echo "selected"; } ?>>- Delivery Receipt -</option>
									<option value="STR" <?php if($res['ref_type'] == "STR") { echo "selected"; } ?>>- Stocks Transfer Receipt-</option>
									<option value="INV" <?php if($res['ref_type'] == "INV") { echo "selected"; } ?>>- Sales Return -</option>
									<option value="OTH" <?php if($res['ref_type'] == "OTH") { echo "selected"; } ?>>- Others -</option>
								</select>
							</td>				
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">SRR No.&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:70%;" type=text name="srr_no" id="srr_no" value="<?php echo $srr_no; ?>" >
							</td>				
						</tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">Date Received&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:70%;" type=text name="srr_date" id="srr_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>" readonly>
							</td>				
						</tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">Reference No.&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:70%;" type=text name="ref_no" id="ref_no" value="<?php echo $res['ref_no']; ?>" onchange='javascript: saveSRRHeader();'>
							</td>				
						</tr>
						<tr>
							<td align="left" width="50%" class="bareBold" style="padding-left: 35px;">Reference Date&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:70%;" type=text name="ref_date" id="ref_date" value="<?php echo $res['rd8']; ?>" readonly>
							</td>				
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<table cellspacing=0 cellpadding=0 border=0 width=100%>
			<tr>
				<td align=left class="ui-state-default" style="padding: 5px 5px 5px 25px;" width="15%">ITEM CODE</td>
				<td align=left class="ui-state-default" style="padding: 5px;">DESCRIPTION</td>
				<td align=center class="ui-state-default" style="padding: 5px;" width="15%">UoM</td>
				<td align=center class="ui-state-default" style="padding: 5px;" width="15%">QTY</td>
			</tr>
			<?php
				if($status == "Active" || $status == "") {
					echo '<tr>
							<td align=center class="grid" colspan=2><input type="hidden" id="product_code" /><input type=text class="inputSearch" style="padding-left: 22px; 100%;" id="description" /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="unit" style="width: 95%; text-align: center;" readonly /></td>
							<td align=left class="grid" colspan=2><input class="gridInput" type=text id="qty" style="width: 98%; text-align: right;"/></td>
						</tr>';
					$i++;
				}
			?>
		</table>
		<div id="details" style="height: <?php echo $height; ?>; overflow-x: auto; border-bottom: 3px solid #4297d7; scrollbar-width: none;">
			<?php showDetails($srr_no,$status,$lock); ?>
		</div>
		<table width=100% class="td_content">
			<tr>
				<td width=70%>
				<br/><br/>
					Transaction Remarks: <br/>
					<textarea rows=2 type="text" id="remarks" style="width:83%;"><?php echo $res['remarks']; ?></textarea>
				</td>
				<td align=right width=30% valign=top>
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
</body>
</html>