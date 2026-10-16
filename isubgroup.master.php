<?php require_once("handlers/initDB.php"); $con = new myDB; ?>

<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Redviper Ventures & Development Corp. ERP System Ver. 2.0</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script>
		var id;
		
		function init() {
			var table = $("#itemlist").DataTable();
			$.each(table.rows('.selected').data(), function() {
				id = this["id"];
			});	
		}
		
		$(document).ready(function() {
			$('#itemlist').dataTable({
				"scrollY":  "220px",
				"select":	'single',
				"pagingType": "full_numbers",
				"bProcessing": true,
				"sAjaxSource": "data/listings.php?mod=sgroup&sid="+Math.round()+"",
				"aoColumns": [
				  { mData: 'id' },
				  { mData: 'code' },
				  { mData: 'sgroup' },
				  { mData: 'mgroup' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1] },
					{ "targets": [0], "visible": false, "searchable": false }
				]
			});
		});
	
		function newRecord() {
			
			$(document.recordDetails)[0].reset();
			$('#code').attr('readonly', false);
			$("#recordDetails").dialog({ title: "New Record", width: 480,resizable: false, modal: true, buttons: {
				"Save": function() {
							if($("#code") == "" && $("#description").val() == "") {
								parent.sendErrorMessage("Required data missing!");
							} else {
								$.post("src/sjerp.php", { mod: "newSGroup", code: $("#code").val(), description: $("#description").val(), maingrp: $("#maingrp").val(), sid: Math.random() }, function(result) {
									if(result == "DUPLICATE") {
										parent.sendErrorMessage("Unable to save this record as it appears that the short code specified is already in use..");
									} else {
										alert("Record Successfully Saved!");
										parent.showSgroup();
									}
								});
							}
					}
			}});
		}
	
		function getCInfo() {
			init();
			
			if(!id || id == undefined) {
				parent.sendErrorMessage("Please select a record from the list, and once highlighted, press  \"<b><i>View/Update Record</i></b>\" button again...");
			} else {
				$.post("src/sjerp.php", { mod: "retrieveSGroup", id: id, sid: Math.random() }, function(rset) {
					$("#sid").val(id);
					$("#code").val(rset['code']);
					$("#description").val(rset['sgroup']);
					$("#maingrp").val(rset['mid']);
					$('#code').attr('readonly', true);
					
					$("#recordDetails").dialog({ title: "Sub-group Details", width: 480,resizable: false, modal: true, buttons: {
						"Update Record": function() {
								if($("#group_code") == "" && $("#description").val() == "") {
									parent.sendErrorMessage("Required data missing!");
								} else {
									$.post("src/sjerp.php", { mod: "updateSGroup", id: id, code: $("#code").val(), description: $("#description").val(), maingrp: $("#maingrp").val(), sid: Math.random() }, function(result) {
										alert("Record Successfully Saved!");
										parent.showSgroup();
									});
								}
						},
						"Delete Record": function() {
							if(confirm("Are you sure you want to delete this record?") == true) {
								$.post("src/sjerp.php", { mod: "deleteSGroup", id: id, sid: Math.random() }, function() {
									alert("Record successfully marked as deleted. Record remains in the database but unusable for future use.");
									parent.showSgroup();
								});
							}
						}
					}});
					
				},"json");
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
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
	<div id="main">
		<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-bottom: 5px;">
			<tr>
				<td align=left style="padding-right: 20px;">
					<a href="#" class="topClickers" onClick="newRecord();"><img src="images/icons/add.png" width=18 height=18 align=absmiddle />&nbsp;Add Group</a>
					<a href="#" class="topClickers" onClick="getCInfo();"><img src="images/icons/edit.png" width=18 height=18 align=absmiddle />&nbsp;View/Update Selected Record</a>
					<a href="#" class="topClickers" onClick="parent.showSgroup();"><img src="images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;Reload List</a>
				</td>
			</tr>
		</table>
		<table id="itemlist" style="font-size:11px;">
			<thead>
				<tr>
					<th>ID</th>
					<th width=25%>CODE</th>
					<th>DESCRIPTION</th>
					<th width=25%>MAIN GROUP</th>
				</tr>
			</thead>
		</table>
	</div>
	<div id="recordDetails" style="display:none;">
		<form name="recordDetails" id="recordDetails">
			<table border="0" cellpadding="0" cellspacing="0" width=100%>
				<tr>
					<td width=35%><span class="spandix-l">Group Code :</span></td>
					<td>
						<input type="text" id="code" name="code" class="nInput" style="width: 80%;" />
						<input type="hidden" id="sid" value="sid">
					</td>
				</tr>
				<tr><td height=4></td></tr>
				<tr>
					<td width=35%><span class="spandix-l">Description :</span></td>
					<td>
						<input type="text" id="description" name="description" class="nInput" style="width: 80%;" />
					</td>
				</tr>
				<tr><td height=4></td></tr>
				<tr>
					<td width=35%><span class="spandix-l">Main Group :</span></td>
					<td>
						<select id="maingrp" name="maingrp" style="width: 80%; font-size: 11px;" class="nInput" />
							<?php
								$grp = $con->dbquery("SELECT mid,mgroup FROM options_mgroup order by mgroup;");
								while($row = $grp->fetch_array()) {
									print "<option value='$row[mid]'>$row[mgroup]</option>";
								}
							?>
						</select>
					</td>
				</tr>
			</table>
		</form>
	</div>
</body>
</html>