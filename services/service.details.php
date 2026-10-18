<?php
	session_start();

	// ini_set("display_errors","On");
	require_once "../handlers/_generics.php";
	$con = new myDB;
	$res = array();
	
	if(isset($_GET['id']) && $_GET['id'] != "") { 
		$res = $con->getArray("select * from services_master where id = '$_GET[id]';"); 
	}
	
	function getMod($def,$mod) {
		if($def == $mod) { return "class=\"float2\""; }
	}
	
	function setNavButtons($id) {
		global $con;
		
		$first = 1;	$fwd = $id+1; $prev = $id-1;

		list($last) = $con->getArray("select id from services_master order by id desc limit 1;");
		if($prev > 0)
			$nav = $nav . "<a href=# onclick=\"parent.showServiceInfo('$prev');\"><img src='../images/resultset_previous.png'  title='Previous Record' /></a>";
		if($fwd > 0) 
			$nav = $nav . "<a href=# onclick=\"parent.showServiceInfo('$fwd');\"><img src='../images/resultset_next.png' 'title='Next Record' /></a>";
		echo "<a href=# onclick=\"parent.showServiceInfo('$first');\"><img src='../images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.showServiceInfo('$last');\"><img src='../images/resultset_last.png' title='Last Record' /></a>";
	}
	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>RJZ Fix Hub Repair Shop System Ver. 1.0a</title>
<link href="../style/style.css" rel="stylesheet" type="text/css" />
<link href="../ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="../ui-assets/datatables/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="../ui-assets/jquery/jquery-1.12.3.js"></script>
<script type="text/javascript" charset="utf8" src="../ui-assets/themes/smoothness/jquery-ui.js"></script>
<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/dataTables.jqueryui.js"></script>
<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/dataTables.select.js"></script>
<script type="text/javascript" charset="utf8" src="../js/tableH.js"></script>
<script>
	// $(document).ready(function(){

		
	// });
	
	
	function savePInfo(rid) {

		if (!confirm("Are you sure you want to save changes to this service profile?")) {
			return;
		}

		var msg = "";

		if ($("#item_code").val() == "") {
			msg += "- Invalid Service Code or Service Code is empty<br/>";
		}

		if ($("#unit_price").val() == "" ||
			isNaN(parent.stripComma($("#unit_price").val()))) {
			msg += "- Invalid Unit Price<br/>";
		}

		if (msg != "") {
			parent.sendErrorMessage(msg);
			return;
		}

		$.post("services.datacontrol.php", {
			mod: "checkServiceDupCode",
			rid: rid,
			item_code: $("#item_code").val(),
			barcode: $("#barcode").val(),
			sid: Math.random()
		}, function(data) {

			data = $.trim(data);

			if (data == "NODUPLICATE") {

				var url = "mod=saveSInfo&" + $("#merchandise").serialize();

				$.post("services.datacontrol.php", url, function(result) {

					alert("Record Successfully Saved!");
					parent.closeDialog("#itemdetails");
					parent.showServices($("#item_code").val());

				}).fail(function(xhr) {
					parent.sendErrorMessage(
						"Unable to save service record. Check the PHP error log."
					);

					if (window.console) {
						console.log(xhr.responseText);
					}
				});

			} else if (data == "DUPLICATE") {

				parent.sendErrorMessage(
					"Duplicate Service Code detected!"
				);

			} else {

				parent.sendErrorMessage(
					"Unable to validate Service Code. Response: " + data
				);
			}

		}, "text").fail(function(xhr) {

			parent.sendErrorMessage(
				"Unable to check Service Code. Check services.datacontrol.php."
			);

			if (window.console) {
				console.log(xhr.responseText);
			}
		});
	}

	function deletePro(rid) {
		/* Check for existing Transactions */
		$.post("services.datacontrol.php", { mod: "checkServicesTransaction", code: $("#item_code").val(), sid: Math.random() }, function(result) {
			if(result == "notOk") {
				parent.sendErrorMessage("Unable to delete this record as it appears it has already subsequent transactions.");
			} else {
				if(confirm("Are you sure you want to delete this record?") == true) {
					$.post("services.datacontrol.php", { mod: "deleteService", rid: rid, sid: Math.random() }, function() { 
						alert("Record Successfully Deleted!"); 
						parent.closeDialog("#itemdetails");	
						parent.showServices();
					});
				}

			}

		},"html");
	}
	
	
	function getMyCode() {
		$.post("services.datacontrol.php", { mod: "getServiceCode", mid: $("#item_category").val(), subcat: $("#subcategory").val(), sid: Math.random() }, function(data) {
			$("#item_code").val(data);
		});
	}
	
	function getSgroup(parent) {
		$.post("services.datacontrol.php", { mod: "getServiceSubgroup", parent: parent, sid: Math.random() }, function(data) {
			$("#subcategory").html(data);
		},"html");
	}
	
	
	function computeLabTat() {
		var msg = "";
		var coll = $("#collection_tat").val();
		var acc = $("#accession_tat").val();
		var pro = $("#processing_tat").val();

		if(isNaN(coll) == true) { msg = msg + "- Invalid Collection Turn-around value.<br/>"; }
		if(isNaN(acc) == true) { msg = msg + "- Invalid Accession Turn-around value.<br/>"; }
		if(isNaN(pro) == true) { msg = msg + "- Invalid Processing Turn-around value.<br/>"; }

		if(msg != '') {
			parent.sendErrorMessage(msg);
		} else {
			var total = parseFloat(coll) + parseFloat(acc) + parseFloat(pro);
			    total = total.toFixed(2);
			
			$("#lab_tat").val(total);
		}
	}

