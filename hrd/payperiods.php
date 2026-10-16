<?php
	require_once "../handlers/initDB.php";
	$con = new myDB;
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Superior Gas & Equipment Co. of Cebu, Inc. Payroll System Ver. 1.0a</title>
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
			   arr.push(this["period_id"]);
		   });
		   UID = arr[0];
		}
		
		$(document).ready(function() { 
			$("#dtf").datepicker(); $("#dt2").datepicker(); 
			
			$('#itemlist').dataTable({
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"pageLength": 25,
				"bProcessing": true,
				"order": [[ 4, "desc" ]],
				"sAjaxSource": "listings.php?mod=cutoffs&sid="+Math.random()+"",
				"aoColumns": [
				  { mData: 'period_id' },
				  { mData: 'pstart' },
				  { mData: 'pend' },
				  { mData: 'remarks' },
				  { mData: 'period_end' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1,2]},
					{ "targets": [0,4], "visible": false }
				]
			});
			
		});

		
		function viewRecord() {
			
			init();
			
			if(!UID) {
				parent.sendErrorMessage("Unable to retrieve record. Please select a record from the list, and once highlighted, press  \"<b><i>View Record Details</i></b>\" button again...");
			} else {
				$.post("misc-data.php", { mod: "getCutoff", id: UID, sid: Math.random() }, function(ech) {
					$("#rid").val(ech['rid']);
					$("#dtf").val(ech['dtf']);
					$("#dt2").val(ech['dt2']);
					$("#month").val(ech['reportingMonth']);
					$("#year").val(ech['reportingYear']);
					$("#remarks").html(ech['remarks']);
					$("#payroll_type").val(ech['payroll_type']);
					$("#week").val(ech['weekOfMonth']);
					
					if(ech['payroll_type'] == 3) { 
						$("#week").val(''); $("#week").attr({'disabled': true}); 
					} else { $("#week").attr({'disabled': false}); }
					
					$("#record").dialog({title: "Payroll Cut-off", width: 440, modal: true, resizable: false, buttons: { 
						"Save Changes": function() {
							
							if($("#payroll_type").val() != '3' && $("#week").val() == "") {
								parent.sendErrorMessage("You must identify either this period is for the first half or second half of the Month");
							} else {
								$.post("misc-data.php", { mod: "updateCutoff", rid: $("#rid").val(), dtf: $("#dtf").val(), dt2: $("#dt2").val(), month: $("#month").val(), year: $("#year").val(), week: $("#week").val(), remarks: $("#remarks").val(), sid: Math.random() }, function() {
									alert("Record Successfully Updated!");
									location.reload();
								});
							}
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
			$(document.xform)[0].reset(); $("#remarks").html('');
			$("#record").dialog({title: "Payroll Cut-off", width: 440, modal: true, resizable: false, buttons: {
				"Save Record": function() {
					var msg = "";
					if($("#dtf").val() == "") { msg = "<b>Period Start</b> must not be empty!<br/>"; }
					if($("#dt2").val() == "") { msg = "<b>Period End</b> must not be empty!"; }
					if($("#payroll_type").val() != '3' && $("#week").val() == "") { msg = msg + "You must identify either this period is for the first half or second half of the Month"; }
					if(msg != "") { parent.sendErrorMessage(msg); } else {
						$.post("misc-data.php", { mod: "newCutoff", dtf: $("#dtf").val(), dt2: $("#dt2").val(), month: $("#month").val(), year: $("#year").val(), week: $("#week").val(), remarks: $("#remarks").val(), sid: Math.random() }, function() {
							alert("Record Successfully Saved!");
							location.reload();
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
					parent.sendErrorMessage("Nothing to delete. Please select a record from the list, and once highlighted, press  \"<b><i>Delete Record</i></b>\" button again...");
				} else {	
				if(confirm("Are you sure you want to delete this record?") == true) {
					$.post("misc-data.php", { mod: "deleteCutoff", rid: UID, sid: Math.random() }, function() {
						alert("Record Successfully Deleted!");
						parent.showPayPeriods();
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
				<th width=1%>PID</th>
				<th width=20%>PERIOD START</th>
				<th width=20%>PERIOD END</th>
				<th>REMARKS</th>
				<th></th>
			</tr>
		</thead>
	</table>
</div>
<div id="record" style="display: none;">
	<form name="xform" id="xform">
	<table width=100% cellspacing=2 cellpadding=0>
		<tr>
			<td class="bareThin" align=right width=40% style="padding-right: 30px;">Reporting Month :</td>
			<td align=left>
				<select id="month" name="month" style="width: 80%; font-size: 11px;" class="gridInput">
					<option value="01">January</option>
					<option value="02">February</option>
					<option value="03">March</option>
					<option value="04">April</option>
					<option value="05">May</option>
					<option value="06">June</option>
					<option value="07">July</option>
					<option value="08">August</option>
					<option value="09">September</option>
					<option value="10">October</option>
					<option value="11">November</option>
					<option value="12">December</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="bareThin" align=right width=40% style="padding-right: 30px;">Reporting CY :</td>
			<td align=left>
				<input type="text" id="year" name="year" class="gridInput" style="width: 80%;" value="<?php echo date('Y'); ?>" />
			</td>
		</tr>
		<tr>
			<td class="bareThin" align=right width=40% style="padding-right: 30px;">Half :<br>
				<span style="font-size:8px; font-style: italic">(Semi-Monthly Paid Employees)</span></td>
			<td align=left>
				<select id="week" name="week" class="gridInput" style="width : 80%; font-size: 11px;">
					<option value = "">- Not Applicable -</option>
					<option value = "1"> First Half </option>
					<option value = "2"> Second Half </option>
 				</select>
			</td>
		</tr>
		<tr>
			<td class="bareThin" align=right width=40% style="padding-right: 30px;">Period Start :</td>
			<td align=left><input type="hidden" name="rid" id="rid" value=""><input type="text" name="dtf" id="dtf" style="width: 80%;"></td>
		</tr>
		<tr>
			<td class="bareThin" align=right width=40% style="padding-right: 30px;">Period End :</td>
			<td align=left><input type="text" name="dt2" id="dt2" style="width: 80%;"></td>
		</tr>
		<tr>
			<td class="bareThin" align=right width=40% valign=top style="padding-right: 30px;">Remarks :</td>
			<td align=left><textarea style="width: 80%; font-size: 11px;" rows=1 name="remarks" id="remarks"></textarea></td>
		</tr>
		
		</tr>
	</table>
	</form>
</div>
</body>
</html>
<?php mysql_close($con); ?>