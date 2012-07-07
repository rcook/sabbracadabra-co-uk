<?php
class Updater {
  var $updateServer = "numo.server-apps.com";
  
  function Updater() {
	$this->init();
	$this->query();
	
  }
  
  function updatesAvailable() {
	return $this->getNumberOfUpdates() > 0;  
  }
  
  function getNumberOfUpdates() {
	return 0;  
  }
  
  function init() {
	global $modules;
	global $numoModules;
	global $_SERVER;
	foreach ($modules as $key => $module) {
		if ($_SERVER['HTTP_HOST'] != DEMO_SERVER || $numoModules["$module"]) {
		  if (file_exists(MODULES_FOLDER_NAME."/".$module."/configuration/updates.xml")) {
			//  $fileData =file_get_contents(MODULES_FOLDER_NAME."/".$module."/configuration/updates.xml");
			  //$xmlData = simplexml_load_string($fileData);
			 // print htmlentities($fileData);
			//  print "x[".$xmlData."]";
			 // foreach ($xmlData as $info) {
				//  print "$xmlData";
			  //}
			 // print sizeof($xmlData['updates']);
			  //$updates = $this->getXMLData("update");
			  //print $fileData;
		  }
		}
	}
	// load information for each module including:
	//   license id
	//   installed xml update list
	  
  }
  
  function query() {
	// query update server
	//   current xml update list
	//   license validity
	  
  }
  
  function update() {
	// open curl
	// submit key and domain
	  
	  
  }
  
  function getXMLData($element_name, $xml, $content_only = true) {
    if ($xml == false) {
        return false;
    }
    $found = preg_match('#<'.$element_name.'(?:\s+[^>]+)?>(.*?)'.
            '</'.$element_name.'>#s', $xml, $matches);
    if ($found != false) {
        if ($content_only) {
            return $matches[1];  //ignore the enclosing tags
        } else {
            return $matches[0];  //return the full pattern match
        }
    }
    // No match found: return false.
    return false;
}
}
$updater = new Updater();
?>