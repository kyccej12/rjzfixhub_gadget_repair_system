<?php
	session_start();
	//ini_set("display_errors","On");
	require_once "../handlers/_generics.php";
	
	$con = new _init;
	$date = date('Y-m-d');
	
	switch($_POST['mod']) {
		
		/* USERS DATA */
		case "getUinfo":
			list($uname) = $con->getArray("select fullname from user_info where emp_id = '$_POST[uid]';");
			echo $uname;
		break;
		case "checkUname":
			list($count) = $con->getArray("select count(*) from user_info where username = '$_POST[uname]';"); echo $count;
		break;
		case "checkUnameUID":
			list($count) = $con->getArray("select count(*) from user_info where username = '$_POST[uname]' and emp_id!='$_POST[uid]';"); echo $count;
		break;
		
		case "addUser":

			$fname = $con->escapeString(
				htmlentities(isset($_POST['fname']) ? $_POST['fname'] : '')
			);

			$uname = $con->escapeString(
				isset($_POST['uname']) ? $_POST['uname'] : ''
			);

			$pass = $con->escapeString(
				isset($_POST['pass']) ? $_POST['pass'] : ''
			);

			$utype = (isset($_POST['utype']) && $_POST['utype'] == 'admin')
				? 'admin' : 'user';

			$rtype = (isset($_POST['rtype']) && $_POST['rtype'] == 'admin')
				? 'admin' : 'user';

			$email = $con->escapeString(
				isset($_POST['email']) ? $_POST['email'] : ''
			);

			$changePass = (isset($_POST['changePass']) && $_POST['changePass'] == 'Y')
				? 'Y' : 'N';

			$branches = isset($_POST['branches']) ? $_POST['branches'] : array();

			// Require at least one branch.
			if (!is_array($branches) || count($branches) == 0) {
				echo "Please select at least one branch.";
				break;
			}

			// Create the user account.
			$con->dbquery("
				INSERT INTO user_info
					(username, password, fullname, user_type,
					r_type, require_change_pass, email)
				VALUES
					('$uname', MD5('$pass'), '$fname', '$utype',
					'$rtype', '$changePass', '$email')
			");

			// Retrieve the new user ID.
			$uid = $con->getConnection()->insert_id;

			if (!$uid) {
				echo "Unable to create user. Check whether the username already exists.";
				break;
			}

			// Assign the selected branches.
			$assigned = 0;

			foreach ($branches as $branch) {
				$branch = (int)$branch;

				if ($branch <= 0) {
					continue;
				}

				$check = $con->dbquery("
					SELECT branch_code
					FROM options_branches
					WHERE branch_code = '$branch'
					LIMIT 1
				");

				if ($check && mysqli_num_rows($check) > 0) {
					$insert = $con->dbquery("
						INSERT IGNORE INTO user_branch_access
							(user_id, branch_code)
						VALUES ('$uid', '$branch')
					");

					if ($insert) {
						$assigned++;
					}
				}
			}

			if ($assigned == 0) {
				echo "User created, but no valid branches were assigned. Please check the user branch access.";
				break;
			}

			echo "OK";

		break;

		
		case "updateUInfo":

			$uid = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;

			$fname = $con->escapeString(
				htmlentities(isset($_POST['fname']) ? $_POST['fname'] : '')
			);

			$uname = $con->escapeString(
				isset($_POST['uname']) ? $_POST['uname'] : ''
			);

			$utype = (isset($_POST['utype']) && $_POST['utype'] == 'admin')
				? 'admin' : 'user';

			$rtype = (isset($_POST['rtype']) && $_POST['rtype'] == 'admin')
				? 'admin' : 'user';

			$email = $con->escapeString(
				isset($_POST['email']) ? $_POST['email'] : ''
			);

			$branches = isset($_POST['branches'])
				? $_POST['branches']
				: array();

			if ($uid <= 0) {
				echo "Invalid user ID.";
				break;
			}

			if (!is_array($branches) || count($branches) == 0) {
				echo "Please select at least one authorized branch.";
				break;
			}

			// Check whether another user already uses this username.
			$checkUser = $con->dbquery("
				SELECT emp_id
				FROM user_info
				WHERE username = '$uname'
				AND emp_id <> '$uid'
				LIMIT 1
			");

			if (!$checkUser) {
				echo "Unable to validate username.";
				break;
			}

			if (mysqli_num_rows($checkUser) > 0) {
				echo "Username already exists.";
				break;
			}

			// Validate the selected branches before updating the user.
			$validBranches = array();

			foreach ($branches as $branch) {
				$branch = (int)$branch;

				if ($branch <= 0) {
					continue;
				}

				$checkBranch = $con->dbquery("
					SELECT branch_code
					FROM options_branches
					WHERE branch_code = '$branch'
					LIMIT 1
				");

				if ($checkBranch && mysqli_num_rows($checkBranch) > 0) {
					$validBranches[] = $branch;
				}
			}

			$validBranches = array_unique($validBranches);

			if (count($validBranches) == 0) {
				echo "No valid branches were selected.";
				break;
			}

			// Update the user's information.
			$update = $con->dbquery("
				UPDATE user_info
				SET fullname = '$fname',
					username = '$uname',
					user_type = '$utype',
					r_type = '$rtype',
					email = '$email'
				WHERE emp_id = '$uid'
			");

			if (!$update) {
				echo "Unable to update user information.";
				break;
			}

			// Remove old branch assignments.
			$delete = $con->dbquery("
				DELETE FROM user_branch_access
				WHERE user_id = '$uid'
			");

			if (!$delete) {
				echo "User information updated, but branch assignments could not be updated.";
				break;
			}

			// Save the newly selected branches.
			foreach ($validBranches as $branch) {
				$branch = (int)$branch;

				$insert = $con->dbquery("
					INSERT IGNORE INTO user_branch_access
						(user_id, branch_code)
					VALUES ('$uid', '$branch')
				");

				if (!$insert) {
					echo "User information updated, but a branch assignment failed.";
					break 2;
				}
			}

			echo "OK";

		break;

		case "deleteUser":
			$con->dbquery("delete from user_info where emp_id = '$_POST[uid]';");
		break;
		case "checkOldPass":
			list($count) = $con->getArray("select count(*) from user_info where emp_id='$_POST[uid]' and password=md5('$_POST[old_pass]');");	
			if($count>0) { echo "Ok"; } else { echo "noOk"; }
		break;
		case "changePassword":
			$con->dbquery("update ignore user_info set password=md5('$_POST[pass]'), require_change_pass='N' where emp_id='$_POST[uid]';");
		break;
		case "resetPassword":
			$con->dbquery("update ignore user_info set password=md5('123456'), require_change_pass='Y' where emp_id='$_POST[uid]';");
		break;
		case "insertRights":
			list($module,$id) = explode("|",$_REQUEST['val']);
			if($_REQUEST['push'] == "N") { 
				$xfind = $con->getArray("select count(*) from user_rights where UID='$_POST[uid]' and MENU_MODULE='$module' and MENU_ID='$id';");
				if($xfind[0] > 0) { 
					$con->dbquery("delete from user_rights where UID='$_POST[uid]' and MENU_MODULE='$module' and MENU_ID='$id';"); 
					//$con->trailer("USER MANAGEMENT","RIGHTS REMOVED FOR UID $_POST[uid] -> SUBMENU ID # $id");
				}
			} else {
				$xfind = $con->getArray("select count(*) from user_rights where UID='$_POST[uid]' and MENU_MODULE='$module' and MENU_ID='$id';");
				if($xfind[0] == 0) { 
					$con->dbquery("insert ignore into user_rights (UID,MENU_MODULE,MENU_ID) values ('$_REQUEST[uid]','$module','$id');"); 
					//$con->trailer("USER MANAGEMENT","RIGHTS ADDED TO UID $_POST[uid] -> SUBMENU ID # $id");
				}
			}
		break;

		
		case "getBranches":

			$uid = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;

			$branches = $con->dbquery("
				SELECT
					b.branch_code,
					b.branch_name,
					CASE
						WHEN uba.user_id IS NULL THEN 0
						ELSE 1
					END AS is_selected
				FROM options_branches b
				LEFT JOIN user_branch_access uba
					ON uba.branch_code = b.branch_code
					AND uba.user_id = '$uid'
				ORDER BY b.branch_name
			");

			if (!$branches) {
				echo "<option value=''>Unable to load branches</option>";
				break;
			}

			if (mysqli_num_rows($branches) == 0) {
				echo "<option value=''>No branches found</option>";
				break;
			}

			while ($row = mysqli_fetch_assoc($branches)) {
				$code = (int)$row['branch_code'];
				$name = htmlspecialchars(
					$row['branch_name'],
					ENT_QUOTES,
					'UTF-8'
				);

				$selected = ($uid > 0 && $row['is_selected'] == 1)
					? " selected='selected'"
					: "";

				echo "<option value='$code'$selected>$name</option>";
			}

		break;


		/* Products */
		case "getIcode":
			switch($_POST['mid']) { case "1": $main="CP"; break; case "2": $main="LP"; break; case "3": $main="ACC"; break; case "4": $main="RC"; break; case "5": $main="RT"; break; case "6": $main="PKG"; break; case "7": $main="OP"; break;}
			
			if($_POST['sgroup'] > 0) {
				list($scode) = $con->getArray("select `code` from options_sgroup where sid = '$_POST[sgroup]';");
			} else { $scode = '000'; }
			
			list($series) = $con->getArray("SELECT LPAD(IFNULL(MAX(series+1),1),4,0) FROM (SELECT TRIM(LEADING '0' FROM(SUBSTRING_INDEX(SUBSTRING_INDEX(item_code, '-', 3), '-', -1))) AS series FROM products_master WHERE SUBSTRING_INDEX(SUBSTRING_INDEX(item_code, '-', 1), '-', -1) = '$main' AND subgroup='$_POST[sgroup]') a;");		
			echo $main.'-'.$scode.'-'.$series;
		break;
		case "getSgroup":
			$a = $con->dbquery("select sid, sgroup from options_sgroup where mid = '$_POST[mgroup]';");
			echo "<option value='0'>- Not Applicable -</option>";
			while(list($y,$z) = $a->fetch_array()) {
				echo "<option value = '$y'>$z</option>";
			}
		break;
		
		case "getCgroup":
			$a = $con->dbquery("select id, cgrp_name from options_cgroup where sgrp_id = '$_POST[sgrp_id]';");
			echo "<option value='0'>- Not Applicable -</option>";
			while(list($y,$z) = $a->fetch_array()) {
				echo "<option value = '$y'>$z</option>";
			}
		break;

		case "copyInfo":
			$a = $con->dbquery("select * from products_master where indcode = '$_POST[stockCode]' limit 1;");
			if($a) {
				echo json_encode($a->fetch_array());
			} else {
				echo json_encode(array("noerror"=>true));
			}
		break;
		case "checkDupCode":
			if($_POST['rid'] != "") {
				list($isExist) = $con->getArray("select count(*) from products_master where item_code = '$_POST[item_code]' and company = '1' and record_id != '$_POST[rid]';");
			} else {
				list($isExist) = $con->getArray("select count(*) from products_master where item_code = '$_POST[item_code]' and company = '1';");
			}
			
			if($isExist == 0) { echo "NODUPLICATE"; }
		break;
		case "savePInfo":
			if(!$_POST['status']) { $stat = "Y"; } else { $stat = $_POST['status']; }
			if(isset($_POST['rid']) && $_POST['rid'] != "") {
				$con->dbquery("update ignore products_master set category = '$_POST[item_category]', subgroup='$_POST[item_sgroup]', cgroup = '$_POST[item_cgroup]', item_code='".$con->escapeString($_POST['item_code'])."', indcode = '$_POST[item_indcode]', brand = '".strtoupper($_POST['item_brand'])."', description='".$con->escapeString(htmlentities($_POST['item_description']))."',full_description='".$con->escapeString(htmlentities($_POST['item_fdescription']))."',beg_qty='".$con->formatDigit($_POST['item_beginning'])."',minimum_level='".$con->formatDigit($_POST['item_mininv'])."',reorder_pt='$_POST[item_reorder]',unit = '$_POST[item_unit]',unit_cost='".$con->formatDigit($_POST['item_unitcost'])."',srp='".$con->formatDigit($_POST['srp'])."',vat_exempt='$_POST[vat_exempt]',rev_acct='$_POST[rev_acct]',cogs_acct='$_POST[cogs_acct]',exp_acct='$_POST[exp_acct]',asset_acct='$_POST[asset_acct]',supplier='$_POST[supplier]',`active`='$stat',updated_by='$uid', updated_on=now(), with_expiration = '$_POST[with_expiration]' where record_id = '$_POST[rid]';");
				echo "update ignore products_master set category = '$_POST[item_category]', subgroup='$_POST[item_sgroup]', item_code='".$con->escapeString($_POST['item_code'])."', brand = '".strtoupper($_POST['item_brand'])."', description='".$con->escapeString(htmlentities($_POST['item_description']))."',full_description='".$con->escapeString(htmlentities($_POST['item_fdescription']))."',beg_qty='".$con->formatDigit($_POST['item_beginning'])."',minimum_level='".$con->formatDigit($_POST['item_mininv'])."',reorder_pt='$_POST[item_reorder]',unit = '$_POST[item_unit]',unit_cost='".$con->formatDigit($_POST['item_unitcost'])."',srp='".$con->formatDigit($_POST['srp'])."',vat_exempt='$_POST[vat_exempt]',rev_acct='$_POST[rev_acct]',cogs_acct='$_POST[cogs_acct]',exp_acct='$_POST[exp_acct]',asset_acct='$_POST[asset_acct]',supplier='$_POST[supplier]',`active`='$stat',updated_by='$uid', updated_on=now() where record_id = '$_POST[rid]';";
			} else {
				$con->dbquery("insert ignore into products_master (company,category,subgroup,cgroup,item_code,indcode,brand,description,full_description,unit,unit_cost,srp,beg_qty,minimum_level,reorder_pt,vat_exempt,supplier,rev_acct,cogs_acct,exp_acct,asset_acct,`active`,with_expiration,encoded_by,encoded_on) values ('$_SESSION[company]','$_POST[item_category]','$_POST[item_sgroup]','$_POST[item_cgroup]','".$con->escapeString(htmlentities($_POST['item_code']))."','$_POST[item_indcode]','".$con->escapeString(htmlentities($_POST['item_brand']))."','".$con->escapeString(htmlentities($_POST['item_description']))."','".$con->escapeString(htmlentities($_POST['item_fdescription']))."','$_POST[item_unit]','".$con->formatDigit($_POST['item_unitcost'])."','".$con->formatDigit($_POST['srp'])."','".$con->formatDigit($_POST['item_beginning'])."','".$con->formatDigit($_POST['item_mininv'])."','".$con->formatDigit($_POST['item_reorder'])."','$_POST[vat_exempt]','$_POST[supplier]','$_POST[rev_acct]','$_POST[cogs_acct]','$_POST[exp_acct]','$_POST[asset_acct]','$stat','$_POST[with_expiration]','$uid',now());");
			}
		break;
		case "deletePro":
			$con->dbquery("update products_master set `active` = 'N', file_status = 'Deleted', deleted_by = '$_SESSION[userid]', deleted_on = now() where record_id = '$_POST[rid]';");
		break;
		
		case "restorePro":
			$con->dbquery("update products_master set `active` = 'Y', file_status = 'Active' where record_id = '$_POST[rid]';");
		break;
		
		case "getItemTitle":
			$_h = $con->getArray("select `group`,concat(item_code, ' :: ',description) from products_master where record_id = '$_POST[fid]';");
			echo json_encode($_h);
		break;
		
		case "getGroups":
			$o = $con->dbquery("select `group`,`group_description` from options_igroup where mid = '$_POST[type]' order by `group_description` asc;");
			echo "<option value=''>- Not Applicable -</option>\n";
			while($oo = $o->fetch_array()) {
				echo "<option value='$oo[0]'>$oo[1]</option>\n";
			}
		break;
		
		case "newSGroup":
			list($isE) = $con->getArray("select count(*) from options_sgroup where code = '$_POST[code]';");
			if($isE > 0) {
				echo "DUPLICATE";
			} else {
				$con->dbquery("INSERT INTO options_sgroup (`mid`,sgroup,`code`) VALUES ('$_POST[maingrp]','".$con->escapeString(htmlentities($_POST['description']))."','$_POST[code]');");
			}
		break;
		
		case "retrieveSGroup":
			echo json_encode($con->getArray("select * from options_sgroup where sid = '$_POST[id]';"));
		break;
		
		case "updateSGroup":
			$con->dbquery("update options_sgroup set `mid` = '$_POST[maingrp]', sgroup = '" . $con->escapeString(htmlentities($_POST['description'])) . "' where sid = '$_POST[id]';");
		break;
		
		case "deleteSGroup":
			$con->dbquery("update options_sgroup set file_status = 'Deleted', deleted_by = '$uid', deleted_on = now() where sid = '$_POST[id]';");
		break;

		/* Address Book */
		case "getCities":
			$cq = $con->dbquery("select citymunCode, citymunDesc from options_cities where provCode = '$_POST[pid]';");
			while(list($cid,$ctname) = $cq->fetch_array()) {
				echo "<option value='$cid'>$ctname</option>\n";
			}
		break;
		case "getBrgy":
			$cq = $con->dbquery("select brgyCode, brgyDesc from options_brgy where citymunCode = '$_POST[city]';");
			echo "<option value='0'>- Not Applicable -</option>\n";
			while(list($cid,$ctname) = $cq->fetch_array()) {
				echo "<option value='$cid'>$ctname</option>\n";
			}
		break;
		case "saveCInfo":
			if(isset($_POST['fid']) && $_POST['fid'] != "") {
				$con->dbquery("update ignore contact_info set lname='".$con->escapeString(htmlentities($_POST['tradename']))."',address='".$con->escapeString(htmlentities($_POST['address']))."',billing_address='".$con->escapeString(htmlentities($_POST['billing_address']))."',shipping_address='".$con->escapeString(htmlentities($_POST['shipping_address']))."',bizstyle='".$con->escapeString($_POST['bizstyle'])."',brgy='$_POST[brgy]', city='$_POST[city]',province='$_POST[province]',country='$_POST[country]',tel_no='".$con->escapeString($_POST['telno'])."',cperson='".$con->escapeString($_POST['cperson'])."',terms='$_POST[terms]',credit_limit='".$con->formatDigit($_POST['climit'])."',email='".$con->escapeString($_POST['email'])."',srep='$_POST[srep]',tin_no='$_POST[tin_no]',vatable='$_POST[vatable]', updated_by='$_SESSION[userid]', updated_on=now() where file_id = '$_POST[fid]';");
			} else {
				$con->dbquery("insert ignore into contact_info (`type`,tradename,address,brgy,city,province,country,bizstyle,billing_address,shipping_address,tel_no,email,cperson,srep,terms,credit_limit,vatable,tin_no,created_by,created_on) values ('$_POST[type]','".$con->escapeString(htmlentities($_POST['tradename']))."','".$con->escapeString(htmlentities($_POST['address']))."','$_POST[brgy]','$_POST[city]','$_POST[province]','$_POST[country]','".$con->escapeString($_POST['bizstyle'])."','".$con->escapeString(htmlentities($_POST['billing_address']))."','".$con->escapeString(htmlentities($_POST['shipping_address']))."','".$con->escapeString($_POST['telno'])."','".$con->escapeString($_POST['email'])."','$_POST[cperson]','$_POST[srep]','$_POST[terms]','".$con->formatDigit($_POST['climit'])."','$_POST[vatable]','$_POST[tin_no]','$_SESSION[userid]',now());");
			}
		break;
		case "deleteCust":
			$con->dbquery("update contact_info set record_status = 'Deleted', deleted_by='$_SESSION[userid]', deleted_on=now() where file_id='$_POST[fid]';");
		break;
		case "verifyCID":
			list($iCount) = $con->getArray("select count(*) from contact_info where file_id = '$_POST[cid]';");
			if($iCount > 0) { echo "Ok"; } else { echo "notOk"; }
		break;

		/* Customer Details */

		case "saveCustomerInfo":
			if(isset($_POST['fid']) && $_POST['fid'] != "") {
				$con->dbquery("update ignore customer_info set lname='".$con->escapeString(htmlentities($_POST['lname']))."', fname='".$con->escapeString(htmlentities($_POST['fname']))."', mname='".$con->escapeString(htmlentities($_POST['mname']))."',bizstyle='".$con->escapeString(htmlentities($_POST['bizstyle']))."', gender = '$_POST[gender]', birthdate = '".$con->formatDate($_POST['bday'])."', address='".$con->escapeString(htmlentities($_POST['address']))."', province='$_POST[province]', contact_no='".$con->escapeString($_POST['contact_no'])."', city='$_POST[city]', email='".$con->escapeString($_POST['email'])."', brgy='$_POST[brgy]', updated_by='$_SESSION[userid]', updated_on=now() where file_id = '$_POST[fid]';");
			} else {
				$con->dbquery("insert ignore into customer_info (lname,fname,mname,gender,birthdate,address,brgy,city,province,bizstyle,shipping_address,contact_no,email,created_by,created_on) values ('".$con->escapeString(htmlentities($_POST['lname']))."','".$con->escapeString(htmlentities($_POST['fname']))."','".$con->escapeString(htmlentities($_POST['mname']))."','$_POST[gender]','".$con->formatDate($_POST['bday'])."','".$con->escapeString(htmlentities($_POST['address']))."','$_POST[brgy]','$_POST[city]','$_POST[province]','".$con->escapeString($_POST['bizstyle'])."','".$con->escapeString(htmlentities($_POST['shipping_address']))."','".$con->escapeString($_POST['contact_no'])."','".$con->escapeString($_POST['email'])."','$_SESSION[userid]',now());");
				echo "insert ignore into customer_info (lname,fname,mname,gender,birthdate,address,brgy,city,province,bizstyle,shipping_address,contact_no,email,created_by,created_on) values ('".$con->escapeString(htmlentities($_POST['lname']))."','".$con->escapeString(htmlentities($_POST['fname']))."','".$con->escapeString(htmlentities($_POST['mname']))."','$_POST[gender]','".$con->formatDate($_POST['bday'])."','".$con->escapeString(htmlentities($_POST['address']))."','$_POST[brgy]','$_POST[city]','$_POST[province]','".$con->escapeString($_POST['bizstyle'])."','".$con->escapeString(htmlentities($_POST['shipping_address']))."','".$con->escapeString($_POST['contact_no'])."','".$con->escapeString($_POST['email'])."','$_SESSION[userid]',now());";
			}
		break;
		case "deleteCustomer":
			$con->dbquery("update customer_info set record_status = 'Deleted', deleted_by='$_SESSION[userid]', deleted_on=now() where file_id='$_POST[fid]';");
		break;
	}
?>