<?php	
	/* UNSET QUED FOR DELETION */
	session_start();
	include("handlers/_generics.php");
	
	$con = new _init();
	
	include("functions/cr.displayDetails.fnc.php");
	$uid = $_SESSION['userid'];
	
	if(isset($_REQUEST['trans_no']) && $_REQUEST['trans_no']!='') { 
		$res = $con->getArray("select *, lpad(trans_no,6,0) as transno, lpad(customer,6,0) as xpayee, date_format(cr_date,'%m/%d/%Y') as d8, if(check_date!='0000-00-00',date_format(check_date,'%m/%d/%Y'),'') as cd8 from cr_header where trans_no = '$_REQUEST[trans_no]';");
		$trans_no = $res['transno']; $status=$res['status']; $cSelected = "Y";
	} else {  
		list($trans_no) = $con->getArray("select lpad((ifnull(max(trans_no),0)+1),6,0) from cr_header;"); 
		$status = "Active"; $dS = "1"; $cSelected = "N";
	}
		
	function setHeaderControls($status,$trans_no,$uid) {
		
		global $con;
		
		
		list($urights) = $con->getArray("select user_type from user_info where emp_id='$uid'");
		if($lock != 'Y') {
			switch($status) {
				case "Posted":
					//list($posted_by,$posted_on) = $con->getArray("select fullname as name, date_format(updated_on,'%m/%d/%Y %p') as date_posted from cr_header a left join user_info b on a.updated_by=b.emp_id where a.trans_no='$trans_no' and branch = '$_SESSION[branchid]';");
					if($urights == "admin") {
						$headerControls = "<a href=\"#\" class=\"topClickers\" onclick=\"javascript: setActive('$trans_no');\"><img src='images/icons/edit.png' align=absmiddle width=16 height=16 />&nbsp;Set this Document to Active Status</a>&nbsp;";
					}
					$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onClick=\"javascript:parent.printCR('$trans_no','$_SESSION[userid]','N');\"><img src=\"images/icons/print.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Print Collection Receipt</a>&nbsp;";
				break;
				case "Cancelled":
					if($urights == "admin") {
						$headerControls = $headerControls . "<a href=\"#\" class=\"topClickers\" onclick=\"javascript:reuseAP('$trans_no');\" ><img src=\"images/icons/refresh.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Recycle this Document</a>&nbsp;";	
					}
				break;
				case "Active": default:
					//$headerControls = "<a href=\"#\" class=\"topClickers\" onClick=\"javascript:finalizeCR('$trans_no','$_SESSION[userid]');\"><img src=\"images/icons/ok.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Finalize Collection Receipt</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:saveCRHeader();\"><img src=\"images/save.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Save Changes</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:applyDocuments();\"><img src=\"images/icons/invoice.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Customer's Invoices</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:applyManual();\"><img src=\"images/icons/invoice.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Receive Advance Payment</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:viewCreditableTaxes();\"><img src=\"images/icons/bir-logo.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;View Applied Creditable Taxes</a>&nbsp;";
					$headerControls = "<a href=\"#\" class=\"topClickers\" onClick=\"javascript:finalizeCR('$trans_no','$_SESSION[userid]');\"><img src=\"images/icons/ok.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Finalize Collection Receipt</a>&nbsp;&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:saveCRHeader();\"><img src=\"images/save.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Save Changes</a>&nbsp;";
					
					if($urights == "admin" && $dS != 1) {
						$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\" onclick=\"javascript:cancelCR('$trans_no');\"><img src=\"images/icons/cancel.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Cancel this Document</a>&nbsp;";
					}
				break;
			}
		} else {
			$headerControls = $headerControls . "&nbsp;<a href=\"#\" class=\"topClickers\"  onClick=\"javascript:parent.printCR('$trans_no','$_SESSION[userid]','Y');\"><img src=\"images/icons/print.png\" width=16 height=16 border=0 align=\"absmiddle\">&nbsp;Re-Print Collection Receipt</a>";
		}
		echo $headerControls;
	}
	
	function setNavButtons($trans_no) {
		
		global $con;
		
		list($fwd) = $con->getArray("select trans_no from cr_header where trans_no > '$trans_no' limit 1;");
		list($prev) = $con->getArray("select trans_no from cr_header where trans_no < '$trans_no' order by trans_no desc limit 1;");
		list($last) = $con->getArray("select trans_no from cr_header order by trans_no desc limit 1;");
		list($first) = $con->getArray("select trans_no from cr_header order by trans_no asc limit 1;");
		if($prev)
			$nav = $nav . "<a href=# onclick=\"parent.viewCR('$prev');\"><img src='images/resultset_previous.png'  title='Previous Record' /></a>";
		if($fwd) 
			$nav = $nav . "<a href=# onclick=\"parent.viewCR('$fwd');\"><img src='images/resultset_next.png' 'title='Next Record' /></a>";
		echo "<a href=# onclick=\"parent.viewCR('$first');\"><img src='images/resultset_first.png' title='First Record' /><a>" . $nav . "<a href=# onclick=\"parent.viewCR('$last');\"><img src='images/resultset_last.png' title='Last Record' /></a>";
	}
	
