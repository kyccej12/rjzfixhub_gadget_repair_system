<?php
	include("handlers/initDB.php");
	$con = new myDB;

	session_start();
	
	if(isset($_GET['id']) && $_GET['id'] != "") { 
		$res = $con->getArray("select * from products_master where record_id = '$_GET[id]';"); 
		if($res['supplier'] != 0 || $res['supplier'] != "") { list($sname) = $con->getArray("select tradename from contact_info where file_id='$res[supplier]';"); }

		$pi = $con->getArray("select ifnull(sum(b.qty),0) from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch = b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$res[item_code]' and a.status = 'Finalized' and a.posting_date > '2026-10-01' GROUP BY b.item_code;");				
		$cur = $con->getArray("select sum(purchases+inbound-outbound-pullouts-sold) as currentbalance from ibook where item_code = '$res[item_code]' and doc_date between '2026-10-01' and '".date('Y-m-d')."' and doc_branch = '$_SESSION[branchid]';");
		$end = ROUND($pi[0]+$cur['currentbalance'],2);

		list($onPO) = $con->getArray("select sum(b.qty) from po_header a left join po_details b on a.po_no = b.po_no and a.branch = b.branch where b.item_code = '$res[item_code]' and a.branch = '$_SESSION[branchid]' and a.status = 'Finalized' and b.qty_dld = 0 and a.po_date = '".date('Y-m-d')."';");
	}
	
	function getMod($def,$mod) {
		if($def == $mod) { echo "class=\"float2\""; }
	}
	
	function setNavButtons($id) {
		global $con;
		
		$first = 1;	$fwd = $id+1; $prev = $id-1;

		list($last) = $con->getArray("select record_id from products_master order by record_id desc limit 1;");
		if($prev > 0)
			$nav = $nav . "<a href=# onclick=\"parent.showItemInfo('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
		if($fwd > 0) 
			$nav = $nav . "<a href=# onclick=\"parent.showItemInfo('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
		echo "<a href=# onclick=\"parent.showItemInfo('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.showItemInfo('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
	}
	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>RJZ Fix Hub System Ver. 1.0a</title>
