<?php

	$con = mysqli_connect("localhost", "root", "", "rjzfixhub");
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	

	function dbquery($query) {
		global $con;
		return $con->query($query);
	}
	
	function getArray($query) {
		global $con;
		$rset = dbquery($query);
		return $rset->fetch_array();
	}
	
	function getUName($uid) {
		list($name) = getArray("select fullname from user_info where emp_id = '$uid';");
		return $name;
	}
	
	function _month($dig) {
		switch($dig) {
			case "01": return "January"; break; case "02": return "February"; break; case "03": return "March"; break; case "04": return "April"; break;
			case "05": return "May"; break; case "06": return "June"; break; case "07": return "July"; break; case "08": return "August"; break;
			case "09": return "September"; break; case "10": return "October"; break; case "11": return "November"; break; case "12": return "December"; break;
		}
	}

	function getCompany($cid,$bid) {
		list($company) = getArray("select short_name from companies where company_id = '$cid';");
		list($bname) = getArray("select branch_name from options_branches where company = '$cid' and branch_code = '$bid';");
		return $company.' ('.$bname.')';
	}

	function getContactName($id) {
		list($cname) = getArray("select tradename from contact_info where file_id = '$id';");
		return $cname;
	}

	function identUnit($abbrv) {
		list($unit) = getArray("select UCASE(description) from options_units where unit = '$abbrv';");
		return $unit;
	}
	
	function formatDate($date) {
		$date = explode("/",$date);
		return $date[2]."-".$date[0]."-".$date[1];
	}
	
	function formatDigit($dig) {
		return preg_replace('/,/','',$dig);
	}

	function formatCY($date) {
		$date = explode("/",$date);
		return $date[2];
	}

	function getAcctDesc($acct,$company) {
		list($desc) = getArray("select description from acctg_accounts where acct_code = '$acct' and company = '$company';");
		return $desc;
	}
	
	function applyBalanceNa($company,$branch,$doc_no,$type,$acct,$amount,$cust) {
		switch($type) {
			case "AP":
				dbquery("update apv_header set balance = balance - $amount, applied_amount = applied_amount + $amount where apv_no = '$doc_no' and company = '$company' and branch = '$branch' and supplier = '$cust';");
			break;
			case "AP-BB":
				dbquery("update apbeg_details set balance = balance - $amount, applied_amount = applied_amount + $amount where invoice_no = '$doc_no' and company='$company' and branch='$branch' and customer = '$cust';");
			break;
		}
	}
	
	function revertBalance($company,$branch,$doc_no,$type,$acct,$amount,$cust) {
		switch($type) {
			case "AP":
				dbquery("update apv_header set balance = balance + $amount, applied_amount = applied_amount - $amount where apv_no = '$doc_no' and company = '$company' and branch = '$branch' and supplier = '$cust';");
			break;
			case "AP-BB":
				dbquery("update apbeg_details set balance = balance + $amount, applied_amount = applied_amount - $amount where invoice_no = '$doc_no' and company='$company' and branch='$branch' and customer = '$cust';");
			break;
		}
	}
	
	function inWords($number) {
	
		$hyphen      = ' ';
		$conjunction = ' ';
		$separator   = ' ';
		$negative    = 'negative ';
		$decimal     = ' point ';
		$dictionary  = array(
			0                   => 'zero',
			1                   => 'one',
			2                   => 'two',
			3                   => 'three',
			4                   => 'four',
			5                   => 'five',
			6                   => 'six',
			7                   => 'seven',
			8                   => 'eight',
			9                   => 'nine',
			10                  => 'ten',
			11                  => 'eleven',
			12                  => 'twelve',
			13                  => 'thirteen',
			14                  => 'fourteen',
			15                  => 'fifteen',
			16                  => 'sixteen',
			17                  => 'seventeen',
			18                  => 'eighteen',
			19                  => 'nineteen',
			20                  => 'twenty',
			30                  => 'thirty',
			40                  => 'forty',
			50                  => 'fifty',
			60                  => 'sixty',
			70                  => 'seventy',
			80                  => 'eighty',
			90                  => 'ninety',
			100                 => 'hundred',
			1000                => 'thousand',
			1000000             => 'million',
			1000000000          => 'billion',
			1000000000000       => 'trillion',
			1000000000000000    => 'quadrillion',
			1000000000000000000 => 'quintillion'
		);
		
		if (!is_numeric($number)) {
			return false;
		}
		
		if (($number >= 0 && (int) $number < 0) || (int) $number < 0 - PHP_INT_MAX) {
			// overflow
			trigger_error(
				'inWords only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
				E_USER_WARNING
			);
			return false;
		}

		if ($number < 0) {
			return $negative . inWords(abs($number));
		}
		
		$string = $fraction = null;
		
		if (strpos($number, '.') !== false) {
			list($number, $fraction) = explode('.', $number);
		}
		
		switch (true) {
			case $number < 21:
				$string = $dictionary[$number];
				break;
			case $number < 100:
				$tens   = ((int) ($number / 10)) * 10;
				$units  = $number % 10;
				$string = $dictionary[$tens];
				if ($units) {
					$string .= $hyphen . $dictionary[$units];
				}
				break;
			case $number < 1000:
				$hundreds  = $number / 100;
				$remainder = $number % 100;
				$string = $dictionary[$hundreds] . ' ' . $dictionary[100];
				if ($remainder) {
					$string .= $conjunction . inWords($remainder);
				}
				break;
			default:
				$baseUnit = pow(1000, floor(log($number, 1000)));
				$numBaseUnits = (int) ($number / $baseUnit);
				$remainder = $number % $baseUnit;
				$string = inWords($numBaseUnits) . ' ' . $dictionary[$baseUnit];
				if ($remainder) {
					$string .= $remainder < 100 ? $conjunction : $separator;
					$string .= inWords($remainder);
				}
				break;
		}
		
		if (null !== $fraction && is_numeric($fraction)) {
			$string .= $decimal;
			$words = array();
			foreach (str_split((string) $fraction) as $number) {
				$words[] = $dictionary[$number];
			}
			$string .= implode(' ', $words);
		}
		
		return strtoupper($string);
	}

?>
