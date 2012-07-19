<?php

DEFINE('DEMO_SERVER', 'webs.my-demos.com');

$numoModules = array();
  
  if ($_SERVER['HTTP_HOST'] == DEMO_SERVER) {
	  $numos = $_GET['numo'];
	  $numoModulesData = explode(',', "accounts,".$_GET['numo'].",settings");
	  
	  foreach ($numoModulesData as $x => $y) {
		  $moduleName = str_replace("-", "_", $y);
		  $numoModules["$moduleName"] = true;		  
	  }
  }
  
  
?>