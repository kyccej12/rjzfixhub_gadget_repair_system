<?php
session_start();

require_once("handlers/_generics.php");

$o = new _init;

if (!isset($_SESSION['authkey']) || empty($_SESSION['authkey'])) {
    header("Location: login/index.php?exception=1");
    exit;
}

$o->validateKey();

if ($o->exception != 0) {
    header("Location: login/index.php?exception=" . (int)$o->exception);
    exit;
}
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>RJZ Fix Hub Repair Shop System Ver. 1.0a</title>
	<link rel="icon" type="image/x-icon" href="logo-small.ico" />
	<link href="ui-assets/themes/redmond/jquery-ui.css" rel="stylesheet" type="text/css" />
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<link href="style/dropMenu.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript" src="ui-assets/themes/redmond/jquery-ui.js"></script>
	<script language="javascript" src="js/jquery.dialogextend.js"></script>
	<script language="javascript" src="js/main.js?<?php echo uniqid(); ?>"></script>
	<script language="javascript" src="js/dropMenu.js"></script>

</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0" <?php if($o->cpass == 'Y') { echo "onLoad=\"showChangePass();\""; } ?> style="background: url(images/magnefic_logo.jpeg); background-size: 100% 100%; background-repeat: no-repeat;">
 <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
	<tr class="ui-dialog-title ui-widget-header">
		<td colspan=2 height=37 style="padding-left: 3px;">
			<a href="#" onclick="javascript: showMenu();"><img src="images/icons/button-menu.png" width=24 height=24 align=absmiddle></a>
		</td>
		<td align=right style="padding-right: 10px;"><img src="images/icons/user.png" align=absmiddle border=0 width=18 height=18 /><span style="font-size: 11px; font-weight: bold; color: #ffffff;">&nbsp;<?php $o->getUname($_SESSION['userid']); ?>@<?php $o->getBranchName($_SESSION['branchid']); ?>&nbsp;&nbsp;&nbsp;|</span>&nbsp;<a href="logout.php" style="font-size: 12px; font-weight: bold; color: #ffffff; text-decoration: none;" title="Click to Logout"><img src="images/button-logout.png" align=absmiddle border=0 width=24 height=24 />Logout</a></td>
	</tr>
	<tr height=90%>
		<td colspan=3>
			<table width="100%" height="100%" align="center" valign=middle>
				<tr>
					<td align=center>
						<img src="images/rjz_logo.png">
					</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr>
		<td colspan=3>
			<table width="100%" height="100%" cellpadding=0 cellspacing=0 align="center" valign=middle>
				<tr bgcolor="#0e5284">
					<td align=center style="font-family: arial, helvetica, sans-serif; color: #fefefe; font-size: 11px; font-weight: bold;">&copy; Exclusively Developed by RJZ Fix Hub. All Rights Reserved 2026</td>
				</tr>
			</table>
		</td>
	</tr>
 </table>
	<?php
		include("includes/main_menu.php");
	?>
	
<div id="userrights" style="display: none;"></div>
<div id="userdetails" style="display: none;"></div>
<div id="userlist" style="display: none;"></div>
<div id="silist" style="display: none;"></div>
<div id="sidetails" style="display: none;"></div>
<div id="siprint" style="display: none;"></div>
<div id="rrlist" style="display: none;"></div>
<div id="rrdetails" style="display: none;"></div>
<div id="rrprint" style="display: none;"></div>
<div id="polist" style="display: none;"></div>
<div id="podetails" style="display: none;"></div>
<div id="poprint" style="display: none;"></div>
<div id="swlist" style="display: none;"></div>
<div id="swdetails" style="display: none;"></div>
<div id="swprint" style="display: none;"></div>
<div id="silist" style="display: none;"></div>
<div id="sidetails" style="display: none;"></div>
<div id="siprint" style="display: none;"></div>
<div id="itemlist" style="display: none;"></div>
<div id="itemdetails" style="display: none;"></div>
<div id="customerlist" style="display: none;"></div>
<div id="customerdetails" style="display: none;"></div>
<div id="pcvlist" style="display: none;"></div>
<div id="pcvdetails" style="display: none;"></div>
<div id="changepass" style="display: none;"></div>
<div id="srrlist" style="display: none;"></div>
<div id="srrdetails" style="display: none;"></div>
<div id="srrprint" style="display: none;"></div>
<div id="crlist" style="display: none;"></div>
<div id="crdetails" style="display: none;"></div>
<div id="crprint" style="display: none;"></div>
<div id="barcode" style="display: none;"></div>
<div id="report5" style="display: none;"></div>
<div id="ibook" style="display: none;"></div>
<div id="phylist" style="display: none;"></div>
<div id="phydetails" style="display: none;"></div>
<div id="jolist" style="display: none;"></div>
<div id="jodetails" style="display: none;"></div>
<div id="joprint" style="display: none;"></div>
<div id="serviceslist" style="display: none;"></div>