?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Basic Prime Construction Supply</title>
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="js/jquery.dialogextend.js"></script>
	<script language="javascript" src="js/cr.js?sessid=<?php echo uniqid(); ?>"></script>
	<script language="javascript" src="js/tableH.js"></script>
	<script>

		var line = "";
	
		$(document).ready(function() {
			
			$('#customer_id').autocomplete({
				source:'suggestContacts.php', 
				minLength:3,
				select: function(event,ui) {
					$("#cSelected").val('Y');
					$("#customer_id").val(ui.item.cid);
					$("#customer_name").val(decodeURIComponent(ui.item.cname));
					$("#cust_address").val(decodeURIComponent(ui.item.addr));
					saveCRHeader();
				}
			});
			
		<?php if ($status == 'Active') { ?>
				$("#cr_date").datepicker();
				$("#man_docdate").datepicker();
				$("#ref_date").datepicker();
				$("#check_date").datepicker();
				$("#app_docdate").datepicker(); 
				$("#cert_docdate").datepicker(); 
			<?php } else { echo "$(\"#xform :input\").prop('disabled',true);"; } ?>
		});
	
		function selectLine(obj,lid) {
			gObj = obj;
			$(obj).closest("tr").siblings().removeClass("diffcolor");
			$(obj).toggleClass("diffcolor");
			line = lid;
		}

		function deleteLine() {
			if(line != "") {
				if(confirm("Are you sure you want to remove this invoice from this Collection Receipt?") == true) {
					$.post("cr.datacontrol.php", { mod: "deleteInvoice", lid: line, trans_no: $("#trans_no").val(), sid: Math.random() }, function(data) {
						$("#details").html(data);
						getTotals($("#trans_no").val());
					},"html");
				}
			} else { parent.sendErrorMessage("- You have not selected any invoice yet to remove."); }
		}
		
		function computeMyEwt(atc) {
			if(atc != "") {
				$.post("cr.datacontrol.php", { mod: "computeEWT", amount: $("#ct_docamt").val(), atc: atc, sid: Math.random() }, function(ewt) { $("#ct_withheld").val(ewt); });
			} else { $("#ct_withheld").val(''); }
		}
		
		function applyCreditable() {
			var msg = "";
			if(line != "") {
				$.post("cr.datacontrol.php", { mod: "fetchInvoiceDetails", lid: line, trans_no: $("#trans_no").val(), sid: Math.random() }, function(si) {
					$("#ct_docno").val(si['doc_no']);
					$("#ct_docdate").val(si['dd8']);
					$("#ct_docamt").val(si['amt']);
					
					$("#applyCreditableTax").dialog({
						title: "Apply Creditable Taxes",
						width: 400,
						resizable: false,
						modal: true,
						buttons: {
							"Apply Creditable Tax": function() {
								if($("#ct_docno").val() == "") { msg = msg + "- Document No. is empty or invalid<br/>"; }
								if($("#ct_docdate").val() == "") { msg = msg + "- Document Date is empty or invalid<br/>"; }
								if($("#ct_atc_code").val() == "") { msg = msg + "- You haven't indicated the appropriate tax code for this creditable tax<br/>"; }
								
								if(msg != "") {
									parent.sendErrorMessage(msg);
								} else {
									$.post("cr.datacontrol.php", { mod: "applyCreditableTax", trans_no: $("#trans_no").val(), doc_no: $("#ct_docno").val(), docdate: $("#ct_docdate").val(), docamt: $("#ct_docamt").val(), atc: $("#ct_atc_code").val(), certdate: $("#cert_docdate").val(), ewt: $("#ct_withheld").val(), sid: Math.random() }, function() {
										$(document.frmCreditableTax)[0].reset();
										$("#applyCreditableTax").dialog("close");
										getTotals($("#trans_no").val());
									});
								}
							},
							"Close": function() {
								if(confirm("Are you sure you want to close this window") == true) {
									$(document.frmCreditableTax)[0].reset();
									$("#applyCreditableTax").dialog("close");
								}
							}
						}
					});
				},"json");
			} else { parent.sendErrorMessage("- You have not selected any invoice yet to apply for creditable tax."); }
		}
		
		function viewCreditableTaxes() {
			$.post("cr.datacontrol.php", { mod: "viewCreditableTaxes", trans_no: $("#trans_no").val(), sid: Math.random() }, function(res) {
				$("#ewtRecords").html(res);
				$("#creditableTaxes").dialog({
					title: "Apply Creditable Taxes",
					width: 640,
					resizable: false,
					modal: true,
					buttons: {
						"Close": function() { $("#creditableTaxes").dialog("close"); }
					}
				});
			},"html");
		}
		
		function removeCreditableTax(lid) {
			if(confirm("Are you sure you want to remove this creditable tax entry from this Collection Receipt?") == true) {
				$.post("cr.datacontrol.php", { mod: "removeCreditableTax", trans_no: $("#trans_no").val(), lid: lid, sid: Math.random() }, function(res) {
					$("#ewtRecords").html(res);
					getTotals($("#trans_no").val());
				},"html");
			}
		}
		
		function carryMyAmount(id,balance,applied,doc_no,doc_date,ref_no,ref_type,terms,duedate) {
			
			var amt1 = parseFloat(balance);
			var amt2 = parseFloat(applied);
			
			if(amt2 > 0) {
				
			}  else {
				
				document.getElementById(id).value = parent.addCommas(balance);
				applyMe(balance,doc_no,doc_date,ref_no,ref_type,terms,duedate,balance);
		
			}
			
		}
		
		function applyMe(amount,doc_no,doc_date,ref_no,ref_type,terms,duedate,balancedue) {
			
			$.post("cr.datacontrol.php", { mod: "applySelected", trans_no: $("#trans_no").val(), doc_no: doc_no, doc_date: doc_date, ref_no: ref_no, ref_type: ref_type, terms: terms, duedate: duedate, balancedue: balancedue, appliedamount: amount, sid: Math.random() }, function() {
				var paid = parseFloat(parent.stripComma($("#amt_paid").val()));
				getTotals($("#trans_no").val());
				
			});
		}

	</script>