<link href="style/style.css" rel="stylesheet" type="text/css" />
<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
<script type="text/javascript" charset="utf8" src="js/tableH.js"></script>
<script>
	$(document).ready(function(){
		$('#supplier').autocomplete({
			source:'suggestContacts.php', 
			minLength:3,
			select: function(event,ui) {
				$("#supplier").val(ui.item.cid);
				$("#supplier_name").val(decodeURIComponent(ui.item.cname));
			}
		});

		<?php 
			switch($_GET['mod']) {
				case "2":
					echo "$('#polist').dataTable({
						\"keys\": true,
						\"scrollY\":  \"225px\",
						\"select\":	\"single\",
						\"pagingType\": \"full_numbers\",
						\"bProcessing\": false,
						\"sAjaxSource\": \"data/items.sublist.php?mod=pendingPO&item=".$res['item_code']."\",
						\"order\": [[ 1, \"asc\" ]],
						\"aoColumns\": [
						  { mData: 'po_no' },
						  { mData: 'mypo' },
						  { mData: 'pd8' },
						  { mData: 'supplier_name' },
						  { mData: 'remarks' },
						  { mData: 'pending_qty', render: $.fn.dataTable.render.number(',', '.', 2, '') },
						],
						\"aoColumnDefs\": [
							{className: \"dt-body-center\", \"targets\": [1,2,5]},
							{ \"targets\": [0], \"visible\": false }
						]
					});";
				break;
				case "3":
					echo "$('#rrlist').dataTable({
						\"keys\": true,
						\"scrollY\":  \"225px\",
						\"select\":	\"single\",
						\"pagingType\": \"full_numbers\",
						\"bProcessing\": false,
						\"sAjaxSource\": \"data/items.sublist.php?mod=rrlist&item=".$res['item_code']."\",
						\"order\": [[ 1, \"asc\" ]],
						\"aoColumns\": [
						  { mData: 'rr_no' },
						  { mData: 'myrr' },
						  { mData: 'rd8' },
						  { mData: 'supplier_name' },
						  { mData: 'remarks' },
						  { mData: 'invoice_no' },
						  { mData: 'idate' },
						],
						\"aoColumnDefs\": [
							{className: \"dt-body-center\", \"targets\": [1,2,5,6]},
							{ \"targets\": [0], \"visible\": false }
						]
					});";
				break;
			}
		?>
	
	});
	
	function savePInfo(rid) {
		
		if(confirm("Are you sure you want to save changes made to this product profile?") == true) {

			var msg = "";
			if($("#item_code").val() == "") { msg = msg + "- Invalid Item Code or Item Code is empty<br/>"; }
			if($("#description").val() == "") { msg = msg + "- Item <b>Short Description</b> is empty<br/>"; }
			if(isNaN(parent.stripComma($("#item_unitcost").val())) == true || $("#item_unitcost").val() == "") { msg = msg + "- Invalid Unit Cost<br/>"; }
			
			if(msg!="") {
				parent.sendErrorMessage(msg);
			} else {
				//if(confirm("Are you sure you want to save changes made to this record?") == true) {
					$.post("src/sjerp.php", { mod: "checkDupCode", rid: rid, item_code: $("#item_code").val(), barcode: $("#item_barcode").val(), sid: Math.random() }, function(data) {
						if(data == "NODUPLICATE") {
							var url = $(document.merchandise).serialize();
								url = "mod=savePInfo&"+url;
							$.post("src/sjerp.php", url);
							alert("Merchandise Successfuly Saved!"); 
							parent.closeDialog("#itemdetails");	
							parent.showItems($("#item_code").val());
						} else { parent.sendErrorMessage("Duplicate Item Code or Barcode has been detected!"); }
					},"html");	
				//}
			}
		}
	}
	
	function deletePro(rid) {
		if(confirm("Are you sure you want to delete this record?") == true) {
			$.post("src/sjerp.php", { mod: "deletePro", rid: rid, sid: Math.random() }, function() { 
				alert("Merchandise Record Successfully Deleted!"); 
				parent.closeDialog("#itemdetails");	
				parent.showItems();
			});
		}
	}
	
	
	function getMyCode() {
		$.post("src/sjerp.php", { mod: "getIcode", mid: $("#item_category").val(), sgroup: $("#item_sgroup").val(), sid: Math.random() }, function(data) {
			$("#item_code").val(data);
		});
	}
	
	function getSgroup(mgroup) {
		$.post("src/sjerp.php", { mod: "getSgroup", mgroup: mgroup, sid: Math.random() }, function(data) {
			$("#item_sgroup").html(data);
		},"html");
	}
	
	function changeMod(mod) {
		document.changeModPage.mod.value = mod;
		document.changeModPage.submit();
	}

	function viewPODetails() {
		var table = $("#polist").DataTable();		
		var arr = [];
	    $.each(table.rows('.selected').data(), function() {
		   arr.push(this["po_no"]);
	    });

		if(!arr[0]) {
			parent.sendErrorMessage("Please select a record to view by clicking the line row...");
		} else {
			parent.viewPO(arr[0]);
		}
	}

	function viewRRDetails() {
		var table = $("#rrlist").DataTable();		
		var arr = [];
	    $.each(table.rows('.selected').data(), function() {
		   arr.push(this["rr_no"]);
	    });

		if(!arr[0]) {
			parent.sendErrorMessage("Please select a record to view by clicking the line row...");
		} else {
			parent.viewRR(arr[0]);
		}
	}

	function getCgroup(sgrp_id) {
		$.post("src/sjerp.php", { mod: "getCgroup", sgrp_id: sgrp_id, sid: Math.random() }, function(data) {
			$("#item_cgroup").html(data);
		},"html");
	}

	function computeSRP(cost) {
		var cost = parseFloat(parent.stripComma(cost));
		var srp = cost * 1.30;
		    srp = srp.toFixed(2);

		$("#srp").val(parent.kSeparator(srp));

	}
	
