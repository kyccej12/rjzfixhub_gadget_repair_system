<?php
	include("includes/dbUSE.php");
	session_start();
	
	list($item_code) = getArray("select item_code from products_master where record_id = '$_GET[fid]';");
	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Habagat Outdoor Equipment ERP Ver2.0</title>
<link href="style/style.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script language="javascript" src="js/date.js"></script>
<script language="javascript" src="js/tableH.js"></script>
<script>
	
	function addVehicle() {
		if($("#plate_no").val() == "") {
			parent.sendErrorMessage("Unable to continue. You must specify vehicle's Plate No!");
		} else {
			$.post("geck.datacontrol.php", { mod: "addVFleet", plate_no: $("#plate_no").val(), vdesc: $("#vdesc").val(), ccode: <?php echo $_GET['fid']; ?>, sid: Math.random() }, function(data) {
				if(data == 'Error') {
					parent.sendErrorMessage("Error: A duplicate Plate Number is detected!");
				} else {
					$("#details").html(data);
					$("#plate_no").val('');
					$("#vdesc").val(''); 
				}
			},"html");
		}
		
	}
	
	function deleteVehicle(vid,ccode) {
		if(confirm("Are you sure you want to delete this record?") == true) {
			$.post("geck.datacontrol.php", { mod: "delVeh", vid: vid, ccode: ccode, sid: Math.random() }, function(data){ 
				"Customer Record Successfully Deleted!";
				$("#details").html(data);
			},"html");
		}
	}
	
	function rawmatLookup(inputString,el) {
			$("#isSearch").val(1);
			if(inputString.length == 0) {
				$('#suggestions').hide();
			} else {
				var op = $("#"+el+"").offset();
				$.post("rawmatlookup.php", {queryString: ""+inputString+"" }, function(data){
				if(data.length > 0) {
					$('#suggestions').css({top: op.top+20, left: op.left, width: '500px'});
					$('#suggestions').show();
					$('#autoSuggestionsList').html(data);
				} else { $("#suggestions").hide(); }
			});
		}
	}
	
	function pickItem(icode,idesc,cost,unit) {
		$("#code").val(icode);
		$("#description").val(decodeURIComponent(idesc));
		$("#unit_cost").val(parent.kSeparator(cost));
		$("#unit").val(unit);
		$("#qty").focus();
	}
	
	function computeAmount() {
		var price = parseFloat(parent.stripComma($("#unit_cost").val()));
		var qty = parseFloat(parent.stripComma($("#qty").val()));
		if(isNaN(qty) == true || isNaN(price) == true || qty == "" || price == "") {
			parent.sendErrorMessage("Invalid Quantity or Price. Please check your entries and try again...")
		} else {
			var amt = price * qty;
			    amt = amt.toFixed(2);
			$("#amount").val(parent.kSeparator(amt));
		}
	}
	
	function addBoQ() {
		var price = parseFloat(parent.stripComma($("#unit_cost").val()));
		var qty = parseFloat(parent.stripComma($("#qty").val()));
		if(isNaN(qty) == true || isNaN(price) == true || qty == "" || price == "") {
			parent.sendErrorMessage("Invalid Quantity or Price. Please check your entries and try again...")
		} else {
			$.post("src/sjerp.php", {mod: "addBoQ", item_code: $("#item_code").val(), code: $("#code").val(), description: $("#description").val(), unit: $("#unit").val(), qty: qty, cost: price, amount: parent.stripComma($("#amount").val()), sid: Math.random() },function(data) {
				$("#details").html(data);
				$("#code").val(''); $("#description").val(''); $("#unit").val(''); $("#qty").val(''); $("#unit_cost").val(''); $("#amount").val('');
			},"html");
		}
	}
	
	function deleteBoQ(lid,item_code) {
		if(confirm("Are you sure you want to delete this entry?") == true) {
			$.post("src/sjerp.php", { mod: "deleteBoQ", lid: lid, item_code: item_code, sid: Math.random()},function(data) {
				$("#details").html(data);
			},"html");
		}
	}
	
	$('html').click(function(){ $("#suggestions").fadeOut(200); });
