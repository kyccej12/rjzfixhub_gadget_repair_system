<?php	
	/* UNSET QUED FOR DELETION */
	session_start();
	include("handlers/_generics.php");
	$con = new _init;
	
	$uid = $_SESSION['userid'];
	if(isset($_REQUEST['doc_no']) && $_REQUEST['doc_no'] != '') { 
		$res = $con->getArray("select *, lpad(doc_no,6,0) as docno, date_format(posting_date,'%m/%d/%Y') as dd8 from phy_header where doc_no='$_REQUEST[doc_no]' and branch = '$_SESSION[branchid]';");
	 	$status = $res['status']; $doc_no = $res['docno']; $traceNo = $res['trace_no'];
	} else {  
		$status = "Active"; $dS = "1"; $traceNo = $con->generateRandomString();
	}
		
	function setHeaderControls($status,$dS) {
		global $con;

		list($urights) = $con->getArray("select user_type from user_info where emp_id='$_SESSION[userid]';");
		switch($status) {
			case "Finalized":
				if($urights == "admin") {
					$headerControls = "<a href=\"#\" class=\"topClickers\" onclick=\"javascript: reopenPhy();\"><img src='images/icons/edit.png' align=absmiddle width=16 height=16 />&nbsp;Set this Document to Active Status</a>&nbsp;";
				}
				$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript: printDocument();\"><img src=\"images/icons/print.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Print Physical Inventory Form</a>&nbsp;";
			break;
			case "Cancelled":
				if($urights == "admin") {
					$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:reusePhy();\" style=\"padding: 5px;\"><img src=\"images/icons/refresh.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Recycle this Document</a>";	
				}
			break;
			case "Active": default:
				$headerControls = "<a href=\"#\" class=\"topClickers\" onClick=\"javascript:finalizePhy();\"><img src=\"images/icons/ok.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Finalize & Post This Document</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:savePhyHeader();\"><img src=\"images/save.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Save Changes</a>&nbsp;";
				if($urights == "admin" && $dS != 1) {
					$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:cancelPhy();\"><img src=\"images/icons/cancel.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Cancel this Document</a>";
				}
			break;
		}
		echo $headerControls;
	}
	
	function setNavButtons($doc_no) {
		global $con;

		list($fwd) = $con->getArray("select doc_no from phy_header where doc_no > $doc_no and branch = '$_SESSION[branchid]' limit 1;");
		list($prev) = $con->getArray("select doc_no from phy_header where doc_no < $doc_no and branch = '$_SESSION[branchid]' order by doc_no desc limit 1;");
		list($last) = $con->getArray("select doc_no from phy_header where branch = '$_SESSION[branchid]' order by doc_no desc limit 1;");
		list($first) = $con->getArray("select doc_no from phy_header where branch = '$_SESSION[branchid]' order by doc_no asc limit 1;");
		if($prev)
			$nav = $nav . "<a href=# onclick=\"parent.viewPhy('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
		if($fwd) 
			$nav = $nav . "<a href=# onclick=\"parent.viewPhy('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
		echo "<a href=# onclick=\"parent.viewSRR('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.viewPhy('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
	}
	
