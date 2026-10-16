<?php
	include("includes/dbUSE.php");
	session_start();
	
	if(isset($_GET['fid']) && $_GET['fid'] != "") { $res = getArray("select *, date_format(birthdate,'%m/%d/%Y') as birthdate from customer_info where file_id='$_GET[fid]';"); }
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

	$(document).ready(function() {
		 $("#bday").datepicker({changeMonth: true, changeYear: true, yearRange: "-90:+00"});
	});
	function saveCustomerInfo(fid) {
		if(confirm("Are you sure you want to save changes made to this record?") == true) {
			var msg = "";
			
			if($("#lname,#fname").val() == "") { msg = msg + "- You did not specify customer name/trade name.<br/>"; }
				
			if(msg!="") {
				parent.sendErrorMessage(msg);
			} else {
				var url = $(document.contactinfo).serialize();
				url = "mod=saveCustomerInfo&"+url;
				$.post("src/sjerp.php", url);
				// alert("Record Successfully Added or Updated!")
				parent.closeDialog("#customerdetails");	
				parent.showCustProfiles();
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
				<a href="#" onClick="saveCustomerInfo(<?php echo $_GET['fid']; ?>);" class="topClickers"><img src="images/icons/floppy.png" width=18 height=18 align=absmiddle />&nbsp;Add/Save Changes Made</a>
				<?php if($_GET['fid'] || $_GET['fid'] != "") { ?>
				&nbsp;&nbsp;<a href="#" onClick="deleteCust('<?php echo $_GET['fid']; ?>');" class="topClickers"><img src="images/icons/delete.png" width=18 height=18 align=absmiddle />&nbsp;Delete Record</a>
				<?php } ?>
			</td>
		</tr>
	</table>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" >
		<tr>
			<td width="30%" align=center class="spandix-l" rowspan=11>
				<img src="images/id/main.png" width=120 height=120 >
				<?php
					if($_GET['fid'] != '') { echo "<br/><br/><span>Customer ID: <b>" . str_pad($_GET['fid'],6,'0',STR_PAD_LEFT). "</b></span>"; }

				?>
			</td>
			<td width="70%" align=left colspan=2>
			<table width=100% cellpadding=0 cellspacing=2>
				<tr>
					<td>
						<input type="text" id="lname" name="lname" class="gridInput" style="width: 100%;" value="<?php echo $res['lname']; ?>" />
						<br/><span style="font-size:7pt;color:#000;padding-left:1%;" >Last Name</span>
					</td>
					<td>
						<input type="text" id="fname" name="fname" class="gridInput" style="width: 100%;" value="<?php echo $res['fname']; ?>" />
						<br/><span style="font-size:7pt;color:#000;padding-left:1%;" >First Name</span>
					</td>
				
					<td>	
						<input type="text" id="mname" name="mname" class="gridInput" style="width: 100%;" value="<?php echo $res['mname']; ?>" />
						<br/><span style="font-size:7pt;color:#000;padding-left:1%;" >Full Middle Name</span>
					</td>	
				</tr>
				<tr>
					<td>
						<input type="text" id="bizstyle" name="bizstyle" class="nInput" style="width: 100%;" value="<?php echo $res['bizstyle']; ?>" />
						<br/><span style="font-size:7pt;color:#000;padding-left:1%;" >Business Style</span>
					</td>
				</tr>
				<tr>
					<td>
						<select id="gender" name="gender" class="gridInput" style="width: 100%;">
							<option value="M" <?php if($res['gender'] == 'M') { echo "selected"; } ?>>Male</option>
							<option value="F" <?php if($res['gender'] == 'F') { echo "selected"; } ?>>Female</option>	
						</select>
						<br/><span style="font-size:7pt;color:#000;" >Gender</span>			
					</td>
				</tr>
				<tr>
					<td>
						<input type="text" id="bday" name="bday" class="gridInput" style="width: 100%;" value="<?php echo $res['birthdate']; ?>" />
						<br/><span style="font-size:7pt;color:#000;" >Birthdate</span>			
					</td>			
				</tr>
			</table>
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
				<td width=20% class="spandix-l">Contact No. :</td>
				<td width=30%><input type="text" id="contact_no" name="contact_no" class="nInput" style="width: 100%;" value="<?php echo $res['contact_no']; ?>" /></td>
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
			<td width=20% class="spandix-l">Email Address :</td>
			<td width=30%>
				<input type="text" id="email" name="email" class="nInput" style="width: 100%;" value="<?php echo $res['email']; ?>" />
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
		</tr>
		<tr>
			<td colspan=4 width=100% align=left><hr width=100% align=center></hr></td>
		</tr>
		<tr>
			<td width=20% class="spandix-l" valign=top>Complete Shipping Address :</td>
			<td width=80% colspan=3><textarea name="shipping_address" id="shipping_address" style="width: 100%;" rows=2><?php echo $res['shipping_address']; ?></textarea></td>
		</tr>
	</table>
</form>
<form name="changeModPage" id="changeModPage" action="customer.details.php" method="GET" >
	<input type="hidden" name="fid" id="fid" value="<?php echo $_GET['fid']; ?>">
	<input type="hidden" name="mod" id="mod">
</form>
</body>
</html>
<?php mysql_close($con);