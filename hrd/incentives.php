<?php
	session_start();
	require_once "../handlers/_generics.php";
	$mydb = new _init;
	
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
		var ptype = "";

		function init() {
			var xUID;
			var table = $("#itemlist").DataTable();
			var arr = [];
		   
		    $.each(table.rows('.selected').data(), function() {
			   arr.push(this["rid"]);
			});
		   
		   if(arr[0]) {
		   
				var xUID = arr[0].split('|');
					UID = xUID[0];
					ptype = xUID[1];
		   } else { UID = ''; ptype = ''; }
		}	
		
		
		$(document).ready(function() { 
			$("#date").datepicker(); 
		
			$('#itemlist').dataTable({
					"scrollY":  "300px",
					"select":	'single',
					"pagingType": "full_numbers",
					"bProcessing": true,
					"sAjaxSource": "listings.php?mod=incentives&sid="+Math.random()+"",
					"aoColumns": [
					  { mData: 'rid' },
					  { mData: 'emp_id' },
					  { mData: 'emp_name' },
					  { mData: 'incType' },
					  { mData: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '') },
					  { mData: 'period' },
					  { mData: 'remarks' }
					],
					"aoColumnDefs": [
						{ className: "dt-body-center", "targets": [1,3,4,5]},
						{ "targets": [0], "visible": false }
					]
				});	
		
		});
		
		function getPayperiods(ptype,selbox) {
			$.post("misc-data.php", { mod: "getPeriods", type: ptype, sid: Math.random() }, function(data) { document.getElementById(selbox).innerHTML = data; },"html");
		}
		
		function getEmployees(ptype,selbox){
			$.post("misc-data.php", { mod: "getEmployees", type: ptype, sid: Math.random() }, function(data) { document.getElementById(selbox).innerHTML = data; },"html");
		}
		
		function viewRecord() {
			
			init();
			
			if(!UID) {
				parent.sendErrorMessage("Unable to retrieve record. Please select a record from the list, and once highlighted, press  \"<b><i>View Record Details</i></b>\" button again...");
			} else {
				
				$.post("misc-data.php", { mod: "getIncentive", id: UID, sid: Math.random() }, function(ech) {
					
					$("#rid").val(ech['record_id']);
					$("#incentive_type").val(ech['incentive_type']);
					$("#pay_period").val(ech['period_id']);
					$("#amount").val(ech['amt']);
					$("#remarks").html(ech['remarks']);
					$("#emp_id").val(ech['emp_id']);
					

					$("#record").dialog({title: "Record Details", width: 440, resizable: false, modal: true, buttons: { 
						"Save Changes": function() {
							$.post("misc-data.php", { mod: "updateIncentive", rid: $("#rid").val(), emp_id: $("#emp_id").val(), type: $("#incentive_type").val(), period: $("#pay_period").val(), amount: $("#amount").val(), remarks: $("#remarks").val(), sid: Math.random() }, function() {
								alert("Record Successfully Updated!");
								parent.showIncentives();
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
			$(document.irec)[0].reset();
			$("#record").dialog({title: "New Record", width: 440, resizable: false, modal: true, buttons: {
				"Save Record": function() {
					var msg = "";
					if($("#emp_id").val() == "") { msg = msg + "- Please select employee from the given list<br/>"; }
					if($("#remarks").val() == "") { msg = msg + "- Transaction remarks must be duly noted for future reference.<br/>"; }
					if($("#amount").val() != "") {
						var amt = parent.stripComma($("#amount").val());
						if(isNaN(amt) == true) { msg = msg + "- You have specified an invalid amount."; }
					}
					
				
					
					if(msg != "") { parent.sendErrorMessage(msg); } else {
						$.post("misc-data.php", { mod: "newIncentive",emp_id: $("#emp_id").val(), type: $("#incentive_type").val(), period: $("#pay_period").val(), amount: $("#amount").val(), remarks: $("#remarks").val(), sid: Math.random() }, function() {
							alert("Record Successfully Saved!");
							parent.showIncentives();
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
			if(!UID) {
					parent.sendErrorMessage("Nothing to delete. Please select a record from the list, and once highlighted, press  \"<b><i>Delete Record</i></b>\" button again...");
			} else {
				if(confirm("Are you sure you want to delete this record?") == true) {
					$.post("misc-data.php", { mod: "deleteIncentive", rid: UID, sid: Math.random() }, function() {
						alert("Record Successfully Deleted!");
						parent.showIncentives();
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
					<th width=10%>TYPE</th>
					<th width=10%>AMOUNT</th>
					<th width=20%>PERIOD</th>
					<th>REMARKS</th>
				</tr>
			</thead>
		</table>
	</div>
	 <div id="record" style="display: none;">
		<form name="irec" id="irec">
			<table width=100% cellspacing=2 cellpadding=0>
				<tr>
					<td class="bareThin" align=left width=40%>Employee :</td>
					<td align=left>
						<input type="hidden" name="rid" id="rid" value="">
						<select name="emp_id" id="emp_id" style="width: 80%; font-size: 11px; padding: 5px;">
							<?php
								$eQuery = $mydb->dbquery("select EMP_ID, concat(LNAME,', ',FNAME) from emp_masterfile where EMP_TYPE = '$_SESSION[payclass]' and FILE_STATUS != 'DELETED' AND EMPLOYMENT_STATUS NOT IN (7,8,9,10) ORDER BY LNAME, FNAME;");
								while(list($eid,$ename) = $eQuery->fetch_array()) {
									echo "<option value='$eid'>$ename</option>";
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Type :</td>
					<td align=left>
						<select name="incentive_type" id="incentive_type" style="width: 80%; font-size: 11px; padding: 5px;">
							<option value="">- Select Type -</option>
							<?php 
								$d_res = $mydb->dbquery("select id, incType from option_incentives order by incType asc;");
								while($d_row = $d_res->fetch_array(MYSQLI_BOTH)) {
									print "<option value='$d_row[0]' ";
										if($d_row[0] == $data['incType']){ print "selected"; }
									print ">$d_row[1]</option>";
								}
								unset($d_res);
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Payroll Period :</td>
					<td align=left>
						<select name="pay_period" id="pay_period" style="width: 80%; font-size: 11px; padding: 5px;">
							<?php
								$coQuery = $mydb->dbquery("select period_id,concat(date_format(period_start,'%m/%d/%Y'),' - ',date_format(period_end,'%m/%d/%Y')) from pay_periods order by period_end desc;");
								while(list($periodID,$periodRange) = $coQuery->fetch_array()) {
									echo "<option value='$periodID'>$periodRange</option>";
								}
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Amount :</td>
					<td align=left>
						<input type="text" name="amount" id="amount" style="width: 80%;">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40% valign=top>Other Remarks :</td>
					<td align=left><textarea style="width: 80%; font-size: 11px;" rows=3 name="remarks" id="remarks"></textarea></td>
				</tr>
			</table>
		</form>
	</div>
</body>
</html>
<?php mysql_close($con); ?>