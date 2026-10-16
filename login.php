<?php

	require_once('handlers/_generics.php');
	$con = new _init();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>SUPERIOR GAS & EQUIPMENT CO. OF CEBU INC. PAYROLL SYSTEM VER. 1.0bs</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script language="javascript">	
		
		function checkFields() {
			var uname = document.loginForm.txtname.value;		
			if(uname=='') {
				alert("ERROR: Please key in your username...");
				return false;
			} else {
				document.loginForm.submit();
			}
		}


		function getBranchList(company) {
			$.post("src/sjerp.php", { mod: "getBranchList", company: company, sid: Math.random() },function(data) {
				$("#branch").html(data);
			},"html");
		}
		
	</script>
</head>
<body bgcolor="#000000" leftmargin=0 rightmargin=0 bottommargin=0 topmargin=0 onLoad="document.loginForm.txtname.focus();">
<table height="25%"><tr><td></td></tr></table>
<table width=450 border="0" cellpadding="0" cellspacing="0" valign="middle" align=center style="background-color: #FFFFFF;">
<form name="loginForm" action="authenticate.php" method="post" onsubmit="javascript: checkFields();">
	<tr>
		<td width="100%" height="100%" align=center valign="middle" style="border: 2px #dfc341 solid;">
			<table align=center width=100% border=0 cellspacing=0 cellpadding=0 style="border-bottom:1px solid #dfc341; background-color:#4c6ea4; font-weight:bold; color:#ffffff;">
				<tr><td height=6></td></tr>
				<tr>
					<td align="left" style="font-weight:bold; font-size: 10px;">&nbsp;&nbsp;USER LOGIN - PRIME CARE CEBU PAYROLL SYSTEM VER. 1.0b</td>
				</tr>
				<tr><td height=6></td></tr>
			</table>
			<table width=100% align=center border=0 cellspacing=0 cellpadding=0>
				<tr>
					<td width="30%" align=center style="padding-left: 10px;"><img src="images/login2.png"></img></td>
					<td width="70%">
						<table width="100%" border=0 cellspacing=0 cellpadding=0>
							<tr><td height=16></td></tr>
							<tr><td colspan=2 align=left class="loginTextBold"></td></tr>
							<tr><td height=16></td></tr>
							<?php
								if(isset($_REQUEST['exception'])) { ?>
								<tr>
									<td>
										<table width=100% cellpadding=0 cellspacing=0 border=0>
											<tr>
												<td width="18px;" valign=top style="padding-left: 20px;"><img src="images/icons/warning.png" width=20 height=20 /></td>
												<td valign=middle style="font-size: 11px; font-weight: bold; color: red; padding-right: 10px; padding-left: 5px;"><?php $con->loginError($_REQUEST['exception']); ?></td>
											</tr>
										</table>
									</td>
								</tr>
								<tr><td height=8></td></tr>
							<?php } ?>
							<tr>
								<td class=loginText>Username<br>
									<input type="text" name="txtname" id="txtname" style="width:90%" class="gridInput" autocomplete="off">	
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td class=loginText>Password<br>
									<input type="password" name="txtpass" id="txtpass" class="gridInput" style="width:90%">
									<input type="hidden" name="company" id="company" value="1">
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td class=loginText>Payroll Class<br>
									<select name="payclass" id="payclass" class="gridInput" style="width:90%">
										<?php
											$etypeQuery = $con->dbquery("select eTypeID,eType from option_emptype;");
											while(list($eTypeID,$eType) = $etypeQuery->fetch_array(MYSQLI_BOTH)) {
												echo "<option value='$eTypeID' $selected>$eType</option>";
											}
										?>
									</select>
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td style="padding: 5px 20px 10px 20px; color: #4a4a4a;">
									<p style='text-align: justify; font-size: 11px;'><font style='font-weight: bold; font-size: 11px;'>NOTICE:</font> Use of this network, its equipment, and resources is monitored at all times and requires explicit permission from the network administrator.</p>
								</td>
							</tr>
						</table>
						<table width="100%" align=center>
							<tr><td height=4></td></tr>
							<tr>
								<td align=left style="padding-left: 20px;"><button type=button onclick="checkFields();"><image src="images/icons/key.png" height=18 width=18 align=absmiddle border=0 />&nbsp;Login</button></td>
							</tr>
							<tr><td height=16></td></tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</form>
</table>
<table width=450 align=center cellspacing=0 cellpadding=0 bgcolor="#000000" border=0 style="padding-top:5px;">
	<tr><td valign="top" align=center height=25 style="font-size: 9px; color: #ffffff">&copy; Prime Care Cebu Incorporated</td></tr>
</table>
<table height="25%"><tr><td></td></tr></table>
</body>
</html>
<?php unset($_SESSION['logerror']); ?>