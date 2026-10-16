<?php
	include("includes/dbUSE.php");
	session_start();
	$res = getArray("select * from user_info where emp_id = '$_GET[eid]';");
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	<title>$co[company_name] ERP System Ver. 1.0b</title>
	<link href="style/style.css" rel="stylesheet" type="text/css" />
	<script language="javascript" src="ui-assets/jquery/jquery-1.12.3.js"></script>
	<script>
		
	$(document).ready(function () {
		$.post(
			"src/sjerp.php",
			{
				mod: "getBranches",
				uid: $("#emp_id").val(),
				sid: Math.random()
			},
			function (data) {
				$("#branches").html(data);
			},
			"html"
		).fail(function (xhr) {
			alert("Unable to load branches.");
			console.log(xhr.responseText);
		});
	});

	function updateUser() {
		var branches = $("#branches").val() || [];

		if (branches.length === 0) {
			alert("Please select at least one authorized branch.");
			return;
		}

		if (confirm("Are you sure you want to update this record?")) {
			$.post(
				"src/sjerp.php",
				{
					mod: "updateUInfo",
					uid: $("#emp_id").val(),
					fname: $("#fname").val(),
					uname: $("#uname").val(),
					utype: $("#utype").val(),
					rtype: $("#rtype").val(),
					email: $("#uemail").val(),
					branches: branches,
					sid: Math.random()
				},
				function (response) {
					response = $.trim(response);

					if (response === "OK") {
						alert("Record Successfully Updated");
						parent.closeDialog("#userdetails");
						parent.showUsers();
					} else {
						alert(response);
					}
				}
			).fail(function (xhr) {
				alert("Unable to update user.");
				console.log(xhr.responseText);
			});
		}
	}

		
		function deleteUser() {
			if(confirm("The system requires that this record should remain intact. Do you want to continue deleting this record?") == true) {
				$.post("src/sjerp.php", {mod: "deleteUser", uid: $("#emp_id").val(), sid: Math.random() }, function() {
					parent.closeDialog("#userdetails");
					parent.showUsers();
				})
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
									<input type="text" id="fname" class="nInput" style="width: 80%;" value="<?php echo $res['fullname']; ?>" />
									<input type="hidden" id="emp_id" class="nInput" style="width: 80%;" value="<?php echo $res['emp_id']; ?>" />
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%><span class="spandix-l">Username :</span></td>
								<td>
									<input type="text" id="uname" class="nInput" style="width: 80%;" value="<?php echo $res['username']; ?>" />
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%><span class="spandix-l">Docs. Privileges :</span></td>
								<td>
									<select id="utype" style="width: 80%;" class="nInput">
										<option value="user" <?php if($res['user_type'] == "user") { echo "selected"; } ?>>Limited User</option>
										<option value="admin" <?php if($res['user_type'] == "admin") { echo "selected"; } ?>>Super User</option>
									</select>
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr>
								<td width=35%><span class="spandix-l">Reports Privileges :</span></td>
								<td>
									<select id="rtype" style="width: 80%;" class="nInput">
										<option value="user" <?php if($res['r_type'] == "user") { echo "selected"; } ?>>Limited User</option>
										<option value="admin" <?php if($res['r_type'] == "admin") { echo "selected"; } ?>>Super User</option>
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
									<input type="text" id="uemail" class="nInput" style="width: 80%;" value="<?php echo $res['email']; ?>" />
								</td>
							</tr>
							<tr><td height=4></td></tr>
							<tr><td colspan=2><hr></hr></td></tr>
							<tr>
								<td align=center colspan=2>
									<button onClick="updateUser();" class=buttonding><img src="images/icons/floppy.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Update Info</b></button>
									<button onClick="deleteUser();" class=buttonding><img src="images/icons/delete.png" width=18 height=18 align=absmiddle />&nbsp;&nbsp;Delete User</b></button>
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