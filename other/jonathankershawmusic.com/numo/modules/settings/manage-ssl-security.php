<?php

if($_POST['cmd'] == "save") {
	if (is_writable("configuration/database_connection_information.php") && false) {
		$changes = "";
	
		if ($sslAddress == "") {
			$sslAddress = $_SERVER['HTTP_HOST'];
		}
	
		//open and write to database connection information file
		$f = fopen("configuration/database_connection_information.php", w); //open for write
	
		fwrite($f, "<"."?php\n");
		fwrite($f, "define(DATABASE_HOST, '".DATABASE_HOST."');\n");
		fwrite($f, "define(DATABASE_NAME, '".DATABASE_NAME."');\n");
		fwrite($f, "define(DATABASE_USERNAME, '".DATABASE_USERNAME."');\n");
		fwrite($f, "define(DATABASE_PASSWORD, '".DATABASE_PASSWORD."');\n");
		fwrite($f, "define(NUMO_SITE_ID, '1');\n");
		fwrite($f, "define(NUMO_SERVER_ADDRESS, '".NUMO_SERVER_ADDRESS."');\n");
		fwrite($f, "define(NUMO_FOLDER_PATH, '".NUMO_FOLDER_PATH."');\n");
	
		//fwrite($f, "define(NUMO_SECURE_ADDRESS,  '".$_POST['system_setting_ssl_url']."');\n");
		//fwrite($f, "define(NUMO_SECURE_BACKEND,   ".($_POST['system_setting_secure_backend_via_ssl'] == true ? "true" : "false").");\n");
		//fwrite($f, "define(NUMO_SECURE_FRONTEND,  ".($_POST['system_setting_secure_frontend_via_ssl'] == true ? "true" : "false").");\n");
		
		//fwrite($f, "define(USE_INTERNAL_SESSIONS, ".(USE_INTERNAL_SESSIONS == true ? "true" : "false").");\n");
	
		fwrite($f, "?".">");
	
		fclose($f); //close
		
		// attempt to change the permissions
		@chmod ("configuration/database_connection_information.php", 0444);
		

		
		
		// push back to regular http://
		if ($_SERVER['HTTPS'] == "on" && $_POST['system_setting_secure_backend_via_ssl'] == false) {
		  print '<meta http-equiv="refresh" content="0;http://'.NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH.'module/settings/manage-ssl-security/" />';
		} else 	if ($_SERVER['HTTPS'] != "on" && $_POST['system_setting_secure_backend_via_ssl'] == true) {
		  print '<meta http-equiv="refresh" content="0;https://'.$_POST['system_setting_ssl_url'].NUMO_FOLDER_PATH.'module/settings/manage-ssl-security/" />';
			

		} else {
		  print '<meta http-equiv="refresh" content="0;" />';
			
		}
		
		exit;
	} else {
	  $update = "UPDATE sites SET ssl_address='{$_POST['system_setting_ssl_url']}', ssl_secure_backend='{$_POST['system_setting_secure_backend_via_ssl']}', ssl_secure_frontend='{$_POST['system_setting_secure_frontend_via_ssl']}' WHERE id='".NUMO_SITE_ID."'"; 
	  $dbObj->query($update);
	  $numo->loadSettings();
	  //print $update;
	 // print mysql_error();
	  //exit;
	  
// push back to regular http://
		if ($_SERVER['HTTPS'] == "on" && $_POST['system_setting_secure_backend_via_ssl'] == false) {
		  print '<meta http-equiv="refresh" content="0;http://'.NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH.'module/settings/manage-ssl-security/" />';
		} else 	if ($_SERVER['HTTPS'] != "on" && $_POST['system_setting_secure_backend_via_ssl'] == true) {
		  print '<meta http-equiv="refresh" content="0;https://'.$_POST['system_setting_ssl_url'].NUMO_FOLDER_PATH.'module/settings/manage-ssl-security/" />';
			

		} else {
		  print '<meta http-equiv="refresh" content="0;" />';
			
		}	  
	 // $error = "Could not write to configuration file.";
	}
	
}
?>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/settings/general/">System</a> <span class="divider">/</span></li>
  <li class="active">SSL Security</li>
</ul>
<h3>Manage SSL Security</h3>

<style>


.bttm_submit_button { position: fixed; bottom: 0px; right: 0px; background: #aaaaaa;  width: 100%; height: 70px; padding: 0px 20px; margin: 0px;}
.bttm_submit_button input { margin: 10px 0px 10px 210px;}
html { padding-bottom: 50px; }


		form ul {padding: 0px; margin: 0px; list-style-type: none; }
		 form ul li { padding: 2px 0px; font-size: 11px; color: #f00; padding-left: 50px;}
		 form ul li label { float: left; width: 20em; font-weight: bold; font-size: 13px; color: #000;}
		 form ul li label.step3 {  width: 260px; } 
		form ul li label.step3-short {  width: 225px; }  
		 form ul li span.url { color: #333; }
		 form ul li input { width: 175px; }
</style>
<?php if ($error != "") { ?>
<div class='module_pending_install' style='padding-top: 5px; padding-bottom: 10px;'><h2>Error!</h2>
<p><?php print $error; ?></p></div>
<?php } ?>
<?php if (!is_writable("configuration/database_connection_information.php") && false) { ?>
<div class='module_pending_install' style='padding-top: 5px; padding-bottom: 10px;'><h2>Notice!</h2>
<p>You must enable write permissions on the file "numo/configuration/database_connection_information.php" on your server in order to change these settings.</p></div>
<?php } ?>
<form method="post">
<ul>
    <li><label class='step3-short' for="system_setting_ssl_url">SSL Address:</label><span class='url'>https://</span><input type="text" id="system_setting_ssl_url" name="system_setting_ssl_url" value="<? echo $numo->siteSettings["ssl_address"]; ?>" /></li>
    <li><label class='step3' for="system_setting_secure_backend_via_ssl">Admin Area Secured via SSL:</label>
        <select id="system_setting_secure_backend_via_ssl" name="system_setting_secure_backend_via_ssl">
            <option value='0'>No</option>
            <option value='1' <?php if ($numo->siteSettings["ssl_secure_backend"] == 1) { print "selected"; } ?>>Yes</option>
        </select>
    </li>
    <li><label class='step3' for="system_setting_secure_frontend_via_ssl">User Logins Secured via SSL:</label>
        <select id="system_setting_secure_frontend_via_ssl" name="system_setting_secure_frontend_via_ssl">
            <option value='0'>No</option>
            <option value='1' <?php if ($numo->siteSettings["ssl_secure_frontend"] == 1) { print "selected"; } ?>>Yes</option>
        </select>                
    </li>
</ul>

<div class="bttm_submit_button">
<input type="hidden" value="save" name="cmd" />
<input type="submit" class='btn btn-large btn-success' value="Save" name="nocmd" />
</div>
</form>