<?php
  error_reporting (E_ALL ^ E_NOTICE && E_WARNING);
  
  include ("__preamble.php");



//clean GET inputs
foreach($_GET as $key => $value) {
	if (!get_magic_quotes_gpc()) {
	  $_GET["$key"] = addslashes($value);
	}
	//print $key."=".$value;
	//if(is_string($value)) {
	//	$_GET[$key] = htmlentities($value);
	//}

}

foreach($_POST as $key => $value) {
	if (!get_magic_quotes_gpc() && !is_array($value)) {
	  $_POST["$key"] = addslashes($value);
	}
	//print "p ". $key."=".$value."\n";
	//if(is_string($value)) {
	//	$_GET[$key] = htmlentities($value);
	//} 

}
ob_start();
require("__setup.php");

	$result = $dbObj->query("SHOW COLUMNS FROM `sites` LIKE 'last_updated'");
	$exists = (mysql_num_rows($result))?TRUE:FALSE;
	if (!$exists) {
	  $dbObj->query("ALTER TABLE `sites` ADD `last_updated` DATETIME default '0000-00-00'");
	}
	//$result = $dbObj->query("SHOW COLUMNS FROM `sites`");
	//while ($rec = mysql_fetch_array($result)) {
	//	print $rec['Field']."<br>";
	//}
	
	$query = "SELECT * FROM sites WHERE id='".NUMO_SITE_ID."'";
	$siteResult = $dbObj->query($query);
    $siteData   = mysql_fetch_array($siteResult);
	mysql_free_result($siteResult);

//display page without menu or other containing HTML code.
if($_GET['display'] == "response_only") {
	include(MODULES_FOLDER_NAME."/".$_GET['m']."/".$_GET['i'].".php");
	exit();
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>Administrative Area</title>
        <?php if ($_SERVER['HTTPS'] == "on") { ?>
		<base href="https://<?php echo NUMO_SECURE_ADDRESS."".NUMO_FOLDER_PATH; ?>" />	
        <?php } else { ?>
		<base href="http://<?php echo NUMO_SERVER_ADDRESS."".NUMO_FOLDER_PATH; ?>" />	
        <?php } ?>	
        <link rel="stylesheet" type="text/css" href="styles/template.css" />
	<link rel="stylesheet" type="text/css" href="styles/menu-v.css" />
<?php
//display page without menu or other containing HTML code.
if($_GET['display'] == "min") {
	print "</head><body>";
	if ($access->hasAccess()) {
	  include(MODULES_FOLDER_NAME."/".$_GET['m']."/".$_GET['i'].".php");
	} else {
	  print "You do have have access to view this administrative function.";
	}
	print "</body></html>";
	exit();
}
?>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
	<script type="text/javascript" src="javascript/menu.js">

	/***********************************************
	* Smooth Navigational Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
	* This notice MUST stay intact for legal use
	* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
	***********************************************/

	</script>

	<script type="text/javascript">
	ddsmoothmenu.init({
		mainmenuid: "menu", //Menu DIV id
		orientation: 'v', //Horizontal or vertical menu: Set to "h" or "v"
		classname: 'menu-v', //class added to menu's outer DIV
		//customtheme: ["#804000", "#482400"],
		contentsource: "markup" //"markup" or ["container_id", "path_to_menu_file"]
	})
	</script>

</head>
<body>
<?php if (is_writable("configuration/database_connection_information.php")) { ?>
	<div id="notice_bar">
		<img src="images/configuration.png" style='float: left;' /><p>Warning!</p>
        <p>Your "<span class='file_name'>numo/configuration/database_connection_information.php</span>" file is currently writable.  To ensure that you do not lose your system connection settings, you should remove write permissions on this file immediately.</p>
		
	</div>
<?php } ?>
	<div id="top_bar">
		<img src="images/logo.jpg" alt="NUMO" title="NUMO" />
		<p>Welcome <?=$_SESSION['full_name']?> (<a href="?cmd=exit">logout</a>)</p>
	</div>
	<div id="container">
	<div id="menu" class="menu-v">
	<ul>
		<li><a href="./">Home</a></li>
		<?php
		//print sizeof ($modules);
		
		foreach($modules as $key => $module) {
			if ($_SERVER['HTTP_HOST'] != DEMO_SERVER || $numoModules["$module"]) {
			  include(MODULES_FOLDER_NAME."/".$module."/configuration/menu.htm");
			}
		}
		?>
        <li class='menu-separator'><a href="http://<?php echo NUMO_SERVER_ADDRESS.str_replace("/numo/", "/", NUMO_FOLDER_PATH);?>">Exit Admin Panel</a></li>

	</ul>
	<br style="clear: left" />
	</div>
	<div id="content">
    
	<?php
		if ($REMOTE_ADDR == "xx.xx.xx.xx") {
		  include(MODULES_FOLDER_NAME."/settings/classes/Updater.php");
		  
		  if ($updater->updatesAvailable()) {
			print "<p class='notification'>There are ".$updater->getNumberOfUpdates()." updates available for this Numo installation.  <input style='float: right' name='cmd' type='submit' value='Install' /></p>";							 
		  }
		}
		
	    if ($_GET['m'] == "" || $access->hasAccess()) {
			if(!(@include MODULES_FOLDER_NAME."/".$_GET['m']."/".$_GET['i'].".php")) {
				include("landing.php");
			}
		} else {
		  print "You do not have access to view this adminstrative function.";	
		}
		
	?>
	</div>
	</div>
</body>
</html>
<?php
ob_end_flush();
?>