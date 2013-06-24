<?
	/**
	 * Share-Online Loader
	 *
	 * @version	0.1
	 * @license	GPL 2 (http://www.gnu.org/licenses/gpl.html)
	 * @author	Heiko Barth <https://www.heiko-barth.de/contact>
	 */
 
	/* Settings */
 
	$user = "04851494694";
	$pass = "wieselwiesel";
	$downloadDir = "/downloads";
	$downloadList = "/downloads/downloads.txt";
 
	$cookie = "$downloadDir/.auth.cookie";
	$maxFail = 5;
	$failDelay = 60;
	$ua = "Share-Online Loader 0.1";
 
	/* Settings */
 
	if (php_sapi_name() != 'cli') die("ERROR: Not running from CLI.");
 
	preg_match('!columns ([0-9]+?);!', shell_exec("stty -a 2> /dev/null"), $columns);
	//if (is_numeric($columns[1]) && $columns[1] >= 88) {
		// display ascii art, if terminal have 88 columns or more
		//$logo = <<<LOGO
// __ _                      ___       _ _                 __                 _           
/// _\ |__   __ _ _ __ ___  /___\_ __ | (_)_ __   ___     / /  ___   __ _  __| | ___ _ __ 
///\ \| '_ \ / _` | '__/ _ \//  // '_ \| | | '_ \ / _ \   / /  / _ \ / _` |/ _` |/ _ \ '__|
//_\ \ | | | (_| | | |  __/ \_//| | | | | | | | |  __/  / /__| (_) | (_| | (_| |  __/ |   
//\__/_| |_|\__,_|_|  \___\___/ |_| |_|_|_|_| |_|\___|  \____/\___/ \__,_|\__,_|\___|_|   
//LOGO;
//	}
	//else {
		$logo = "Share-Online Loader 0.1";
	//}
 
	set_time_limit(0);
	ob_implicit_flush();
 
	echo $logo . "\n\n";
 
	$red = "\033[31m";
	$green = "\033[32m";
	$yellow = "\033[33m";
	function cecho($str, $color, $return = false) {
		if ($return) {
			return $color . $str . "\033[0m";
		}
		else {
			echo $color . $str . "\033[0m";
		}
	}
 
	function echoError($msg) {
		global $red;
		file_put_contents("php://stderr", cecho("ERROR: $msg\n", $red, true));
	}
 
	function dieHard($msg, $exit = 1) {
		global $red;
		file_put_contents("php://stderr", cecho("ERROR: $msg\n\n", $red, true));
		exit($exit);	
	}
 
	@unlink($cookie);
 
	//if ($_SERVER["argv"][1] == "-h" || $_SERVER["argv"][1] == "--help") {
	//	echo "If no download link is supplied, the download list '$downloadList' will be processed.\n\n";
	//	die("Syntax: " . basename(__FILE__) . " [Download Link]\n\n");
	//}
 
	if (!function_exists("curl_init"))
		dieHard("PHP cURL extension not found.");
 
	passthru('wget --version > /dev/null 2>&1', $errorCode);
	if ($errorCode > 0)
		dieHard("'wget' binary not found.");
 
	if (!$user || !$pass)
		dieHard("No username or password defined.");
 
	@chdir($downloadDir) or
		dieHard("Failed to change to download direcoty '$downloadDir'");
 
	if ($_SERVER["argc"] < 2) {
		// load download list
		$downloads = @file($downloadList) or
			dieHard("Could not open download list '$downloadList'");
	}
	else {
		// build download array
		for ($i = 1; $i < $_SERVER["argc"]; $i++) {
			$downloads[] = $_SERVER["argv"][$i];
		}
	}
 
	function setOptions($ch) {
		// Set (curl) options
		global $ua, $cookie;
		curl_setopt($ch, CURLOPT_USERAGENT, $ua);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
		curl_setopt($ch, CURLOPT_AUTOREFERER, true);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, true);
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_FAILONERROR, true);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_ENCODING, "");
		curl_setopt($ch , CURLOPT_SSL_VERIFYPEER , true);
		curl_setopt($ch , CURLOPT_SSL_VERIFYHOST , 2);
		curl_setopt($ch , CURLOPT_TIMEOUT, 10);
	}
 
	function cURL($url) {
		global $ch;
		curl_setopt($ch, CURLOPT_URL, $url);
		return curl_exec($ch);
	}
 
	function login() {
		global $ch;
		global $user;
		global $pass;
		global $login;
		global $green;
		global $red;
 
		echo "Requesting authentication cookie.. ";
 
		$url = "https://www.share-online.biz/user/login";
		$credentials = "user=$user&pass=$pass";
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $credentials);
		$html = cURL($url);
		if (!$html) {
			$curl_error_message = "\n\ncURL-Error: " . curl_error($ch) . "\n\n" . print_r(curl_getinfo($ch), true);
			dieHard($curl_error_message);
		}
		curl_setopt($ch, CURLOPT_POST, false);
 
		if (strpos($html, $user) === false) {
			echo "[ ";
			cecho("FAILED",$red);
			echo " ]\n\n";
			dieHard("Login failed. Check username and password.");
		}
		cecho("OK\n", $green);
		$login = true;
	}
 
	$ch = curl_init();
	setOptions($ch);
 
	foreach ($downloads as $download) {
		$download = trim($download);
		restart:
		if ((substr($download, 0 , 27) != "http://www.share-online.biz") && (substr($download, 0 , 23) != "http://share-online.biz")) {
			cecho("SKIP: No share-online link \"$download\"\n", $yellow);
			continue;
		}
		# URL VALID.
		# CHECK LINK
		$url = "http://api.share-online.biz/linkcheck.php?md5=1&links=" . urlencode($download);
		$html = cURL($url);
		if (!$html) {
			$curl_error_message = "\n\ncURL-Error: " . curl_error($ch) . "\n\n" . print_r(curl_getinfo($ch), true);
			dieHard($curl_error_message);
		}
 
		# http://www.share-online.biz/linkcheckapi
		list($id, $status, $name, $size, $md5) = explode(";", $html);
		$name = basename($name);
		$md5 = trim($md5);
		if ($status != "OK") {
			// file not available
			echoError("File $id not available");
			continue;
		}
 
		# CHECK IF FILE ALREADY DOWNLOADED
		if ($size == @filesize("./$name")) {
			cecho("INFO: File \"$name\" already downloaded.\n", $yellow);
			continue;
		}
 
		if(!$login)
			login();
 
		# GET DOWNLOAD URL
		$html = cURL($download);
		if (!$html) {
			$curl_error_message = "\n\ncURL-Error: " . curl_error($ch) . "\n\n" . print_r(curl_getinfo($ch), true);
			dieHard($curl_error_message);
		}
 
		$pat = ';var dl="(.+?)";s';
		if(!preg_match($pat, $html, $match))
			dieHard("preg_match failed. Could not find download link.");
 
		$dl = base64_decode($match[1]); # Download URL
		
		curl_close($ch); // save cookie for external usage
		$ch = curl_init();
		setOptions($ch);
 
		# Download file
		cecho("\nFile: $name\n", $yellow);
		passthru("wget -c -t 1 -O \"$name\" -U \"$ua\" --load-cookies \"$cookie\" '$dl'");
		echo "Validating file.. ";
		if (md5_file($name) != $md5) {
			cecho("FAILED\n\n", $red);
			$fail++;
			@unlink($name) or
				dieHard("Failed to remove invalid file. Exiting..");
			if ($fail > $maxFail) {
				curl_close($ch);
				@unlink($cookie);
				dieHard("Maximum fail count of $maxFail reached. Exiting..");
			}
			echoError("Invalid download detected. Restarting download in $failDelay seconds..");
			sleep($failDelay);
			goto restart;
		}
		cecho("OK\n\n", $green);
	}
	curl_close($ch);
	@unlink($cookie);
	echo ($fail) ? "\nWarnings: $fail" : "";
	echo "\nJob done.\n\n";
	?>