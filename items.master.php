<?php
	session_start();
	include("handlers/_generics.php");
	$con = new _init();
	$bid = 1;

	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>RJZ Fix Hub System Ver. 1.0a</title>
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

	$(document).ready(function() {

			var myTable = $('#itemlist').DataTable({
				"scrollY":  "380",
				"select":	'single',
				"searching": false,
				"paging": false,
				"info": false,
				"bSort": false,
				"aoColumnDefs": [
					{ className: "dt-body-right", "targets": [5,6]},
					{ className: "dt-body-center", "targets": [0,1,4,7,8,9]},
					
				]
			});

			$('#itemlist tbody').on('dblclick', 'tr', function () {
				var data = myTable.row( this ).data();	
				parent.showItemInfo(data[0]);
			});

			$("#stxt").keyup(function(e) { 
				if(e.keyCode === 13 ) { searchRecord(); }
			})

		});
	
	function showPrintMaster() {
		parent.showFilterDiv();
	}
	
	function retrieveGroups(type) {
		if(type != "") {
			if(type == 1 || type == 2 || type == 4) {
				$.post("src/sjerp.php", { mod: "getGroups", type: type, sid: Math.random() }, function(data) {
					$("#item_group").html(data);
					$("#item_code").val('');	
				},"html");
			} else { $("#item_group").html('<option value="">- All Groups -</option>'); }
		} else {
			$("#item_group").html('<option value="">- All Groups -</option>');
		}
	}
	
	function printMaster() {
		window.open("reports/itemmaster.php?category="+$("#item_mgroup").val()+"&group="+$("#item_group").val()+"&sid="+Math.random()+"","Item Master List","location=1,status=1,scrollbars=1,width=640,height=720");
	}

	function editRecord() {
		var table = $("#itemlist").DataTable();		
		var rid;
		$.each(table.rows('.selected').data(), function() {
			rid = this[0];
		});

		if(!rid) {
			parent.sendErrorMessage("Please select record to view.");
		} else {
			parent.showItemInfo(rid);
		}
	}

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
	    font-size: 11px; padding: 3px;
		width: 99%; 
	}

	.ui-widget {
		font-size: 0.65em !important; /* Adjust this value down (e.g., 12px or 0.8em) until it fits your layout */
	}
	
	table.dataTable tr.odd { background-color: #f5f5f5;  }
	table.dataTable tr.even { background-color: white; }
	.dataTables_filter input { width: 250px; }
</style>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div id="mainDiv">
	<table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
		<tr>
			<td style="padding:0px;" valign=top>
				<table width="100%" cellspacing="0" cellpadding="0" style="margin-bottom: 5px;">
					<tr>
						<td>
							<a href="#" class="topClickers" onClick="parent.showItemInfo('');"><img src="images/icons/add.png" width=18 height=18 align=absmiddle />&nbsp;Add New Item</a>&nbsp;&nbsp;
							<a href="#" id="edit" class="topClickers" onClick="editRecord();"><img src="images/icons/edit.png" width=18 height=18 align=absmiddle />&nbsp;Edit Selected Record</a>&nbsp;&nbsp;
							<a href="#" class="topClickers" onClick="parent.showItems();"><img src="images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;Refresh List</a>&nbsp;
							<a href="#" class="topClickers" onClick="showPrintMaster();"><img src="images/icons/print.png" width=18 height=18 align=absmiddle />&nbsp;Print Master List</a>
						</td>
						<td align=right>
							<input name="stxt" id="stxt" type="text" class="gridInput" style="width: 240px; height: 24px;" value="<?php echo $_REQUEST['searchtext']; ?>" placeholder="Search Record">
							<button class="ui-button ui-widget ui-corner-all" onClick="javascript: searchRecord();">
								<span class="ui-icon ui-icon-search"></span> Search Record
							</button>
						</td>
					</tr>
				</table>
				<table class="cell-border" id="itemlist" style="font-size:11px;">
					<thead>
						<tr>
							<th width=5%>RECORD NO.</th>
							<th width=10%>ITEM CODE</th>
							<th>DESCRIPTION</th>
							<th width=12%>BRAND</th>
							<th width=15%>MODEL</th>
							<th width=8%>UNIT</th>
							<th width=8%>UNIT COST</th>
							<th width=8%>SRP</th>
							<th width=15%>CATEGORY</th>
							<th width=8%>REMAINING STOCKS</th>
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
							
							list($dCount) = $con->getArray("select count(*) from products_master where (item_code like '%$term%' || description like '%$term%');");
							if($dCount > 0) {
								$inQuery = '';
								$dCountQuery = $con->dbquery("select item_code from products_master where (item_code like '%$term%' || description like '%$term%');");
								while(list($dSO) = $dCountQuery->fetch_array()) {
									$inQuery .= "'$dSO',";
								}
								$inQuery .= "'0'";
								$inSO = "item_code in ($inQuery)";
							} else { $inSO = "item_code like '%$term%'"; }
							
							$searchString .= " and ($inSO || a.brand like '%$term%' || a.description like '%$term%' || a.full_description like '%$term%') ";
						
							list($totalRows) = $con->getArray("select format(count(*),0) from products_master;");
							$ender = "(filtered from $totalRows total entries)";
			
						} else { $ender = "entries"; }
				

						$query = "SELECT LPAD(a.record_id,6,0) as id, item_code,a.description,full_description as fulldesc,brand,d.description AS unit,unit_cost,b.mgroup,srp,c.sgroup, '' as qty_onhand FROM products_master a LEFT JOIN options_mgroup b ON a.category = b.mid LEFT JOIN options_sgroup c ON a.subgroup = c.sid LEFT JOIN options_units d ON a.unit = d.unit WHERE `active` = 'Y' $searchString";								
			
						/* Paging Section */
						$numrows = $con->getArray("select count(*) from ($query) a;");
						$maxPage = ceil($numrows[0]/$rowsPerPage);
						$_i = $con->dbquery("$query ORDER BY item_code asc LIMIT $offset,$rowsPerPage");

						$showFrom = ($pageNum - 1) * $rowsPerPage + 1;
						$showTo = $showFrom + $rowsPerPage - 1;
						if($showTo > $numrows[0]) { $showTo = $numrows[0]; }

						while($row = $_i->fetch_array()) {

						$pi = $con->getArray("select ifnull(sum(b.qty),0) as sumQ from phy_header a left join phy_details b on a.doc_no = b.doc_no and a.branch = b.branch where a.branch = '$bid' and b.item_code = '$row[item_code]' and a.status = 'Finalized' and a.posting_date = '2022-02-09' GROUP BY b.item_code;");				
						$cur = $con->getArray("select sum(purchases+inbound-outbound-pullouts-sold) as currentbalance from ibook where item_code = '$row[item_code]' and doc_date between '2022-02-09' and '".date('Y-m-d')."' and doc_branch = '$bid';");
						$onhand = ROUND($pi['sumQ']+$cur['currentbalance'],2);

							echo "<tr>
									<td>$row[id]</td>
									<td>$row[item_code]</td>
									<td>$row[description]</td>
									<td>$row[brand]</td>
									<td>$row[fulldesc]</td>
									<td>$row[unit]</td>
									<td>$row[unit_cost]</td>
									<td>$row[srp]</td>
									<td>$row[mgroup]</td>
									<td>$onhand</td>
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
			</td>
		</tr>
	</table>
 </div>
<div id="masterDiv" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td class="spandix-l" width="35%">Category :</td>
			<td align="left">
				<select name="item_mgroup" id="item_mgroup" style="width:  80%; font-size: 11px;" class="nInput" onchange="retrieveGroups(this.value);">
					<option value="">- All Categories -</option>
					<?php
						$mit = mysql_query("select mid,mgroup from options_mgroup;");
						while(list($o,$oo) = mysql_fetch_array($mit)) {
							echo "<option value='$o'>$oo</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4 colspan="2"></td></tr>
		<tr>
			<td class="spandix-l" width="35%">Inventory Group :</td>
			<td align="left">
				<select name="item_group" id="item_group" style="width: 80%; font-size: 11px;" class="nInput">
					<option value="">- All Groups -</option>
					<?php
						$iut = mysql_query("select `group`,group_description from options_igroup order by group_description asc;");
						while(list($t,$tt) = mysql_fetch_array($iut)) {
							echo "<option value='$t'>$tt</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4 colspan="2"></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="printMaster();" class="buttonding" style="font-size: 11px;"><img src="images/icons/pdf.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Generate Report</button>
			</td>
		</tr>
	</table>
</div>
<div id="mainLoading" style="display:none; width:100%;height:100%;position:absolute;top:0;margin:auto;"> 
	<div style="background-color:white;width:10%;height:20%;;margin:auto;position:relative;top:100;">
		<img style="display:block;margin-left:auto;margin-right:auto;" src="images/ajax-loader.gif" width=100 height=100 align=absmiddle /> 
	</div>
	<div id="mainLoading2" style="background-color:white;width:100%;height:100%;position:absolute;top:0;margin:auto;opacity:0.8;"> </div>
</div>
<form name="frmSearch" id="frmSearch" action="items.master.php" method="POST">
	<input type="hidden" name="isSearch" id="isSearch" value="Y">
	<input type="hidden" name="searchtext" id="searchtext" value="<?php echo $_REQUEST['searchtext']; ?>">
</form>
<form name="frmPaging" id="frmPaging" action="items.master.php" method="POST">
	<input type="hidden" name="page" id="page" value="<?php echo $pageNum; ?>">
	<input type="hidden" name="searchtext" id="searchtext" value="<?php echo $_REQUEST['searchtext']; ?>">	
</form>

</body>
</html>