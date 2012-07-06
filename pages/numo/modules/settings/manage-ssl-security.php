<?php

if($_POST['cmd'] == "save") {
	if (is_writable("configuration/database_connection_information.php")) {
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
	
		fwrite($f, "define(NUMO_SECURE_ADDRESS,  '".$_POST['system_setting_ssl_url']."');\n");
		fwrite($f, "define(NUMO_SECURE_BACKEND,   ".($_POST['system_setting_secure_backend_via_ssl'] == true ? "true" : "false").");\n");
		fwrite($f, "define(NUMO_SECURE_FRONTEND,  ".($_POST['system_setting_secure_frontend_via_ssl'] == true ? "true" : "false").");\n");
		
		fwrite($f, "define(USE_INTERNAL_SESSIONS, ".(USE_INTERNAL_SESSIONS == true ? "true" : "false").");\n");
	
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
	  $error = "Could not write to configuration file.";
	}
	
}
?>
<h2>Manage SSL Security</h2>
<script type="text/javascript" src="modules/<?=$_GET['m']?>/javascript/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="modules/<?=$_GET['m']?>/javascript/jquery-ui-1.8.2.custom.min.js"></script>
<script type="text/javascript">
	$(function(){
		$('#tabs').tabs();
	});
</script>
<style>

.ui-helper-hidden { display: none; }
.ui-helper-hidden-accessible { position: absolute; left: -99999999px; }
.ui-helper-reset { margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none; }
.ui-helper-clearfix:after { content: "."; display: block; height: 0; clear: both; visibility: hidden; }
.ui-helper-clearfix { display: inline-block; }
/* required comment for clearfix to work in Opera \*/
* html .ui-helper-clearfix { height:1%; }
.ui-helper-clearfix { display:block; }
/* end clearfix */

.ui-widget-content { border: 1px solid #ccc; background: #fff; color: #333333; }
.ui-widget-content a { color: #333333; }
.ui-widget-header { border: 1px solid #2A61B3; background: #2A61B3 repeat-x; color: #ffffff; font-weight: bold; }
.ui-widget-header a { color: #ffffff; }

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default { border: 1px solid #cccccc; background: #eee; font-weight: bold; color: #3473D1; }
.ui-state-default a, .ui-state-default a:link, .ui-state-default a:visited { color: #3473D1; text-decoration: none; }
.ui-state-hover, .ui-widget-content .ui-state-hover, .ui-widget-header .ui-state-hover, .ui-state-focus, .ui-widget-content .ui-state-focus, .ui-widget-header .ui-state-focus { border: 1px solid #3473D1; background: #DBE6F7; font-weight: bold; color: #3473D1; }
.ui-state-hover a, .ui-state-hover a:hover { color: #3473D1; text-decoration: none; }
.ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active { border: 1px solid #DBE6F7; background: #ffffff; font-weight: bold; color: #2A61B3; }
.ui-state-active a, .ui-state-active a:link, .ui-state-active a:visited { color: #2A61B3; text-decoration: none; }
.ui-widget :active { outline: none; }
.ui-tabs { position: relative; padding: .2em; zoom: 1; } /* position: relative prevents IE scroll bug (element with position: relative inside container with overflow: auto appear as "fixed") */
.ui-tabs .ui-tabs-nav { margin: 0; padding: .2em .2em 0; }
.ui-tabs .ui-tabs-nav li { list-style: none; float: left; position: relative; top: 1px; margin: 0 .2em 1px 0; border-bottom: 0 !important; padding: 0; white-space: nowrap; }
.ui-tabs .ui-tabs-nav li a { float: left; padding: .5em 1em; text-decoration: none; }
.ui-tabs .ui-tabs-nav li.ui-tabs-selected { margin-bottom: 0; padding-bottom: 1px; }
.ui-tabs .ui-tabs-nav li.ui-tabs-selected a, .ui-tabs .ui-tabs-nav li.ui-state-disabled a, .ui-tabs .ui-tabs-nav li.ui-state-processing a { cursor: text; }
.ui-tabs .ui-tabs-nav li a, .ui-tabs.ui-tabs-collapsible .ui-tabs-nav li.ui-tabs-selected a { cursor: pointer; } /* first selector in group seems obsolete, but required to overcome bug in Opera applying cursor: text overall if defined elsewhere... */
.ui-tabs .ui-tabs-panel { display: block; border-width: 0; padding: 1em 0em; background: none; }
.ui-tabs .ui-tabs-hide { display: none !important; }

.bttm_submit_button {position: fixed; bottom: 0px; right: 0px; background: #779FE1; border-top: 1px solid #2A61BD; width: 100%; height: 50px; padding: 0px 20px; margin: 0px;}
.bttm_submit_button input {background: #EEEEEE; color: #333; border: 1px solid #333; height: 30px; margin: 10px 0px 10px 210px;}
.bttm_submit_button input:hover {background: #bbb; color: #333; border: 1px solid #333; cursor: pointer;}
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
<?php if (!is_writable("configuration/database_connection_information.php")) { ?>
<div class='module_pending_install' style='padding-top: 5px; padding-bottom: 10px;'><h2>Notice!</h2>
<p>You must enable write permissions on the file "numo/configuration/database_connection_info.php" on your server in order to change these settings.</p></div>
<?php } ?>
<form method="post">
<ul>
    <li><label class='step3-short' for="system_setting_ssl_url">SSL Address:</label><span class='url'>https://</span><input type="text" id="system_setting_ssl_url" name="system_setting_ssl_url" value="<? echo NUMO_SECURE_ADDRESS; ?>" /></li>
    <li><label class='step3' for="system_setting_secure_backend_via_ssl">Admin Area Secured via SSL:</label>
        <select id="system_setting_secure_backend_via_ssl" name="system_setting_secure_backend_via_ssl">
            <option value='0'>No</option>
            <option value='1' <?php if (NUMO_SECURE_BACKEND == true) { print "selected"; } ?>>Yes</option>
        </select>
    </li>
    <li><label class='step3' for="system_setting_secure_frontend_via_ssl">User Logins Secured via SSL:</label>
        <select id="system_setting_secure_frontend_via_ssl" name="system_setting_secure_frontend_via_ssl">
            <option value='0'>No</option>
            <option value='1' <?php if (NUMO_SECURE_FRONTEND == true) { print "selected"; } ?>>Yes</option>
        </select>                
    </li>
</ul>

<div class="bttm_submit_button">
<input type="hidden" value="save" name="cmd" />
<input type="submit" value="Save" name="nocmd" />
</div>
</form>