?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/datatables/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script language="javascript" src="js/phy.js?sessid=<?php echo uniqid(); ?>"></script>
	<script>
	
	
		$(document).ready(function($) {
			$("#doc_date").datepicker();
			
			$('#itemDescription').autocomplete({
				source:'suggestItemsCost.php', 
				minLength:3,
				select: function(event,ui) {
					$("#itemCode").val(ui.item.item_code);
					$("#itemUnit").val(decodeURIComponent(ui.item.unit));
					$("#itemCost").val(decodeURIComponent(ui.item.unit_price));
					computeItemAmount($("#itemQty").val());
				}
			});

			$('#conducted_by').autocomplete({
				source:'suggestEmployee.php', 
				minLength:3
			});

			$('#verified_by').autocomplete({
				source:'suggestEmployee.php', 
				minLength:3
			});

			$('#details').dataTable({
				"ajax": {
					"url": "phy.datacontrol.php",
					"data": { trace_no: "<?php echo $traceNo; ?>", mod: "retrieve", sid: Math.random() },
					"method": "POST"	
				},
				"scrollY":  "230",
				"select":	'single',
				"pagingType": "full_numbers",
				"bProcessing": true,
				"searching": false,
				"paging": false,
				"info": false,
				
				"aoColumns": [
					{ mData: 'id' },
					{ mData: 'item_code' },
					{ mData: 'description' },
					{ mData: 'unit' },
					{ mData: 'brand' },
					{ mData: 'model' },
					{ mData: 'qty', render: $.fn.dataTable.render.number(',', '.', 2, '')},
					{ mData: 'cost', render: $.fn.dataTable.render.number(',', '.', 2, '') },
					{ mData: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '') },
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1,3,4,5,6]},
					{ className: "dt-body-right", "targets": [7,8]},
					{ "targets": [0], "visible": false }
				]
			});
		
			<?php if($status == 'Finalized' || $status == 'Cancelled') { echo "$(\"#xform :input\").prop('disabled',true);"; } else { ?>
				$("#posting_date").datepicker(); $("#itemExpiry").datepicker();
			<?php } ?>	
		});
		
		function redrawDataTable() {
			$('#details').DataTable().ajax.url("phy.datacontrol.php?mod=retrieve&trace_no=<?php echo $traceNo; ?>").load();
		}
	</script>

	<style>
		.dataTables_wrapper {
			display: inline-block;
			font-size: 11px; 
			width: 100%; 
		}
		
		table.dataTable tr.even { background-color: #f5f5f5;  }
		table.dataTable tr.odd { background-color: white; }
		.dataTables_filter input { width: 250px; }
	</style>
</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div>
	<form name="xform" id="xform">
		<input type="hidden" name="cSelected" id="cSelected" value="<?php echo $cSelected; ?>">
		<input type="hidden" name="trace_no" id="trace_no" value="<?php echo $traceNo; ?>">
		<table width=98% cellpadding=0 cellspacing=0 border=0 align=center>
			<tr>
				<td class="upper_menus" align=left>
					<?php setHeaderControls($res['status'],$dS); ?>
				</td>
				<td width=30% align=right style='padding-right: 5px;'><?php if($doc_no) { setNavButtons($doc_no); } ?></td>
			</tr>
			<tr><td height=2></td></tr>
		</table>

		<table border="0" cellpadding="0" cellspacing="1" width=100% class="td_content">
			<tr>
				<td class="bareBold" align=left width=150 style="padding-left: 35px;">Doc No :</td>
				<td align="left">
					<input class="gridInput" style="width:200px;" type=text name="doc_no" id="doc_no" value="<?php echo $doc_no; ?>" readonly>
				</td>
			</tr>
			<tr>
				<td class="bareBold" align=left width=150 style="padding-left: 35px;">Posting Date :</td>
				<td align="left">
					<input class="gridInput" style="width:200px;" type=text name="doc_date" id="doc_date" value="<?php if($res['dd8'] != '') { echo $res['dd8']; } else { echo date('m/d/Y'); } ?>">
				</td>
			</tr>
			<tr>
				<td align="left" class="bareBold" style="padding-left: 35px;">Conducted By :</td>
				<td align=left>
					<input class="inputSearch2" style="width:200px; padding-left: 22px;" type=text name="conducted_by" id="conducted_by" value="<?php echo $res['conducted_by']; ?>">
				</td>				
			</tr>
			<tr>
				<td align="left" class="bareBold" style="padding-left: 35px;">Verified By :</td>
				<td align=left>
					<input class="inputSearch2" style="width:200px;  padding-left: 22px;" type=text name="verified_by" id="verified_by" value="<?php echo $res['verified_by']; ?>">
				</td>				
			</tr>

		</table>

		<table id="details">
			<thead>
				<tr>
					<th></th>
					<th width=15%>ITEM CODE</th>
					<th >DESCRIPTION</th>
					<th width=10%>UNIT</th>
					<th width=10%>LOT #</th>
					<th width=10%>EXPIRY</th>
					<th width=10%>QTY</th>
					<th width=10%>COST</th>
					<th width=12%>AMOUNT</th>
				</tr>
			</thead>
		</table>

		<table width=100% class="td_content">
			<tr>
				<td width=50%>
					Transaction Remarks: <br/>
					<textarea rows=2 type="text" id="remarks" style="width:83%;" onchange='javascript: saveSWHeader();'><?php echo $res['remarks']; ?></textarea>
				</td>
				<td align=right width=50% valign=top>
					
					Transaction Total : &nbsp;&nbsp;<input style="width:200px;text-align:right;" type=text name="grandTotal" id="grandTotal" value="<?php echo number_format($res['amount'],2); ?>" readonly>

				</td>
			</tr>
			<tr>
				<td align=left colspan=2 style="padding-top: 15px;">
					<?php if($status == 'Active' || $status == '') { ?>
						<a href="#" class="topClickers" onClick="javascript:addItem();"><img src="images/icons/add-2.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Add Item</a>&nbsp;
						<a href="#" class="topClickers" onClick="javascript:updateItem();"><img src="images/icons/edit.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Update Selected Item</a>&nbsp;
						<a href="#" class="topClickers" onClick="javascript:deleteItem();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Selected Item</a>
					<?php } ?>
				</td>
			</tr>
		</table>	
	</form>
</div>
<div id="itemEntry" style="display: none;">
	<form name="frmItemEntry" id="frmItemEntry">
		<input type="hidden" id="recordId" name="recordId">
		<table width="100%" cellspacing=2 cellpadding=0 >
			<tr>
				<td class="bareThin" align=left width=40%>Description :</td>
				<td align=left>
					<input type="text" name="itemDescription" id="itemDescription" class="inputSearch2" style="width: 80%; padding-left: 22px;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Item Code :</td>
				<td align=left>
					<input type="text" name="itemCode" id="itemCode" class="gridInput" style="width: 80%;" disabled>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Unit :</td>
				<td align=left>
					<input type="text" name="itemUnit" id="itemUnit" class="gridInput" style="width: 80%;" disabled>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Quantity :</td>
				<td align=left>
					<input type="text" name="itemQty" id="itemQty" class="gridInput"style="width: 80%;" value=0 onchange="javascript: computeItemAmount(this.value);">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Unit Cost :</td>
				<td align=left>
					<input type="text" name="itemCost" id="itemCost" class="gridInput" style="width: 80%;" value='0.00' disabled>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Amount :</td>
				<td align=left>
					<input type="text" name="itemAmount" id="itemAmount" class="gridInput" style="width: 80%;" value='0.00' disabled>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Brand :</td>
				<td align=left>
					<input type="text" name="itemBrand" id="itemBrand" class="gridInput" style="width: 80%;">
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Expiry (If Applicable) :</td>
				<td align=left>
					<input type="text" name="itemExpiry" id="itemExpiry" class="gridInput"style="width: 80%;">
				</td>
			</tr>
		</table>
	</form>
</div>
</body>
</html>