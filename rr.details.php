<?php	
	session_start();
	unset($_SESSION['ques']);
	

	require_once "handlers/_rrfunct.php";
	$p = new myRR;
	//$res = array();
	
	$uid = $_SESSION['userid'];
	if(isset($_REQUEST['rr_no']) && $_REQUEST['rr_no'] != '') {  
		$res = $p->getArray("select *, lpad(rr_no,6,0) as rrno, lpad(supplier,6,0) as sup_code, date_format(rr_date,'%m/%d/%Y') as d8, if(invoice_date!='',if(invoice_date='0000-00-00','',date_format(invoice_date,'%m/%d/%Y')),date_format(invoice_date,'%m/%d/%Y')) as id8 from rr_header where rr_no='$_REQUEST[rr_no]' and branch = '$_SESSION[branchid]';");
		$cSelected = "Y"; $status = $res['status']; $lock = $res['locked']; $rr_no = $res['rrno'];
	} else {  
		list($rr_no) = $p->getArray("select lpad((ifnull(max(rr_no),0)+1),6,0) from rr_header where branch = '$_SESSION[branchid]';"); 
		$status = "Active"; $dS = "1"; $cSelected = "N"; $lock = "N";
	}
	
	if($status != "Active") { $isReadOnly = "readonly"; $isDisabled = "disabled"; }
	
