<?php
class Numo {
	function Numo() {
		// Define GLOBAL variables
		DEFINE('NUMO_VERSION', '1.0');
		DEFINE('EXTENSIONS_FOLDER_NAME', 'extensions');

		$this->extensions = array();
		$this->extensions['captcha'] = true;
		$this->extensions['wysiwyg'] = true;
		$this->siteSettings = array();
	}
	
	function getRootFolder($includeDomain = true, $includeSubfolder = false) {
	  global $dbObj;
		 
	  $query = "SELECT * FROM sites WHERE id='".NUMO_SITE_ID."'";
	  $results = $dbObj->query("$query");
	  $record = mysql_fetch_array($results);
	  
	  if ($includeDomain) {
		  if ($record['domain'] == "single domain") {
			  $record['domain'] = $_SERVER['HTTP_HOST'];
		  }
		  if ($_GET['subfolder'] != "" && $includeSubfolder) {
			$record['location'] .= "/".trim($_GET['subfolder'], "/");  
		  }
		  if ($record['location'] == "") {
			if (REMOTE_SERVICE === true) {
	    	  return $record['domain'];
				
			} else {
	    	  return $record['domain'].rtrim(substr(NUMO_FOLDER_PATH,0, -5), "/")."";
				
			}
			  
		  } else {
	    	return $record['domain']."/".trim($record['location'], "/");
		  }
	  } else {
		  if ($_GET['subfolder'] != "" && $includeSubfolder) {
			$record['location'] .= "/".trim($_GET['subfolder'], "/");  
		  } 		  
		return trim($record['location'], "/"); 
	  }
		
	}
	
	function getRegisteredDomain() {
	  global $dbObj;
		  
	  $query = "SELECT * FROM sites WHERE id='".NUMO_SITE_ID."'";
	  $results = $dbObj->query("$query");
	  $record = mysql_fetch_array($results);
	  $this->siteSettings = $record;

	  return $record['domain'];
	}
	function remoteFileExists($url) {
		//print NUMO_SITE_ID;
		//$url = $this->getRootFolder()."/".$url;
	  // Check to see if the file exists by trying to open it for read only
	  $fp = fopen($url, "r");
	  if ($fp) {
		  fclose($fp);
		  return true;
	  } else {
		  return false;
	  }
	}
	function strToHex($string) {
    $hex='';
    for ($i=0; $i < strlen($string); $i++)
    {
        $hex .= dechex(ord($string[$i]));
    }
    return $hex;
}

function loadSettings() {
	global $dbObj;
	$query = "SELECT * FROM sites WHERE id='".NUMO_SITE_ID."'";
	//print $query;
	$result = $dbObj->query($query);
	//print mysql_error();
    $this->siteSettings = mysql_fetch_array($result);
	//print "yup. ".$this->siteSettings['ssl_address'];
}

function hexToStr($hex)
{
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2)
    {
		//print "v";
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}
	function assertSiteID() {
	  global $dbObj;
	  global $numoDomain;
	  if ($_SESSION['numo_site_id'] != "") {
		  define(NUMO_SITE_ID, $_SESSION['numo_site_id']);
		  $numoDomain = $this->getRegisteredDomain();
	  } else if ($_POST['nsid'] != "") {
		  define(NUMO_SITE_ID, $_POST['nsid']);

	  } else if ($_GET['nsid'] != "" && $_GET['module'] == "shopping_cart" && $_GET['component'] == "process") {
		 // print $this->hexToStr($_GET['nsid'])."x";
		  define(NUMO_SITE_ID, $this->hexToStr($_GET['nsid']));

	  } else if ($_POST['numo_domain'] != "") {
		  $query = "SELECT * FROM sites WHERE domain='{$_POST['numo_domain']}'";
		  $results = $dbObj->query("$query");
		  $record = mysql_fetch_array($results);
	  		$this->siteSettings = $record;
		  
		  if ($record['id'] != "") {
		    define(NUMO_SITE_ID, $record['id']);
		  } else {
		    define(NUMO_SITE_ID, 0);
		  }
		  $numoDomain = $_POST['numo_domain'];
		  
	  } else if ((strstr($_SERVER['HTTP_REFERER'], "my.luckymarble.com") || strstr($_SERVER['HTTP_REFERER'], "my.i3dthemes.com")) && $_GET['numo_domain'] != "") {
		  $query = "SELECT * FROM sites WHERE domain='{$_GET['numo_domain']}'";
		  $results = $dbObj->query("$query");
		  $record = mysql_fetch_array($results);
	 	  $this->siteSettings = $record;
		  
		  if ($record['id'] != "") {
		    define(NUMO_SITE_ID, $record['id']);
		  } else {
		    define(NUMO_SITE_ID, 0);
		  }
		  $numoDomain = $_POST['numo_domain'];
		  
	 
	  } else if (!strstr($_SERVER['HTTP_REFERER'], NUMO_SERVER_ADDRESS) && $_SERVER['HTTP_REFERER'] != "") {
		 // print "test";
		  $referringDomain = $this->extractDomainName($_SERVER['HTTP_REFERER']);
		  $referringDomain2 = str_replace("www.", "", $referringDomain);
		  $numoDomain = $referringDomain2;
		  
		  $query = "SELECT * FROM sites WHERE domain='{$referringDomain}' OR domain='{$referringDomain2}'";
		  $results = $dbObj->query("$query");
		  
		  $record = mysql_fetch_array($results);
		  
	  $this->siteSettings = $record;
		  
		  if ($record['id'] != "") {
		    define(NUMO_SITE_ID, $record['id']);
			$_SESSION['numo_site_id'] = $record['id'];
		  } else {
		    define(NUMO_SITE_ID, 0);
		  }
	  } else if (DIRECT_PROCESSING === true) {
		  $referringDomain = $this->extractDomainName($_SERVER['HTTP_USER_AGENT'], true);
		  $referringDomain2 = str_replace("www.", "", $referringDomain);
		  $numoDomain = $referringDomain2;
		  
		  $query = "SELECT * FROM sites WHERE domain='{$referringDomain}' OR domain='{$referringDomain2}'";
		  $results = $dbObj->query("$query");
		  $record = mysql_fetch_array($results);
		  
	 	   $this->siteSettings = $record;
		  
		  if ($record['id'] != "") {
		    define(NUMO_SITE_ID, $record['id']);
			$_SESSION['numo_site_id'] = $record['id'];
		  } else {
		    define(NUMO_SITE_ID, 0);
		  }
		  
	  } else {
	    define(NUMO_SITE_ID, 0);	
	  }
	}
	
