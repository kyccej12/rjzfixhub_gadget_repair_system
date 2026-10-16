<?php
	session_start();
	require_once '../handlers/_generics.php';
	$mydb = new _init;

?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Red Global Land Properties Corp.</title>
	<link href="../ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="../style/style.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" type="text/css" href="../ui-assets/datatables/css/jquery.dataTables.css">
	<script language="javascript" src="../ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="../ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="../js/jquery.dialogextend.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="../ui-assets/datatables/js/dataTables.select.js"></script>
	<script>

		var UID = "";
		
		function init() {
			var table = $("#itemlist").DataTable();
			var arr = [];
		   $.each(table.rows('.selected').data(), function() {
			   arr.push(this["record_id"]);
		   });
		   UID = arr[0];
		}
		
		$(document).ready(function() {
			$("#loan_date").datepicker(); 
			$("#doc_date").datepicker();
			$("#effective_date").datepicker() 
		
			$('#itemlist').dataTable({
				"scrollY":  "350px",
				"select":	'single',
				"pagingType": "full_numbers",
				"bProcessing": true,
				"order": [[ 9, "desc" ]], 
				"sAjaxSource": "listings.php?mod=loans&sid="+Math.random()+"",
				"aoColumns": [
				  { mData: 'record_id' },
				  { mData: 'emp_id' },
				  { mData: 'emp_name' },
				  { mData: 'deyt' },
				  { mData: 'loan_type' },
				  { mData: 'loan_terms' },
				  { mData: 'gross_amt', render: $.fn.dataTable.render.number(',', '.', 2, '') },
				  { mData: 'monthly_amrtz', render: $.fn.dataTable.render.number(',', '.', 2, '') },
				  { mData: 'balance', render: $.fn.dataTable.render.number(',', '.', 2, '') },
				  { mData: 'date_loan' },
				  
				],
				"aoColumnDefs": [
					{ className: "dt-body-center", "targets": [1,3,4,5,6,7,8]},
					{ "targets": [0,9], "visible": false }
				]
			});
		});
		
		function viewRecord() {
			
			init();
			
			if(!UID) {
				parent.sendErrorMessage("Unable to retrieve record. Please select a record from the list, and once highlighted, press  \"<b><i>View Record Details</i></b>\" button again...");
			} else {
				$.post("misc-data.php", { mod: "getLoan", id: UID, sid: Math.random() }, function(ech) {
					$("#rid").val(ech['record_id']);
					$("#emp_id").val(ech['emp_id']);
					$("#loan_type").val(ech['loan_type']);
					$("#loan_date").val(ech['date_loan']);
					$("#loan_terms").val(ech['loan_terms']);
					$("#amount").val(ech['gamt']);
					$("#semi_amrtz").val(ech['amrtz']);
					$("#monthly_amrtz").val(parent.kSeparator(ech['monthly_amrtz']));
					$("#dedu_type").val(ech['dedu_type']);
					$("#effective_date").val(ech['eff']);
					$("#doc_no").val(ech['doc_no']);
					$("#doc_date").val(ech['dd8']);
					$("#doc_type").val(ech['doc_type']);
					$("#is_active").val(ech['active']);
					$("#remarks").html(ech['remarks']);
					
					$("#record").dialog({title: "Record Details", width: 440, resizable: false, modal: true, buttons: { 
						"Save Changes": function() {
							$.post("misc-data.php", { mod: "updateLoan", rid: $("#rid").val(), emp_id: $("#emp_id").val(), type: $("#loan_type").val(), loan_date: $("#loan_date").val(), amount: $("#amount").val(), terms: $("#loan_terms").val(), amrtz: $("#semi_amrtz").val(), monthly_amrtz: $("#monthly_amrtz").val(), eff: $("#effective_date").val(), dedu_type: $("#dedu_type").val(), active: $("#is_active").val(), remarks: $("#remarks").val(), doc_no: $("#doc_no").val(), doc_date: $("#doc_date").val(), doc_type: $("#doc_type").val(), sid: Math.random() }, function() {
								alert("Record Successfully Updated!");
								parent.showLoans();
							});
						},
						"Close": function() { $("#record").dialog("close"); }
					}}).dialogExtend({
						"closable" : true,
						"maximizable" : false,
						"minimizable" : true
					});
				},"json");
			}
		}
		
		function newRecord() {
			$(document.irec)[0].reset(); $("#remarks").html('');
			$("#record").dialog({title: "New Record", width: 440, resizable: false, modal: true, buttons: {
				"Save Record": function() {
					var msg = "";
					if($("#emp_id").val() == "") { msg = msg + "- Please select employee from the given list<br/>"; }
					if($("#remarks").val() == "") { msg = msg + "- Transaction remarks must be duly noted for future reference.<br/>"; }
					if($("#amount").val() != "") {
						var amt = parent.stripComma($("#amount").val());
						if(isNaN(amt) == true) { msg = msg + "- You have specified an invalid amount."; }
					}
					
				
					
					if(msg != "") { parent.sendErrorMessage(msg); } else {
						$.post("misc-data.php", { mod: "newLoan", emp_id: $("#emp_id").val(), type: $("#loan_type").val(), loan_date: $("#loan_date").val(), amount: $("#amount").val(), terms: $("#loan_terms").val(), amrtz: $("#semi_amrtz").val(), monthly_amrtz: $("#monthly_amrtz").val(), eff: $("#effective_date").val(), dedu_type: $("#dedu_type").val(), active: $("#is_active").val(), remarks: $("#remarks").val(), doc_no: $("#doc_no").val(), doc_date: $("#doc_date").val(), doc_type: $("#doc_type").val(), sid: Math.random() }, function() {
							alert("Record Successfully Saved!");
							parent.showLoans();
						});
					}
				},
				"Close": function() { $("#record").dialog("close"); }
			}}).dialogExtend({
				"closable" : true,
				"maximizable" : false,
				"minimizable" : true
			});
		}
		
		function deleteRecord() {
			if(UID == "") {
					parent.sendErrorMessage("Nothing to delete. Please select a record from the list, and once highlighted, press  \"<b><i>Delete Record</i></b>\" button again...");
			} else {
				if(confirm("Are you sure you want to delete this record?") == true) {
					$.post("misc-data.php", { mod: "deleteLoan", rid: UID, sid: Math.random() }, function() {
						alert("Record Successfully Deleted!");
						parent.showLoans();
					});
				}	
			}
		}
		
		function compute_semi() {
			var amt = parseFloat(parent.stripComma($("#amount").val()));
			var a = parseFloat($("#loan_terms").val());
			if(amt > 0 && a > 0) {
				
				var ansMonthly =  parseFloat(amt / a);
					ansMonthly = ansMonthly.toFixed(2);
				
				var ans = parseFloat(amt / (a * 2));
					ans = ans.toFixed(2);
					
				$("#semi_amrtz").val(parent.kSeparator(ans));
				$("#monthly_amrtz").val(parent.kSeparator(ansMonthly));
			}
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
	<div id="main">
		<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-top: 10px;">
			<tr>
				<td align=left style="padding-right: 20px;">
					<a href="#" class="topClickers" onClick="newRecord();"><img src="../images/icons/adduser.png" width=18 height=18 align=absmiddle />&nbsp;New Record</a>&nbsp;
					<a href="#" class="topClickers" onClick="viewRecord();"><img src="../images/icons/edit.png" width=18 height=18 align=absmiddle />&nbsp;View/Edit Selected Record</a>&nbsp;
					<a href="#" class="topClickers" onClick="deleteRecord();"><img src="../images/icons/delete.png" width=18 height=18 align=absmiddle />&nbsp;Delete Selected Record</a>
				</td>
			</tr>
		</table>
		<table id="itemlist" style="font-size:11px;">
			<thead>
				<tr>
					<th width=10%>RID</th>
					<th width=10%>EMP. ID</th>
					<th width=20%>EMP. NAME</th>
					<th width=10%>DATE</th>
					<th width=20%>LOAN TYPE</th>
					<th width=10%>TERMS</th>
					<th width=10%>AMOUNT</th>
					<th width=10%>AMRTZ'N</th>
					<th>BALANCE</th>
				</tr>
			</thead>
		</table>
	</div>
	 <div id="record" style="display: none;">
		<form name="irec" id="irec">
			<table width=100% cellspacing=2 cellpadding=0>
				<tr>
					<td class="bareThin" align=left width=40%>Employee :</td>
					<td align=left>
						<input type="hidden" name="rid" id="rid" value="">
						<select name="emp_id" id="emp_id" style="width: 80%; font-size: 11px; padding: 5px;">
							<option value = "">- Select Employee -</option>
							<?php 
								$qemp = $mydb->dbquery("select emp_id, concat(lname,', ',fname,' ',left(mname,1),'.') as name from emp_masterfile where emp_type = '$_SESSION[payclass]' and FILE_STATUS != 'DELETED' AND EMPLOYMENT_STATUS NOT IN (7,8,9,10) order by lname asc;");
								while($emprow = $qemp->fetch_array(MYSQLI_BOTH)) {
									print "<option value='$emprow[0]' ";
										if($emprow[0] == $emp_id){ print "selected"; }
									print ">$emprow[1]</option>";
								}
								unset($qemp);
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Type :</td>
					<td align=left>
						<select name="loan_type" id="loan_type" style="width: 80%; font-size: 11px; padding: 5px;">
							<option value="">- Select Type -</option>
							<?php 
								$l_res = $mydb->dbquery("select id, loan_type from option_loantype order by loan_type asc;");
								while($l_row = $l_res->fetch_array(MYSQLI_BOTH)) {
									print "<option value='$l_row[0]' ";
										if($l_row[0] == $data['loan_type']){ print "selected"; }
									print ">$l_row[1]</option>";
								}
								unset($l_res);
							?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Loan Date :</td>
					<td align=left>
						<input type="text" name="loan_date" id="loan_date" style="width: 80%;">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Gross Amount :</td>
					<td align=left>
						<input type="text" name="amount" id="amount" style="width: 80%;" onchange="compute_semi();">
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Loan Terms :</td>
					<td align=left>
						<input type="text" name="loan_terms" id="loan_terms" style="width: 80%;" onchange="compute_semi();"> <font style="font-size: 11px;">(Mos.)</font>
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Semi-Monthly Amortization :</td>
					<td align=left>
						<input type="text" name="semi_amrtz" id="semi_amrtz" style="width: 80%;" readonly> 
					</td>
				</tr>
					<tr>
					<td class="bareThin" align=left width=40%>Monthly Amortization :</td>
					<td align=left>
						<input type="text" name="monthly_amrtz" id="monthly_amrtz" style="width: 80%;" readonly> 
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Effective Date of Deduction :</td>
					<td align=left>
						<input type="text" name="effective_date" id="effective_date" style="width: 80%;"> 
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Deduct Every :</td>
					<td align=left>
						<select class="gridInput" name="dedu_type" id="dedu_type" style="width: 80%; font-size: 11px;">
							<option value = "1">Every 15th</option>
							<option value = "2">Every 30th</option>
							<option value = "3">15th & 30th</option>
						</select>
					</td>
				</tr>
				
				<tr>
					<td class="bareThin" align=left width=40%>Ref. No. :</td>
					<td align=left>
						<input type="text" name="doc_no" id="doc_no" style="width: 80%;"> 
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Ref. Date :</td>
					<td align=left>
						<input type="text" name="doc_date" id="doc_date" style="width: 80%;"> 
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40%>Ref. Type :</td>
					<td align=left>
						<select class="gridInput" name="doc_type" id="doc_type" style="width: 80%; font-size: 11px;">
							<option value='SI'>Sales Invoice</option>
							<option value='DA'>Debit/Credit Advise</option>
							<option value='CV'>Check Voucher</option>
							<option value='JV'>Journal Voucher</option>
							<option value="">Others (See Remarks)</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40% valign=top>Notes/Remarks :</td>
					<td align=left><textarea style="width: 80%; font-size: 11px;" rows=1 name="remarks" id="remarks"></textarea></td>
				</tr>
				<tr>
					<td class="bareThin" align=left width=40% valign=top>Account Status:</td>
					<td align=left>
						<select class="gridInput" name="is_active" id="is_active" style="width: 80%; font-size: 11px;">
							<option value = "Y">- Active -</option>
							<option value = "N">- Deferred -</option>
						</select>
					</td>
				</tr>
			</table>
		</form>
	</div>
</body>
</html>