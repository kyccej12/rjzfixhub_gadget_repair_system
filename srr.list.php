<?php
	session_start();
?>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Basic Prime Construction Supply</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script>
		function viewSRR() {
			var table = $("#itemlist").DataTable();
			var arr = [];
		    $.each(table.rows('.selected').data(), function() { arr.push(this["srr"]); });
			
			if(!arr[0]) {
				parent.sendErrorMessage("Please select record to view.");
			} else {
				parent.viewSRR(arr[0]);
			}
		}
		$(document).ready(function() {
			$('#itemlist').dataTable({
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"sAjaxSource": "data/srrlist.php",
				"aoColumns": [
				  { mData: 'srr' } ,
				  { mData: 'sdate' },
				  { mData: 'received_from' },
				  { mData: 'remarks' },
				  { mData: 'status' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [0,1,4] }
				],
				"order": [[ 1, "desc" ]]
			});

			$('#itemlist tbody').on('dblclick', 'tr', function () {
				viewSRR();
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
	<table width=100% cellpadding=0 cellspacing=0 style="padding-left: 5px; margin-bottom: 2px;">
		<tr>
			<td align=left>
				<a href="#" onClick="parent.viewSRR('');" class="topClickers"><img src="images/icons/add-2.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;New Record</a>&nbsp;
				<a href="#" onClick="viewSRR();" class="topClickers"><img src="images/icons/bill.png" width=16 height=16 align=absmiddle />&nbsp;&nbsp;View/Edit Record</a>
				<a href="#" onClick="parent.showSRR();" class="topClickers"><img src="images/icons/refresh.png" width=16 height=16 align=absmiddle />&nbsp;&nbsp;Refresh List</a>
			</td>
		</tr>
	</table>
	<table id="itemlist" class="cell-border" style="font-size:11px;">
		<thead>
			<tr>
				<th width=8%>DOC #</th>
				<th width=10%>DOC DATE</th>
				<th width=20%>RECEIVED FROM</th>
				<th>DOCUMENT REMARKS</th>
				<th width=12%>DOC STATUS</th>
			</tr>
		</thead>
	</table>
</body>
</html>