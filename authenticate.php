<?php
	
	require_once 'handlers/initDB.php';
	
	class authenticate extends myDB {
		function generateUniqueId($maxLength = null) {
			$entropy = '';
			if (function_exists('openssl_random_pseudo_bytes')) {
				$entropy = openssl_random_pseudo_bytes(64, $strong);
				if($strong !== true) {
					$entropy = '';
				}
			}
				$entropy .= uniqid(mt_rand(), true);
			if (class_exists('COM')) {
				try {
					$com = new COM('CAPICOM.Utilities.1');
					$entropy .= base64_decode($com->GetRandom(64, 0));
					} catch (Exception $ex) {
				}
			}
				
			if (is_readable('/dev/urandom')) {
				$h = fopen('/dev/urandom', 'rb');
				$entropy .= fread($h, 64);
				fclose($h);
			}

			$hash = hash('whirlpool', $entropy);
			if ($maxLength) {
				return substr($hash, 0, $maxLength);
			}
				return $hash;
		}
		
		

		public $loginError = 0;

		function verify($uname, $pass, $branch) {
			if (empty($uname) || empty($pass) || $branch === '') {
				$this->loginError = 1;
				return false;
			}

			$branch = (int)$branch;

			if ($branch <= 0) {
				$this->loginError = 1;
				return false;
			}

			// Use the database connection to escape input.
			$db = parent::getConnection();

			$uname = mysqli_real_escape_string($db, $uname);
			$pass  = mysqli_real_escape_string($db, $pass);

			// Verify username, password, and active status.
			$res = parent::dbquery("
				SELECT emp_id AS user_id, user_type
				FROM user_info
				WHERE username = '$uname'
				AND password = MD5('$pass')
				AND status = 'A'
				LIMIT 1
			");

			if (!$res || mysqli_num_rows($res) == 0) {
				$this->loginError = 1;
				return false;
			}

			$row = mysqli_fetch_assoc($res);
			$uid = (int)$row['user_id'];
			$utype = $row['user_type'];

			// Verify the selected branch is assigned to this user.
			$access = parent::dbquery("
				SELECT uba.branch_code
				FROM user_branch_access uba
				INNER JOIN options_branches b
					ON b.branch_code = uba.branch_code
				WHERE uba.user_id = '$uid'
				AND uba.branch_code = '$branch'
				LIMIT 1
			");

			if (!$access || mysqli_num_rows($access) == 0) {
				$this->loginError = 5;
				return false;
			}

			// Create session only after branch authorization succeeds.
			if ($this->storeSession($uid, $utype, $branch)) {
				return true;
			}

			$this->loginError = 1;
			return false;
		}

		function storeSession($uid, $utype, $branch) {
			if (session_id() == '') {
				session_start();
			}

			$uid = (int)$uid;
			$branch = (int)$branch;
			$skey = $this->generateUniqueId(32);

			$result = parent::dbquery("
				SELECT branch_name, company
				FROM options_branches
				WHERE branch_code = '$branch'
				LIMIT 1
			");

			if (!$result || mysqli_num_rows($result) == 0) {
				return false;
			}

			$row = mysqli_fetch_assoc($result);

			$db = parent::getConnection();
			$ip = isset($_SERVER['REMOTE_ADDR'])
				? mysqli_real_escape_string($db, $_SERVER['REMOTE_ADDR'])
				: '';

			parent::dbquery("
				INSERT IGNORE INTO active_sessions
					(userid, timestamp, sessid)
				VALUES ('$uid', '" . time() . "', '$skey')
			");

			parent::dbquery("
				UPDATE user_info
				SET last_logged_in = NOW(),
					ws_last_logged_in = '$ip'
				WHERE emp_id = '$uid'
			");

			session_regenerate_id(true);

			$_SESSION['userid'] = $uid;
			$_SESSION['authkey'] = $skey;
			$_SESSION['branchid'] = $branch;
			$_SESSION['branchname'] = $row['branch_name'];
			$_SESSION['company'] = $row['company'];
			$_SESSION['LAST_ACTIVITY'] = time();

			return true;
		}


	}
	
	$auth = new authenticate();

	$uname = isset($_POST['txtname']) ? $_POST['txtname'] : '';
	$pass = isset($_POST['txtpass']) ? $_POST['txtpass'] : '';
	$branch = isset($_POST['branch']) ? $_POST['branch'] : '';

	if ($auth->verify($uname, $pass, $branch)) {
		$URL = $HTTP_REFERER . "index.php";
	} else {
		$URL = $HTTP_REFERER . "login?exception=" . $auth->loginError;
	}

	header("Location: $URL");
	exit();
?>
