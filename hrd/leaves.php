<?php
	//ini_set("display_errors","On");
	require_once "../handlers/initDB.php";
	$mydb = new myDB;
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Prime Care Cebu Payroll System Ver. 1.0b</title>
	<link href="../ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="../style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="../ui-assets/datatables/css/jquery.dataTables.css">
	<script language="javascript" src="../ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="../ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="../js/jquery.dialogextend.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/dataTables.select.js"></script>
	<script>

		var UID = "";
		
		function init() {
			var table = $("#itemlist").DataTable();
			var arr = [];
		   $.each(table.rows('.selected').data(), function() {
			   arr.push(this["id"]);
		   });
		   UID = arr[0];
		}
		
		$(document).ready(function() { 
		
			$("#date").datepicker(); $("#dateFrom").datepicker(); $("#dateTo").datepicker();

			$('#emp_name').autocomplete({
				source:'../suggestEmployee.php', 
				minLength:3,
				select: function(event,ui) {
					$("#emp_id").val(ui.item.emp_id);
				}
			});
			
			$('#itemlist').dataTable({
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"bProcessing": true,
				"sAjaxSource": "listings.php?mod=leaves&sid="+Math.random()+"",
				"aoColumns": [
				  { mData: 'id' },
				  { mData: 'emp_id' },
				  { mData: 'name' },
				  { mData: 'tdate' },
				  { mData: 'length' },
				  { mData: 'range' },
				  { mData: 'type' },
				  { mData: 'reasons' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1,3,4,5,6]},
					{ "targets": [0], "visible": false }
				]
			});
			
		});
		
		function viewRecord() {
			
			init();

			
		
			if(!UID) {
				parent.sendErrorMessage("Unable to retrieve record. Please select a record from the list, and once highlighted, press  \"<b><i>View/Edit Record Details</i></b>\" button again...");
			} else {
				$.post("misc-data.php", { mod: "getLeave", id: UID, sid: Math.random() }, function(ech) {
					$("#rid").val(ech['trans_id']);
					$("#emp_id").val(ech['emp_id']);
					$("#emp_name").val(ech['emp_name']);
					$("#date").val(ech['tdate']);
					$("#dateFrom").val(ech['dtf']);
					$("#dateTo").val(ech['dt2']);
					$("#length").val(ech['length']);
					$("#type").val(ech['leave_type']);
					$("#reasons").html(ech['reasons']);
					$("#address").html(ech['address_on_leave']);
					$("#w_pay").val(ech['w_pay']);
					
					$("#record").dialog({title: "Record Details", width: 440, resizable: false, modal: true, buttons: { 
						"Save Changes": function() {
							$.post("misc-data.php", { mod: "updateLeave", rid: $("#rid").val(), emp_id: $("#emp_id").val(), emp_name: $("#emp_name").val(), date: $("#date").val(), dateFrom: $("#dateFrom").val(), dateTo: $("#dateTo").val(), length: $("#length").val(), type: $("#type").val(), reasons: $("#reasons").val(), address: $("#address").val(), w_pay: $("#w_pay").val(), sid: Math.random() }, function() {
								alert("Record Successfully Updated!");
								parent.showLeaves();
							});
						},
						"Close": function() { $("#record").dialog("close"); }
					}}).dialogExtend({
						"closable" : true,
						"maximizable" : false,
						"minimizable" : true
					});
				},"json");
			}
		}
		
		function newRecord() {
			$(document.irec)[0].reset(); $("#reasons").html(''); $("#address").html('');
			$("#record").dialog({title: "New Record", width: 440, resizable: false, modal: true, buttons: {
				"Save Record": function() {
					var msg = "";
					if($("#date").val() == "") { msg = msg + "<b>Date</b> is invalid or empty...<br/>"; }
					if($("#reasons").val() == "") { msg = msg + "Employee's reason for leave of absence must be duly noted for future reference."; }
					if($("#dateFrom").val() == "" || $("#dateTo").val() == "") { msg = msg + "Please specify inclusive dates of employee's leave or absence"; }
					
					if(msg != "") { parent.sendErrorMessage(msg); } else {
						$.post("misc-data.php", { mod: "newLeave", emp_id: $("#emp_id").val(), emp_name: $("#emp_name").val(), date: $("#date").val(), dateFrom: $("#dateFrom").val(), dateTo: $("#dateTo").val(), length: $("#length").val(), type: $("#type").val(), reasons: $("#reasons").val(), address: $("#address").val(), w_pay: $("#w_pay").val(), sid: Math.random() }, function() {
							alert("Record Successfully Saved!");
							parent.showLeaves();
						});
					}
				},
				"Close": function() { $("#record").dialog("close"); }
			}}).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		}
		
		function deleteRecord() {
			
			init();
			
			if(!UID) {
					parent.sendErrorMessage("Nothing to delete. Please select a record from the list, and once highlighted, press  \"<b><i>Delete Selected Record</i></b>\" button again...");
			} else {
				if(confirm("Are you sure you want to delete this record?") == true) {
					$.post("misc-data.php", { mod: "deleteLeave", rid: UID, sid: Math.random() }, function() {
						alert("Record Successfully Deleted!");
						parent.showLeaves();
					});
				}	
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
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
	<div id="main">
		<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-top: 10px;">
			<tr>
				<td align=left style="padding-right: 20px;">
					<a href="#" class="topClickers" onClick="newRecord();"><img src="../images/icons/adduser.png" width=18 height=18 align=absmiddle />&nbsp;New Record</a>&nbsp;
					<a href="#" class="topClickers" onClick="viewRecord();"><img src="../images/icons/edit.png" width=18 height=18 align=absmiddle />&nbsp;View/Edit Selected Record</a>&nbsp;
					<a href="#" class="topClickers" onClick="deleteRecord();"><img src="../images/icons/delete.png" width=18 height=18 align=absmiddle />&nbsp;Delete Selected Record</a>
				</td>
			</tr>
		</table>
		<table id="itemlist" style="font-size:11px;">
			<thead>
				<tr>
					<th width=1%>RID</th>
					<th width=10%>EMP. ID</th>
					<th width=15%>EMP. NAME</th>
					<th width=10%>DATE FILED</th>
					<th width=10%>NO. OF DAYS</th>
					<th width=20%>INCLUSIVE DATES</th>
					<th width=10%>TYPE</th>
					<th>REASONS STATED</th>
				</tr>
			</thead>
		</table>
	</div>
	<div id="record" style="display: none;">
		<form name="irec" id="irec">
			<input type="hidden" name="rid" id="rid" value="">
			<table width=100% cellspacing=2 cellpadding=0>
				<tr>
					<td class="bareThin" align=left width=40%>Employee Name :</td>
					<td align=left>
						<input type="text" name="emp_name" id="emp_name" class="inputSearch2" style="width: 80%; padding-left: 22px;">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Employee ID :</td>
					<td align=left>
						<input type="text" name="emp_id" id="emp_id" class="gridInput" style="width: 80%;" readonly>
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Date Filed :</td>
					<td align=left>
						<input type="text" name="date" id="date" style="width: 80%;">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>No. of Days :</td>
					<td align=left>
						<input type="text" name="length" id="length" class="gridInput" style="width: 80%;">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Type :</td>
					<td align=left>
						<select name="type" id="type" style="width: 80%; font-size: 11px; padding: 5px;">
							<?php 
								$qemp2 = $mydb->dbquery("select * from options_leavetype;");
								while(list($type,$tdesc) = $qemp2->fetch_array()) {
									print "<option value='$type'>$tdesc</option>";
								}
								unset($qemp2);
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Inclusive Dates :</td>
					<td align=left>
						<input type="text" name="dateFrom" id="dateFrom" style="width: 80%;">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Inclusive Dates :</td>
					<td align=left>
						<input type="text" name="dateTo" id="dateTo" style="width: 80%;">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40% valign=top>Reasons :</td>
					<td align=left><textarea style="width: 80%; font-size: 11px;" rows=1 name="reasons" id="reasons"></textarea></td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40% valign=top>Address While On-Leave :</td>
					<td align=left><textarea style="width: 80%; font-size: 11px;" rows=1 name="address" id="address"></textarea></td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>With Pay? </td>
					<td align=left>
						<select name="w_pay" id="w_pay" style="width: 80%; font-size: 11px; padding: 5px;">
							<option value="Y">Yes</option>
							<option value="N">No</option>
						</select>
					</td>
				</tr>
			</table>
		</form>
	</div>
</body>
</html>
<?php mysql_close($con); ?>