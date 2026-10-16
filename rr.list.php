<?php
	session_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Red Global Land Properties Corp.</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script>

		function viewRR() {
			var table = $("#itemlist").DataTable();
			var arr = [];
		    $.each(table.rows('.selected').data(), function() { arr.push(this["rr"]); });
			
			if(!arr[0]) {
				parent.sendErrorMessage("Please select record to view.");
			} else {
				parent.viewRR(arr[0]);
			}
		}
		$(document).ready(function() {
			var myTable = $('#itemlist').DataTable({
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"sAjaxSource": "data/rrlist.php",
				"aoColumns": [
				  { mData: 'rr' } ,
				  { mData: 'rdate' },
				  { mData: 'supplier_name' },
				  { mData: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '') },
				  { mData: 'remarks' },
				  { mData: 'status' },
				  { mData: 'apv' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [0,1,5] },
					{ className: "dt-body-right", "targets": [3] }
				],
				"order": [[ 1, "desc" ]]
			});

			$('#itemlist tbody').on('dblclick', 'tr', function () {
				var data = myTable.row( this ).data();	
				parent.viewRR(data['rr']);
			});
		});
	</script>
	<style>
		table.dataTable tbody td { display: vertical-align: top; }
		table.dataTable tr.odd { background-color: #f5f5f5; }
		table.dataTable tr.even { background-color: white; }
		.dataTables_filter input { width: 350px; }
	</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
	<table width=100% cellpadding=0 cellspacing=0 style="padding-left: 5px; margin-bottom: 2px;">
		<tr>
			<td align=left>
				<a href="#" onClick="parent.viewRR('');" class="topClickers"><img src="images/icons/add.png" width=18 height=18 align=absmiddle />&nbsp;New Receiving Report</a>&nbsp;
				<a href="#" onClick="viewRR();" class="topClickers"><img src="images/icons/bill.png" width=18 height=18 align=absmiddle />&nbsp;View Record Details</a>
				
			</td>
	</table>
	<table class="cell-border" id="itemlist" style="font-size:11px;">
		<thead>
			<tr>
				<th width=8%>RR #</th>
				<th width=8%>DATE</th>
				<th width=25%>SUPPLIER</th>
				<th width=10%>AMOUNT</th>
				<th>DOCUMENT REMARKS</th>
				<th width=12%>DOC STATUS</th>
				<th width=10%>APV REF#</th>
			</tr>
		</thead>
	</table>
	</body>
</html>