?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Shuttle Dome Badminton Court</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/tautocomplete.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="js/jquery.dialogextend.js"></script>
	<script language="javascript" src="js/tautocomplete.js"></script>
	<script language="javascript" src="js/tableH.js"></script>
	<script language="javascript" src="js/rr.js?sid=<?php echo uniqid(); ?>"></script>
	<script>
		
		var line;
		function selectLine(obj,lid) {
			gObj = obj;
			$(obj).closest("tr").siblings().removeClass("diffcolor");
			$(obj).toggleClass("diffcolor");
			line = lid;
		}
		
		$(document).ready(function($){
			<?php if($status == 'Finalized' || $status == 'Cancelled') { echo "$(\"#xform :input\").prop('disabled',true);"; } ?>
			$("#po_date").datepicker(); $("#rr_date").datepicker(); $("#invoice_date").datepicker();
			
			$("#qty, #unit_price, #amount").keyup(
				function(e) {
					if(e.keyCode == 13) {
						addDetails();
					}
				}
			);
			
			var myProduct = $("#description").tautocomplete({
				width: "720px",
				columns: ['Item Code','Description','Unit','Unit Price'],
				hide: false,
				ajax: {
					url:  "suggestItemsCost-2.php",
					type: "GET",
					data:function() {var x = { term: myProduct.searchdata() }; return x; },
					success: function (data) {
						var filterData = [];
						var searchData = eval("/" + myProduct.searchdata() + "/gi");
						$.each(data, function (i,v) {
							if (v.description.search(new RegExp(searchData)) != -1) {
								filterData.push(v);
							}
						});
						return filterData;
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
			
			$('#customer_id').autocomplete({
				source:'suggestContacts.php', 
				minLength:3,
				select: function(event,ui) {
					$("#cSelected").val('Y');
					$("#customer_id").val(ui.item.cid);
					$("#customer_name").val(decodeURIComponent(ui.item.cname));
					$("#cust_address").val(decodeURIComponent(ui.item.addr));
					saveRRHeader();
				}
			});
		});
		
		function deleteLine() {
			if(line == "") {
				parent.sendErrorMessage("- You have not selected any record that you wish to remove.");
			} else {
				if(confirm("Are you sure you want to remove this entry?") == true) {
					$.post("rr.datacontrol.php", { mod: "deleteLine", lid: line, rr_no: $("#rr_no").val(), sid: Math.random() }, function(ret) {
						$("#details").html(ret); line = "";
						getTotals();
					},"html");
				}
			}
		}
	</script>
</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div style="padding: 10px;">
	<form name="xform" id="xform">
		<input type="hidden" name="cSelected" id="cSelected" value="<?php echo $cSelected; ?>">
		<input type=hidden name="prev_rr_date" id="prev_rr_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>" >
		<table width=100% cellpadding=0 cellspacing=0 border=0 align=center>
			<tr>
				<td class="upper_menus" align=left>
					<?php $p->setHeaderControls($status,$rr_no,$_SESSION['userid']); ?>
				</td>
				<td width=30% align=right style='padding-right: 5px;'><?php if($rr_no) { $p->setNavButtons($rr_no); } ?></td>
			</tr>
			<tr><td height=2></td></tr>
		</table>

		<table border="0" cellpadding="0" cellspacing="1" width=100% class="td_content">
			<tr>
				<td width=50% valign=top>
					<table width=100% style="padding:0px 0px 0px 0px;">
						<tr><td height=2></td></tr>
						<tr>
							<td class="bareBold" align=left valign=top width=25% style="padding-left: 35px;">Supplier&nbsp;:</td>
							<td align="left">
								<table cellspacing=0 cellpadding=0 border=0 width=100%>
									<tr>
										<td width=25%><input type="text" id="customer_id" name="customer_id" value="<?php echo $res['sup_code']?>" class="inputSearch2" style="padding-left: 22px;"></td>
										<td width=75% align=right colspan=2><input type="text" name="customer_name" id="customer_name" autocomplete="off" class="gridInput" value="<?php echo $res['supplier_name']; ?>" style="width: 100%;;" readonly></td>
									</tr>
									<tr>
										<td style="font-size: 9px; padding-left: 5px;">Code</td><td colspan=2 style="font-size: 9px; padding-left: 20px;">Supplier Name</td>
									</tr>
									<tr>
										<td width=100% colspan=2><input class="gridInput" type="text" id="cust_address" name="cust_address" value="<?php echo $res['supplier_addr']?>" style="width: 100%;" readonly></td>
									</tr>
									<tr>
										<td colspan=2 style="font-size: 9px; padding-left: 5px;" colspan=2 >Address</td>
									</tr>
								</table>
							</td>
						</tr>
						<tr>
							<td align="left" class="bareBold" style="padding-left: 35px;">Received By&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:60%;" type=text name="received_by" id="received_by" value="<?php echo $res['received_by']; ?>">
							</td>				
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" width="40%" class="bareBold" style="padding-left: 55px;">RR. No.&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:40%;" type=text name="rr_no" id="rr_no" value="<?php echo $rr_no; ?>" readonly>
							</td>				
						</tr>
						<tr>
							<td align="left" width="40%" class="bareBold" style="padding-left: 55px;">Trans. Date&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:40%;" type=text name="rr_date" id="rr_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>" onchange="javascript: checkLockDate(this.id,this.value,$('#prev_rr_date').val());" >
							</td>				
						</tr>
						<tr>
							<td align="left" width="40%" class="bareBold" style="padding-left: 55px;">Delivery Ref. # (Invoice, DR)&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:40%;" type=text name="invoice_no" id="invoice_no" value="<?php echo $res['invoice_no']; ?>"  onchange='javascript: checkDuplicateInvoice(this.value);'>
							</td>				
						</tr>
						<tr>
							<td align="left" width="40%" class="bareBold" style="padding-left: 55px;">Delivery Ref. Date&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:40%;" type=text name="invoice_date" id="invoice_date" value="<?php echo $res['id8']; ?>" onchange='javascript: saveRRHeader();'>
							</td>				
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<table cellspacing=0 cellpadding=0 border=0 width=100% style="margin-top: 2px;">
			<tr>
				<td align=left class="ui-state-default" style="padding: 5px 5px 5px 25px;" width="10%">P.O NO</td>
				<td align=left class="ui-state-default" style="padding: 5px 5px 5px 25px;" width="10%">PO DATE</td>
				<td align=left class="ui-state-default" style="padding: 5px 5px 5px 25px;" width="10%">ITEM CODE</td>
				<td align=left class="ui-state-default" style="padding: 5px;" width="30%">DESCRIPTION</td>
				<td align=center class="ui-state-default" style="padding: 5px;" width="10%">UNIT</td>
				<td align=center class="ui-state-default" style="padding: 5px;" width="10%">QTY</td>
				<td align=center class="ui-state-default" style="padding: 5px;" width="10%">UNIT PRICE</td>
				<td align=center class="ui-state-default" style="padding: 5px;" width="10%">AMOUNT</td>
			</tr>
			<?php
				if($status == "Active" || $status == "") {
					echo '<tr>
							<td align=center class="grid"><input class="gridInput" type=text id="po_no" style="width: 98%;" /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="po_date" style="width: 98%;" /></td>
							<td align=center class="grid" colspan=2><input type="hidden" id="product_code" /><input type=text class="inputSearch" style="padding-left: 22px; width: 98%" id="description" /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="unit" style="width: 98%; text-align: center;" readonly /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="qty" style="width: 98%; text-align: right;" onblur="computeAmount();" /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="unit_price" style="width: 98%; text-align: right;" onchange="computeAmount();"/></td>
							<td align=center class="grid" colspan=2><input class="gridInput" type=text id="amount" style="width: 98%;text-align: right;" readonly/></td>
						</tr>';
					$i++;
				}
			?>
		</table>
		<div id="details" style="height: 170px; overflow-x: auto; border-bottom: 3px solid #4297d7;">
			<?php $p->RRDETAILS($rr_no,$status) ?>
		</div>
		<table width=100% class="td_content">
			<tr>
				<td width=50%>
					Transaction Remarks: <br/>
					<textarea type="text" id="remarks" style="width:83%;"><?php echo $res['remarks']; ?></textarea>
				</td>
				<td align=right width=50% valign=top>
					Total Amount&nbsp;:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input style="width:160px; text-align:right;" type=text name="total_amount" id="total_amount" value="<?php echo number_format(($res['amount']+$res['discount']),2); ?>" readonly>
				</td>
			</tr>
			<tr>
				<td align=left colspan=2 style="padding-top: 15px;">
					<?php if($status == 'Active') { ?>
						<a href="#" class="topClickers" onClick="javascript:deleteLine();"><img src="images/icons/delete.png" width=16 height=16 border=0 align="absmiddle">&nbsp;Remove Selected Line Entry</a>
					<?php } ?>
				</td>
			</tr>
		</table>
	</form>
</div>
<div id="loaderMessage" title="Processing..." style="display: none;">
	<p><span style="float:left; margin:0 7px 20px 0;"><img src="images/loader.gif" /></span>Please wait while the system is processing your request...</p>
</div>
<div id="invoiceAttachment" style="display: none;"></div>
</body>
</html>