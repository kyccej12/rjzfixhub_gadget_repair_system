<?php
	session_start();
	
	/* Added for Advance Search Function */
	if($_POST['sflag'] == 'Y') {
		if($_POST['stxt_description'] != '') { $b = "&idesc=$_POST[stxt_description]"; }
		if($_POST['stxt_dtf'] != '' && $_POST['stxt_dt2'] != '') {
			$c = "&dtf=".formatDate($_POST['stxt_dtf'])."&dt2=".formatDate($_POST['stxt_dt2']);
		} else {
			if($_POST['stxt_dtf'] != '') { $c = "&doc_date=".formatDate($_POST['stxt_dtf']); } 
			if($_POST['stxt_dt2'] != '') { $c = "&doc_date= '".formatDate($_POST['stxt_dt2']); }
		}
	}
	
?>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Medgruppe Polyclinics & Diagnostic Center, Inc.</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script>
		function viewPhy() {
			var table = $("#itemlist").DataTable();
			var arr = [];
		    $.each(table.rows('.selected').data(), function() { arr.push(this["docno"]); });
			
			if(!arr[0]) {
				parent.sendErrorMessage("Please select record to view.");
			} else {
				parent.viewPhy(arr[0]);
			}
		}
		$(document).ready(function() {
			$('#itemlist').dataTable({
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"sAjaxSource": "data/phylist.php?1=1<?php echo $a.$b.$c; ?>",
				"aoColumns": [
				  { mData: 'docno' } ,
				  { mData: 'dd8' },
				  { mData: 'conducted_by' },
				  { mData: 'verified_by' },
				  { mData: 'remarks' },
				  { mData: 'amount' },
				  { mData: 'status' }
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [0,1,4] }
				]
			});
		});
		
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

	<table width=100% cellpadding=5 cellspacing=0>
		<tr>
			<td align=left>
				<a href="#" onClick="parent.viewPhy('');" class="topClickers"><img src="images/icons/add.png" width=18 height=18 align=absmiddle />&nbsp;New Record</b></a>&nbsp;
				<a href="#" onClick="viewPhy();" class="topClickers"><img src="images/icons/bill.png" width=16 height=16 align=absmiddle />&nbsp;View/Edit Record</b></button>&nbsp;
				<a href="#" onClick="parent.showPhy();" class="topClickers"><img src="images/icons/refresh.png" width=16 height=16 align=absmiddle />&nbsp;Reload List</b></button>&nbsp;
				<a href="#" onClick="searchRecord();" class="topClickers"><img src="images/icons/search.png" width=16 height=16 align=absmiddle />&nbsp;Advance Search</b></button>
			</td>
		</tr>
	</table>
	<table id="itemlist" style="font-size:11px;">
		<thead>
			<tr>
				<th width=8%>DOC #</th>
				<th width=12%>POSTING DATE</th>
				<th width=15%>CONDUCTED BY</th>
				<th width=15%>VERIFIED BY</th>
				<th>DOCUMENT REMARKS</th>
				<th width=12%>AMOUNT</th>
				<th width=12%>DOC STATUS</th>
			</tr>
		</thead>
	</table>
    <div id="searchDiv" style="display: none;">
		<form name = "frmSearch" id = "frmSearch" method = "POST" action = "phy.list.php">
			<input type = "hidden" name = "sflag" id = "sflag" value = "Y">
			<table width = "100%" cellpading = 0 cellspacing = 0>
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