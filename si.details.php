<?php	
	/* UNSET QUED FOR DELETION */
	session_start();
	//ini_set("display_errors","On");
	include("handlers/_generics.php");
	$con = new _init();
	
	include("functions/si.displayDetails.fnc.php");
	
	list($urights) = $con->getArray("select user_type from user_info where emp_id = '$_SESSION[userid]';");
	
	$uid = $_SESSION['userid'];
	if(isset($_REQUEST['docno']) && $_REQUEST['docno'] != '') { 
		$docno = $_REQUEST['docno']; 
		$res = $con->getArray("select *, if(customer=0,'CASH WALKIN CUSTOMER',customer_name) as cname, if(customer=0,'SURIGAO CITY',customer_addr) as caddr, lpad(customer,6,0) as cid, date_format(invoice_date,'%m/%d/%Y') as d8, if(posting_date='0000-00-00','',date_format(posting_date,'%m/%d/%Y')) as pd8 from invoice_header where doc_no = '$docno';");
		$cSelected = "Y"; $trace_no = $res['trace_no']; $status = $res['status']; $lock = $res['lock'];
	} else {  
		$trace_no = genTraceno();
		list($docno) = $con->getArray("SELECT lpad((IFNULL(MAX(doc_no),0)+1),9,0) FROM invoice_header;");
		$status = "Active"; $dS = "1"; $cSelected = "N"; $lock = "N";
	}
	
	function genTraceno() {

		global $con;

		$flag = true;
		while($flag){
			$trace_no = $con->generateRandomString();
			list($traceCount) = $con->getArray("SELECT COUNT(*) FROM invoice_header a WHERE a.trace_no = '$trace_no';");
			if($traceCount>0){
				$flag = true;
			}else{
				$flag = false;
			}
		}
		return $trace_no;
	}
	
	function setHeaderControls($status,$docno,$uid,$urights) {
		
		global $con;
		
		$headerControls = '';
		
		switch($status) {
			case "Finalized":
				if($urights == "admin") {
					$headerControls = "<a href=\"#\" class=\"topClickers\" onclick=\"javascript: reopenSI('$docno');\"><img src='images/icons/edit.png' align=absmiddle width=16 height=16 />&nbsp;Set this Document to Active Status</a>&nbsp;";
				}
				$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript:printSI('N');\"><img src=\"images/icons/print.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Print Sales Invoice</a>";
			break;
			case "Cancelled":
				if($urights == "admin") {
					$headerControls = $headerControls . "<a href=\"#\" class=\"topClickers\" onclick=\"javascript:reuseSI();\" style=\"padding: 5px;\"><img src=\"images/icons/refresh.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Recycle this Document</a>";	
				}
			break;
			case "Active": default:
			
				$headerControls .= "<a href=\"#\" class=\"topClickers\" onClick=\"javascript:finalizeSI();\"><img src=\"images/icons/ok.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Checkout & Finalize Transaction</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript:saveInvHeader();\"><img src=\"images/icons/floppy.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Save Changes Made</a>&nbsp;&nbsp;";

				if($urights == "admin") {
					$headerControls = $headerControls . "<a href=\"#\" class=\"topClickers\" onclick=\"javascript:cancelSI();\"><img src=\"images/icons/cancel.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Cancel this Document</a>";
				}
			break;
		} 

		echo $headerControls;
	}
	
	function setNavButtons($docno) {

		global $con;

		list($fwd) = $con->getArray("select doc_no from invoice_header where doc_no > '$docno' limit 1;");
		list($prev) = $con->getArray("select doc_no from invoice_header where doc_no < '$docno' order by doc_no desc limit 1;");
		list($last) = $con->getArray("select doc_no from invoice_header order by doc_no desc limit 1;");
		list($first) = $con->getArray("select doc_no from invoice_header order by doc_no asc limit 1;");
		if($prev) {
			$nav = $nav . "<a href=# onclick=\"parent.viewSI('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
		}
		if($fwd) {
			$nav = $nav . "<a href=# onclick=\"parent.viewSI('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
		}
		echo "<a href=# onclick=\"parent.viewSI('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.viewSI('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
	}
	
