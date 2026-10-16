
<?php	
	session_start();
	//ini_set("display_errors","On");
	require_once("handlers/_pofunct.php");
	$p = new myPO;
	
	
	$uid = $_SESSION['userid'];
	if(isset($_REQUEST['po_no']) && $_REQUEST['po_no'] != '') { 
		$res = $p->getArray("select *, lpad(po_no,6,0) as pono, lpad(supplier,6,0) as sup_code, date_format(po_date,'%m/%d/%Y') as d8, if(date_needed != '0000-00-00',date_format(date_needed,'%m/%d/%Y'),'') as nd8 from po_header where po_no='$_REQUEST[po_no]' and branch = '$_SESSION[branchid]';");
		$cSelected = "Y"; $status = $res['status']; $po_no = $res['pono']; $lock = $res['locked'];
		list($d)  = $p->getArray("SELECT SUM(ROUND(qty*discount,2)) FROM po_details  WHERE po_no = '$_REQUEST[po_no]' and branch = '$_SESSION[branchid]';");
		$res['discount'] = $d; $res['amount'] = $res['amount'] - $res['discount'];
	} else {  
		list($po_no) = $p->getArray("select lpad((ifnull(max(po_no),0)+1),6,0) from po_header where branch = '$_SESSION[branchid]';"); 
		$status = "Active"; $cSelected = "N"; $lock = 'N';
	}
		
	
	if($res['status'] != "Active") { $isReadOnly = "readonly"; $isDisabled = "disabled"; }
	
