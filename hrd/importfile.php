<?php

	function generateRandomString($length = 10) {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}


	$tmpfilename = generateRandomString();
	$temp = explode(".",$_FILES["uploadedfile"]["name"]);
	$filename =  $tmpfilename . "." . end($temp);

	$path = "temp/$filename";
	move_uploaded_file($_FILES["userfile"]["tmp_name"],$path);
	
	$file = "temp/$filename";
	$handle = fopen($file, "r");
	$read = file_get_contents($file);
	$lines = explode("\n", $read);
	

	$i = 0; $sqlString = "";
	foreach($lines as $key => $value){
		//$cols[$i] = explode(' ', trim($value));
		$cols[$i] = explode("\t", $value);
		
		$empID = trim($cols[$i][0]);
		list($date,$tmpTime) = explode(' ',$cols[$i][1]);
		$time = substr_replace($tmpTime,"00",-2);


		

	
	}
	
?>