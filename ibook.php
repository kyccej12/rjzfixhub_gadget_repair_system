<?php
	
	session_start();
	include("handlers/_generics.php");
	$con = new _init();
	$today = date('Y-m-d');
	
	if(isset($_REQUEST['searchtext']) && !empty($_REQUEST['searchtext'])) { 
		$xsearch = " and (item_code like '%$_REQUEST[searchtext]%' || description like '%$_REQUEST[searchtext]%') "; 
	}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/datatables/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script>
		var sPO = "";

		$(document).ready(function() {
			$('#details').DataTable({
				"scrollY":  "340",
				"select":	'single',
				"searching": false,
				"paging": false,
				"info": false,
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [2,3,4,5,6,7,8,9,10,11]}
				]
			});
		});

		function viewStockcard() {
	
			var table = $("#details").DataTable();
			$.each(table.rows('.selected').data(), function() {
				item_code = this[0];
				lot_no = this[3];
				expiry = this[4];
			});

			if(!item_code) {
				parent.sendErrorMessage("- It appears you have not selected an item from the given list...")
			} else {
				parent.viewStockcard(item_code,lot_no,expiry,'<?php echo $_REQUEST['dtf']; ?>','<?php echo $_REQUEST['dt2']; ?>');
			}
		}

		
		function showSearch() {
			$("#search").dialog({title: "Search Record", width: 400, resizable: false, modal: true });
		}
		
		function searchRecord() {
			document.frmIbook.submit();
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
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
	<table id="details">	
		<thead>
			<tr>
				<th width=8%>CODE</th>
				<th>DESCRIPTION</th>
				<th width=7%>UNIT</th>
				<th width=7%>LOT NO</th>
				<th width=7%>EXPIRY</th>
				<th width=7%>BEG.</th>
				<th	with=12%>PURCHASES</th>
				<th width=8%>RETURNS</th>
				<th width=10%>WITHDRAWALS</th>
				<th width=8%>TRANSFERS</th>
				<th width=8%>SOLD</th>
				<th width=8%>QTY END</th>
			</tr>
		</thead>
		<tbody>
		<?php

			$rowsPerPage = 25;
			if(isset($_REQUEST['page'])) { if($_REQUEST['page'] <= 0) { $pageNum = 1; } else { $pageNum = $_REQUEST['page']; }} else { $pageNum = 1; }
			$offset = ($pageNum - 1) * $rowsPerPage;
			$searchString = '';

			if($_GET['group'] != "") { $searchString .= " and `category` = '$_GET[group]' "; }
			if($_GET['searchtext'] != '') { $searchString .= " and (a.item_code = '$_GET[searchtext]' || b.description like '%$_GET[searchtext]%') "; }
			$dtf = $con->formatDate($_GET['dtf']);
			$dt2 = $con->formatDate($_GET['dt2']);

			$query = "SELECT a.*,b.description, b.unit FROM (SELECT item_code, lot_no, expiry FROM phy_details WHERE branch = '$_SESSION[branchid]' UNION  SELECT item_code, lot_no, expiry FROM ibook WHERE doc_branch = '$_SESSION[branchid]') a LEFT JOIN products_master b ON a.item_code = b.item_code WHERE 1=1 $searchString group by a.item_code";
			//echo $query;
			/* Paging */
			$numrows = $con->getArray("select count(*) from ($query) a;");
			$maxPage = ceil($numrows[0]/$rowsPerPage);
			$_i = $con->dbquery("$query ORDER BY a.item_code LIMIT $offset,$rowsPerPage");
			
			while($row = $_i->fetch_array()) {
				
				/* Check Last Physical Inventory for the Item */
				list($baseD8) = $con->getArray("select posting_date from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch = b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$row[item_code]' and b.lot_no = '$row[lot_no]' and b.expiry = '$row[expiry]' order by posting_date desc limit 1;");
				if($baseD8 == '') { $baseD8 = '2022-02-09'; }
				
				if($dtf < $baseD8) { $dtf = $baseD8; }
			

				/* Forward Balance = From Last Date of Physical Count and Before Period Start */
				$pi = $con->getArray("select ifnull(sum(b.qty),0) from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch=b.branch where a.branch = '$_SESSION[branchid]' and b.item_code = '$row[item_code]' and lot_no = '$row[lot_no]' and expiry = '$row[expiry]' and a.status = 'Finalized' and a.posting_date = '$baseD8' GROUP BY b.item_code;");
				$run = $con->getArray("select sum(purchases+inbound-outbound-pullouts-sold) as run from ibook where doc_date >= '$baseD8' and doc_date < '" . $con->formatDate($_GET['dtf']) . "' and item_code = '$row[item_code]' and lot_no = '$row[lot_no]' and expiry = '$row[expiry]' and doc_branch = '$_SESSION[branchid]';");
				
				/* Inventory Net Balance for the Specified Period */
				$cur = $con->getArray("SELECT IFNULL(SUM(purchases),0) AS purchases, IFNULL(SUM(inbound),0) AS `returns`, IFNULL(SUM(pullouts),0) AS withdrawals, IFNULL(SUM(outbound),0) AS transfers, IFNULL(SUM(sold),0) AS sold, IFNULL(SUM(purchases+inbound-outbound-pullouts-sold),0) AS currentbalance from ibook where item_code = '$row[item_code]' and doc_date between '$dtf' and '$dt2' and doc_branch = '$_SESSION[branchid]';");
				//echo "select sum(purchases) as purchases, sum(inbound) as returns, sum(pullouts) as withdrawals, sum(outbound) as transfers, sum(sold) as sold, sum(purchases+inbound-outbound-pullouts-sold) as currentbalance from ibook where item_code = '$row[item_code]' and lot_no = '$row[lot_no]' and expiry = '$row[expiry]' and doc_date between '" . $con->formatDate($_GET['dtf']) . "' and '"  .$con->formatDate($_GET['dt2']) . "' and doc_branch = '$_SESSION[branchid]';<br/>";
				
				$end = ROUND($pi[0]+$run[0]+$cur['currentbalance'],2);
				//if($end!=0){
					echo "<tr>
						<td>".$row['item_code']."</td>
						<td>".$row['description']."</td>
						<td>".$row['unit']."</td>
						<td>".$row['lot_no']."</td>
						<td>".$row['expiry']."</td>
						<td>".number_format(($pi[0]+$run[0]),2)."</td>
						<td>".number_format($cur['purchases'],2)."</td>
						<td>".number_format($cur['returns'],2)."</td>
						<td>".number_format($cur['withdrawals'],2)."</td>
						<td>".number_format($cur['transfers'],2)."</td>
						<td>".number_format($cur['sold'],2)."</td>
						<td>".number_format($end,2)."</td>
					</tr>"; $i++;
					unset($pi); unset($cur); unset($run); $end = 0;
				//}
			}

		?>
		</tbody>

	</table>

    <table bgcolor="#7f7f7f" width=100% cellpadding=5 cellspacing=0>
		<tr>
			<td align=left>
				<button onClick="viewStockcard();" class="buttonding"><img src="images/icons/bill.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;View Item Stock Card</b></button>
				<button onClick="showSearch();" class="buttonding"><img src="images/icons/search.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Search Record</b></button>
			</td>
			<?php if($numrows[0] > 0) { ?>
			<td align=right style="padding-right: 10px;"><?php if ($pageNum > 1) { ?><a href="javascript: parent.jumpIBookPage('<?php echo ($pageNum - 1); ?>','<?php echo $_REQUEST['searchtext']; ?>','<?php echo $_REQUEST['group']; ?>','<?php echo $_REQUEST['dtf']; ?>','<?php echo $_REQUEST['dt2']; ?>')" class="a_link" title="Previous Page"><span style="font-size: 18px;">&laquo;</span></a>&nbsp;<?php } ?>
				<span style="font-size: 12px;">Page <?php echo $pageNum; ?> of <?php echo $maxPage; ?></span>&nbsp;
					<?php if($pageNum != $maxPage) { ?><a href="javascript: parent.jumpIBookPage('<?php echo ($pageNum + 1); ?>','<?php echo $_REQUEST['searchtext']; ?>','<?php echo $_REQUEST['group']; ?>','<?php echo $_REQUEST['dtf']; ?>','<?php echo $_REQUEST['dt2']; ?>')" class="a_link" title="Next Page"><span style="font-size: 18px;">&raquo;</span></a><?php } ?>&nbsp;&nbsp;
						<?php if($maxPage > 1) { ?>
						<span style="font-size: 12px;">Jump To: </span><select id="jpage" name="jpage" style="width: 40px; padding: 0px;" onchange="javascript: parent.jumpIBookPage(this.value,'<?php echo $_REQUEST['searchtext']; ?>','<?php echo $_REQUEST['group']; ?>','<?php echo $_REQUEST['dtf']; ?>','<?php echo $_REQUEST['dt2']; ?>');">
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

	<div id="search" style="display: none;">
		<form name="frmIbook" id="frmIbook" action="ibook.php" method=GET>
			<table width=100% border=0 cellspacing=2 cellpadding=0>
				<tr>
					<td valign=top width="95%" class="td_content" style="padding: 10px;">		
						<table border="0" cellpadding="0" cellspacing="0" width=100%>
							<tr>
								<td width=35%><span class="spandix-l" valign=top>Search String :</span></td>
								<td>
									<input type="hidden" id="group" name="group" value="<?php echo $_GET['group']; ?>">
									<input type="hidden" id="dtf" name="dtf" value="<?php echo $_GET['dtf']; ?>">
									<input type="hidden" id="dt2" name="dt2" value="<?php echo $_GET['dt2']; ?>">
									<input type="text" id="searchtext" name="searchtext" class="nInput" style="width: 100%;" value="" />
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr><td colspan=2><hr></hr></td></tr>
							<tr>
								<td align=center colspan=2>
									<button type="button" onClick="searchRecord();" onkeypress="if(event.keyCode == 13) { searchRecord(); }" class="buttonding" style="width: 180px; font-size: 11px;"><img src="images/icons/search.png" width=24 height=24 align=absmiddle />&nbsp;&nbsp;Search Record Now</button>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</form>
	</div>
	</body>
</html>