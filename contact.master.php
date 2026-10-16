<?php
	session_start();
	
	function getMod($def,$mod) {
		if($def == $mod) { echo "class=\"float2\""; }
	}
	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Bata Esensyal</title>
<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="style/style.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
<script>

	function changeMod(mod) {
		document.changeModPage.mod.value = mod;
		document.changeModPage.submit();
	}

	function editRecord(){
		var table = $("#itemlist").DataTable();
		var arr = [];
	   $.each(table.rows('.selected').data(), function() {
		   arr.push(this["cid"]);
	   });
	  
		if(!arr[0] || arr[0] == "undefined") {
			parent.sendErrorMessage("Please select a record from the list, and once highlighted, click \"<b><i>Edit Selected Record</i></b>\" again...");
		} else {
			parent.showPayeeInfo(arr[0]);	
		}
	}
	
	$(document).ready(function() {
		var myTable = $('#itemlist').DataTable({
			"scrollY":  "340px",
			"select":	'single',
			"pagingType": "full_numbers",
			"bProcessing": true,
			"sAjaxSource": "data/contactlist.php?mod=<?php echo $_GET['mod']; ?>",
			"aoColumns": [
			  { mData: 'cid' } ,
			  { mData: 'ctype' },
			  { mData: 'cname' },
			  { mData: 'caddress' },
			  { mData: 'ctelno' },
			  { mData: 'cperson' }
			]
		});

		$('#itemlist tbody').on('dblclick', 'tr', function () {
			var data = myTable.row( this ).data();	
			parent.showPayeeInfo(data[0]);
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
		<td style="padding:0px;" valign=top>
			<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-top: 10px;">
				<tr>
					<td align=left style="padding-right: 20px;">
						<a href="#" class="topClickers" onClick="parent.addPayee('');"><img src="images/icons/adduser.png" width=18 height=18 align=absmiddle />&nbsp;New Payee/Supplier</a>&nbsp;&nbsp;
						<a href="#" class="topClickers" onClick="editRecord();"><img src="images/icons/edit.png" width=18 height=18 align=absmiddle />&nbsp;Edit Selected Record</a>&nbsp;&nbsp;
						<a href="#" class="topClickers" onClick="parent.showCust();"><img src="images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;Reload List</a>
					</td>
				</tr>
			</table>
			<table class="cell-border" id="itemlist" style="font-size:11px;">
				<thead>
					<tr>
						<th width=7%>ID</th>
						<th width=10%>TYPE</th>
						<th width=25%>NAME / TRADE NAME</th>
						<th>ADDRESS</th>
						<th width=15%>TEL NO</th>
						<th width=10%>SALES REP</th>
					</tr>
				</thead>
			</table>
		</td>
	</tr>
 </table>
 <form name="changeModPage" id="changeModPage" action="contact.master.php" method="GET" >
	<input type="hidden" name="mod" id="mod">
</form>
</body>
</html>