</script>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<form name="merchandise" id="merchandise">
	<input type="hidden" name="rid" id="rid" value="<?php echo $res['record_id']; ?>">
	<table width="100%" cellspacing="0" cellpadding="5" style="border-bottom: 1px solid black; margin-bottom: 5px;">
		<tr>
			<td align=left>
				<?php
					if($_GET['mod'] == 1) {
						echo '<a href="#" onClick="savePInfo(\'' . $_GET['id'] . '\');" class="topClickers"><img src="images/icons/floppy.png" width=18 height=18 align=absmiddle />&nbsp;Add/Save Changes Made</a>';
						if($res['record_id'] != "") {
							echo '&nbsp;&nbsp;<a href="#" onClick="deletePro(\'' . $_GET['id'] . '\');" class="topClickers"><img src="images/icons/delete.png" width=18 height=18 align=absmiddle />&nbsp;Delete Record</a>';
						}
					}
				?>
			</td>
			<td align=right>
				<?php if($_GET['id']) { setNavButtons($_GET[id]); } ?>
			</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" >
		<tr>
			<td width="10%" align=left class="spandix-l">
				Item Code :
			</td>
			<td width="40%" align=left>
				<input type="text" name="item_code" id="item_code" style="width: 80%;" class="nInput" value="<?php echo $res['item_code']; ?>" readonly>
			</td>
			<td width="10%" class="spandix-l">
				Category :
			</td>
			<td width="40%">
				<select name="item_category" id="item_category" style="width: 70%;" class="nInput" <?php if($res['record_id'] == '') { echo "onChange=\"javascript: getMyCode(); getSgroup(this.value);\""; } ?>>
					<option value="">- Select Category -</option>
					<?php
						$mit = $con->dbquery("select mid,mgroup from options_mgroup;");
						while(list($o,$oo) = $mit->fetch_array()) {
							echo "<option value='$o' ";
								if($res['category'] == $o) { echo "selected"; }
							echo ">$oo</option>";
						}
						unset($mit);
					?>
				</select>
			</td>
		</tr>
		<tr><td height=1></td></tr>
		<tr>
			<td width="10%" align=left class="spandix-l">
				Short Description :
			</td>
			<td width="40%" align=left>
				<input type="text" name="item_description" id="item_description" style="width: 80%;" class="nInput" value="<?php echo $res['description']; ?>">
			</td>
			<td width="10%" class="spandix-l">Group :</td>
			<td width="40%">
				<select name="item_sgroup" id="item_sgroup" style="width: 70%;" class="nInput" <?php if($res['record_id'] == '') { echo "onChange=\"javascript: getMyCode(); getCgroup(this.value);\""; } ?>>
					<option value="0">- Not Applicable -</option>
					<?php
						
						if($res['record_id'] != '') {
							$mit = $con->dbquery("select sid, if(`file_status`='Deleted',concat(sgroup,' (Deleted'),sgroup) as sgroup from options_sgroup where 1=1 and mid = '$res[category]' order by sgroup asc;");
							while(list($o,$oo) = $mit->fetch_array()) {
								echo "<option value='$o' ";
									if($res['subgroup'] == $o) { echo "selected"; }
								echo ">$oo</option>";
							}
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=1></td></tr>
		<tr>
			<td width="10%" align=left class="spandix-l" valign=top>
				Brand :
			</td>
			<td width="40%" align=left valign=top>
				<input type="text" name="item_brand" id="item_brand" style="width: 80%;" class="nInput" value="<?php echo $res['brand']; ?>">
			</td>
			<td width="10%" class="spandix-l" valign=top>Sub-group :</td>
			<td width="40%">
				<select name="item_cgroup" id="item_cgroup" style="width: 70%;" class="nInput" <?php if($res['record_id'] == '') { echo "onChange=\"javascript: getMyCode();\""; } ?>>
					<option value="0">- Not Applicable -</option>
					<?php
						
						if($res['record_id'] != '') {
							$mit = $con->dbquery("select id, cgrp_name from options_cgroup where 1=1 and sgrp_id = '$res[subgroup]' order by cgrp_name asc;");
							while(list($o,$oo) = $mit->fetch_array()) {
								echo "<option value='$o' ";
									if($res['group'] == $o) { echo "selected"; }
								echo ">$oo</option>";
							}
						}
					?>
				</select><input type="checkbox" name="status" id="status" <?php if($res['active'] == 'N') { echo "checked"; } ?> value="N"><span class="spandix-l">&nbsp;Inactive</span>
			</td>
		</tr>
		<tr><td height=1></td></tr>
		<tr>
			<td width="10%" align=left class="spandix-l" valign=top>
				Barcode :
			</td>
			<td width="40%" align=left valign=top>
				<input type="text" name="item_indcode" id="item_indcode" style="width: 80%;" class="nInput" value="<?php echo $res['indcode']; ?>">
			</td>
			<td width="10%" class="spandix-l" valign=top></td>
			<td width="40%">
				<input type="checkbox" name="status" id="status" <?php if($res['active'] == 'N') { echo "checked"; } ?> value="N"><span class="spandix-l">&nbsp;Inactive</span>
			</td>
		</tr>
	</table>
	<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-top: 10px;">
		<tr>
			<td style="padding: 0px 0px 1px 0px;">
				<div id="custmenu" align=left class="ddcolortabs">
					<ul class=float2>
						<li><a href="#" <?php getMod("1",$_GET['mod']); ?> onclick="javascript: changeMod(1);"><span id="tbbalance3">General Product Info</span></a></li>
						<?php if($_GET['id'] != '') { ?>
							<li><a href="#" <?php getMod("2",$_GET['mod']); ?> onclick="javascript: changeMod(2);"><span id="tbbalance2">Pending Purchase Orders</span></a></li>
							<li><a href="#" <?php getMod("3",$_GET['mod']); ?> onclick="javascript: changeMod(3);"><span id="tbbalance2">Recent Deliveries</span></a></li>
							<li><a href="#" <?php getMod("4",$_GET['mod']); ?> onclick="javascript: changeMod(4);"><span id="tbbalance2">Inventory Stockcard</span></a></li>
						<?php } ?>
					</ul>
				</div>
			</td>
		</tr>
	</table>
	
	<?php switch($_GET['mod']) { case "1": ?>
	<table width="100%" cellpadding=0 cellspacing=1 class="td_content" style="padding:10px;" border=0>
		<tr>
			<td width=20% class="spandix-l" valign=top>Full Product Description:</td>
			<td width=85% colspan=3><textarea name="item_fdescription" id="item_fdescription" style="width: 100%;" rows=1><?php echo $res['full_description']; ?></textarea></td>
		</tr>
		<tr>
			<td width=20% class="spandix-l">Stocking Unit :</td>
			<td width=35%> 
				<select name="item_unit" id="item_unit" style="width: 200px;" class="nInput">
					<?php
						$iun = $con->dbquery("select unit, description from options_units;");
						while(list($u,$uu) = $iun->fetch_array()) {
							echo "<option value='$u' ";
								if($res['unit'] == $u) { echo "selected"; }
							echo ">$uu</option>";
						}
					?>
				</select>
			</td>
			<td width=20% class="spandix-l">Revenue Account :</td>
			<td width=35% align=right>
				<select name="rev_acct" id="rev_acct" style="width: 100%;" class="nInput">
					<option value="">- NA -</option>
					<?php
						$iun = $con->dbquery("select acct_code,description from acctg_accounts where acct_grp in ('9','10') order by description;");
						while(list($aa,$ab) = $iun->fetch_array()) {
							echo "<option value='$aa' ";
							if($res['rev_acct'] == $aa) { echo "selected"; }
							echo ">[$aa] $ab</option>";
						}
					?>
				</select>
			</td>
		</tr>
		
		<tr>
			<td width=20% class="spandix-l">Unit Cost :</td>
			<td width=35%>
				<input type="text" name="item_unitcost" id="item_unitcost" style="width: 200px; text-align: right;"  value='<?php echo number_format($res['unit_cost'],2); ?>' class="nInput" <?php echo $isReadOnly; ?> onchange = "javascript: computeSRP(this.value);"> 
			</td>
			<td width=20% class="spandix-l">Asset/Inventory Account :</td>
			<td width=35% align=right>
				<select name="asset_acct" id="asset_acct" style="width: 100%;" class="nInput">
					<option value="">- NA -</option>
					<?php
						$iun = $con->dbquery("select acct_code,description from acctg_accounts where acct_grp in ('3','4') and description not like ('%accu%') order by description;");
						while(list($cc,$cd) = $iun->fetch_array()) {
							echo "<option value='$cc' ";
							if($res['asset_acct'] == $cc) { echo "selected"; }
							echo ">[$cc] $cd</option>";
						}
					?>
				</select>
			</td>
		</tr>
		
		<tr>
			<td width=15% class="spandix-l">Selling Price :</td>
			<td width=30%>
				<input type="text" name="srp" id="srp" style="width: 200px; text-align: right;" value='<?php echo number_format($res['srp'],2); ?>' class="nInput" <?php echo $isReadOnly; ?>>
			</td>
			<td width=20% class="spandix-l">Purchases/Expense Account :</td>
			<td width=35% align=right>
				<select name="exp_acct" id="exp_acct" style="width: 100%;" class="nInput">
					<option value="">- NA -</option>
					<?php
						$iun = $con->dbquery("SELECT acct_code,description FROM acctg_accounts WHERE acct_grp IN ('12','13') order by description;");
						while(list($bb,$bc) = $iun->fetch_array()) {
							echo "<option value='$bb' ";
							if($res['exp_acct'] == $bb) { echo "selected"; }
							echo ">[$bb] $bc</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">With Expiration Date</td>
			<td width=30%>
				<select name="with_expiration" id="with_expiration" style="width: 200px;" class="nInput">
					<option value="N" <?php if($res['with_expiration'] == 'N') { echo "selected"; } ?>>- No -</option>
					<option value="Y" <?php if($res['with_expiration'] == 'Y') { echo "selected"; } ?>>- Yes -</option>
				</select>


			</td>
			<td width=20% class="spandix-l">Cost of Services Account :</td>
			<td width=35% align=right>
				<select name="cogs_acct" id="cogs_acct" style="width: 100%;" class="nInput">
					<option value="">- NA -</option>
					<?php
						$iun = $con->dbquery("select acct_code,description from acctg_accounts where acct_grp in ('14') order by description;");
						while(list($bb,$bc) = $iun->fetch_array()) {
							echo "<option value='$bb' ";
							if($res['cogs_acct'] == $bb) { echo "selected"; }
							echo ">[$bb] $bc</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">VAT Exempted :</td>
			<td width=30%>
				<select name="vat_exempt" id="vat_exempt" style="width: 30%;" class="nInput">
					<option value = "N" <?php if($res['vat_exempt'] == 'N') { echo "selected"; } ?>>- No -</option>
					<option value = "Y" <?php if($res['vat_exempt'] == 'Y') { echo "selected"; } ?>>- Yes -</option>
				</select>
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=35%>
				
			</td>
		</tr>
		<tr>
			<td colspan=2 width=45% align=left><hr width=70% align=left></hr></td>
			<td colspan=2 width=55% align=right><hr width=100% align=right></hr></td>
		</tr>

		<tr>
			<td width=15% class="spandix-l"></td>
			<td width=30%>
				
			</td>
			<td width=55% class="spandix-l" colspan=2 align=right><b>Qty-On-Hand : &nbsp;&nbsp;</b><?php echo number_format($end,2);?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Qty Available : &nbsp;&nbsp;</b><?php if($end < 0) { echo "0"; } else { echo number_format($end,2); }?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Qty-On-PO : &nbsp;&nbsp;</b><?php echo number_format($onPO,2);?></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l"></td>
			<td width=30%>
			
			</td>
			<td width=55% colspan=2 align=right class="spandix-l">Minimum Inventory : <input type="text" name="item_mininv" id="item_mininv" style="width: 30%;text-align: right;" class="nInput" value="<?php echo number_format($res['minimum_level']); ?>"></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l"></td>
			<td width=30%>
				
			</td>
			<td width=55% colspan=2 align=right class="spandix-l">Reorder Point : <input type="text" name="item_reorder" id="item_reorder" style="width: 30%;text-align: right;" class="nInput" value="<?php echo number_format($res['reorder_pt'],2); ?>"></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l"></td>
			<td width=30%>
				
			</td>
			<td width=55% colspan=2 align=right class="spandix-l">Inventory Beginning : <input type="text" name="item_beginning" id="item_beginning" style="width: 30%;text-align: right;" class="nInput" value="<?php echo number_format($pi[0],2); ?>" readonly></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l"></td>
			<td width=30%></td>
			<td width=55% colspan=2 align=right class="spandix-l">
				Preferred Supplier : <input type="text" class="inputSearch2" style="width: 15%; padding-left: 22px;margin-bottom:2px;" id="supplier" name="supplier" value="<?php echo $res['supplier']; ?>"> <input type="text" name="supplier_name" id="supplier_name" style="width: 50%;" class="nInput" value="<?php echo $sname; ?>">
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=35%></td>
		</tr>
	</table>
	<?php break; case "2": ?>
		<table id="polist" style="font-size:11px;">
			<thead>
				<tr>
					<th></th>
					<th width=10%>PO #</th>
					<th width=10%>DATE</th>
					<th width=20%>SUPPLIER</th>
					<th>TRANSACTION REMARKS</th>
					<th width=15%>PENDING QTY</th>
				</tr>
			</thead>
		</table>
		<table>
			<tr>
				<td align=left colspan=2 style="padding-top:5px;">
					<a href="#" class="topClickers" onClick="javascript:viewPODetails();"><img src="images/icons/invoice.png" width=16 height=16 border=0 align="absmiddle">&nbsp;View Transaction Details</a>
				</td>
			</tr>
		</table>

	<?php break; case "3": ?>
		<table id="rrlist" style="font-size:11px;">
			<thead>
				<tr>
					<th></th>
					<th width=8%>RR #</th>
					<th width=10%>DATE</th>
					<th width=25%>SUPPLIER</th>
					<th>TRANSACTION REMARKS</th>
					<th width=10%>S.I/D.R #</th>
					<th width=12%>S.I/D.R DATE</th>
				</tr>
			</thead>
		</table>
		<table>
			<tr>
				<td align=left colspan=2 style="padding-top:5px;">
					<a href="#" class="topClickers" onClick="javascript:viewRRDetails();"><img src="images/icons/invoice.png" width=16 height=16 border=0 align="absmiddle">&nbsp;View Transaction Details</a>
				</td>
			</tr>
		</table>
	<?php break; } ?>
</form>
<div id="iCopy" style="display: none; padding: 10px;">
	<table width=100% cellpadding=0 cellspacing=0>
		<tr><td class="spandix-l" width=100%>Main Stock Code: <input type="text" name="mstockcode" id="mstockcode" class="nInput" style="width: 200px;">
	</table>
</div>
<form name="changeModPage" id="changeModPage" action="items.details.php" method="GET" >
	<input type="hidden" name="id" id="id" value="<?php echo $_GET['id']; ?>">
	<input type="hidden" name="mod" id="mod">
</form>
</body>
</html>
<?php mysql_close($con);