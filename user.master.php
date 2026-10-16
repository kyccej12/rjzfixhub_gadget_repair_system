<?php
	session_start();
	include("includes/dbUSE.php");	
	
	if(isset($_REQUEST['searchtext']) && $_REQUEST['searchtext']!=''){
		$srch = " and username like '%$_REQUEST[searchtext]%' ";
	}
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="style/style.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
<script>

	var UID = "";
	
	
	function init() {
		var table = $("#itemlist").DataTable();
		var arr = [];
	   $.each(table.rows('.selected').data(), function() {
		   arr.push(this["uid"]);
	   });
	   UID = arr[0];
	}
	
	function getUser() {
		init();
		if(UID == "" || UID == "undefined") {
			parent.sendErrorMessage("Unable to retrieve record. Please select a record from the list, and once highlighted, press  \"<b><i>View User Information</i></b>\" button again...");
		} else {
			parent.showUserDetails(UID);
		}
	}
	
	function viewUserInfo() {
		init();
		if(UID == "" || UID == "undefined") {
			parent.sendErrorMessage("Please select a record from the list, and once highlighted, press  \"<b><i>View User Information</i></b>\" button again...");
		} else {
			parent.viewUserInfo(UID);
		}
	}
	
	function getLocation() {
		init();
		if(UID == "" || UID == "undefined") {
			parent.sendErrorMessage("Unable to retrieve record. Please select a record from the list, and once highlighted, press  \"<b><i>View User Information</i></b>\" button again...");
		} else {
			parent.showUserLocation(UID);
		}
	}
	
	function resetPass() {
		init();
		
		if(UID == "") {
			parent.sendErrorMessage("Unable to continue. Please select a record from the list, and once highlighted, press  \"<b><i>Reset User Password</i></b>\" button again...");
		} else {
			if(confirm("Are you sure you want to reset this user's password?") == true) {
				$.post("src/sjerp.php", { mod: "resetPassword", uid: UID, sid: Math.random()}, function() {
					alert("User password was set to default (123456). The specified user will be required to change his/her password during his/her next login.")
				});
			}
		}
	}
	
	$(document).ready(function() {
	    $('#itemlist').dataTable({
			"scrollY":  "340px",
			"select":	'single',
			"pagingType": "full_numbers",
			"bProcessing": true,
			"sAjaxSource": "data/userlist.php",
			"aoColumns": [
			  { mData: 'uid' } ,
			  { mData: 'uname' },
			  { mData: 'fullname' },
			  { mData: 'utype' },
			  { mData: 'lastlogged' },
			  { mData: 'stat' }
			],
			"aoColumnDefs": [
				{ className: "dt-body-center", "targets": [0,3,4,5]}
            ]
		});
		
		
	});
	
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
 <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
	<tr>
		<td  style="padding:0px;" valign=top>
			<table width="100%"  cellspacing="0" cellpadding="0" style="padding-left: 5px; margin-bottom: 5px;">
				<tr>
					<td>
						<a href="#" class="topClickers" onClick="parent.addUser();"><img src="images/icons/adduser.png" width=18 height=18 align=absmiddle />&nbsp;New User</a>&nbsp;&nbsp;
						<a href="#" class="topClickers" onClick="viewUserInfo();"><img src="images/icons/personalinfo.png" width=18 height=18 align=absmiddle />&nbsp;Edit Selected Record</a>&nbsp;&nbsp;
						<a href="#" class="topClickers" onClick="getUser();"><img src="images/icons/options.png" width=18 height=18 align=absmiddle />&nbsp;Manage User Privileges</a>&nbsp;&nbsp;
						<a href="#" class="topClickers" onClick="parent.showUsers();"><img src="images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;Reload List</a>&nbsp;&nbsp;
						<a href="#" class="topClickers" onClick="resetPass();"><img src="images/icons/secrecy-icon.png" width=18 height=18 align=absmiddle />&nbsp;Reset User Password</a>
					</td>
				</tr>
			</table>
			<table id="itemlist" class="cell-border" style="font-size:11px;">
				<thead>
					<tr>
						<th width=10%>USER ID</th>
						<th width=10%>USERNAME</th>
						<th>FULL NAME</th>
						<th width=15%>PRIVILEGE TYPE</th>
						<th width=15%>LAST ACTIVE</th>
						<th width=15%>STATUS</th>
					</tr>
				</thead>
			</table>
		</td>
	</tr>
 </table>
</body>
</html>
<?php mysql_close($con);