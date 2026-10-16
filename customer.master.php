<?php
	session_start();
	// ini_set("display_errors","on");
	include("handlers/_generics.php");
	$con = new _init();
	
	function getMod($def,$mod) {
		if($def == $mod) { echo "class=\"float2\""; }
	}
	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Bata Esensyal</title>
<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
<link href="style/style.css" rel="stylesheet" type="text/css" />
<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
<script>

	function changeMod(mod) {
		document.changeModPage.mod.value = mod;
		document.changeModPage.submit();
	}

	function editRecord() {

		var table = $("#itemlist").DataTable();
		var data = table.rows('.selected').data();

		if(data.length === 0) {

			parent.sendErrorMessage(
				'Please select a record from the list, and once highlighted, click "<b><i>Edit Selected Record</i></b>" again...'
			);

			return;
		}

		// Column 0 = Customer ID
		var custID = data[0][0];

		if(!custID || custID === "undefined") {

			parent.sendErrorMessage(
				'Unable to determine the selected customer.'
			);

			return;
		}

		parent.showCustomerInfo(custID);
	}
	
	$(document).ready(function() {
		var myTable = $('#itemlist').DataTable({
			"scrollY":  "380",
			"select":	'single',
			"searching": false,
			"paging": false,
			"info": false,
			"bSort": false,
			"aoColumnDefs": [
				{ className: "dt-body-center", "targets": [0,1,4]},
			]
		});

		$('#itemlist tbody').on('dblclick', 'tr', function () {
			var data = myTable.row( this ).data();	
			parent.showCustomerInfo(data[0]);
		});

		$("#stxt").keyup(function(e) { 
			if(e.keyCode === 13 ) { searchRecord(); }
		})
	});

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
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" >
 <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
	<tr>
		<td style="padding:0px;" valign=top>
			<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-top: 10px;">
				<tr>
					<td align=left style="padding-right: 20px;">
						<a href="#" class="topClickers" onClick="parent.addCustomer('');"><img src="images/icons/adduser.png" width=18 height=18 align=absmiddle />&nbsp;New Customer</a>&nbsp;&nbsp;
						<a href="#" class="topClickers" onClick="editRecord();"><img src="images/icons/edit.png" width=18 height=18 align=absmiddle />&nbsp;Edit Record</a>&nbsp;&nbsp;
						<a href="#" class="topClickers" onClick="parent.showCustProfiles();"><img src="images/icons/refresh.png" width=18 height=18 align=absmiddle />&nbsp;Reload List</a>
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
						<th width=7%>ID</th>
						<th width=15%>LAST NAME</th>
						<th width=15%>FIRST NAME</th>
						<th width=15%>MIDDLE NAME</th>
						<th>ADDRESS</th>
						<th width=15%>CONTACT NO</th>
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
							$searchString .= " and (file_id = '$_REQUEST[searchtext]' || lname like '$term%' || fname like '$term%' || mname like '$term%') ";
						
							list($totalRows) = $con->getArray("select format(count(*),0) from customer_info;");
							$ender = "(filtered from $totalRows total entries)";
			
						} else { $ender = "entries"; }

						$query = "SELECT LPAD(file_id,6,0) AS cust_id, a.lname, a.fname, a.mname, a.brgy, a.city, a.province, email, contact_no FROM customer_info a WHERE 1=1 AND record_status != 'Deleted' $searchString";

						/* Paging Section */
						$numrows = $con->getArray("select count(*) from ($query) a;");
						$maxPage = ceil($numrows[0]/$rowsPerPage);
						$_i = $con->dbquery("$query ORDER BY lname, fname, mname LIMIT $offset,$rowsPerPage");
						
						$showFrom = ($pageNum - 1) * $rowsPerPage + 1;
						$showTo = $showFrom + $rowsPerPage - 1;
						if($showTo > $numrows[0]) { $showTo = $numrows[0]; }

						while($row = $_i->fetch_array()) {
							$myaddress = "";
				
							list($brgy) = $con->getArray("SELECT brgyDesc FROM options_brgy WHERE brgyCode = '$row[brgy]';");
							list($ct) = $con->getArray("SELECT citymunDesc FROM options_cities WHERE cityMunCode = '$row[city]';");
							list($prov) = $con->getArray("SELECT provDesc FROM options_provinces WHERE provCode = '$row[province]';");
						
							if($brgy != "") { $myaddress.=$brgy.", "; }
							if($ct != "") { $myaddress.=$ct.", "; }
							if($prov != "")  { $myaddress.=$prov.", "; }
							$myaddress = substr($myaddress,0,-2);

							echo "<tr>
									<td>$row[cust_id]</td>
									<td>$row[lname]</td>
									<td>$row[fname]</td>
									<td>$row[mname]</td>
									<td>".$myaddress."</td>
									<td>".$row['contact_no']."</td>
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
 <div id="mainLoading" style="display:none; width:100%;height:100%;position:absolute;top:0;margin:auto;"> 
	<div style="background-color:white;width:10%;height:20%;;margin:auto;position:relative;top:100;">
		<img style="display:block;margin-left:auto;margin-right:auto;" src="images/ajax-loader.gif" width=100 height=100 align=absmiddle /> 
	</div>
	<div id="mainLoading2" style="background-color:white;width:100%;height:100%;position:absolute;top:0;margin:auto;opacity:0.8;"> </div>
</div>
 <form name="changeModPage" id="changeModPage" action="customer.master.php" method="GET" >
	<input type="hidden" name="mod" id="mod">
</form>
<form name="frmSearch" id="frmSearch" action="customer.master.php" method="POST">
	<input type="hidden" name="isSearch" id="isSearch" value="Y">
	<input type="hidden" name="searchtext" id="searchtext" value="<?php echo $_REQUEST['searchtext']; ?>">
</form>
<form name="frmPaging" id="frmPaging" action="customer.master.php" method="POST">
	<input type="hidden" name="page" id="page" value="<?php echo $pageNum; ?>">
	<input type="hidden" name="searchtext" id="searchtext" value="<?php echo $_REQUEST['searchtext']; ?>">	
</form>
</body>
</html>
