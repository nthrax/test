<?php
error_reporting(E_ALL);
include "tim.html"; // Include Download Form
$url=($_POST["postLink"]);
$cookiedir = '..\htdocs\loader\kekse\\';
$cookiefile = $cookiedir."keks";
//get cookie

function so_fetchCookieFile(){
	$user = "04851494694";
	$pass = "wieselwiesel";

	$args = ("user=".$user."&pass=".$pass);
	$url = "https://www.share-online.biz/user/login";
		$cookiedir = '..\htdocs\loader\kekse\\';
		$cookiefile = $cookiedir."keks";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL,$url);
		curl_setopt ($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $args);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $cookiefile);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiefile);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_UNRESTRICTED_AUTH, true);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		//curl_setopt($curl, CURLOPT_VERBOSE, true);
		$msg = curl_exec($curl);
		curl_close ($curl);
		return $cookiefile;
	}

so_fetchCookieFile();

// get filename
$api_url="http://api.share-online.biz/linkcheck.php?links=";
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiefile);
		curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($curl, CURLOPT_URL,$api_url.$url);
		$data=curl_exec ($curl);
		curl_close ($curl);

			$tmp=explode (';',$data);
			$filename=$tmp[2];	
	

			
//get_page
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiefile);
		curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($curl, CURLOPT_URL,$url);
		
		$data=curl_exec ($curl);
		curl_close ($curl);
		
			$tmplink=explode('var dl="',$data);
			$baselink =explode('"',$tmplink[1]);
			$baselink1= $baselink[0];
			$dllink=base64_decode($baselink1);
			var_dump($baselink1);
			var_dump($dllink);
			
$file = fopen($filename, "w+");
//get_file
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_COOKIEFILE, $cookiefile);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_FILE,$file);
		curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_USERAGENT, "MozillaXYZ/1.0");
		curl_setopt ($curl, CURLOPT_URL,$dllink);
		curl_setopt($curl, CURLOPT_VERBOSE, true);
		$data=curl_exec ($curl);
		curl_close ($curl);
//passthru("wget -c -t 1 -O \"$filename\" -U \"$ua\" --load-cookies \"$cookiefile\" '$dllink'");		
fclose($file);



	
?>