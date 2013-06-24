<?php
$downloaded = 0;
$newfile;
$newlink;

	function start_download()
	{
		global $newfile;
		
		$baselink= "http://www.landofbitcoin.com/";


		// Get Filename
		if (strpos($filename,"html") !== false) $newfile= preg_replace("%.html%","",$filename);
		elseif (strpos($filename,"htm") !== false) $newfile= preg_replace("%.htm%","",$filename);
		else  $newfile= $filename;
		$newlink;

		$file = fopen($newfile, "w"); // Curl Output (the actual download)


		// Curl Stuff
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		//curl_setopt($curl,CURLOPT_NOPROGRESS,false);
		//curl_setopt($curl,CURLOPT_PROGRESSFUNCTION,'callback');
		curl_setopt($curl, CURLOPT_BUFFERSIZE, 512);
		curl_setopt($curl, CURLOPT_FILE,$file);
		curl_setopt ($curl, CURLOPT_URL,$newlink);
		curl_exec ($curl);


		fclose($file); // Finish Download

	}



start_download();
?>