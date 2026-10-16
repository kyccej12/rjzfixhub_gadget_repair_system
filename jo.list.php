<?php
	session_start();
	include("handlers/_generics.php");
	require_once("handlers/session.guard.php");
	$con = new _init();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>RJZ Fix Hub Repair Shop System Ver. 1.0a</title>
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

		function viewJO() {
				var table = $("#itemlist").DataTable();
				var row = table.row(".selected").node();

				if (!row) {
					parent.sendErrorMessage("Please select a Job Order to open.");
					return;
				}

				var jo_no = $(row).attr("data-jo-no");

				if (!jo_no) {
					parent.sendErrorMessage("Unable to retrieve the Job Order number.");
					return;
				}

				parent.viewJO(jo_no);
			}
		
		$(document).ready(function() {
			var myTable = $('#itemlist').DataTable({
				"scrollY":  "430px",
				"select":	'single',
				"searching": false,
				"paging": false,
				"info": false,
				"bSort": false,
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [0,1,3,5,6] },
					{ "targets": [4], "visible": false }

				]
			});

			$('#itemlist tbody').on('dblclick', 'tr', function () {
				var jo_no = $(this).attr("data-jo-no");

				if (jo_no) {
					parent.viewJO(jo_no);
				} else {
					parent.sendErrorMessage("Unable to retrieve the Job Order number.");
				}
			});

		});
		
		// function exportList() {
		// 	var dis = $("#export").dialog({
		// 		title: "Export List to Excel",
		// 		width: 480,
		// 		modal: true,
		// 		resizable: false,
		// 		buttons: {
		// 			"Export List to Excel": function() {
		// 				window.open("export/crlist.php?dtf="+$("#dtf").val()+"&dt2="+$("#dt2").val()+"&cname="+$("#cname").val()+"&sid="+Math.random()+"","Collection Receipt Summary","location=1,status=1,scrollbars=1,width=640,height=720");
		// 			},
		// 			"Close": function() { dis.dialog("close"); }
		// 		}
		// 	});
		// }

		function searchRecord() {
			$("#mainLoading").css("z-index","999");
			$("#mainLoading").show();

			var stxt = $("#stxt").val();
			document.frmSearch.searchtext.value = stxt;
			document.frmSearch.submit();
		}

		function jumpPage(page,stxt) {

			$("#mainLoading").css("z-index","999");
			$("#mainLoading").show();

			document.frmPaging.page.value = page;
			document.frmPaging.searchtext.value = stxt;
			document.frmPaging.submit();
		}
		
	</script>
	<style>
		.dataTables_wrapper {
			display: inline-block;
			font-size: 11px;
			width: 99%; 
		}
		
		table.dataTable tr.odd { background-color: #f5f5f5;  }
		table.dataTable tr.even { background-color: white; }
		.dataTables_filter input { width: 250px; }

		.custom-header-blue .ui-dialog-titlebar {
    background: #0073aa !important; /* Forces your color over theme backgrounds */
    color: #ffffff !important;      /* Changes text color */
}

	</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
	<div id="mainDiv">
		<table style="width: 100%; border-collapse: collapse; margin-bottom: 5px; font-family: sans-serif; font-size: 11px;">
			<tr>
				<!-- Left Side Buttons -->
				<td style="padding: 0; display: flex; gap: 6px;">
					<button class="ui-button ui-widget ui-corner-all" onClick="parent.viewJO('');">
						<span class="ui-icon ui-icon-plusthick"></span> Create Job Order
					</button>
					<button class="ui-button ui-widget ui-corner-all" onClick="viewJO();">
						<span class="ui-icon ui-icon-newwin"></span> Open Selected Job Order
					</button>
					<button class="ui-button ui-widget ui-corner-all" onClick="parent.showJO();">
						<span class="ui-icon ui-icon-refresh"></span> Reload List
					</button>
				</td>
				
				<!-- Right Side Search Box -->
				<td style="text-align: right;">
					<input name="stxt" id="stxt" type="text" class="gridInput" style="width: 240px; height: 25px; border-radius: 4px; vertical-align: middle;" value="<?php echo $_REQUEST['searchtext']; ?>" placeholder="Search Record">
					<button class="ui-button ui-widget ui-corner-all" style="vertical-align: middle;" onClick="searchRecord();">
						<span class="ui-icon ui-icon-search"></span> Search Record
					</button>
				</td>
			</tr>
		</table>

		<table class="cell-border" id="itemlist" style="font-size:11px;">
			<thead>
				<tr>
					<th width=12%>JO #</th>
					<th width=8%>JO DATE</th>
					<th width=20%>CUSTOMER</th>
					<th width=10%>UNIT</th>
					<th>HISTORY</th>
					<th width=10%>AMOUNT TOTAL</th>
					<th width=12%>STATUS</th>
				</tr>
			</thead>
			<tbody>
				<?php
					$rowsPerPage = 50;
					if(isset($_REQUEST['page'])) { if($_REQUEST['page'] <= 0) { $pageNum = 1; } else { $pageNum = $_REQUEST['page']; }} else { $pageNum = 1; }
					$offset = ($pageNum - 1) * $rowsPerPage;
					$searchString = '';

					if($_REQUEST['searchtext'] && $_REQUEST['searchtext'] != '') {
						$term = htmlentities(trim($_REQUEST['searchtext']));
						
						list($dCount) = $con->getArray("select count(*) from jo_details where (code = '$_REQUEST[searchtext]' || description like '%$term%');");
						if($dCount > 0) {
							$inQuery = '';
							$dCountQuery = $con->dbquery("select jo_no from jo_details where (code = '$_REQUEST[searchtext]' || description like '%$term%');");
							while(list($dSO) = $dCountQuery->fetch_array()) {
								$inQuery .= "'$dSO',";
							}
							$inQuery .= "'0'";
							$inSO = "a.jo_no in ($inQuery)";
						} else { $inSO = "a.jo_no = '$_REQUEST[searchtext]'"; }
						
						$searchString .= " and ($inSO || a.customer_code = '$_REQUEST[searchtext]' || a.customer_name like '%$term%' || a.remarks like '%$term%' || b.description like '%$_REQUEST[searchtext]%' || DATE_FORMAT(a.jo_date,'%m/%d/%Y') like '%$_REQUEST[searchtext]%') ";
					
						list($totalRows) = $con->getArray("select format(count(*),0) from so_header;");
						$ender = "(filtered from $totalRows total entries)";
		
					} else { $ender = "entries"; }

					$query = "SELECT distinct
						a.jo_no AS jo_no,
						LPAD(a.jo_no,6,'0') AS jo,
						DATE_FORMAT(a.jo_date,'%m/%d/%Y') AS sdate,
						a.customer_name, 
						IF(a.customer_code=0,
						CONCAT(a.customer_name, '(Customer)'),
						a.customer_name) AS customer,
						b.model,
						b.brand,
						concat(a.unit_name,'  ',a.unit_model) as unit,
						FORMAT(a.amount,2) AS amount,
						a.status
					FROM jo_header a
					LEFT JOIN jo_details b
						ON a.jo_no = b.jo_no
						AND a.trace_no = b.trace_no
					WHERE a.branch = '$_SESSION[branchid]' $searchString";

					/* Paging Section */
					$numrows = $con->getArray("select count(*) from ($query) a;");
					$maxPage = ceil($numrows[0]/$rowsPerPage);
					$_i = $con->dbquery("$query ORDER BY a.jo_no desc LIMIT $offset,$rowsPerPage");
					
					$showFrom = ($pageNum - 1) * $rowsPerPage + 1;
					$showTo = $showFrom + $rowsPerPage - 1;
					if($showTo > $numrows[0]) { $showTo = $numrows[0]; }

					while($row = $_i->fetch_array()) {
		
						echo "<tr data-jo-no='" . htmlspecialchars($row['jo_no'], ENT_QUOTES, 'UTF-8') . "'>							
								<td>$row[jo]</td>
								<td>$row[sdate]</td>
								<td>$row[customer_name]</td>
								<td>$row[unit] </td>
								<td>$row[remarks]</td>
								<td>$row[amount]</td>
								<td>$row[status]</td>
						</tr>";

					}

				?>

			</tbody>
		</table>
		<table bgcolor="#e9e9e9" width=100% cellpadding=5 cellspacing=0>
			<tr>
				<?php if($numrows[0] > 0) { ?>
				<td>
					<span style="font-size: 11px; font-weight: bold;"><?php echo "Showing " . number_format($showFrom) . " to " . number_format($showTo) . " of " . number_format($numrows[0]) . " " . $ender ?></span>
				</td>
				<td align=right style="padding-right: 10px;"><?php if ($pageNum > 1) { ?><a href="javascript:jumpPage('<?php echo ($pageNum - 1); ?>','<?php echo $_REQUEST['searchtext']; ?>')" class="a_link" title="Previous Page"><span style="font-size: 18px;">&laquo;</span></a>&nbsp;<?php } ?>
					<span style="font-size: 11px;">Page <?php echo $pageNum; ?> of <?php echo $maxPage; ?></span>&nbsp;
						<?php if($pageNum != $maxPage) { ?><a href="javascript:jumpPage('<?php echo ($pageNum + 1); ?>','<?php echo $_REQUEST['searchtext']; ?>')" class="a_link" title="Next Page"><span style="font-size: 18px;">&raquo;</span></a><?php } ?>&nbsp;&nbsp;
							<?php if($maxPage > 1) { ?>
							<span style="font-size: 11px;">Jump To: </span>
								<select id="jpage" name="jpage" style="width: 40px; padding: 0px;" onchange="javascript:jumpPage(this.value,'<?php echo $_REQUEST['searchtext']; ?>');">
								<?php
										for ($x = 1; $x <= $maxPage; $x++) {
											echo "<option value='$x' ";
											if($pageNum == $x) { echo "selected"; }
											echo ">$x</option>";
										}
									?>
									</select>
						<?php } ?>
				</td> 
				<?php } ?>
			</tr>
		</table>
	</div>
	<div id="mainLoading" style="display:none; width:100%;height:100%;position:absolute;top:0;margin:auto;"> 
	<div style="background-color:white;width:10%;height:20%;;margin:auto;position:relative;top:100;">
		<img style="display:block;margin-left:auto;margin-right:auto;" src="images/ajax-loader.gif" width=100 height=100 align=absmiddle /> 
	</div>
	<div id="mainLoading2" style="background-color:white;width:100%;height:100%;position:absolute;top:0;margin:auto;opacity:0.8;"> </div>
	</div>
	<form name="frmSearch" id="frmSearch" action="so.list.php" method="POST">
		<input type="hidden" name="isSearch" id="isSearch" value="Y">
		<input type="hidden" name="searchtext" id="searchtext" value="<?php echo $_REQUEST['searchtext']; ?>">
	</form>
	<form name="frmPaging" id="frmPaging" action="so.list.php" method="POST">
		<input type="hidden" name="page" id="page" value="<?php echo $pageNum; ?>">
		<input type="hidden" name="searchtext" id="searchtext" value="<?php echo $_REQUEST['searchtext']; ?>">	
	</form>
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