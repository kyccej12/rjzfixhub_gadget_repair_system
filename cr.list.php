<?php
	session_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Basic Prime Construction Supply</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<link rel="stylesheet" type="text/css" href="ui-assets/keytable/css/keyTable.jqueryui.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/page.jumpToData().js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/keytable/js/dataTables.keyTable.min.js"></script>
	<script>

		function viewCR() {
			var table = $("#itemlist").DataTable();
			var arr = [];
		    $.each(table.rows('.selected').data(), function() { arr.push(this["docno"]); });
			
			if(!arr[0]) {
				parent.sendErrorMessage("It appears you have yet to select any record to open");
			} else {
				parent.viewCR(arr[0]);
			}
		}
		
		$(document).ready(function() {
			var myTable = $('#itemlist').DataTable({
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"pageLength": 50,
				"sAjaxSource": "data/crlist.php",
				"aoColumns": [
				  { mData: 'docno' } ,
				  { mData: 'cd8' },
				  { mData: 'cust' },
				  { mData: 'cr_no' },
				  { mData: 'trans_ref_no' },
				  { mData: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '') },
				  { mData: 'remarks' },
				  { mData: 'status' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [0,1,3] },
					{ className: "dt-body-right", "targets": [5] }
				],
				"order": [[ 0, "desc" ]]
			});

			$('#itemlist tbody').on('dblclick', 'tr', function () {
				viewCR();
			});
		});
		
		function exportList() {
			var dis = $("#export").dialog({
				title: "Export List to Excel",
				width: 480,
				modal: true,
				resizable: false,
				buttons: {
					"Export List to Excel": function() {
						window.open("export/crlist.php?dtf="+$("#dtf").val()+"&dt2="+$("#dt2").val()+"&cname="+$("#cname").val()+"&sid="+Math.random()+"","Collection Receipt Summary","location=1,status=1,scrollbars=1,width=640,height=720");
					},
					"Close": function() { dis.dialog("close"); }
				}
			});
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
	<table width=100% cellpadding=0 cellspacing=0 style="padding-left: 5px; margin-bottom: 2px;">
		<tr>
			<td align=left>
				<a href="#" onClick="parent.viewCR('');" class="topClickers"><img src="images/icons/add-2.png" width=16 height=16 align=absmiddle />&nbsp;New Record</b></a>&nbsp;&nbsp;
				<a href="#" onClick="viewCR();" class="topClickers"><img src="images/icons/bill.png" width=16 height=16 align=absmiddle />&nbsp;View Record Details</b></a>&nbsp;&nbsp;
				<a href="#" onClick="parent.showCR();" class="topClickers"><img src="images/icons/refresh.png" width=16 height=16 align=absmiddle />&nbsp;Reload List</b></a>&nbsp;&nbsp;
				<a href="#" onClick="exportList();" class="topClickers"><img src="images/icons/excel.png" width=16 height=16 align=absmiddle />&nbsp;Export List to Excel</b></a>
			</td>
		</tr>
	</table>
	<table class="cell-border" id="itemlist" style="font-size:11px;">
		<thead>
			<tr>
				<th width=8%>DOC #</th>
				<th width=8%>DATE</th>
				<th width=20%>CUSTOMER</th>
				<th width=10%>CR #</th>
				<th width=15%>SALES REF #</th>
				<th width=10%>AMOUNT</th>
				<th>DOCUMENT REMARKS</th>
				<th width=12%>STATUS</th>
			</tr>
		</thead>
	</table>
	<div id="export" style="display: none;">
		<table width=100% cellpadding=1 cellspacing=0>
			<tr>
				<td class="spandix-l" width=35%>Customer Name :</td>
				<td><input type="text" class="gridInput" style="width: 80%;" name="cname" id="cname" placeholder="All Customers"></td>
			</tr>
			<tr>
				<td class="spandix-l" width=35%>Date Covered :</td>
				<td><input type="text" class="gridInput" style="width: 80%;" name="dtf" id="dtf" value="<?php echo date('m/01/Y'); ?>"></td>
			</tr>
			<tr>
				<td class="spandix-l" width=35%></td>
				<td><input type="text" class="gridInput" style="width: 80%;" name="dt2" id="dt2" value="<?php echo date('m/d/Y'); ?>"></td>
			</tr>
		</table>
	</div>
</body>
</html>