</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div style="padding: 10px;">
<form name="xform" id="xform">
	<input type="hidden" name="cSelected" id="cSelected" value="<?php echo $cSelected; ?>">
	<table width=100% cellpadding=0 cellspacing=0 border=0 align=center>
		<tr>
			<td class="upper_menus" align=left>
				<?php setHeaderControls($res['status'],$trans_no,$_SESSION['userid']); ?>
			</td>
			<td align=right style='padding-right: 5px;'><?php if($trans_no) { setNavButtons($trans_no); } ?></td>
		</tr>
		<tr><td height=2></td></tr>
	</table>

	<table border="0" width=100% class="td_content">
		<tr>
			<td width=50% valign=top>
				<table width=100% style="padding:0px 0px 0px 0px;">
					<tr><td height=2></td></tr>
					<tr>
						<td class="bareBold" align=left valign=top width=25% style="padding-left: 35px;">Customer&nbsp;:</td>
						<td align="left">
							<table cellspacing=0 cellpadding=0 border=0 width=100%>
								<tr>
									<td width=25%><input type="text" id="customer_id" name="customer_id" value="<?php echo $res['xpayee']?>" class="inputSearch2" style="padding-left: 22px; width:98%;"></td>
									<td width=75% align=right colspan=2><input type="text" name="customer_name" id="customer_name" autocomplete="off" class="gridInput" value="<?php echo $res['customer_name']; ?>" style="width: 100%;" readonly></td>
								</tr>
								<tr>
									<td style="font-size: 9px; padding-left: 5px;">Code</td><td colspan=2 style="font-size: 9px; padding-left: 20px;">Customer Name</td>
								</tr>
								<tr><td height=2></td></tr>
								<tr>
									<td width=100% colspan=2><input class="gridInput" type="text" id="cust_address" name="cust_address" value="<?php echo $res['customer_addr']?>" style="width: 100%;" readonly></td>
								</tr>
								<tr>
									<td colspan=2 style="font-size: 9px; padding-left: 5px;" colspan=2 >Address</td>
								</tr>
							</table>
						</td>
					</tr>
					<tr>
						<td class="bareBold" align=left width=25% style="padding-left: 35px;">Payment Type&nbsp;:</td>
						<td align="left">
							<select id="pay_type" name="pay_type" style="width: 160px;" class="gridInput" />
								<option value="Cash" <?php if($res['pay_type'] == 'Cash') { echo "selected"; } ?>>- Cash Payment -</option>
								<option value="Check" <?php if($res['pay_type'] == 'Check') { echo "selected"; } ?>>- Check Payment -</option>
								<option value="GiftCard" <?php if($res['pay_type'] == 'GiftCard') { echo "selected"; } ?>>- Gift Card -</option>
								<option value="OL-RCBC" <?php if($res['pay_type'] == 'OL-RCBC') { echo "selected"; } ?>>- Online Payment thru RCBC -</option>
								<option value="OL-MBTC" <?php if($res['pay_type'] == 'OL-MBTC') { echo "selected"; } ?>>- Online Payment thru MBTC -</option>
								<option value="OL-PNB" <?php if($res['pay_type'] == 'OL-PNB') { echo "selected"; } ?>>- Online Payment thru PNB -</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="bareBold" align=left width=25% style="padding-left: 35px;">Amount Received&nbsp;:</td>
						<td align="left">
							<input class="gridInput" style="width:160px;" type=text name="amt_paid" id="amt_paid" value="<?php echo number_format($res['amount_received'],2); ?>" >
						</td>
					</tr>
				</table>
			</td>
			<td valign=top>
				<table border="0" cellpadding="0" cellspacing="1" width=100%>
					<tr><td height=2></td></tr>
					<tr>
						<td align="left" width="30%" class="bareBold" style="padding-left: 35px;">Doc No.&nbsp;:</td>
						<td align=left>
							<input class="gridInput" style="width:140px;" type=text name="trans_no" id="trans_no" value="<?php echo $trans_no; ?>" onchange='javascript: saveCRHeader();'>
						</td>				
					</tr>
					<tr>
						<td class="bareBold" align=left width=25% style="padding-left: 35px;">Transactionn Reference #&nbsp;:</td>
						<td align="left">
							<input class="gridInput" style="width:140px;" type=text name="trans_ref_no" id="trans_ref_no" value="<?php echo $res['trans_ref_no']; ?>" <?php echo $isReadOnly; ?>>
						</td>
					</tr>
					<tr>
						<td align="left" width="30%" class="bareBold" style="padding-left: 35px;">Trans. Date&nbsp;:</td>
						<td align=left>
							<input class="gridInput" style="width:140px;" type=text name="cr_date" id="cr_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>" onchange='javascript: saveCRHeader();'>
						</td>				
					</tr>
					<tr>
						<td class="bareBold" align=left width=25% style="padding-left: 35px;">Payor's Check Provider&nbsp;:</td>
						<td align="left">
							<select id="bank" name="bank" style="width: 140px;" class="gridInput"/>
								<option value="">- Not Applicable -</option>
								<?php
									$cb = $con->dbquery("select bank_code, bank_name from options_banks order by bank_name;");
									while($x = $cb->fetch_array()) {
										echo "<option value='$x[bank_code]' ";
										if($res['bank'] == $x['bank_code']) { echo "selected"; }
										echo ">$x[bank_name]</option>";
									}
								?>
							</select>
						</td>
					</tr>
					<tr>
						<td class="bareBold" align=left width=25% style="padding-left: 35px;">Check # :</td>
						<td align="left">
							<input class="gridInput" type="text" style="width: 140px;" id="check_no" name="check_no" value = "<?php echo $res['check_no']; ?>">&nbsp;<span class="spandix-l">
						</td>
					</tr>
					<tr>
						<td class="bareBold" align=left width=25% style="padding-left: 35px;">Check Date :</td>
						<td align="left">
							<input class="gridInput" type="text" style="width: 140px;" id="check_date" name="check_date" value = "<?php echo $res['cd8']; ?>">
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>

	<table cellspacing=0 cellpadding=0 border=0 width=100%>
		<tr>
			<td align=center class="ui-state-default" style="padding: 10px;" width="10%">DOC #</td>
			<td align=center class="ui-state-default" style="padding: 10px;" width="15%">DOC DATE</td>
			<td align=center class="ui-state-default" style="padding: 10px;" width="15%">REF #</td>
			<td align=center class="ui-state-default" style="padding: 10px;" width="15%">TERMS</td>
			<td align=center class="ui-state-default" style="padding: 10px;" width="15%">DUE DATE</td>
			<td align=center class="ui-state-default" style="padding: 10px;" width="15%">BALANCE DUE</td>
			<td align=center class="ui-state-default" style="padding: 10px;">AMOUNT PAID</td>
		</tr>
	</table>
	<div id="details" style="height: 120px; overflow-x: auto; border-bottom: 3px solid #4297d7; scrollbar-width: none;">
		<?php showDetails($trans_no); ?>
	</div>
	<table width=100% class="td_content" style="padding: 5px;">
		<tr>
			<td width=50%>
				Collector:<br/>
				<select id="sales_rep" class="gridInput" name="sales_rep" style="width: 150px;" />
				<option value="">- None -</option>
			</select>
			<br/><br/>
				Transaction Remarks: <br/>
				<textarea type="text" id="remarks" style="width:83%;"><?php echo $res['remarks']; ?></textarea>
			</td>
			<td align=right width=50% valign=top>
				<table border="0" cellpadding="0" cellspacing="1" width=100%>
					<tr><td height=2></td></tr>
					<tr>
						<td align="left" width="80%" class="bareBold" style="padding-left: 40%;">Total Amount Applied&nbsp;:</td>
						<td align=right>
							<input style="width:80%;text-align:right;" type=text name="gross" id="gross" value="<?php echo number_format($res['amount'],2); ?>" readonly>
						</td>				
					</tr>
					<tr>
						<td align="left" width="80%" class="bareBold" style="padding-left: 40%;">Rebates/Payment Discounts&nbsp;:</td>
						<td align=right>
							<input style="width:80%;text-align:right;" type=text name="discount" id="discount" value="<?php echo number_format($res['discount'],2); ?>" onchange="javascript: computeNet(this.value);">
						</td>				
					</tr>
					<tr>
						<td align="left" width="80%" class="bareBold" style="padding-left: 40%;">Less &raquo; Creditable Tax (EWT)&nbsp;:</td>
						<td align=right>
							<input style="width:80%;text-align:right;" type=text name="ewtgt" id="ewtgt" value="<?php echo number_format($res['ewt'],2); ?>" readonly>
						</td>				
					</tr>
					<tr>
						<td align="left" width="80%" class="bareBold" style="padding-left: 40%;">Net Cash Received&nbsp;:</td>
						<td align=right>
							<input style="width:80%;text-align:right;" type=text name="net" id="net" value="<?php echo number_format($res['net'],2); ?>" readonly>
						</td>				
					</tr>
				</table>
			</td>
		</tr>
		<tr>
			<td align=left colspan=2 style="padding-top: 15px;">
				<?php if($status == 'Active') { ?>
					<!--a href="#" class="topClickers" onClick="javascript:deleteLine();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Line Entries</a>&nbsp;&nbsp;<a href="#" class="topClickers" onClick="javascript:applyCreditable();"><img src="images/icons/bir-logo.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Apply Creditable Tax</a-->
				<?php } ?>
			</td>
		</tr>
	</table>
