<?php
	session_start();
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>$co[company_name] ERP System Ver. 1.0b</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script>
		$(document).ready(function () {
			$.post("src/sjerp.php", {
				mod: "getBranches",
				sid: Math.random()
			}, function (data) {
				$("#branches").html(data);
			}, "html");
		});

		function add() {
			var msg = "";
			
			if($("#fname").val() == "") { msg = msg + "You did not specify user's full name.<br/>"; }
			if($("#uname").val() == "") { msg = msg + "You did not specify username for this user.<br/>"; }
			if($("#pass1").val() == "" || $("#pass2").val() == "") { msg = msg + "The system cannot accept empty password.<br/>"; }
			if($("#pass1").val() != $("#pass2").val()) { msg = msg + "Passwords do not match.<br/>"; }
			if($("#changePass").attr("checked") == 'checked') { var cPass = "Y"; } else { var cPass = "N"; }
			
			
			if(msg!="") {
				parent.sendErrorMessage(msg);
			} else {
				$.post("src/sjerp.php", {mod: "checkUname", uname: $("#uname").val(), sid: Math.random() }, function(data) {
					if(data > 0) {
						parent.sendErrorMessage("Username has already been used by another user");
					} else {
						var selectedBranches = $("#branches").val() || [];

						if (selectedBranches.length === 0) {
							parent.sendErrorMessage(
								"Please select at least one authorized branch."
							);
							return;
						}

						$.post("src/sjerp.php", {
							mod: "addUser",
							fname: $("#fname").val(),
							uname: $("#uname").val(),
							pass: $("#pass1").val(),
							utype: $("#utype").val(),
							rtype: $("#rtype").val(),
							email: $("#uemail").val(),
							changePass: cPass,
							branches: selectedBranches,
							sid: Math.random()
						}, function (response) {
							if (response !== "OK") {
								parent.sendErrorMessage(response);
								return;
							}

							alert("User successfully added to the system");
							parent.closeDialog("#userdetails");
							parent.showUsers();
						}, "text");
					}
				},"html");
			}
			
		}


	</script>
</head>
<body bgcolor="#7f7f7f" leftmargin="0" bottommargin="0" rightmargin="0" topmargin="0">
 <table height="100%" width="100%" border="0" cellspacing="0" cellpadding="0" >
	<tr>
		<td style="padding:0px;" valign=top>
			<table width=100% border=0 cellspacing=2 cellpadding=0>
				<tr>
					<td valign=top width="90%" class="td_content" style="padding: 10px;">		
						<table border="0" cellpadding="0" cellspacing="0" width=100%>
							<tr>
								<td width=35%><span class="spandix-l">Full Name :</span></td>
								<td>
									<input type="text" id="fname" class="nInput" style="width: 80%;" />
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%><span class="spandix-l">Username :</span></td>
								<td>
									<input type="text" id="uname" class="nInput" style="width: 80%;" />
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%><span class="spandix-l">Password :</span></td>
								<td>
									<input type="password" id="pass1" class="nInput" style="width: 80%;" />
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%><span class="spandix-l">Confirm Password :</span></td>
								<td>
									<input type="password" id="pass2" class="nInput" style="width: 80%;" />
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%><span class="spandix-l">User Type :</span></td>
								<td>
									<select id="utype" style="width: 80%;" class="nInput" />
										<option value="user">Limited User</option>
										<option value="admin">Super User</option>
									</select>
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%><span class="spandix-l">Reports Privileges :</span></td>
								<td>
									<select id="rtype" style="width: 80%;" class="nInput" />
										<option value="user">Limited User</option>
										<option value="admin">Super User</option>
									</select>
								</td>
							</tr>
							<tr>
								<td height="4"></td>
							</tr>
							<tr>
								<td width="35%">
									<span class="spandix-l">Authorized Branches:</span>
								</td>
								<td>
									<select id="branches"
											multiple
											class="nInput"
											style="width: 80%; height: 100px;">
									</select>
									<div style="font-size: 11px; color: #555;">
										Hold Ctrl to select multiple branches.
									</div>
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%><span class="spandix-l">Email Address :</span></td>
								<td colspan=3>
									<input type="text" id="uemail" class="nInput" style="width: 80%;" />
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%></td>
								<td>
									<input type="checkbox" id="changePass">&nbsp;<span class="spandix"><i>Let user change his/her password on first login</i></span>
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr><td colspan=2><hr></hr></td></tr>
							<tr>
								<td align=center colspan=2>
									<button onClick="add();" class="buttonding" id="btn_rsv" style="width: 180px;"><img src="images/icons/adduser.png" width=24 height=24 align=absmiddle />&nbsp;&nbsp;Add System User</b></button>
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