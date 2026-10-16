<?php
	include("handlers/_generics.php");
	session_start();
	
	$con = new _init();
	list($urights) = $con->getArray("select user_type from user_info where emp_id = '$_SESSION[userid]';");

	$rowsPerPage = 40;
	if(isset($_REQUEST['page'])) { if($_REQUEST['page'] <= 0) { $pageNum = 1; } else { $pageNum = $_REQUEST['page']; }} else { $pageNum = 1; }
	$offset = ($pageNum - 1) * $rowsPerPage;
	
	
	/* Added for Advance Search Function */
	if($_POST['sflag'] == 'Y') {
		
		$_POST['displayType'] = 6;

		$stx = " and 1=1 ";
		if($_POST['stxt_docno'] != '') { $stx .= " and doc_no = '$_POST[stxt_docno]' "; }
		if($_POST['stxt_amount'] != '') { $stx .= " and amount = '$_POST[stxt_amount]' "; }
		if($_POST['stxt_name'] != '') { $stx .= " and (customer_name like '%$_POST[stxt_name]%' || customer = '$_POST[stxt_name]') "; }
		if($_POST['stxt_invoice'] != '') { $stx .= " and invoice_no = '$_POST[stxt_invoice]' "; }
		if($_POST['stxt_salesrep'] != '') { $stx .= " and sales_rep = '$_POST[stxt_salesrep]' "; }
		
		if($_POST['stxt_description'] != '') { 
			$irec = $con->dbquery("select distinct(doc_no) as doc_no from invoice_details where (description like '%$_POST[stxt_description]%' or item_code = '$_POST[stxt_description]');");
			$sonos = '';
			while(list($soos) = $irec->fetch_array()) {
				$sonos .= $soos . ",";
			}
			$sonos = substr($sonos,0,-1);
			$stx .= " and doc_no in ($sonos) ";
		}
		if($_POST['stxt_crno'] != '') { 
		   	$irec1 = $con->dbquery("select distinct(doc_no) as doc_no from cr_details where trans_no = '$_POST[stxt_crno]';");
			$sonos1 = '';
			while(list($soos1) = $irec1->fetch_array()) {
				$sonos1 .= $soos1 . ",";
			}
			$sonos1 = substr($sonos1,0,-1);
			$stx .= " and doc_no in ($sonos1) ";
		}
		if($_POST['stxt_sono'] != '') { 
		  	$irec2 = $con->dbquery("select distinct(doc_no) as doc_no from invoice_details where so_no = '$_POST[stxt_sono]';");
			$sonos2 = '';
			while(list($soos2) = $irec2->fetch_array()) {
				$sonos2 .= $soos2 . ",";
			}
			$sonos2 = substr($sonos2,0,-1);
			$stx .= " and doc_no in ($sonos2) ";
		}
		if($_POST['stxt_dtf'] != '' && $_POST['stxt_dt2'] != '') {
			$stx .= " and invoice_date between '".$con->formatDate($_POST['stxt_dtf'])."' and '".$con->formatDate($_POST['stxt_dt2']) . "' ";
		} else {
			if($_POST['stxt_dtf'] != '') { $stx .= " and invoice_date = '".$con->formatDate($_POST['stxt_dtf']) . "' "; } 
			if($_POST['stxt_dt2'] != '') { $stx .= " and invoice_date = '".$con->formatDate($_POST['stxt_dt2']) . "' "; }
		}
	}

	/* Display range */
	$range = '';
	switch($_POST['displayType']) {
		case "1": 
			$range .= " and a.invoice_date = '".date('Y-m-d')."' ";
		break;
		case "2":
			list($dtf) = $con->getArray("SELECT DATE_SUB('".date('Y-m-d')."',INTERVAL 7 DAY);");
			$range .= " and a.invoice_date between  '$dtf' and '".date('Y-m-d')."' ";
		break;
		case "3":
			list($dtf) = $con->getArray("SELECT DATE_SUB('".date('Y-m-d')."',INTERVAL 30 DAY);");
			$range .= " and a.invoice_date between '$dtf' and '".date('Y-m-d')."' ";
		break;
		case "4":
			$range .= " and a.status = 'Active' ";
		break;
		case "5":
			$range .= " and a.status = 'Cancelled' ";
		break;
		case "6": default:
			$range = '';
		break;
	}



	
	//echo $stx;
	switch($_POST['sort']) { case "1": $sort = " order by doc_no desc "; break; case "2": $sort = " order by invoice_date desc "; break; case "3": $sort = " order by customer_name asc, invoice_date desc "; break; case "4": $sort = " order by invoice_no asc "; break; default: $sort = " order by doc_no desc "; break; }
	
	$str = "SELECT LPAD(doc_no,9,0) AS docno, DATE_FORMAT(invoice_date,'%m/%d/%Y') AS idate, if(customer=0,'CASH WALKIN CUSTOMER',customer_name) as customer_name, b.description AS terms, remarks, amount, a.status FROM invoice_header a LEFT JOIN options_terms b ON a.terms=b.terms_id where 1=1 $stx $range";
	
	//echo $str;
	$rec = $con->dbquery($str." $sort limit $offset,$rowsPerPage;");
	
	$numrows = $con->getArray("select count(*) from ($str) a;");
	$maxPage = ceil($numrows[0]/$rowsPerPage);
	
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Bata Esensyal</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css"-->
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script>
	

		$(document).ready(function() {
			var myTable = $('#itemlist').DataTable({
				"scrollY":  "370",
				"select":	'single',
				"searching": false,
				"paging": false,
				"info": false,
				"bSort": false,
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [0,1,3]},
					{ className: "dt-body-right", "targets": [4]},
					/* { "targets": [2,3,5,6,10], "visible": false }*/
				]
			});

			$('#itemlist tbody').on('dblclick', 'tr', function () {
				var data = myTable.row( this ).data();	
				parent.viewSI(data[0]);
			});

		});


		function viewSI() {

			var docno;
			var table = $("#itemlist").DataTable();			
			$.each(table.rows('.selected').data(), function() {
				docno = this[0];
			});

			if(docno == "") {
				parent.sendErrorMessage("It appears you have yet to any record to view or update.");
			} else {
				parent.viewSI(docno);
			}
		}
			
		function newSI() {
			
			$.post("si.datacontrol.php", { mod: "checkActive", sid: Math.random() }, function(data) {
				if(data.length > 0) {
					
					parent.sendErrorMessage("You have an existing active document that does not contain any data. Below are the documents that require your attention: <br/><br/>"+data);
					
				} else {
					
					parent.viewSI('');
					
				}
				
			},"html");
			
			
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
		
		function exportList() {
			$("#export_dtf").datepicker(); $("#export_dt2").datepicker();
			$("#exportDiv").dialog({
				title: "Export Invoice List", 
				width: 400,
				resizable: false, 
				modal: true, 
				buttons: {
					"Export List": function() {
						window.open("export/si.list.php?export_name="+$("#export_name").val()+"&export_dtf="+$("#export_dtf").val()+"&export_dt2="+$("#export_dt2").val()+"&sid="+Math.random()+"","List of Issued Invoices","location=1,status=1,scrollbars=1,width=640,height=720");
					},
					"Close": function() { $(this).dialog("close"); }
				}
			});
		}
		
		function paginateMe(pageNum) {
			document.pagination.page.value = pageNum;
			document.pagination.submit();
		}
		
		function sortMe(sort) {
			document.frmSort.sort.value = sort;
			document.frmSort.submit();
		}

		function filterRange(val) {
			document.frmDisplayType.displayType.value = val;
			document.frmDisplayType.submit();

		}
	
	</script>
	<style>
		.dataTables_wrapper {
			display: inline-block;
			font-size: 11px;
			width: 100%;
		}
		
		table.dataTable tr.odd { background-color: #f5f5f5;  }
		table.dataTable tr.even { background-color: white; }
		.dataTables_filter input { width: 250px; }
	</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
	<table width=100% cellpadding=5 cellspacing=0 style="padding-left: 5px; margin-bottom: 5spx;">
		<tr>
			<td align=left>
				<a href="#" class="topClickers" onClick="newSI();"><img src="images/icons/add-2.png" width=18 height=18 align=absmiddle />&nbsp;New Sales Invoice</a>&nbsp;
				<a href="#" class="topClickers" onClick="viewSI();"><img src="images/icons/bill.png" width=16 height=16 align=absmiddle />&nbsp;View/Edit Invoice</a>&nbsp;
				<a href="#" class="topClickers" onClick="parent.showSI();"><img src="images/icons/refresh.png" width=16 height=16 align=absmiddle />&nbsp;Reload List</a>&nbsp;
				<a href="#" onClick="searchRecord();" class="topClickers"><img src="images/icons/search.png" width=18 height=18 align=absmiddle />&nbsp;Search Record</a>
			</td>
			<td align=right style="pdding-right: 10px;">
				<span style="font-size: 11px;">Display Records:</span>&nbsp;&nbsp; 
				<select name="displayType" id="displayType" class="gridInput" style="width: 260px; font-size: 11px;" onchange="javascript: filterRange(this.value);">
					<option value="" <?php if($_POST['displayType'] == '') { echo "selected"; } ?>>All Transactions</option>
					<option value="1" <?php if($_POST['displayType'] == 1) { echo "selected"; } ?>>Today's Transactions</option> 
					<option value="2" <?php if($_POST['displayType'] == 2) { echo "selected"; } ?>>Last 7 Days</option>
					<option value="3" <?php if($_POST['displayType'] == 3) { echo "selected"; } ?>>Last 30 Days</option>
					<option value="4" <?php if($_POST['displayType'] == 4) { echo "selected"; } ?>>Active Transactions</option>
					<option value="5" <?php if($_POST['displayType'] == 5) { echo "selected"; } ?>>Cancelled Transactions</option>
				</select>
			</td>
		</tr>
	</table>
	
	
	<table id="itemlist" class="cell-border" width="100%" style="font-size: 11px;">
		<thead>
			<tr>
				<th width=10%>TRANS. #</th>
				<th width=10%>DOC DATE</th>
				<th width=20%>CUSTOMER NAME</th>
				<th width=10%>TERMS</th>
				<th width=10%>AMOUNT</th>
				<th>DOCUMENT REMARKS</th>
				<th width=10%>STATUS</th>

			</tr>
		</thead>
		<tbody id="itembody">

			<?php
				while($row = $rec->fetch_array()) {
					
					if($i%2==0){ $bgC = "#f5f5f5"; } else { $bgC = "#ffffff"; }
					echo "<tr>
							<td>".$row['docno']."</td>
							<td>".$row['idate']."</td>
							<td>".html_entity_decode($row['customer_name'])."</td>
							<td>".$row['terms']."</td>
							<td>".number_format($row['amount'],2)."</td>
							<td>".strtoupper($row['remarks'])."</td>
							<td>".strtoupper($row['status'])."</td>
						</tr>"; $i++;
				}
				?>
		</tbody>
	</table>
	 <table width=100% cellpadding=5 cellspacing=0>
		<tr>
			<td align=left>
				<span style="font-size: 11px;">Sort By:</span> <select name="mysort" id="mysort" class="gridInput" style="width: 260px; font-size: 11px;" onchange="sortMe(this.value);"><option value="1" <?php if($_POST['sort'] == 1) { echo "selected"; } ?>>Doc. No. (Descending)</option> <option value="2" <?php if($_POST['sort'] == 2) { echo "selected"; } ?>>Doc. Date (Descending)</option><option value="3" <?php if($_POST['sort'] == 3) { echo "selected"; } ?>>Customer Name (Ascending)</option><option value="4" <?php if($_POST['sort'] == 4) { echo "selected"; } ?>>Invoice No. Ascending</option></select>
			</td>
			<?php if($numrows[0] > 0) { ?>
			<td align=right style="padding-right: 10px;"><?php if ($pageNum > 1) { ?><a href="javascript: paginateMe('<?php echo ($pageNum - 1); ?>');" class="a_link" title="Previous Page"><span style="font-size: 18px;">&laquo;</span></a>&nbsp;<?php } ?>
				<span style="font-size: 12px;">Page <?php echo $pageNum; ?> of <?php echo $maxPage; ?></span>&nbsp;
					<?php if($pageNum != $maxPage) { ?><a href="javascript: paginateMe('<?php echo ($pageNum + 1); ?>');" class="a_link" title="Next Page"><span style="font-size: 18px;">&raquo;</span></a><?php } ?>&nbsp;&nbsp;
						<?php if($maxPage > 1) { ?>
						<span style="font-size: 12px;">Jump To: </span><select class="gridInput" id="jpage" name="jpage" style="width: 40px; padding: 0px;" onchange="javascript: paginateMe(this.value);">
								<?php
									for ($x = 1; $x <= $maxPage; $x++) {
										echo "<option value='$x' ";
										if($pageNum == $x) { echo "selected"; }
										echo ">$x</option>";
									}
								?>
								 </select>
					<?php } ?>
			</td> <?php } ?>
		</tr>
	</table>
	<div id="searchDiv" style="display: none;">
		<form name = "frmSearch" id = "frmSearch" method = "POST" action = "si.list.php">
			<input type = "hidden" name = "sflag" id = "sflag" value = "Y">
			<table width = "100%" cellpading = 0 cellspacing = 0>
				<tr>
					<td class="spandix-l">Doc. No. :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_docno" id="stxt_docno"></td>
				</tr>
					<tr>
					<td class="spandix-l">Invoice No. :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_invoice" id="stxt_docno"></td>
				</tr>
				<tr>
					<td class="spandix-l">Customer Code or Name :</td>
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
					<td class="spandix-l">Item Code or Description :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_description" id="stxt_description"></td>
				</tr>
				<tr>
					<td class="spandix-l">C.R # :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_crno" id="stxt_crno"></td>
				</tr>
				<tr>
					<td class="spandix-l">Invoice Amount :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_amount" id="stxt_amount"></td>
				</tr>
				<tr>
					<td class="spandix-l">S.O # :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="stxt_sono" id="stxt_sono"></td>
				</tr>
				<tr>
					<td class="spandix-l">Sales Rep :</td>
					<td >
						<select class="gridInput" id="stxt_salesrep" name="stxt_salesrep" style="width: 80%;" />
							<option value="">- All -</option>
							<?php
								$sr = $con->dbquery("select record_id,sales_rep from options_salesrep order by sales_rep;");
								while($srx = $sr->fetch_array()) {
									echo "<option value='$srx[0]' ";
									if($_POST['stxt_salesrep'] == $srx[0]) { echo "selected"; }
									
									echo ">$srx[1]</option>";

								}
							?>
						</select>
					
					</td>
				</tr>
				
			</table>
		</form>
	</div>
	<div id="exportDiv" style="display: none;">
		<form name = "frmExport" id = "frmExport">
			<table width = "100%" cellpading = 0 cellspacing = 0>
				<tr>
					<td class="spandix-l">Customer Code or Name :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="export_name" id="export_name"></td>
				</tr>
				<tr>
					<td class="spandix-l">Date Covered :</td>
					<td ><input type="text" style="width:80%;" class="nInput" name="export_dtf" id="export_dtf"></td>
				</tr>
				<tr>
					<td class="spandix-l"></td>
					<td ><input type="text" style="width:80%;" class="nInput" name="export_dt2" id="export_dt2"></td>
				</tr>
			</table>
		</form>
	</div>
	<form name="pagination" id="pagination" action = "si.list.php" method = "POST">
		<input type = "hidden" name = "page">
		<input type = "hidden" name = "sort" value = "<?php echo $_POST['sort']; ?>">
		<input type = "hidden" name = "sflag" value = "<?php echo $_POST['sflag']; ?>">
		<input type = "hidden" name = "stxt_docno" value = "<?php echo $_POST['stxt_docno']; ?>">
		<input type = "hidden" name = "stxt_invoice" value = "<?php echo $_POST['stxt_invoice']; ?>">
		<input type = "hidden" name = "stxt_name" value = "<?php echo $_POST['stxt_name']; ?>">
		<input type = "hidden" name = "stxt_dtf" value = "<?php echo $_POST['stxt_dtf']; ?>">
		<input type = "hidden" name = "stxt_dt2" value = "<?php echo $_POST['stxt_dt2']; ?>">
		<input type = "hidden" name = "stxt_description" value = "<?php echo $_POST['stxt_description']; ?>">
		<input type = "hidden" name = "stxt_crno" value = "<?php echo $_POST['stxt_crno']; ?>">
		<input type = "hidden" name = "stxt_sono" value = "<?php echo $_POST['stxt_sono']; ?>">
		<input type = "hidden" name = "stxt_amount" value = "<?php echo $_POST['stxt_amount']; ?>">
		<input type = "hidden" name = "displayType" value = "<?php echo $_POST['displayType']; ?>">
		<input type = "hidden" name = "stxt_salesrep" value = "<?php echo $_POST['stxt_salesrep']; ?>">
	</form>
	<form name="frmSort" id="frmSort" action = "si.list.php" method = "POST">
		<input type = "hidden" name = "sort">
		<input type = "hidden" name = "page" value = "<?php echo $_POST['page']; ?>">
		<input type = "hidden" name = "sflag" value = "<?php echo $_POST['sflag']; ?>">
		<input type = "hidden" name = "stxt_docno" value = "<?php echo $_POST['stxt_docno']; ?>">
		<input type = "hidden" name = "stxt_invoice" value = "<?php echo $_POST['stxt_invoice']; ?>">
		<input type = "hidden" name = "stxt_name" value = "<?php echo $_POST['stxt_name']; ?>">
		<input type = "hidden" name = "stxt_dtf" value = "<?php echo $_POST['stxt_dtf']; ?>">
		<input type = "hidden" name = "stxt_dt2" value = "<?php echo $_POST['stxt_dt2']; ?>">
		<input type = "hidden" name = "stxt_description" value = "<?php echo $_POST['stxt_description']; ?>">
		<input type = "hidden" name = "stxt_crno" value = "<?php echo $_POST['stxt_crno']; ?>">
		<input type = "hidden" name = "stxt_sono" value = "<?php echo $_POST['stxt_sono']; ?>">
		<input type = "hidden" name = "stxt_amount" value = "<?php echo $_POST['stxt_amount']; ?>">
		<input type = "hidden" name = "displayType" value = "<?php echo $_POST['displayType']; ?>">
		<input type = "hidden" name = "stxt_salesrep" value = "<?php echo $_POST['stxt_salesrep']; ?>">
	</form>
	<form name="frmDisplayType" id="frmDisplayType" action = "si.list.php" method = "POST">
		<input type = "hidden" name = "displayType">
		<input type = "hidden" name = "sort" value="<?php echo $_POST['sort']; ?>">
		<input type = "hidden" name = "page" value = "<?php echo $_POST['page']; ?>">
		<input type = "hidden" name = "sflag" value = "<?php echo $_POST['sflag']; ?>">
		<input type = "hidden" name = "stxt_docno" value = "<?php echo $_POST['stxt_docno']; ?>">
		<input type = "hidden" name = "stxt_invoice" value = "<?php echo $_POST['stxt_invoice']; ?>">
		<input type = "hidden" name = "stxt_name" value = "<?php echo $_POST['stxt_name']; ?>">
		<input type = "hidden" name = "stxt_dtf" value = "<?php echo $_POST['stxt_dtf']; ?>">
		<input type = "hidden" name = "stxt_dt2" value = "<?php echo $_POST['stxt_dt2']; ?>">
		<input type = "hidden" name = "stxt_description" value = "<?php echo $_POST['stxt_description']; ?>">
		<input type = "hidden" name = "stxt_crno" value = "<?php echo $_POST['stxt_crno']; ?>">
		<input type = "hidden" name = "stxt_sono" value = "<?php echo $_POST['stxt_sono']; ?>">
		<input type = "hidden" name = "stxt_amount" value = "<?php echo $_POST['stxt_amount']; ?>">
		<input type = "hidden" name = "stxt_salesrep" value = "<?php echo $_POST['stxt_salesrep']; ?>">
	</form>
</body>
</html>