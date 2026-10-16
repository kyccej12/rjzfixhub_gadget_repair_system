<?php
	include("includes/dbUSE.php");
	session_start();
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>Bata Esensyal</title>
<link href="style/style.css" rel="stylesheet" type="text/css" />
<link href="ui-assets/themes/smoothness/jquery-ui.css" rel="stylesheet" type="text/css" />
<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
<script language="javascript" src="ui-assets/themes/smoothness/jquery-ui.js"></script>
<script>
	function changeMyPass(uid) {
		var msg = "";

		if($("#pass1").val() == "" || $("#pass2").val() == "") { msg = msg + "The system cannot accept empty password.<br/>"; }
		if($("#pass1").val() != $("#pass2").val()) { msg = msg + "New Passwords do not match.<br/>"; }
	
		if(msg!="") {
			parent.sendErrorMessage(msg);
		} else {
			$.post("src/sjerp.php", {mod: "checkOldPass", uid: uid, old_pass: $("#old_pass").val(), sid: Math.random() }, function(data) {
				$.post("src/sjerp.php", { mod: "changePassword", uid: uid, pass: $("#pass1").val(), sid: Math.random() },function() {
					alert("You have successfully updated your password!");
					parent.$("#changepass").dialog("close");
				});
			},"html");
		}
	}


</script>
</head>
<body bgcolor="#7f7f7f" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
 <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
	<input type="hidden" id = "file_id" value="<?php echo $_GET['fileID']; ?>">
	<input type="hidden" id = "rsv_id" value="<?php echo $lot['rsv_id']; ?>">
	<tr>
		<td style="padding:0px;" valign=top>
			<table width=100% border=0 cellspacing=2 cellpadding=0>
				<tr>
					<td valign=top width="90%" class="td_content" style="padding: 10px;">		
						<table border="0" cellpadding="0" cellspacing="0" width=100%>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%><span class="spandix-l">New Password :</span></td>
								<td>
									<input type="password" id="pass1" class="nInput" style="width: 80%;"  />
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%><span class="spandix-l">Confirm New Password :</span></td>
								<td>
									<input type="password" id="pass2" class="nInput" style="width: 80%;" />
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr><td colspan=2><hr></hr></td></tr>
							<tr>
								<td align=center colspan=2>
									<button onClick="changeMyPass(<?php echo $_SESSION['userid']; ?>);" class="buttonding" id="btn_rsv" style="width: 180px;"><img src="images/icons/secrecy-icon.png" width=24 height=24 align=absmiddle />&nbsp;&nbsp;Update Password</b></button>
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
		</td>
	</tr>
 </table>
</body>
</html>
<?php mysql_close($con);