</script>
<style>
	.dataTables_wrapper {
		display: inline-block;
		font-size: 11px; padding: 3px;
		width: 99%; 
	}
	
	table.dataTable tr.odd { background-color: #f5f5f5;  }
	table.dataTable tr.even { background-color: white; }
	.dataTables_filter input { width: 250px; }
</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<form name="merchandise" id="merchandise">
	<input type="hidden" name="rid" id="rid" value="<?php echo $res['id']; ?>">
	<table width="100%" cellspacing="0" cellpadding="5" style="border-bottom: 1px solid black; margin-bottom: 5px;">
		<tr>
			<td align=left>
				<button type="button"
					class="ui-button ui-widget ui-corner-all"
					onclick="savePInfo(<?php echo isset($res['id']) ? intval($res['id']) : 0; ?>);">
					<span class="ui-icon ui-icon-disk"></span> Save Record
				</button>
				<button type = "button" class="ui-button ui-widget ui-corner-all" onClick="deletePro(\''. $res['id'] . '\');">
					<span class="ui-icon ui-icon-closethick"></span> Delete Record
				</button>
			</td>
			<td align=right>
				<?php if($_GET['id']) { setNavButtons($_GET['id']); } ?>
			</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" >
		<tr>
			<td width="10%" align=left class="spandix-l">
				Service Code :
			</td>
			<td width="40%" align=left>
				<input type="text" name="item_code" id="item_code" style="width: 80%;" class="nInput" value="<?php echo $res['item_code']; ?>" readonly>
			</td>
			<td width="10%" class="spandix-l">
				Product Category :
			</td>
			<td width="40%">
				<select name="item_category" id="item_category" style="width: 70%;" class="nInput" <?php if($res['id'] == '') { echo "onChange=\"javascript: getMyCode(); getSgroup(this.value);\""; } ?>>
					<option value="">- Select Category -</option>
					<?php
						$mit = $con->dbquery("select id,category from options_servicecat;");
						while(list($o,$oo) = $mit->fetch_array()) {
							echo "<option value='$o' ";
								if($res['item_category'] == $o) { echo "selected"; }
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
				<input type="text" name="short_description" id="short_description" style="width: 80%;" class="nInput" value="<?php echo $res['short_description']; ?>">
			</td>
			<td width="10%" class="spandix-l"></td>
			<td width="40%">
				<select name="subcategory" id="subcategory" style="width: 70%;" class="nInput" <?php if($res['id'] == '') { echo "onChange=\"javascript: getMyCode();\""; } ?>>
					<option value="">- Not Applicable -</option>
					<?php
						
						if($res['id'] != '') {
							$mit = $con->dbquery("select id, subcategory from options_servicesubcat where 1=1 and parent_id = '$res[item_category]' order by subcategory asc;");
							while(list($o,$oo) = $mit->fetch_array()) {
								echo "<option value='$o' ";
									if($res['subcategory'] == $o) { echo "selected"; }
								echo ">$oo</option>";
							}
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=1></td></tr>
		<tr>
			<td width="10%" align=left class="spandix-l">
				Abbreviation. :
			</td>
			<td width="40%" align=left>
				<input type="text" name="abbreviation" id="abbreviation" style="width: 80%;" class="nInput" value="<?php echo $res['abbreviation']; ?>">
			</td>
			<td width="10%" class="spandix-l"></td>
			<td width="40%" class="spandix-l"><input type="checkbox" name="file_status" id="file_status" <?php if($res['file_status'] == 'N') { echo "checked"; } ?> value="N">&nbsp;Inactive</td>
		</tr>
		<tr><td height=1></td></tr>
		<tr>
			<td width="10%" align=left class="spandix-l">
				Barcode :
			</td>
			<td width="40%" align=left>
				<input type="text" name="barcode" id="barcode" style="width: 80%;" class="nInput" value="<?php echo $res['barcode']; ?>">
			</td>
			<td width="10%" class="spandix-l"></td>
			<td width="40%" class="spandix-l"></td>
		</tr>
	</table>
	<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-top: 10px;">
		<tr>
			<td style="padding: 0px 0px 1px 0px;">
				<div id="custmenu" align=left class="ddcolortabs">
					<ul class=float2>
						<li><a href="#"><span id="tbbalance1">General Info</span></a></li>
					</ul>
				</div>
			</td>
		</tr>
	</table>
	<table width="100%" cellpadding=0 cellspacing=1 class="td_content" style="padding:10px;" border=0>
		<tr>
			<td width=20% class="spandix-l" valign=top>Full Description or FAQ:</td>
			<td width=85% colspan=3><textarea name="fulldescription" id="fulldescription" style="width: 100%;" rows=3><?php echo $res['fulldescription']; ?></textarea></td>
		</tr>
		<tr>
			<td width=15% class="spandix-l">Unit Price :</td>
			<td width=30%>
				<input type="text" name="unit_price" id="unit_price" style="width: 200px; text-align: right;" value='<?php echo number_format($res['unit_price'],2); ?>' class="nInput" <?php echo $isReadOnly; ?>>
			</td>
		</tr>
		<tr>
			<td width="20%" class="spandix-l">Service Type :</td>
			<td width="30%">
				<select name="item_service_type" id="item_service_type"
						class="nInput" style="width: 200px;">
					<option value="REPAIR" <?php if($res['item_service_type'] == 'REPAIR') { echo "selected"; } ?>>Repair</option>
					<option value="MAINTENANCE" <?php if($res['item_service_type'] == 'MAINTENANCE') { echo "selected"; } ?>>Maintenance</option>
					<option value="INSTALLATION" <?php if($res['item_service_type'] == 'INSTALLATION') { echo "selected"; } ?>>Installation</option>
					<option value="DIAGNOSTIC" <?php if($res['item_service_type'] == 'DIAGNOSTIC') { echo "selected"; } ?>>Diagnostic</option>
					<option value="OTHER" <?php if($res['item_service_type'] == 'OTHER') { echo "selected"; } ?>>Other</option>
				</select>
			</td>
			<td width="20%" class="spandix-l">Pricing Method :</td>
			<td width="30%">
				<select name="pricing_method" id="pricing_method"
						class="nInput" style="width: 200px;">
					<option value="FIXED" <?php if($res['pricing_method'] == 'FIXED') { echo "selected"; } ?>>Fixed Fee</option>
					<option value="STARTING" <?php if($res['pricing_method'] == 'STARTING') { echo "selected"; } ?>>Starting At</option>
					<option value="ASSESSMENT" <?php if($res['pricing_method'] == 'ASSESSMENT') { echo "selected"; } ?>>Assessment Required</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="spandix-l">Estimated Duration :</td>
			<td>
				<input type="number" name="estimated_time"
					id="estimated_time" min="0" value="<?php echo $res['estimated_time']; ?>"
					class="nInput" style="width: 200px;">
				<span class="spandix-l">days</span>
			</td>
			<td class="spandix-l">Minimum Charge :</td>
			<td>
				<input type="text" name="minimum_charge"
					id="minimum_charge" value="0.00"
					class="nInput"
					style="width: 200px; text-align: right;">
			</td>
		</tr>
		<tr>
			<td class="spandix-l">Warranty Period :</td>
			<td>
				<input type="number" name="warranty_days"
					id="warranty_days" min="0" value="<?php echo $res['warranty_days']; ?>"
					value="0" class="nInput"
					style="width: 200px;">
				<span class="spandix-l">days</span>
			</td>
			<td class="spandix-l">Service Options :</td>
			<td>
				<label class="spandix-l">
					<input type="checkbox" name="requires_diagnosis"
						id="requires_diagnosis" value="Y" <?php if($res['requires_diagnosis'] == 'Y') { echo "checked"; } ?>>
					Requires Diagnosis
				</label>
				&nbsp;&nbsp;&nbsp;
				<label class="spandix-l">
					<input type="checkbox" name="requires_approval"
						id="requires_approval" value="Y" <?php if($res['requires_approval'] == 'Y') { echo "checked"; } ?>>
					Requires Customer Approval
				</label>
			</td>
		</tr>
		<tr>
			<td class="spandix-l" valign="top">Service Instructions :</td>
			<td colspan="3">
				<textarea name="service_instructions"
						id="service_instructions"
						rows="3" style="width: 100%;"><?php echo $res['service_instructions']; ?></textarea>
			</td>
		</tr>

	</table>
</body>
</html>