</form>
</div>
<div id="creditableTaxes" style="display: none;">
	<table align=center cellspacing=0 cellpadding=0 width=100% style="font-weight:bold;">
		<tr>
			<td align="center" class="ui-state-default" style="padding-top: 5px; padding-bottom: 5px;" width="20%">Doc No.</td>
			<td align="center" class="ui-state-default" width="20%">Doc Date</td>
			<td align="center" class="ui-state-default" width="20%">Doc Amount</td>
			<td align="center" class="ui-state-default" width="20%">ATC Code</td>
			<td align="center" class="ui-state-default">Creditable Tax</td>
			<td width=18 class="ui-state-default" style="width: 15px;">&nbsp;</td>
		</tr>
	</table>
	<div name="ewtRecords" id="ewtRecords" style="height: 230px; overflow: auto;"></div>
</div>
<div id="applyCreditableTax" style="padding: 10px; display: none;">
	<form name="frmCreditableTax" id="frmCreditableTax">
		<table align=center border=0 width=100% cellpadding=0 cellspacing=3>
			<tr><td height=2></td></tr>
			<tr><td class=spandix-l align=right style="padding-right: 10px;">Doc No. :</td>
				<td align=left>
					<input type=text id="ct_docno" name="app_docno" class="gridInput" style="width:140px" readonly>
				</td>
			</tr>
			<tr><td height=2></td></tr>
			<tr><td class=spandix-l align=right style="padding-right: 10px;">Date :</td>
				<td align=left>
					<input type=text id="ct_docdate" name="ct_docdate" class="gridInput" style="width:140px" readonly>
				</td>
			</tr>
			<tr><td height=2></td></tr>
			<tr><td class=spandix-l align=right style="padding-right: 10px;">Invoice Amount :</td>
				<td align=left>
					<input type=text id="ct_docamt" name="ct_docamt" class="gridInput" style="width:140px" readonly>
				</td>
			</tr>
			<tr><td height=2></td></tr>
			<tr><td class=spandix-l align=right style="padding-right: 10px;">2307 Cert. Date :</td>
				<td align=left>
					<input type=text id="cert_docdate" name="cert_docdate" class="gridInput" style="width:140px" readonly>
				</td>
			</tr>
			<tr><td height=2></td></tr>
			<tr><td class=spandix-l align=right style="padding-right: 10px;">Tax Code :</td>
				<td align=left>
					<select id="ct_atc_code" name="ct_atc_code" style="width: 140px;" class="gridInput" onchange="computeMyEwt(this.value);" />
						<option value="">- Select Tax Code -</option>
						<?php
							$tq = $con-dbquery("select atc_code, description, rate from options_atc order by rate;");
							while(list($aa,$bb,$cc) = $tq->fetch_array()) {
								echo "<option value='$aa' ";
								if($res['atc_code'] == $aa) { echo "selected"; }
								echo " title='$bb'>$aa ($cc %)</option>";
							}
						?>
					</select>
				</td>
			</tr>
			<tr><td height=2></td></tr>
			<tr><td class=spandix-l align=right style="padding-right: 10px;">Amount Withheld :</td>
				<td align=left>
					<input type=text id="ct_withheld" name="ct_withheld" class="gridInput" style="width:140px" readonly>
				</td>
			</tr>
			<tr><td height=10></td></tr>
		</table>
	</form>