	function extractDomainName($url, $secondaryExtractionMethod = false) {
		if ($secondaryExtractionMethod) {
		  $urlData = explode(";", $url);	
		  $pattern = "/\((.*)?\)/";
		  preg_match($pattern, array_pop($urlData), $matches);
		  $url = $matches[1]; 
		}
		$url = str_replace("https://", "", $url);
		$url = str_replace("http://", "", $url);
		$urlInfo = explode("/", $url);
		
		if (sizeof($urlInfo) > 1) {
			$url = $urlInfo[0];
		}	
		return $url;
	}
	
	function getLicenseKey($module) {
		global $dbObj;
		$query = "SELECT license_key FROM modules WHERE site_id='".NUMO_SITE_ID."' AND name='{$module}'";
		$result = $dbObj->query($query);
		$record = mysql_fetch_array($result);
		return $record['license_key'];
	}
	
	function getRemoteFiles(&$array_of_dirs, &$array_of_files, $licenseKey, $dir) {
		$ch = curl_init(); //init
		curl_setopt($ch, CURLOPT_URL, 'http://'.$this->getRootFolder().'/numo-remote.php'); //setup request to website to check license key
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // return the response
		curl_setopt($ch, CURLOPT_USERAGENT, "Numo Remote File/Folder Query");
		curl_setopt($ch, CURLOPT_POST, 1); //transfer information as a POST request
		curl_setopt($ch, CURLOPT_POSTFIELDS, 'lpk='.urlencode($licenseKey)."&dir=".urlencode($dir)."&cmd=get_remote_files"); //pass product license key and domain name along to be checked
	
		//send request and save response to variable
		$response = curl_exec($ch);
		if ($response == "Bad License Key") {
			// handle bad license key
			print "Bad License Key in your 'numo-remote.php' file.  Please enter {$licenseKey} in the provided space in 'numo-remote.php'";
		} else if ($response != "") {
		  $arrays = json_decode($response, true);
		  $array_of_dirs  = $arrays['array_of_dirs'];
		  $array_of_files = $arrays['array_of_files'];
		}
		curl_close($ch); //close curl connection
		//return ""; //success
		//echo $response;	 
	}
} 
$numo = new Numo();
?>