<?php

	include "tim.html"; // Include Download Form

	$progressEnd = 20; // set to number of progress <span>'s.
	$progressColor = 'black'; // set to progress bar color
	$progressAt = 0;
	$progressTimer;
	$progressIntervall = 500;
	$callbackCount = 0;
	$downloadStatus = 0;
	$progressFinished = 0;
	$newfile;

	function callback($download_size, $downloaded, $upload_size, $uploaded)
	{		
		global $downloadStatus,$progressIntervall,$callbackCount,$progressFinished;
		
		if ($download_size == 0) return 0; 
		
		
		if($callbackCount == 0)
		{
			progress_update();
		}

		$callbackCount = ($callbackCount + 1) % $progressIntervall;
		$downloadStatus = round(($downloaded*100)/($download_size),2);

		flush_buffers();		

		if(($downloaded == $download_size) && !$progressFinished) download_finished();
	}
	
	function progress_init()
	{
		echo " <script type=\"text/javascript\"> document.getElementById('showbar').style.visibility = 'visible'; </script> ";
		progress_update();
		flush_buffers();
	}	

	function progress_update() 
	{
		global $downloadStatus;

		$progressAt = round($downloadStatus/5,0);
		echo " <script type=\"text/javascript\"> document.getElementById('statusNumber').innerHTML = '$downloadStatus%' </script>";

		for($i = 1; $i <= $progressAt; $i++)
		{
			echo " <script type=\"text/javascript\"> document.getElementById('progress$i').style.backgroundColor = 'black' </script>";
		}
		
		flush_buffers();
		
	 }

	function flush_buffers()
	{
		ob_end_flush();
		ob_flush();
		flush();
		ob_start();
	}
 

	function start_download()
	{
		global $newfile;
		
		progress_init();

		$link= $_POST["postLink"];
		$user=("540489");
		$pwd=("sw8CKW");
		$string=$link;
		$replacement=("//$user:$pwd@");
		$pattern=("%//%");

		$newlink=preg_replace($pattern,$replacement, $string); // add user and pass into url
		$chache=preg_split("%/%",$_POST["postLink"]); //split url to get filename
		$filename=$chache[4]; 
		$test="html";
		$test1="htm";


		// Get Filename
		if (strpos($filename,"html") !== false) $newfile= preg_replace("%.html%","",$filename);
		elseif (strpos($filename,"htm") !== false) $newfile= preg_replace("%.htm%","",$filename);
		else  $newfile= $filename;
		

		$file = fopen($newfile, "w"); // Curl Output (the actual download)


		// Curl Stuff
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl,CURLOPT_NOPROGRESS,false);
		curl_setopt($curl,CURLOPT_PROGRESSFUNCTION,'callback');
		curl_setopt($curl, CURLOPT_BUFFERSIZE, 512);
		curl_setopt($curl, CURLOPT_FILE,$file);
		curl_setopt ($curl, CURLOPT_URL,$newlink);
		curl_exec ($curl);


		fclose($file); // Finish Download

	}

	function download_finished()
	{
		global $newfile,$progressFinished;
		$progressFinished = 1;
		progress_update();
		echo "Download Successfull, here is your link:" , '<br>'  ;
		echo ("<a href=$newfile>$newfile</a>");		
	}
	
	if($_POST["postLink"]) start_download();

?>