</div>
<div id="manualForm" style="padding: 10px; display: none;">
	<form name="applydocsManual" id="applydocsManual">
		<table align=center border=0 width=100% cellpadding=0 cellspacing=3>
			<tr><td class=spandix-l align=right style="padding-right: 10px;">Amount Paid :</td>
				<td align=left>
					<input type=text id="man_amount" name="man_amount" class="gridInput" style="width:140px">
					<input type=hidden id="man_docno" name="man_docno" >
					<input type=hidden id="man_docdate" name="man_docdate" >
					<input type=hidden id="man_balance" name="man_balance" >
				</td>
			</tr>
			<tr><td height=10></td></tr>
		</table>
		<table align=center style="border-top: 1px solid black;">
			<tr><td height=8></td></tr>
			<tr>
				<td></td>
				<td>
					<button type="button" onClick='javascript: applyManualNow();' style="height: 30px;"><img src="images/icons/down3.png" with=16 height=16 border=0 align=absmiddle />&nbsp;Apply Document</button>
					<button type="button" onClick='javascript: $("#manualForm").dialog("close"); $(document.applydocsManual)[0].reset();' style="height: 30px;"><img src="images/icons/cancelled.png" with=16 height=16 border=0 align=absmiddle />&nbsp;Close Window</button>
				</td>
			</tr>
		</table>
	</form>
</div>
<div id="invoices" style="display: none;"></div>
<div class="suggestionsBox" id="suggestions" style="display: none;">
	<div class="suggestionList" id="autoSuggestionsList">&nbsp;</div>
</div>
</body>
</html>