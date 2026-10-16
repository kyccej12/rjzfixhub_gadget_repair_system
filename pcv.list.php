<?php
	session_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>TLL BUILDERS</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script>
		var sPO = "";
	
		function init() {
			var table = $("#itemlist").DataTable();
			var arr = [];
		   $.each(table.rows('.selected').data(), function() {
			   arr.push(this["pcv_no"]);
		   });
		   sPO = arr[0];
		}
		
		function viewPCV() {
			init();
			if(!sPO) {
				parent.sendErrorMessage("Please select a voucher from the given list, and click \"<b>View/Update Voucher</b>\" again....");
			} else {
				parent.viewPCV(sPO);
			}
		}
		
			
		$(document).ready(function() {
			$('#itemlist').dataTable({
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"sAjaxSource": "data/pcvlist.php",
				"aoColumns": [
				  { mData: 'pcv_no' },
				  { mData: 'mypcv' },
				  { mData: 'pcv_date' },
				  { mData: 'payeeName' },
				  { mData: 'particulars' },
				  { mData: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '')},
				  { mData: 'status' },
				  { mData: 'substatus' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1,2,3,6,7] },
					{ className: "dt-body-right", "targets": [5] },
					{ "visible": false, "targets": [0] }
				],
				"order": [[ 1, "desc" ]]
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
	<div id="main">
		<table width=100% cellpadding=0 cellspacing=0 style="padding-left: 5px; margin-bottom: 2px;">
			<tr>
				<td align=left>
					<a href="#" class="topClickers" onClick="parent.viewPCV('');"><img src="images/icons/add-2.png" width=18 height=18 align=absmiddle />&nbsp;New Voucher</a>&nbsp;&nbsp;
					<a href="#" class="topClickers" onClick="viewPCV();"><img src="images/icons/docinfo.png" width=18 height=18 align=absmiddle />&nbsp;View/Edit Voucher</a>&nbsp;&nbsp;
					<a href="#" class="topClickers" onClick="parent.showPCV();"><img src="images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;Reload List</a>
				</td>
			</tr>
		</table>
		<table id="itemlist" style="font-size:11px;">
			<thead>
				<tr>
					<th width=1%>PCV</th>
					<th width=8%>PCV #</th>
					<th width=8%>DATE</th>
					<th width=15%>PAYEE</th>
					<th width=25%>PARTICULARS</th>
					<th width=10%>AMOUNT</th>
					<th width=10%>DOC. STATUS</th>
					<th>LIQUIDATION</th>
				</tr>
			</thead>
		</table>
	</div>
</body>
</html>