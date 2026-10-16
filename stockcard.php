<?php
	session_start();
	include("handlers/_generics.php");
	
	$con = new _init();

?>
<html lang="en">
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
			var myTable = $('#details').DataTable({
				"scrollY":  "390",
				"select":	'single',
				"searching": false,
				"paging": false,
				"info": false,
				"bSort": false,
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1,2,3,4,5,6]}
				]
			});

			$('#details tbody').on('dblclick', 'tr', function () {
				viewDetails();
			});

		});


		function viewDetails() {
			
			var table = $("#details").DataTable();	
			var docno;
			var doctype;
		
			$.each(table.rows('.selected').data(), function() {
				docno = this[0];
				doctype = this[2];

			});
			
			if(!docno) {
				parent.sendErrorMessage("Please select any record from the given list first...");
			} else {
	
				switch(doctype) {
					case "SI":
						parent.viewSI(docno);
					break;
					case "RR":
						parent.viewRR(docno);
					break;
					case "SRR":
						parent.viewSRR(docno);
					break;
					case "SW":
						parent.viewSW(docno);
					break;
					case "STR":
						parent.viewSTR(docno);
					break;
					case "SO":
						parent.viewSO(docno);
					break;
				}
			}
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

	<table id = "details" class = "cell-border" style="font-size: 11px; width: 100%;">
		
		<thead>
			<tr>
				<th>DOC #</th>
				<th>DOC DATE</th>
				<th>DOC TYPE</th>
				<th width=30%>DESTINATION/ORIGIN</th>
				<th>IN</th>
				<th>OUT</th>
				<th>RUN. QTY</th>
			</tr>
		</thead>
		
		<tbody>
		
			<?php
			
				list($idesc) = $con->getArray("select description from products_master where item_code = '".addslashes($_GET['item_code'])."';");
				
					
				/* Ending from Previous non-covered period */
				//list($forQty) = $con->getArray("select sum(purchases+inbound-outbound-pullouts-sold) as run from ibook where doc_date >= '2023-01-01' and doc_date < '" . date('Y-m-d') . "' and item_code = '" . addslashes($_GET['item_code']) . "';");
				$forQty = 0;		

				/* Ending from Covered Period */
				$forBalance = $begQty + $forQty;

				echo "<tr>
						<td colspan=6><b>BALANCE FORWARDED FROM PREVIOUS PERIOD >></b></td>
						<td style=\"display: none;\"></td>
						<td style=\"display: none;\"></td>
						<td style=\"display: none;\"></td>
						<td style=\"display: none;\"></td>
						<td style=\"display: none;\"></td>
						<td><b>".number_format($forBalance,2)."</b></td>
					</tr>";
				
				$query = $con->dbquery("select doc_type, doc_no, lpad(doc_no,6,0) as xdoc, cname, date_format(doc_date,'%m/%d/%Y') as dd8, if((purchases+inbound-pullouts-outbound-sold) > 0,abs(purchases+inbound-pullouts-outbound-sold),0) as `in`, if((purchases+inbound-pullouts-outbound-sold) < 0,(purchases+inbound-pullouts-outbound-sold),0) as `out`, (purchases+inbound-pullouts-outbound-sold) as run from ibook where item_code = '" . addslashes($_GET['item_code']) . "' and doc_date between '2023-01-01' and '". date('Y-m-d') ."' order by doc_date asc;");
				while($row = $query->fetch_array()) {
					if($i%2==0){ $bgC = "#f5f5f5"; } else { $bgC = "#ffffff"; }

					if($run != "") { $run += $row['run']; } else { $run = $forBalance + $row['run']; }
					
					echo "<tr>
							<td>".$row['xdoc']."</td>
							<td>".$row['dd8']."</td>
							<td>".$row['doc_type']."</td>
							<td>".$row['cname']."&nbsp;</td>
							<td>".number_format($row['in'],2)."</td>
							<td>".abs(number_format($row['out'],2))."</td>
							<td>".number_format($run,2)."</td>
					</tr>"; $i++;
				}
				
			?>
			</tbody>
		</table>
	</div>
    <table width=100% cellpadding=5 cellspacing=0>
		<tr>
			<td align=left>
				<button onClick="viewDetails();" class="buttonding"><img src="images/icons/bill.png" width=24 height=24 align=absmiddle />&nbsp;&nbsp;View Transaction Details</b></button>
			</td>
		</tr>
	</table>
	</body>
</html>