?>
<!doctype html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>Black Smokehaus Inventory System Ver. 1.0a</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/tautocomplete.css" rel="stylesheet" type="text/css" />
	<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
	<script language="javascript" src="js/jquery.dialogextend.js"></script>
	<script language="javascript" src="js/tautocomplete.js"></script>
	<script language="javascript" src="js/tableH.js"></script>
	<script language="javascript" src="js/po.js?sid=<?php echo uniqid(); ?>"></script>
	<script>
	
		var line;
		
		function selectLine(obj,lid) {
			gObj = obj;
			$(obj).closest("tr").siblings().removeClass("diffcolor");
			$(obj).toggleClass("diffcolor");
			line = lid;
		}
		
		$(document).ready(function() { 
			$("#po_date").datepicker(); 
			$("#date_needed").datepicker(); 
			
			$("#qty, #unit_cost, #amount").keyup(
				function(e) {
					if(e.keyCode == 13) {
						addDetails();
					}
				}
			);


			<?php if($status == 'Finalized' || $status == 'Cancelled') { echo "$(\"#xform :input\").prop('disabled',true);"; } ?>
			
			var myProduct = $("#description").tautocomplete({
				width: "720px",
				columns: ['Item Code','Description','Unit','Unit Cost'],
				hide: false,
				ajax: {
					url:  "suggestItemsCost-2.php",
					type: "GET",
					data:function() {var x = { term: myProduct.searchdata() , supplier : $("#customer_id").val()  }; return x; },
					success: function (data) {
						var filterData = [];
						var searchData = eval("/" + myProduct.searchdata() + "/gi");
						$.each(data, function (i,v) {
							if ((v.description.search(new RegExp(searchData)) != -1) ||(v.indcode.search(new RegExp(searchData)) != -1)) {
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
					$("#unit_cost").val(cellData['Unit Cost']);
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
					$("#terms").val(ui.item.terms);
				}
			});
		});
		
		function deleteLine() {
			if(line == "") {
				parent.sendErrorMessage("- You have not selected any record that you wish to remove.");
			} else {
				if(confirm("Are you sure you want to remove this entry?") == true) {
					$.post("po.datacontrol.php", { mod: "deleteLine", lid: line, po_no: $("#po_no").val(), sid: Math.random() }, function(ret) {
						$("#details").html(ret);
						line = "";
						getTotals();
					},"html");
				}
			}
		}
		
	</script>
	<style>
		::-webkit-scrollbar {
			width: 1px;
			
		}
	</style>
</head>
<body leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<div>
	<form name="xform" id="xform">
		<input type="hidden" name="cSelected" id="cSelected" value="<?php echo $cSelected; ?>">
		<input type=hidden name="prev_po_date" id="prev_po_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>">
		<table width=98% cellpadding=0 cellspacing=0 border=0 align=center>
			<tr>
				<td class="upper_menus" align=left>
					<?php $p->setHeaderControls($status,$po_no,$_SESSION['userid']); ?>
				</td>
				<td width=30% align=right style='padding-right: 5px;'><?php if($po_no) { $p->setNavButtons($po_no); } ?></td>
			</tr>
			<tr><td height=2></td></tr>
		</table>

		<table border="0" cellpadding="0" cellspacing="1" width=100% class="td_content">
			<tr>
				<td width=50% valign=top>
					<table width=100% style="padding:0px 0px 0px 0px;">
						<tr><td height=2></td></tr>
						<tr>
							<td class="bareBold" align=left valign=top width=25% style="padding-left: 35px;">Supplier :</td>
							<td align="left">
								<table cellspacing=0 cellpadding=0 border=0 width=100%>
									<tr>
										<td width=25%><input type="text" id="customer_id" name="customer_id" value="<?php echo $res['sup_code']?>" class="inputSearch2" style="padding-left: 22px;"></td>
										<td width=75% align=right colspan=2><input class="gridInput" type="text" name="customer_name" id="customer_name" autocomplete="off" value="<?php echo $res['supplier_name']; ?>" style="width: 100%;" readonly></td>
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
							<td align="left" class="bareBold" style="padding-left: 35px;" valign=top></td>
							<td align=left>
								<input type="hidden" name="delivery_address" id="delivery_address">
							</td>				
						</tr>
						<tr>
							<td class="bareBold" align=left style="padding-left: 35px;">Credit Term&nbsp;:</td>
							<td align="left">
								<select id="terms" name="terms" class="gridInput" style="width: 150px; font-size: 11px" />
									<?php
										$tq = $p->dbquery("select terms_id, description from options_terms order by terms_id;");
										while(list($tid,$td) = $tq->fetch_array(MYSQLI_BOTH)) {
											echo "<option value='$tid' ";
											if($res['terms'] == $tid) { echo "selected"; }
											echo ">$td</option>";
										}
									?>
								</select>
								<input type="hidden" name="proj" id="proj">
							</td>
						</tr>
					</table>
				</td>
				<td valign=top>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>
						<tr><td height=2></td></tr>
						<tr>
							<td align="left" width="30%" class="bareBold" style="padding-left: 45px;">P.O No.&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:40%;" type=text name="po_no" id="po_no" value="<?php echo $po_no; ?>" readonly>
							</td>				
						</tr>
						<tr>
							<td align="left" width="30%" class="bareBold" style="padding-left: 45px;">Trans. Date&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:40%;" type=text name="po_date" id="po_date" value="<?php if(!$res['d8']) { echo date('m/d/Y'); } else { echo $res['d8']; }?>" readonly>
							</td>				
						</tr>
						<tr>
							<td align="left" class="bareBold" style="padding-left: 45px;">Requested By :</td>
							<td align=left>
								<input class="gridInput" style="width:40%;" type=text name="requested_by" id="requested_by" value="<?php echo $res['requested_by']; ?>">
							</td>				
						</tr>
						<tr>
							<td align="left" width="30%" class="bareBold" style="padding-left: 45px;">Date Needed&nbsp;:</td>
							<td align=left>
								<input class="gridInput" style="width:40%;" type=text name="date_needed" id="date_needed" value="<?php  echo $res['nd8']; ?>">
								<input type="hidden" name="mrs_no" id="mrs_no" value="<?php  echo $res['mrs_no']; ?>">
							</td>				
						</tr>
					</table>
				</td>
			</tr>
		</table>

		<table cellspacing=0 cellpadding=0 border=0 width=100% style="margin-top: 1px;">
			<tr>
				<td align=left class="ui-state-default" style="padding: 5px 5px 5px 25px;" width="10%">ITEM CODE</td>
				<td align=left class="ui-state-default" style="padding: 5px;" width="50%">DESCRIPTION</td>
				<td align=center class="ui-state-default" style="padding: 5px;" width="10%">UNIT</td>
				<td align=center class="ui-state-default" style="padding: 5px;" width="10%">QTY</td>
				<td align=center class="ui-state-default" style="padding: 5px;" width="10%">UNIT COST</td>
				<td align=center class="ui-state-default" style="padding: 5px;" width="10%">AMOUNT</td>
			</tr>
			<?php
				if($status == "Active" || $status == "") {
					echo '<tr>
							<td align=center class="grid" colspan=2><input type="hidden" id="product_code" /><input type=text class="inputSearch" style="padding-left: 22px; width: 98%" id="description" /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="unit" style="width: 98%; text-align: center;" readonly /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="qty" style="width: 98%; text-align: right;" onblur="computeAmount();" /></td>
							<td align=center class="grid"><input class="gridInput" type=text id="unit_cost" style="width: 98%; text-align: right;" onchange="computeAmount();"/></td>
							<td align=center class="grid" colspan=2><input class="gridInput" type=text id="amount" style="width: 98%;text-align: right;" readonly/></td>
						</tr>';
				}
			?>
		</table>
		<div id="details" style="height: 150px; overflow-x: auto; border-bottom: 3px solid #4297d7; scrollbar-width: none;">
			<?php $p->PODETAILS($po_no,$status,$lock) ?>
		</div>
		<table width=100% class="td_content">
			<tr>
				<td width=50%>
					Transaction Remarks: <br/>
					<textarea type="text" id="remarks" style="width:83%;" onchange="savePOHeader();"><?php echo $res['remarks']; ?></textarea>
				</td>
				<td align=right width=50%>
					<table border="0" cellpadding="0" cellspacing="1" width=100%>			
						<tr>
							<td align="left" width="80%" class="bareBold" style="padding-left: 40%;">Total Amount&nbsp;:</td>
							<td align=right>
								<input type=hidden name="amount_b4_discount" id="amount_b4_discount" value="<?php echo number_format(($res['amount']+$res['discount']),2); ?>">
								<input type=hidden name="discount_in_peso" id="discount_in_peso" value="<?php echo number_format($res['discount'],2); ?>">
								<input style="width:80%;text-align:right;" type=text name="total_due" id="total_due" value="<?php echo number_format($res['amount'],2); ?>" readonly>
							</td>				
						</tr>
					</table>
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
<div id="itemDescDiv" style="display: none;" align=center>
	<table border=0>
		<tr>
			<td width=25% valign=top><b>Custom Description : </b></td>
			<td> 
				<textarea name="customDesc" id="customDesc" style="font-family: arial; width: 95%;" rows=3></textarea>
			</td>
		</tr>
	</table>
</div>
<div id="loaderMessage" title="Processing..." style="display: none;">
	<p><span style="float:left; margin:0 7px 20px 0;"><img src="images/loader.gif" /></span>Please wait while the system is processing your request...</p>
</div>
<div id="discountDiv" style="display: none;" align=center>
	<table border=0>
		<tr>
			<td>Discount : </td>
			<td>
				<input type='text' class="nInput" id="poDiscount" style="font-family:Arial; width: 140px;" > / UoM
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
</body>
</html>