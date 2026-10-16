<?php
	session_start();
	
	/* Added for Advance Search Function */
	if($_POST['sflag'] == 'Y') {
		if($_POST['stxt_name'] != '') { $a = "&cname=$_POST[stxt_name]"; }
		if($_POST['stxt_description'] != '') { $b = "&idesc=$_POST[stxt_description]"; }
		if($_POST['stxt_dtf'] != '' && $_POST['stxt_dt2'] != '') {
			$c = "&dtf=".formatDate($_POST['stxt_dtf'])."&dt2=".formatDate($_POST['stxt_dt2']);
		} else {
			if($_POST['stxt_dtf'] != '') { $c = "&po_date=".formatDate($_POST['stxt_dtf']); } 
			if($_POST['stxt_dt2'] != '') { $c = "&po_date= '".formatDate($_POST['stxt_dt2']); }
		}
	}
	
?>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Red Global Land Properties Corp.</title>
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
			   arr.push(this["po"]);
		   });
		   sPO = arr[0];
		}
		
		function viewPO() {
			init();
			if(!sPO) {
				parent.sendErrorMessage("Please select record to view.");
			} else {
				parent.viewPO(sPO);
			}
		}

		function viewDocInfo() {
			if(sPO == "") { 
				parent.sendErrorMessage("Please select record to view."); 
			} else {
				$.post("po.datacontrol.php",  {mod: "getDocInfo", po_no: sPO, sid: Math.random() }, function(data) { $("#docinfo").html(data); },"html")
				$("#docinfo").dialog({title: "Document Info >> PO # "+sPO+"", width: 340, height: 200, resizable: false });
			}
		}
		
		function searchRecord() {
			$("#stxt_dtf").datepicker(); $("#stxt_dt2").datepicker();
			$("#searchDiv").dialog({
				title: "Search Record", 
				width: 400,
				resizable: false, 
				modal: true, 
				buttons: {
					"Search Record": function() {
						document.getElementById('frmSearch').submit();
					},
					"Close": function() { $(this).dialog("close"); }
				}
			});
		}
		
		$(document).ready(function() {
			var myTable = $('#itemlist').DataTable({
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"sAjaxSource": "data/polist.php?1=1<?php echo $a.$b.$c; ?>",
				"aoColumns": [
				  { mData: 'po' } ,
				  { mData: 'pdate' },
				  { mData: 'supplier_name' },
				  { mData: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '') },
				  { mData: 'remarks' },
				  { mData: 'status' },
				  { mData: 'rr' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [0,1,5,6] },
					{ className: "dt-body-right", "targets": [3] }
				],
				"order": [[ 1, "desc" ]]
			});

			
			$('#itemlist tbody').on('dblclick', 'tr', function () {
				var data = myTable.row( this ).data();	
				parent.viewPO(data['po']);
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
				<a href="#" onClick="parent.viewPO('');" class="topClickers"><img src="images/icons/add.png" width=18 height=18 align=absmiddle />&nbsp;New Purchase Order</a>&nbsp;
				<a href="#" onClick="viewPO();" class="topClickers"><img src="images/icons/bill.png" width=16 height=16 align=absmiddle />&nbsp;View/Edit P.O</a>&nbsp;
				<a href="#" onClick="parent.showPOList();" class="topClickers"><img src="images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;Reload List</a>&nbsp;
				<a href="#" onClick="searchRecord();" class="topClickers"><img src="images/icons/search.png" width=18 height=18 align=absmiddle />&nbsp;Advance Search</a>
			</td>
		</tr>
	</table>
	<table class="cell-border" id="itemlist" style="font-size:11px;">
		<thead>
			<tr>
				<th width=8%>PO #</th>
				<th width=8%>DATE</th>
				<th width=22%>SUPPLIER</th>
				<th width=10%>AMOUNT</th>
				<th>PURPOSE</th>
				<th width=10%>STATUS</th>
				<th width=10%>RR #</th>
			</tr>
		</thead>
	</table>
	<div id="searchDiv" style="display: none;">
		<form name = "frmSearch" id = "frmSearch" method = "POST" action = "po.list.php">
			<input type = "hidden" name = "sflag" id = "sflag" value = "Y">
			<table width = "100%" cellpading = 0 cellspacing = 0>
				<tr>
					<td class="spandix-l">Customer Name :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_name" id="stxt_name"></td>
				</tr>
				<tr>
					<td class="spandix-l">Date Covered :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_dtf" id="stxt_dtf"></td>
				</tr>
				<tr>
					<td class="spandix-l"></td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_dt2" id="stxt_dt2"></td>
				</tr>
				<tr>
					<td class="spandix-l">Item Description :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_description" id="stxt_description"></td>
				</tr>
			</table>
		</form>
	</div>
</body>
</html>