<?php
error_reporting (E_ALL ^ E_NOTICE && E_WARNING);
ini_set ( "max_execution_time" , "600" );

//error_reporting (E_ALL);
include ("__preamble.php");
// clean GET inputs
foreach($_GET as $key => $value) {
	if (!get_magic_quotes_gpc()) {
	  $_GET["$key"] = addslashes($value);
	}
}

// clean POST inputs

foreach($_POST as $key => $value) {
	if (!get_magic_quotes_gpc() && !is_array($value)) {
	  $_POST["$key"] = addslashes($value);
	}
}
ob_start();
require("__setup.php");
$result = $dbObj->query("SHOW COLUMNS FROM `sites` LIKE 'last_updated'");
$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
  $dbObj->query("ALTER TABLE `sites` ADD `last_updated` DATETIME default '0000-00-00'");
}

$query = "SELECT * FROM sites WHERE id='".NUMO_SITE_ID."'";
$siteResult = $dbObj->query($query);
$siteData   = mysql_fetch_array($siteResult);
mysql_free_result($siteResult);

// display page without menu or other containing HTML code.
if($_GET['display'] == "response_only") {
	include(MODULES_FOLDER_NAME."/".$_GET['m']."/".$_GET['i'].".php");
	exit();
}

//if ($REMOTE_ADDR == "96.50.118.234" && false) {
	$adminVersion = 3;
//}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
	<title>Administrative Area</title>
        <?php if ($_SERVER['HTTPS'] == "on") { ?>
		<base href="https://<?php echo NUMO_SECURE_ADDRESS."".NUMO_FOLDER_PATH;
		if ($_GET['numo'] != "") { print "({$_GET['numo']})/"; } ?>" />
        <?php } else { ?>
		<base href="http://<?php echo NUMO_SERVER_ADDRESS."".NUMO_FOLDER_PATH;
		if ($_GET['numo'] != "") { print "({$_GET['numo']})/"; } ?>" />
        <?php } ?>
        <?php if ($adminVersion == "3") { ?>
        <link rel="stylesheet" type="text/css" href="styles/template-v3.css" />
		<link rel="stylesheet" type="text/css" href="styles/menu-h.css" />
		<?php } else { ?>
        <link rel="stylesheet" type="text/css" href="styles/template.css" />
		<link rel="stylesheet" type="text/css" href="styles/menu-v.css" />
        <?php } ?>

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
<script type="text/javascript" src="javascript/jquery-1.6.4.min.js"></script>
	<script type="text/javascript" src="javascript/menu.js">

	/***********************************************
	* Smooth Navigational Menu- (c) Dynamic Drive DHTML code library (www.dynamicdrive.com)
	* This notice MUST stay intact for legal use
	* Visit Dynamic Drive at http://www.dynamicdrive.com/ for full source code
	***********************************************/

	</script>

	<script type="text/javascript">
	<? if ($adminVersion == "3") { ?>
	ddsmoothmenu.init({
		mainmenuid: "menu", //Menu DIV id
		orientation: 'h', //Horizontal or vertical menu: Set to "h" or "v"
		//customtheme: ["#804000", "#482400"],
		contentsource: "markup" //"markup" or ["container_id", "path_to_menu_file"]
	})

	<? } else { ?>
	ddsmoothmenu.init({
		mainmenuid: "menu", //Menu DIV id
		orientation: 'v', //Horizontal or vertical menu: Set to "h" or "v"
		classname: 'menu-v', //class added to menu's outer DIV
		//customtheme: ["#804000", "#482400"],
		contentsource: "markup" //"markup" or ["container_id", "path_to_menu_file"]
	})
	<? } ?>
	</script>

</head>
<body>
<?php if (is_writable("configuration/database_connection_information.php")) { ?>
	<div id="notice_bar">
		<img src="images/configuration.png" style='float: left;' /><p>Warning!</p>
        <p>Your "<span class='file_name'>numo/configuration/database_connection_information.php</span>" file is currently writable.  To ensure that you do not lose your system connection settings, you should remove write permissions on this file immediately. <a href="http://www.i3dthemes.com/blog/numo-your-numoconfigurationdatabase_connection_information-php-file-is-currently-writable/" target="_blank">click here to learn how</a></p>

	</div>
<?php } ?>
	<div id="top_bar">
		<a href="//<?=NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH?>" ><img src="images/logo<?=$adminVersion?>.jpg" alt="NUMO Dashboard" title="Go To NUMO Dashboard Home" /></a>

		<p><?php if (REMOTE_SERVICE === true) { ?>
       <span class='registered-domain'><a href="//<?php print $numo->getRegisteredDomain(); ?>/"><?php print $numo->getRegisteredDomain(); ?></a></span> <? } ?><span class='logged-in-user'><? if ($adminVersion == "3") {
	   if ($userImageExists) { ?>

	  <?php } else { ?>

      <?php } ?>

       <?php } else { ?>: Welcome<?php } ?><span class='logged-in-name'><?=$_SESSION['full_name']?></span><span class='logged-in-username'><?=$_SESSION['login_id']?></span> <span class='logged-in-logout-link'>(<a href="?cmd=exit">logout</a>)</span>
       </span></p>
	</div>
	<div id="container">
	<div id="menu" <? if ($accountVersion != "3") { ?>class="menu-v"<? } ?>>
	<ul><? if ($adminVersion != "3") { ?>
		<li><a href="./">Home</a></li>
        <? } ?>
		<?php
		//print sizeof ($modules);

		foreach($modules as $key => $module) {
			if ($_SERVER['HTTP_HOST'] != DEMO_SERVER || $numoModules["$module"]) {
			  include(MODULES_FOLDER_NAME."/".$module."/configuration/menu.htm");
			 // print MODULES_FOLDER_NAME."/".$module."/configuration/menu.htm";
			}
		}
		?>
        <li class='menu-separator'><a href="http://<?php
		if (REMOTE_SERVICE === true) {
			echo "".$numo->getRootFolder();
			if ($doDemoModules) {
				echo "/".$_GET['numo']."/";
			}
		} else {
			echo NUMO_SERVER_ADDRESS.str_replace("/numo/", "/", NUMO_FOLDER_PATH);
		}
		?>">Exit Admin Panel</a></li>

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
update_admin_header();
//ob_end_flush();
?>