<?php for($rpt = 1; $rpt <= 10; $rpt++) { echo "<div id=\"report$rpt\" style=\"display: none;\"></div>"; } ?>

<div id="salesinquiry" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Sales Date :</span></td>
			<td>
				<input type="text" id="sinq_date" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
	</table>
</div>

<div id="arSummary" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Customer :</span></td>
			<td>
				<select id="ar_customer" style="width: 80%; font-size: 11px;" class="gridInput" />
					<option value = ''>- All Customer -</option>
					<?php
						$cQuery = $o->dbquery("select customer,customer_name from invoice_header where customer != 0 group by customer;");
						while($cRow = $cQuery->fetch_array()) {
							
							echo "<option value='$cRow[0]'>$cRow[1]</option>";
							
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="ar_asof" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
	</table>
</div>

<div id="inventoryReport" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% style="padding: 10px;">
		<tr>
			<td width=35% valign=top><span class="spandix-l">Product Group :</span></td>
			<td>
				<select id = "ir_group" class="gridInput" style="width: 80%; font-size: 11px;">
					<option value=''>- All -</option>
					<?php
						$gQuery = $o->dbquery("select mid, mgroup from options_mgroup order by mgroup;");
						while($gRow = $gQuery->fetch_array()) {
							echo "<option value='$gRow[0]'>$gRow[1]</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="ir_dtf" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="ir_dt2" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
	</table>
</div>

<div id="grossProfitSales" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% style="padding: 10px;">
		<tr>
			<td width=35% valign=top><span class="spandix-l">Product Group :</span></td>
			<td>
				<select id = "gps_group" class="gridInput" style="width: 80%; font-size: 11px;">
					<option value=''>- All -</option>
					<?php
						$gQuery = $o->dbquery("select mid, mgroup from options_mgroup order by mgroup;");
						while($gRow = $gQuery->fetch_array()) {
							echo "<option value='$gRow[0]'>$gRow[1]</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="gps_dtf" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="gps_dt2" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
	</table>
</div>

<div id="goodsReceivedSummary" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="grs_dtf" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="grs_dt2" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
	</table>
</div>

<div id="goodsWithdrawnSummary" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="gws_dtf" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="gws_dt2" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
	</table>
</div>

<div id="pettyCashSummary" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Report Type :</span></td>
			<td>
				<select id="pcs_type" style="width: 80%; font-size: 11px;" class="gridInput" />
					<option value="">- All -</option>
					<option value="Y">- Liquidated -</option>
					<option value="N">- Unliquidated -</option>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Payee :</span></td>
			<td>
				<input type="text" id="pcs_name" class="inputSearch2" style="width: 80%;padding-left: 22px;" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="pcs_dtf" class="gridInput" style="width: 80%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="pcs_dt2" class="gridInput" style="width: 80%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
	</table>
</div>

<div id="inventorybook" style="display:none;">	
	<table border="0" cellpadding="0" cellspacing="0" width=100% class="td_content" style="padding: 10px;">
		<tr>
			<td width=35%><span class="spandix-l">Type :</span></td>
			<td>
				<select id="ibook_group" style="width: 90%; font-size: 11px;" class="gridInput">
				<option value="">- All Inventory Items -</option>
				<?php
					$iut = $o->dbquery("select `mid`,mgroup from options_mgroup;");
					while(list($t,$tt) = $iut->fetch_array()) {
						echo "<option value='$t'>$tt</option>";
					}
				?>
				</select>
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l">Covered Period :</span></td>
			<td>
				<input type="text" id="ibook_dtf" class="gridInput" style="width: 90%;" value="<?php echo date('m/01/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr>
			<td width=35%><span class="spandix-l"></span></td>
			<td>
				<input type="text" id="ibook_dt2" class="gridInput" style="width: 90%;" value="<?php echo date('m/d/Y'); ?>" />
			</td>
		</tr>
		<tr><td height=4></td></tr>
		<tr><td colspan=2><hr></hr></td></tr>
		<tr>
			<td align=center colspan=2>
				<button onClick="processInventory();" class="buttonding" style="font-size: 11px;"><img src="images/icons/processraw.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;View Inventory</button>
				<button onClick="exportInventoryNow();" class="buttonding" style="font-size: 11px;"><img src="images/icons/excel.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Export to Excel</button>
			</td>
		</tr>
	</table>
</div>
<script>
function computeItemAmount() {
    var qtyValue = $("#itemQty").val() || "0";
    var amountValue = $("#itemAmount").val() || "0";

    var qty = parseFloat(
        String(qtyValue).replace(/,/g, "")
    );

    var amount = parseFloat(
        String(amountValue).replace(/,/g, "")
    );

    if (isNaN(qty) || qty <= 0) {
        $("#itemAmountDue").val("0.00");
        return;
    }

    if (isNaN(amount)) {
        amount = 0;
    }

    var total = qty * amount;

    $("#itemAmountDue").val(
        total.toLocaleString("en-US", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        })
    );
}
</script>
<div id="itemEntry" style="display:none;">
    <form name="frmItemEntry" id="frmItemEntry">
        <input type="hidden" id="recordId" name="recordId">

        <table width="100%" cellspacing="2" cellpadding="0">
            <tr>
                <td class="bareThin" width="40%">Description :</td>
                <td>
                    <input type="text" name="itemDescription"
                        id="itemDescription" class="inputSearch2"
                        style="width:90%; padding-left:22px;">
                </td>
            </tr>
            <tr>
                <td class="bareThin" width="40%">Item Code :</td>
                <td>
                    <input type="text" name="itemCode"
                        id="itemCode" class="gridInput"
                        style="width:90%;" disabled>
                </td>
            </tr>
            <tr>
                <td class="bareThin" width="40%">Brand :</td>
                <td>
                    <input type="text" name="itemBrand"
                        id="itemBrand" class="gridInput"
                        style="width:90%;" disabled>
                </td>
            </tr>
            <tr>
                <td class="bareThin" width="40%">Quantity :</td>
                <td>
					<input type="text" name="itemQty"  id="itemQty" class="gridInput" style="width:90%;" value="1"  oninput="computeItemAmount();">
            </tr>
            <tr>
                <td class="bareThin" width="40%">Amount :</td>
                <td>
                    <input type="text" name="itemAmount"
                        id="itemAmount" class="gridInput"
                        style="width:90%;" value="0.00" disabled>
                </td>
            </tr>
            <tr>
                <td class="bareThin" width="40%">Amount Total :</td>
                <td>
                    <input type="text" name="itemAmountDue"
                        id="itemAmountDue" class="gridInput"
                        style="width:90%;" value="0.00" disabled>
                </td>
            </tr>
            <tr>
                <td class="bareThin" width="40%">Remaining Item Left :</td>
                <td>
                    <input type="text" name="itemRemaining"
                        id="itemRemaining" class="gridInput"
                        style="width:90%;" value="0.00" disabled>
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="servicesEntry" style="display:none;">
    <form name="frmServicesEntry" id="frmServicesEntry">
        <input type="hidden" id="recordId" name="recordId">

        <table width="100%" cellspacing="2" cellpadding="0">
            <tr>
                <td class="bareThin" width="40%">Description :</td>
                <td>
                    <input type="text" name="serviceDescription"
                        id="serviceDescription" class="inputSearch2"
                        style="width:90%; padding-left:22px;">
                </td>
            </tr>
            <tr>
                <td class="bareThin" width="40%">Service Code :</td>
                <td>
                    <input type="text" name="serviceCode"
                        id="serviceCode" class="gridInput"
                        style="width:90%;" disabled>
                </td>
            </tr>
            <tr>
                <td class="bareThin" width="40%">Quantity :</td>
                <td>
					<input type="text" name="serviceQty"  id="serviceQty" class="gridInput" style="width:90%;" value="1">
            </tr>
            <tr>
                <td class="bareThin" width="40%">Amount :</td>
                <td>
                    <input type="text" name="serviceAmount"
                        id="serviceAmount" class="gridInput"
                        style="width:90%;" value="0.00" disabled>
                </td>
            </tr>
            <tr>
                <td class="bareThin" width="40%">Amount Total :</td>
                <td>
                    <input type="text" name="serviceAmountDue"
                        id="serviceAmountDue" class="gridInput"
                        style="width:90%;" value="0.00" disabled>
                </td>
            </tr>
        </table>
    </form>
</div>

<div id="loaderMessage" style="display: none;">
	<table width=100%>
		<tr>
			<td align=center style="color:grey; padding-top: 40px; font-size: 11px;"><img src="images/ajax-loader.gif" align=absmiddle>&nbsp;Please wait while the system is processing your request...</td>
		</tr>
	</table>
</div>
<div id = "popSaver" class = "popSaver">Record Has Been Successfully Saved...</div>
<div id="errorMessage" title="Error Message" style="display: none;">
	<p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span><b>Unable to continue due to the following error(s):</b></p>
	<p style="margin-left: 20px; text-align: justify;" id="message"></span></p>
</div>
<div id="mainLoading" style="display:none; width:100%;height:100%;position:absolute;top:0;margin:auto;"> 
	<div style="background-color:white;width:10%;height:20%;;margin:auto;position:relative;top:100;">
		<img style="display:block;margin-left:auto;margin-right:auto;" src="images/ajax-loader.gif" width=128 height=128 align=absmiddle /> 
	</div>
	<div id="mainLoading2" style="background-color:white;width:100%;height:100%;position:absolute;top:0;margin:auto;opacity:0.5;"> </div>
</div>
<div class="suggestionsBox" id="suggestions" style="display: none;">
	<div class="suggestionList" id="autoSuggestionsList">&nbsp;</div>
</div>
</body>
</html>