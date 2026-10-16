<?php
	include("includes/dbUSE.php");
	session_start();
	
	if(isset($_GET['fid']) && $_GET['fid'] != "") { $res = getArray("select * from contact_info where file_id='$_GET[fid]';"); }
	function getMod($def,$mod) {
		if($def == $mod) { echo "class=\"float2\""; }
	}
	
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Bata Ensesyal</title>
<link rel="stylesheet" type="text/css" href="style/style.css" />
<link rel="stylesheet" type="text/css" href="ui-assets/themes/smoothness/jquery-ui.css" />
<link rel="stylesheet" type="text/css" href="ui-assets/datatables/css/jquery.dataTables.css">
<script type="text/javascript" charset="utf8" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/jquery.dataTables.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.jqueryui.js"></script>
<script type="text/javascript" charset="utf8" src="ui-assets/datatables/js/dataTables.select.js"></script>
<script>

	function saveCInfo(fid) {
		if(confirm("Are you sure you want to save changes made to this record?") == true) {
			var msg = "";
			if($("#type").val() == "FSUPPLIER") {
				if($("#billing_address").val() == "") {
					var msg = msg + "- You have not specified foreign address of this foreign supplier<br/>"; 
				}
			} else {
				if($("#tradename").val() == "") { msg = msg + "- You did not specify customer/supplier name/trade name.<br/>"; }
				if($("#province").val() == "") { msg = msg + "- You did not specify Provincial Address for this customer/supplier<br/>"; }
				if($("#city").val() == "") { msg = msg + "- You did not specify City/Municipal Address for this customer/supplier<br/>"; }
			}

			if(msg!="") {
				parent.sendErrorMessage(msg);
			} else {
				var url = $(document.contactinfo).serialize();
				url = "mod=saveCInfo&"+url;
				$.post("src/sjerp.php", url);
				alert("Record Successfully Added or Updated!")
				parent.closeDialog("#customerdetails");	
				parent.showCust();
			}
		}
	}
	
	function deleteCust(fid) {
		if(confirm("Are you sure you want to delete this record?") == true) {
			$.post("src/sjerp.php", { mod: "deleteCust", fid: fid, sid: Math.random() }, function(){ "Customer Record Successfully Deleted!"; parent.closeDialog("#customerdetails"); parent.showCust(); });
		}	
	}

	function getCities(pid) {
		$.post("src/sjerp.php", { mod: "getCities", pid: pid, sid: Math.random() }, function(data) {
			$("#city").html(data);
		},"html");
	}
	
	function getBrgy(city) {
		$.post("src/sjerp.php", { mod: "getBrgy", city: city, sid: Math.random() }, function(data) {
			$("#brgy").html(data);
		},"html");
	}
	
	function changeMod(mod) {
		document.changeModPage.mod.value = mod;
		document.changeModPage.submit();
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
<body bgcolor="#ffffff" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
<form name="contactinfo" id="merchandise">
	<input type="hidden" name="fid" id="fid" value="<?php echo $res['file_id']; ?>">
	<table width="100%" cellspacing="0" cellpadding="5" style="border-bottom: 1px solid black; margin-bottom: 5px;">
		<tr>
			<td align=left>
				<a href="#" onClick="saveCInfo(<?php echo $_GET['fid']; ?>);" class="topClickers"><img src="images/icons/floppy.png" width=18 height=18 align=absmiddle />&nbsp;Add/Save Changes Made</a>
				<?php if($_GET['fid'] || $_GET['fid'] != "") { ?>
				&nbsp;&nbsp;<a href="#" onClick="deleteCust('<?php echo $_GET['fid']; ?>');" class="topClickers"><img src="images/icons/delete.png" width=18 height=18 align=absmiddle />&nbsp;Delete Record</a>
				<?php } ?>
			</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="1" cellpadding="0" >
		<tr>
			<td width="20%" align=left class="spandix-l">
				Trade or Individual Name :
			</td>
			<td width="40%" align=left>
				<input type="text" id="tradename" name="tradename" class="nInput" style="width: 80%;" value="<?php echo $res['tradename']; ?>" />
			</td>
			<td width="10%" class="spandix-l">
				Customer Type :
			</td>
			<td width="30%">
				<select id="type" name="type" style="width: 80%;" class="nInput" />
					<option value="SUPPLIER" <?php if($res['type'] == "SUPPLIER") { echo "selected"; }?>>Supplier</option>
				</select>
			</td>
		</tr>
		<tr>
			<td width="20%" align=left class="spandix-l">
				Business Style :
			</td>
			<td width="40%" align=left>
				<input type="text" id="bizstyle" name="bizstyle" class="nInput" style="width: 80%;" value="<?php echo $res['bizstyle']; ?>" />
			</td>
			<td width="10%" class="spandix-l"></td>
			<td width="30%" class="spandix-l"><input type="checkbox" name="status" id="status" <?php if($res['active'] == 'N') { echo "checked"; } ?> value="N">&nbsp;Inactive</td>
		</tr>
	</table>
	<table cellspacing=0 cellpadding=0 width=100% align=center style="margin-top: 10px;">
		<tr>
			<td style="padding: 0px 0px 1px 0px;">
				<div id="custmenu" align=left class="ddcolortabs">
					<ul class=float2>
						<li><a href="#" <?php getMod("1",$_GET[mod]); ?> onclick="javascript: changeMod(1);"><span id="tbbalance3">General Information</span></a></li>
					</ul>
				</div>
			</td>
		</tr>
	</table>

	<table width="100%" cellpadding=0 cellspacing=1 class="td_content" style="padding:10px;">
		<tr>
			<td width=20% class="spandix-l" valign=top>Lot No./Street #/Village :</td>
			<td width=80% colspan=3><textarea name="address" id="address" style="width: 100%;" rows=2><?php echo $res['address']; ?></textarea></td>
		</tr>
		<tr>
			<td width=20% class="spandix-l">Province :</td>
			<td width=30%>
				<select id="province" name="province" style="width: 100%;" class="nInput" onchange="getCities(this.value);" />
					<option value="">- Select Province -</option>
					<?php
						$q0 = dbquery("select provCode, provDesc from options_provinces order by provDesc asc;");
						while($_0 = mysql_fetch_array($q0)) {
							print "<option value='$_0[0]' "; if($_0[0] == $res['province']) { echo "selected"; }
							print ">$_0[1]</option>";
						}
						
					?>
				</select>
			</td>
			<td width=20% class="spandix-l">Contact Person :</td>
			<td width=30%>
				<input type="text" id="cperson" name="cperson" class="nInput" style="width: 100%;" value="<?php echo $res['cperson']; ?>" />
			</td>
		</tr>
		<tr>
			<td width=20% class="spandix-l">Town / City :</td>
			<td width=30%>
				<select id="city" name="city" style="width: 100%;" class="nInput" onChange="getBrgy(this.value);" />
					<option value="">- Select City -</option>
					<?php
						$q1 = dbquery("select citymunCode, citymunDesc from options_cities where provCode = '$res[province]' order by citymunDesc asc;");
						while($_1 = mysql_fetch_array($q1)) {
							print "<option value='$_1[0]' "; if($_1[0] == $res['city']) { echo "selected"; }
							print ">$_1[1]</option>";
						}
					?>
				</select>
			</td>
			<td width=20% class="spandix-l">Assigned Sales Rep :</td>
			<td width=30%>
				<select id="srep" name="srep" style="width: 100%;" class="nInput" />
					<option value="">None</option>
					<?php
						$srtq = dbquery("select record_id, sales_rep from options_salesrep order by sales_rep;");
						while(list($srid,$srtd) = mysql_fetch_array($srtq)) {
							echo "<option value='$srid' ";
							if($res['srep'] == $srid) { echo "selected"; }
							echo ">$srtd</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td width=20% class="spandix-l">Barangay :</td>
			<td width=30%>
				<select id="brgy" name="brgy" style="width: 100%;" class="nInput" />
					<option value="">- Select Brgy -</option>
					<?php
						$q0 = dbquery("select brgyCode, brgyDesc from options_brgy where citymunCode='$res[city]' order by brgyDesc asc;");
						while($_0 = mysql_fetch_array($q0)) {
							print "<option value='$_0[0]' "; if($_0[0] == $res['brgy']) { echo "selected"; }
							print ">$_0[1]</option>";
						}
						
					?>
				</select>
			</td>
			<td width=20% class="spandix-l">Credit Limit :</td>
			<td width=30%>
				<input type="text" id="climit" name="climit" class="nInput" style="width: 100%;" value="<?php echo number_format($res['credit_limit'],2); ?>" onfocus="if(this.value == '0.00') { this.value = ''; }" onblur="if(this.value=='') { this.value='0.00'; } if(isNaN(parent.stripComma(this.value)) == true) { parent.sendErrorMessage('Error: Invalid User Input!'); this.value='0.00'; this.focus(); } " />
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=30%><input type="hidden" name="price_level" name="price_level"></td>
		</tr>
		<tr>
			<td width=20% class="spandix-l">Tel. No. :</td>
			<td width=30%><input type="text" id="telno" name="telno" class="nInput" style="width: 100%;" value="<?php echo $res['tel_no']; ?>" /></td>
			<td width=20% class="spandix-l">Credit Terms :</td>
			<td width=30%>
				<select id="terms" name="terms" style="width: 100%;" class="nInput" />
					<?php
						$tq = dbquery("select terms_id, description from options_terms order by terms_id;");
						while(list($tid,$td) = mysql_fetch_array($tq)) {
							echo "<option value='$tid' ";
							if($res['terms'] == $tid) { echo "selected"; }
							echo ">$td</option>";
						}
					?>
				</select>
			</td>
		</tr>
		<tr>
			<td width=20% class="spandix-l">Fax No. :</td>
			<td width=30%><input type="text" id="faxno" name="faxno" class="nInput" style="width: 100%;" value="<?php echo $res['fax_no']; ?>" /></td>
			<td width=20% class="spandix-l">Vatable Supplier :</td>
			<td width=30%>
				<select id="vatable" name="vatable" style="width: 100%;" class="nInput" />
					<option value="N" <?php if($res['vatable'] == 'N') { echo "selected"; } ?>>No</option>
					<option value="Y" <?php if($res['vatable'] == 'Y') { echo "selected"; } ?>>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td width=20% class="spandix-l">Email Address :</td>
			<td width=30%>
				<input type="text" id="email" name="email" class="nInput" style="width: 100%;" value="<?php echo $res['email']; ?>" />
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=30%></td>
		</tr>
		<tr>
			<td width=20% class="spandix-l">T-I-N No. :</td>
			<td width=30%>
				<input type="text" id="tin_no" name="tin_no" class="nInput" style="width: 100%;" value="<?php echo $res['tin_no']; ?>" />
			</td>
			<td width=20% class="spandix-l"></td>
			<td width=30%></td>
		</tr>
		<tr>
			<td colspan=4 width=100% align=left><hr width=100% align=center></hr></td>
		</tr>
		<tr>
			<td width=20% class="spandix-l" valign=top>Complete Foreign Address :</td>
			<td width=80% colspan=3><textarea name="billing_address" id="billing_address" style="width: 100%;" rows=2><?php echo $res['billing_address']; ?></textarea></td>
		</tr>
		<tr>
			<td width=20% class="spandix-l" valign=top>Complete Shipping Address :</td>
			<td width=80% colspan=3><textarea name="shipping_address" id="shipping_address" style="width: 100%;" rows=2><?php echo $res['shipping_address']; ?></textarea></td>
		</tr>
	</table>
</form>
<form name="changeModPage" id="changeModPage" action="contact.details.php" method="GET" >
	<input type="hidden" name="fid" id="fid" value="<?php echo $_GET['fid']; ?>">
	<input type="hidden" name="mod" id="mod">
</form>
</body>
</html>
<?php mysql_close($con);