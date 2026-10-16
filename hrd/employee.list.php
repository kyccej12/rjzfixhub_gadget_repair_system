<?php
	require_once "../handlers/_generics.php";
	$mydb = new _init;
	
?>
<html>
<head>
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
			   arr.push(this["emp_id"]);
		   });
		   UID = arr[0];
		}
		
		$(document).ready(function() {
			$('#itemlist').dataTable({
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"bProcessing": true,
				"pageLength": 25,
				"sAjaxSource": "listings.php?mod=employees&sid="+Math.random()+"",
				"aoColumns": [
				  { mData: 'emp_id' },
				  { mData: 'lname' },
				  { mData: 'fname' },
				  { mData: 'mname' },
				  { mData: 'dept_name' },
				  { mData: 'desg' },
				  { mData: 'emp_status' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [0] }
				]
			});	
			
		});
		
		
		function showEdu() { parent.showEdu(UID); }
		function showFam() { parent.showFam(UID); }
		function showErecord() { parent.showErecord(UID); }
		function showErecord2() { parent.showErecord2(UID); }
		function showCert() { parent.showCert(UID); }
		
	
		function viewEmployee() {
			
			init();
			
			if(!UID) {
				parent.sendErrorMessage("Unable to retrieve record. Please select a record from the list, and once highlighted, press  \"<b><i>View Employee Profile</i></b>\" button again...");
			} else {
				parent.showEmpProfile(UID);
			}
		}
		
		function show201() {
			
			init();
			
			if(!UID) {
				parent.sendErrorMessage("Unable to retrieve record. Please select a record from the list, and once highlighted, press  \"<b><i>Employee 201 File</i></b>\" button again...");
			} else {
				$.post("../payroll.datacontrol.php", { mod: "getEmpName", record_id: UID, sid: Math.random() }, function(data) {
					$("#employee201").dialog({title: "Employee 201 File ("+data+")", width: 600, height: 380 }).dialogExtend({
						"closable" : true,
						"maximizable" : false,
						"minimizable" : true
					});
				});
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
					<a href="#" class="topClickers" onClick="parent.newEmp();"><img src="../images/icons/adduser.png" width=18 height=18 align=absmiddle />&nbsp;New Employee Profile</a>&nbsp;
					<a href="#" class="topClickers" onClick="viewEmployee();"><img src="../images/icons/edit.png" width=18 height=18 align=absmiddle />&nbsp;Open Selected File</a>&nbsp;
					<a href="#" class="topClickers" onClick="parent.showEmployees();"><img src="../images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;Reload List</a>
				</td>
			</tr>
		</table>
		<table id="itemlist" style="font-size:11px;">
			<thead>
				<tr>
					<th width=10%>EMP. ID</th>
					<th width=15%>LAST NAME</th>
					<th width=15%>FIRST NAME</th>
					<th width=15%>MIDDLE NAME</th>
					<th width=15%>DEPARTMENT</th>
					<th width=15%>POSITION</th>
					<th>STATUS</th>
				</tr>
			</thead>
		</table>
	</div>
	<div id="employee201" style="display: none;">
		<div style="padding: 20px;">
			<div class="fileObjects"><a href="#" onclick="showIDs();"><img src="../images/icons/camera.png" width=60 height=60 /><br/><br/>ID Pictures</a></div>
			<div class="fileObjects"><a href="#" onclick="showFam();"><img src="../images/icons/family.png" width=60 height=60 /><br/><br/>Family Background</a></div>
			<div class="fileObjects"><a href="#" onclick="showEdu();"><img src="../images/icons/education.png" width=60 height=60 /><br/><br/>Educational Background</a></div>
			<div class="fileObjects"><a href="#" onclick="showErecord();"><img src="../images/icons/employment.png" width=60 height=60 /><br/><br/>Work Experience (External)</a></div>
			<div class="fileObjects"><a href="#" onclick="showErecord2();"><img src="../images/icons/employment.png" width=60 height=60 /><br/><br/>Work Experience (Internal)</a></div>
			<div class="fileObjects"><a href="#" onclick="showCert();"><img src="../images/icons/certificates.png" width=60 height=60 /><br/><br/>Memos, Certificates & Clearances</a></div>
		</div>
	</div>
</body>
</html>