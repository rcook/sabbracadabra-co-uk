<?php
class Access {
  function Access() {
	  global $modules;
	  global $_GET;
  }
  function hasAccess($module = "", $component = "", $accountID = "") {
	global $dbObj;
	global $_SESSION;
	global $_GET;
	
	if ($module == "") {
	  $module = $_GET['m'];
	}
	if ($component == "") {
		$component = $_GET['i'];
	}
	if ($accountID == "") {
	  $accountID = $_SESSION['account_id'];	
	}

	if ($accountID == $_SESSION['account_id'] && $_SESSION['is_admin'] == "1") {
		return true;
	} else {
	
	  $query = "SELECT * FROM admin_privileges WHERE account_id='{$accountID}' AND module='{$module}' AND site_id='".NUMO_SITE_ID."'";

	  $result = $dbObj->query($query);
	  
	  $record = mysql_fetch_array($result);
	 // print $query."<br>";
	  
	  $accessPoints = explode(",", $record['components']);
	  //print $component."!={$record['components']}<br>";
	  if (in_array($component, $accessPoints)) {
		  return true;
	  } else {
		  return false;
	  }
	}
  }
}
$access = new Access();
?>