<?php
	session_start();
	require_once '../handlers/_generics.php';
	
	$con = new _init();
	
?>


<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Red Global Land Properties Corp.</title>
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
			$("#date").datepicker(); 
			
			$('#itemlist').dataTable({
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"bProcessing": true,
				"sAjaxSource": "listings.php?mod=locholidays&sid="+Math.random()+"",
				"aoColumns": [
				  { mData: 'id' },
				  { mData: 'proj' },
				  { mData: 'xdate' },
				  { mData: 'occasion' },
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1,2]},
					{ "targets": [0], "visible": false }
				]
			});
		
		});

		function viewRecord() {
			
			init();
			
			if(!UID) {
				parent.sendErrorMessage("Unable to retrieve record. Please select a record from the list, and once highlighted, press  \"<b><i>View Record Details</i></b>\" button again...");
			} else {
				$.post("misc-data.php", { mod: "getLocHoliday", id: UID, sid: Math.random() }, function(ech) {
					$("#rid").val(ech['id']);
					$("#date").val(ech['xdate']);
					$("#area").val(ech['area']);
					$("#occasion").val(ech['occasion']);
					
					$("#record").dialog({title: "Holiday Occasion", width: 440, modal: true, resizable: false, buttons: { 
						"Save Changes": function() {
							if($("#type").val() != '') {
								$.post("misc-data.php", { mod: "updateLocHoliday", rid: $("#rid").val(), area: $("#area").val(), date: $("#date").val(),  occasion: $("#occasion").val(), sid: Math.random() }, function() {
									alert("Record Successfully Updated!");
									parent.showLocalHolidays();
								});
							} else { parent.sendErrorMessage("Please identify holiday type"); }
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
			$("#date").val(''); $("#occasion").val('');
			$("#record").dialog({title: "Holiday Occasion", width: 440, modal: true, resizable: false, buttons: {
				"Save Record": function() {
					var msg = "";
					if($("#dtf").val() == "") { msg = "<b>Date</b> must not be empty!<br/>"; }
					
					if(msg != "") { parent.sendErrorMessage(msg); } else {
						$.post("misc-data.php", { mod: "newLocHoliday", area: $("#area").val(), date: $("#date").val(),  occasion: $("#occasion").val(), sid: Math.random() }, function() {
							alert("Record Successfully Saved!");
							parent.showLocalHolidays();
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
			if(UID == "") {
					parent.sendErrorMessage("Nothing to delete. Please select a record from the list, and once highlighted, press  \"<b><i>Delete Record</i></b>\" button again...");
			} else {
				if(confirm("Are you sure you want to delete this record?") == true) {
				
					$.post("misc-data.php", { mod: "deleteLocHoliday", rid: UID, sid: Math.random() }, function() {
						alert("Record Successfully Deleted!");
						parent.showLocalHolidays();
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
				<th width=10%>RID</th>
				<th width=20%>AREA/LOCATION</th>
				<th width=15%>DATE</th>
				<th>OCCASSION</th>
			</tr>
		</thead>
	</table>
</div>
<div id="record" style="display: none;">
	<form name="fhrmHoliday" id="frmHoliday">
		<table width=100% cellspacing=2 cellpadding=0>
			<tr>
				<td class="bareThin" align=left width=40%>Area/Location :</td>
				<td align=left>
					<select name="area" id="area" style="width: 80%; font-size: 11px; padding: 5px;">
						<?php
							$bQuery = $con->dbquery("select `area`,`region` from emp_areas order by `area`;");
							while(list($bid,$bname) = $bQuery->fetch_array()) {
								echo "<option value='$bid'>$bname</option>";
							}
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40%>Date :</td>
				<td align=left><input type="hidden" name="rid" id="rid" value="">
				<input type="text" name="date" id="date" style="width: 80%;"></td>
			</tr>
			<tr>
				<td class="bareThin" align=left width=40% valign=top>Event or Occasion :</td>
				<td align=left><textarea style="width: 80%; font-size: 11px;" rows=1 name="occasion" id="occasion"></textarea></td>
			</tr>
		</table>
	</form>
</div>
</body>
</html>