</script>
</head>
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
 <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
	<form name="contactinfo" id="contactinfo">
		<input type="hidden" id = "item_code" name="item_code" value="<?php echo $item_code; ?>">
		 <table cellspacing=0 cellpadding=0 border=0 width=100%>
			<tr bgcolor="#887e6e">
				<td align=left class="gridHead" width="12%">CODE</td>
				<td align=left class="gridHead">DESCRIPTION</td>
				<td align=center class="gridHead" width="10%">UNIT</td>
				<td align=center class="gridHead" width="10%">QTY</td>
				<td align=center class="gridHead" width="15%">UNIT COST</td>
				<td align=center class="gridHead" width="15%">AMOUNT</td>
				<td align=center class="gridHead" width="18">&nbsp;</td>
			</tr>
			<tr bgcolor="#fefefe">
				<td align=left class="grid" colspan=2>
					<input type="hidden" id="code" name="code" style="width:95%" />
					<input class='inputSearch2' type="text" id="description" name="description" style="width:95%" onkeyup="rawmatLookup(this.value,this.id);" /></td>
				<td align=centerclass="grid">
					<select id='unit' class='gridInput' style='width: 95%'>
						<?php 
							$uLoop = dbquery("select unit,description from options_units order by description");
							while(list($myUnit,$myDesc) = mysql_fetch_array($uLoop)) {
								echo "<option value='$myUnit'>".strtoupper($myDesc)."</option>";
							}
						?>
					</select>
				</td>
				<td align=left class="grid"><input class='gridInput' type="text" id="qty" name="qty" style="width:95%; text-align: right;" onchange="computeAmount();" /></td>
				<td align=left class="grid"><input class='gridInput' type="text" id="unit_cost" name="unit_cost" style="width:95%; text-align: right;" onchange="computeAmount();" /></td>
				<td align=left class="grid"><input class='gridInput' type="text" id="amount" name="amount" style="width:95%; text-align: right;" value = "0.00" readonly /></td>
				<td align=center width="18" class="grid"><a href="#" onclick="javascript: addBoQ();"><img src="images/icons/add-2.png" border=0 width=20 height=20 align=absmiddle title="Click to Add to List" /></a></td>
			</tr>
		</table>
		<table cellspacing=0 cellpadding=0 border=0 width=100% id="details">
		<?php
			$vf = dbquery("SELECT id, code, description, unit, qty, unit_cost, amount from bom WHERE product = '$item_code';");
			while($row = mysql_fetch_array($vf)) {
				if($i%2==0){ $bgC = "#ededed"; } else { $bgC = "#ffffff"; }
				echo "<tr bgcolor=\"$bgC\">
							<td class=grid align=center width=\"12%\">$row[code]</td>
							<td class=grid align=left>$row[description]</td>
							<td class=grid align=center width=\"10%\">$row[unit]</td>
							<td class=grid align=right width=\"10%\" style='padding-right: 20px;'>".number_format($row['qty'],2)."</td>
							<td class=grid align=right width=\"15%\" style='padding-right: 20px;'>".number_format($row['unit_cost'],4)."</td>
							<td class=grid align=right width=\"15%\" style='padding-right: 20px;'>".number_format($row['amount'],4)."</td>
							<td class=grid align=right style='padding-right: 5px;' with=18><a href='#' onclick=\"javascript: deleteBoQ($row[id],'$item_code');\"><img src='images/icons/delete.png' border=0 width=14 height=14 align=absmiddle title='Click to delete from list' /></a></td>
					  </tr>"; $i++; $amtGT+=$row['amount'];
			}
										
			if($i < 6) {
				for($i; $i <= 5; $i++) {
					if($i%2==0){ $bgC = "#ededed"; } else { $bgC = "#ffffff"; }
					echo '<tr bgcolor="'.$bgC.'">
							<td align=left class="grid" width="100%" colspan=7>&nbsp;</td>
		     			  </tr>';
				}
			}
			echo "<tr>
					<td class=grid align=left colspan=5><b>GRAND TOTAL </b></td>
					<td class=grid align=right width=\"15%\" style='padding-right: 20px;'><b>".number_format($amtGT,4)."</b></td>
					<td class=grid align=right style='padding-right: 5px;' with=18>&nbsp;</td>
			 </tr>";
		?>
		</table>
	 </form>
 </table>
 <div class="suggestionsBox" id="suggestions" style="display: none;">
	<div class="suggestionList" id="autoSuggestionsList">&nbsp;</div>
</div>
</body>
</html>
<?php mysql_close($con);