?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Bata Esensyal</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/datatables/css/jquery.dataTables.css" rel="stylesheet" type="text/css" />
	<link href="style/tautocomplete.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/barcodescanner/jquery.scannerdetection.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
	<script language="javascript" src="js/tautocomplete.js?sessid=<?php echo uniqid(); ?>"></script>
	<script language="javascript" src="js/tableH.js"></script>
	<script language="javascript" src="js/si.js?sessid=<?php echo sessid; ?>"></script>
	<script>
	
		function selectSLid(obj,lid) {
			gObj = obj;
			$(obj).closest("tr").siblings().removeClass("diffcolor");
			$(obj).toggleClass("diffcolor");
			SLid = lid;
		}

		$(document).ready(function($) {
		
			$("#qty, #unit_price, #amount").keyup(
				function(e) {
					if(e.keyCode == 13) {
						addDetails();
					}
				}
			);

			<?php if($status == 'Finalized' || $status == 'Cancelled') {	
				echo "$(\"#xform :input\").prop('disabled',true);"; 
			} else { ?>
				$("#posting_date").datepicker(); 
				$("#ref_date").datepicker(); 
				$("#cheq_date").datepicker(); 
				$("#invoice_date").datepicker(); 
	
				$('#details tbody').on('dblclick', 'tr', function () {
					updateQty();
				});

				$('#customer_id').autocomplete({
					source:'suggestContacts.php', 
					minLength:3,
					select: function(event,ui) {
						$("#cSelected").val('Y');
						$("#customer_id").val(ui.item.cid);
						$("#customer_name").val(decodeURIComponent(ui.item.cname));
						$("#cust_address").val(decodeURIComponent(ui.item.addr));
						$("#terms").val(ui.item.terms);
						saveInvHeader();
					}
				});
				
				var myProduct = $("#description").tautocomplete({
					width: "800px",
					columns: ['Item Code','Description','Unit','Unit Price', 'Qty On-hand'],
					hide: false,
					highlight: false,
					placeholder: "Search Item Code or Product Description...",
					ajax: {
						url:  "suggestItems.php",
						type: "GET",
						data:function() { var x = { term: myProduct.searchdata() }; return x; },
						success: function (data) {
							return data;
						}
					},
					onchange: function () {
						var cellData = myProduct.all();
						$("#product_code").val(cellData['Item Code']);
						$("#description").val(cellData['Description']);
						$("#unit").val(cellData['Unit']);
						$("#unit_price").val(cellData['Unit Price']);
						$("#qty").focus();
					}
				});


				$(document).scannerDetection({
					preventDefault: false,
					endChar: [13],
					stopPropagation: false,
					onComplete: function(e,data) {
						validScan = true;
						processBarcode(e);
					}
					
				});
			
			<?php } ?>

		});

		function processBarcode(barcode) {
			$.post("src/sjerp.php", { mod: "checkItemBarcode", bcode: barcode, sid: Math.random() }, function(data) {
				var count = parseFloat(data);

				if(count > 0) {
					$.post("si.datacontrol.php", { mod: "addItemByCode",trace_no: $("#trace_no").val(), doc_no: $("#doc_no").val(), bcode: barcode, sid: Math.random() }, function(data) {
						$("#details").html(data);
						updateTotals(trace_no);
					},"html");
				} else {
					parent.sendErrorMessage("Barcode Not Found!");
				}
			},"html");
		}
			
	</script>
	<style>
		table.dataTable tr.odd { background-color: #f5f5f5;  }
		table.dataTable tr.even { background-color: white; }
		
		::-webkit-scrollbar {
			width: 1px;
			
		}
	</style>
</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div style="padding: 10px;">
	<form name="xform" id="xform">
		<input type=hidden id="trace_no" value="<?php echo $trace_no; ?>">
		<input type="hidden" name="cSelected" id="cSelected" value="<?php echo $cSelected; ?>">
		<table width=100% cellpadding=0 cellspacing=0 border=0 align=center>
			<tr>
				<td class="upper_menus" align=left id="uppermenus">
					<?php setHeaderControls($res['status'],$docno,$_SESSION['userid'],$urights); ?>
				</td>
				<td width=30% align=right style='padding-right: 5px;'><?php if($docno) { setNavButtons($docno); } ?></td>
			</tr>
			<tr><td height=2></td></tr>
		</table>

		<table border="0" cellpadding="0" cellspacing="1" width=100% class="td_content">
			<tr>
				<td width=50% valign=top>
					<table width=100% style="padding:0px 0px 0px 0px;">
						<tr><td height=2></td></tr>
						<tr>
							<td class="bareBold" align=right valign=top width=25% style="padding-right: 5px;">Customer&nbsp;:</td>
							<td align="left">
								<table cellspacing=0 cellpadding=0 border=0 width=100%>
									<tr>
										<td width=25%><input type="text" id="customer_id" name="customer_id" value="<?php echo $res['customer']?>" class="inputSearch2" style="padding-left: 22px;" placeholder="0" onchange="javascript: saveInvHeader();"></td>
										<td width=75% align=right colspan=2><input type="text" name="customer_name" id="customer_name" autocomplete="off" value="<?php echo $res['cname']; ?>" style="width: 100%;" placeholder="CASH WALKIN CUSTOMER" readonly></td>
									</tr>
									<tr>
										<td style="font-size: 9px; padding-left: 5px;">Code</td><td colspan=2 style="font-size: 9px; padding-left: 20px;">Customer Name</td>
									</tr>
									<tr><td height=2></td></tr>
									<tr>
										<td width=100% colspan=2><input type="text" class="nInput" id="cust_address" name="cust_address" value="<?php echo $res['caddr']?>" style="width: 100%;" placeholder="MANDAUE CITY" readonly></td>
									</tr>
									<tr>
										<td colspan=2 style="font-size: 9px; padding-left: 5px;" colspan=2 >Address</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td class="bareBold" align=right width=25% style="padding-right: 5px;">Credit Term&nbsp;:</td>
							<td align="left">
								<select class="gridInput" id="terms" name="terms" style="width: 150px;" onchange = "saveInvHeader();"/>
									<option value="0">Cash</option>
									<?php
										$tq = $con->dbquery("select terms_id, description from options_terms order by terms_id;");
										while(list($tid,$td) = $tq->fetch_array()) {
											echo "<option value='$tid' ";
											if($res['terms'] == $tid) { echo "selected"; }
											echo ">$td</option>";
										}
									?>
								</select>
							</td>
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" width="40%" class="bareBold" style="padding-left: 20%;">Transaction No.&nbsp;</td>
							<td align=left>
								<input style="width:40%;" type=text name="doc_no" id="doc_no" value="<?php echo $docno; ?>" readonly>
							</td>				
						</tr>
						<tr>
							<td align="left" width="40%" class="bareBold" style="padding-left: 20%;">Transaction Date&nbsp;:</td>
							<td align=left>
								<input style="width:40%;" type=text name="invoice_date" id="invoice_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>" onchange = "javascript: saveInvHeader();">
							</td>				
						</tr>
						<tr>
							<td align="left" width="40%" class="bareBold" style="padding-left: 20%;">Posting Date&nbsp;:</td>
							<td align=left>
								<input style="width:40%;" type=text name="posting_date" id="posting_date" value="<?php if(!$res['pd8']) { echo date('m/d/Y'); } else { echo $res['pd8']; }?>"  >
							</td>				
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<table style="font-size: 11px;" cellpadding=0 cellspacing=0 width=100%>
			<thead>
				<tr>
					<td align=center class="ui-state-default" style="padding: 8px 10px; font-weight: bold;" width=10%>ITEM CODE</td>
					<td align=center class="ui-state-default" style="padding: 8px 10px; font-weight: bold;" width=50%>DESCRIPTION</td>
					<td align=center class="ui-state-default" style="padding: 8px 10px; font-weight: bold;" width=10%>UNIT</td>
					<td align=center class="ui-state-default" style="padding: 8px 10px; font-weight: bold;" width=10%>QTY</td>
					<td align=center class="ui-state-default" style="padding: 8px 10px; font-weight: bold;" width=10%>UNIT PRICE</td>
					<td align=center  class="ui-state-default" style="padding: 8px 10px; font-weight: bold;" width=10%>AMOUNT</td>
				</tr>
				<?php
				if($status == "Active" || $status == "") {
					echo '<tr>
							<td align=center class="grid" colspan=2><input type="hidden" id="product_code"><input type=text style="padding-left: 22px; width: 99%;" id="description"></td>
							<td align=center class="grid"><input class="gridInput" type=text id="unit" style="width: 98%; text-align: center;" readonly /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="qty" style="width: 99%; text-align: center;" onchange="javascript: computeAmount();" /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="unit_price" style="width: 99%; text-align: center;" onchange="javascript: computeAmount();" readonly /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="amount" id="amount" style="width: 99%; text-align: center;" readonly /></td>
						</tr>';
					$i++;
				}
			?>
		</table>
		<div id="details" style="height: 120px; overflow-x: auto; border-bottom: 3px solid #4297d7; scrollbar-width: none;">
			<?php showDetails($trace_no,$status,$lock,$urights); ?>
		</div>

		<table width=100% class="td_content">
			<tr>
				<td width=50%>
					Sales Representative:<br/>
					<select class="gridInput" id="sales_rep" name="sales_rep" style="width: 200px;" onchange="javascript: saveInvHeader();" />
						<option value="0" <?php if($res['sales_rep'] == 0) { echo "selected"; } ?>>- None -</option>
						<?php
							$sr = $con->dbquery("select record_id,sales_rep from options_salesrep order by sales_rep;");
							while($srx = $sr->fetch_array()) {
								echo "<option value='$srx[0]' ";
								if($res['sales_rep'] == $srx[0]) { echo "selected"; }
								echo ">$srx[1]</option>";

							}
						?>
					</select>
				<br/><br/>
					Transaction Remarks: <br/>
					<textarea type="text" id="remarks" style="width:83%;" onchage="javascript: saveInvHeader();"><?php echo $res['remarks']; ?></textarea>
				</td>
				<td align=right width=50%>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" width="80%" class="bareBold" style="padding-left: 40%;">Total Before Discount &nbsp;:</td>
							<td align=right>
								<input style="width:80%;text-align:right;" type=text name="amount_b4_discount" id="amount_b4_discount" value="<?php echo number_format(($res['amount']+$res['discount']),2); ?>" readonly>
							</td>				
						</tr>
						<tr>
							<td align="left" width="80%" class="bareBold" style="padding-left: 40%;">Less &raquo; Discount&nbsp;:</td>
							<td align=right>
								<input style="width:80%;text-align:right;" type=text name="discount_in_peso" id="discount_in_peso" value="<?php echo number_format($res['discount'],2); ?>" readonly>
							</td>				
						</tr>
					
						<tr>
							<td align="left" width="80%" class="bareBold" style="padding-left: 40%;">Total Due&nbsp;:</td>
							<td align=right>
								<input style="width:80%;text-align:right;" type=text name="total_due" id="total_due" value="<?php echo number_format($res['amount'],2); ?>" readonly>
							</td>				
						</tr>
						<tr>
							<td align="left" width="80%" class="bareBold" style="padding-left: 40%;">Amount Settled&nbsp;:</td>
							<td align=right>
								<input style="width:80%;text-align:right;" type=text name="amount_applied" id="amount_applied" value="<?php echo number_format($res['applied_amount'],2); ?>">
							</td>				
						</tr>
						<tr>
							<td align="left" width="80%" class="bareBold" style="padding-left: 40%;">Balance Due&nbsp;:</td>
							<td align=right>
								<input style="width:80%;text-align:right;" type=text name="balance_due" id="balance_due" value="<?php echo number_format($res['balance'],2); ?>" readonly>
							</td>				
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td align=left colspan=2 style="padding-top: 15px;">
					<?php if($status == 'Active') { ?>
						<a href="#" class="topClickers" onClick="javascript:deleteLine();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Line Entries</a>&nbsp;&nbsp;<a href="#" class="topClickers" onClick="javascript:applyDiscount();"><img src="images/icons/discount-icon.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Apply Discount</a>&nbsp;&nbsp;<a href="#" class="topClickers" onClick="javascript:clearItems();"><img src="images/icons/bin.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Clear Item Details</a>
					<?php } ?>
				</td>
			</tr>
		</table>
	</form>
</div>
<div class="suggestionsBox" id="suggestions" style="display: none;">
	<div class="suggestionList" id="autoSuggestionsList">&nbsp;</div>
</div>
<div id="invoiceAttachment" style="display: none;"></div>
<div id="loading_popout" style="display:none;" align=center>
	<progress id='progess_trick' value='40' max ='100' width='220px'></progress> <br>
	Please wait while the server is processing our request.
</div>
<div id="loaderMessage" title="Processing..." style="display: none;">
	<p><span style="float:left; margin:0 7px 20px 0;"><img src="images/loader.gif" /></span>Please wait while the system is processing your request...</p>
</div>
<div id="discountDiv" style="display: none;" align=center>
	<table border=0>
		<tr>
			<td>Discount : </td>
			<td>
				<input type='text' class="nInput" id="salesDiscount" style="font-family:Arial; width: 140px;" > / UoM
			</td>
		</tr>
		<tr><td></td>
			<td>
				<input type="radio" name="type" id="type" value="PCT" checked>&nbsp;<span class="baregray" style="font-size: 10px;">Percent</span>&nbsp;
				<input type="radio" name="type" id="type" value="AMT">&nbsp;<span class="baregray" style="font-size: 10px;">Peso Value</span>&nbsp;
			</td>
		</tr>
	</table>
</div>
 <div id="payment_mode" style="display: none;">
	<table width=100% class="td_content" cellpadding=0 cellspacing=1>
		<tr>
			<td style="padding: 20px;" align=center>
				<button style="width:200px; height: 80px; font-size: 11px; padding: 5px;" onclick="javascript: cashCheckOut('<?php echo $trace_no; ?>');"><img src="images/icons/price-icon.png" width=48 height=48/><br/>Cash Payment</button>&nbsp;&nbsp;
				<button style="width:200px; height: 80px; font-size: 11px; padding: 5px;" onclick="javascript: ccCheckOut('<?php echo $trace_no; ?>');"><img src="images/icons/credit_card.png" width=48 height=48/><br/>Credit Card</button>
			</td>
		</tr>
	</table>
</div>
  <div id="cashCheckOutForm" style="display: none;">
	<table width=100% align=center class="td_content">
		<tr><td height=16></td></tr>
		<tr><td class="spandix-l">Amount Due<br/>
				<input class="nInput4" type=text id="amountDue" name="amountDue" style="width:100%; text-align: center; height: 80px; font-size: 60px;" value = "<?php echo number_format($res['amount'],2); ?>" readonly>
			</td>
		</tr>
		<tr><td height=2></td></tr>
		<tr><td class="spandix-l">Amount Tendered<br/>
				<input class="nInput4" type=text id="amountTendered" name="amountTendered" style="width:100%; text-align: center; height: 80px; font-size: 60px;" onChange="computeChange(this.value);" value = "<?php echo number_format($res['amount_tendered'],2); ?>">
			</td>
		</tr>
		<tr><td height=2></td></tr>
		<tr><td valign=top class="spandix-l">Change Due<br/>
				<input class="nInput4" type=text id="changeDue" name="changeDue" style="width:100%; text-align: center; height: 80px; font-size: 60px;" value = "<?php echo number_format($res['change_due'],2); ?>" readonly >
			</td>
		</tr>
	</table>
 </div>
  <div id="cardCheckOutForm" style="display: none;">
	<table width=80% align=center class="td_content">
		<tr><td height=16></td></tr>
		<tr><td class="spandix-l">CARD TYPE<br/>
				<select class="nInput4" name="cc_type" id="cc_type" style="width: 100%;">
					<option value="MASTERCARD">MASTERCARD</option>
					<option value="VISA">VISA</option>
					<option value="AMEX">AMERICAN EXPRESS</option>
					<option value="JCB">JCB</option>
				</select>
			</td>
		</tr>
		<tr><td height=2></td></tr>
		<tr><td class="spandix-l">ISSUING BANK<br/>
				<select class="nInput4" name="cc_bank" id="cc_bank" style="width: 100%;">
					<option value="BDO">BANCO DE ORO</option>
					<option value="MBTC">METROBANK</option>
					<option value="EW">EASTWEST BANK</option>
					<option value="CHINA">CHINABANK</option>
					<option value="SC">STANDARD CHARTER</option>
					<option value="CTB">CITI BANK</option>
					<option value="HSBC">HSBC</option>
					<option value="UB">UNION BANK</option>
					<option value="RCBC">RCBC</option>
					<option value="PNB">PNB</option>
				</select>
			</td>
		</tr>
		<tr><td height=2></td></tr>
		<tr><td class="spandix-l">CARD HOLDER NAME<br/>
				<input class="nInput4" type=text id="cc_name" name="cc_name" style="width:100%; text-align: center;">
			</td>
		</tr>
		<tr><td height=2></td></tr>
		<tr><td class="spandix-l">CREDIT CARD NO.<br/>
				<input class="nInput4" type=text id="cc_no" name="cc_no" style="width:100%; text-align: center;">
			</td>
		</tr>
		<tr><td height=2></td></tr>
		<tr><td class="spandix-l">CARD EXPIRY (MM/YY)<br/>
				<input class="nInput4" type=text id="cc_expiry" name="cc_expiry" style="width:100%; text-align: center;">
			</td>
		</tr>
		<tr><td height=2></td></tr>
		<tr><td valign=top class="spandix-l">TRANSACTION APPROVAL NO.<br/>
				<input class="nInput4" type=text id="cc_approvalno" name="cc_approvalno" style="width:100%; text-align: center;">
			</td>
		</tr>
		<tr><td height=2></td></tr>
	</table>
 </div>
  <div id="cheqCheckOutForm" style="display: none;">
	<table width=80% align=center class="td_content">
		<tr><td height=8></td></tr>
		
		<tr><td height=2></td></tr>
		<tr><td class="spandix-l">ISSUING BANK<br/>
				<select class="nInput4" name="cheq_bank" id="cheq_bank" style="width: 100%;">
					<?php
						$cb = $con->dbquery("select bank_code, bank_name from options_banks order by bank_name;");
						while($x = $cb->fetch_array()) {
							echo "<option value='$x[bank_code]'>$x[bank_name]</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=2></td></tr>
		<tr><td class="spandix-l">Check No.<br/>
				<input class="nInput4" type=text id="cheq_no" name="cheq_no" style="width:100%; text-align: center;">
			</td>
		</tr>
		<tr><td height=2></td></tr>
		<tr><td class="spandix-l">Check Date<br/>
				<input class="nInput4" type=text id="cheq_date" name="cheq_date" style="width:100%; text-align: center;">
			</td>
		</tr>

		<tr><td height=2></td></tr>
	</table>
 </div>
<div id = "soList" style = "display: none;">
	<table class="cell-border" id = "sodetails" style="font-size: 11px; width: 100%;">
		<thead>
			<tr>
				<th>SO No.</th>
				<th>Date</th>
				<th width=25%>Transaction Remarks</th>
				<th width=25%>Amount</th>		
			</tr>
		</thead>
	</table>				
</div>
<div id="itemQtyDiv" style="display: none;" align=center>
	<table border=0>
		<tr>
			<td width=25% valign=top><b>Qty: </b></td>
			<td> 
				<input type="number" id = "itemQty" name = "itemQty" class="gridInput" step = "1"> 
			</td>
		</tr>
	</table>
</div>
</body>
</html>
