<?php

DEFINE('DEMO_SERVER', 'webs.my-demos.com');
DEFINE('REMOTE_SERVER', 'numo.server-apps.com');

$numoModules = array();
  
  if ($_SERVER['HTTP_HOST'] == DEMO_SERVER) {
	  $numos = $_GET['numo'];
	  print "yup"; 
	  $numoModulesData = explode(',', "accounts,".$_GET['numo'].",settings");
	  
	  foreach ($numoModulesData as $x => $y) {
		  $moduleName = str_replace("-", "_", $y);
		  $numoModules["$moduleName"] = true;		  
	  }
  } else if ($_SERVER['HTTP_HOST'] == REMOTE_SERVER) {
	  if (DIRECT_PROCESSING == true && $_GET['numo'] != "") {
		 // print "yes";
		 $doDemoModules = true;
		  $numoModulesData = explode(',', "accounts,".$_GET['numo'].",settings");
		  
		  foreach ($numoModulesData as $x => $y) {
			  $moduleName = str_replace("-", "_", $y);
			  $numoModules["$moduleName"] = true;		
			  //print $moduleName;
		  }
	  }
	 // print NUMO_SITE_ID;
	  /*$numos = $_GET['numo'];
	  $numoModulesData = explode(',', "accounts,".$_GET['numo'].",settings");
	  
	  foreach ($numoModulesData as $x => $y) {
		  $moduleName = str_replace("-", "_", $y);
		  $numoModules["$moduleName"] = true;		  
	  }
	  */
	  
  }
  
  
?>