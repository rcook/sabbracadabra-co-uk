<?php
//sss
?>
<html>
<head>
	<title>NUMO Installation</title>
	<style>
		body { text-align: center; padding: 0px; margin: 0px; font-family: Arial, sans-serif;}
		div { padding: 0px; margin: 0px; }
		h2 { padding: 0px; margin: 0px; }
		p { padding: 0px; margin: 0px; }

		#top_bar {background: #E4E4E4 url('images/bar_bg.jpg') repeat-x; height: 40px; text-align: left; color: #2C62B4;}
		#top_bar img { position: absolute; top: 0px; right: 0px;}
		#top_bar p { margin: 0px; padding: 0px 5px; line-height: 40px;}

		.content_box {width: 640px; 

			 margin: 25px auto; 
			 text-align: left; 
			 border: 1px solid #dddddd;
			 border-radius: 5px;
			 box-shadow: 0 4px 18px #cccccc;
			 margin-top: 80px;		
		}
		.content_box p {color: #999; font-size: 0.75em; padding: 10px 5px;}
		.content_box form {padding: 0px 5px; margin: 0px;}
		.content_box form ul {padding: 0px; margin: 0px; list-style-type: none; }
		.content_box form ul li { padding: 2px 0px; font-size: 11px; color: #f00; padding-left: 50px;}
		.content_box form ul li label { float: left; width: 20em; font-weight: bold; font-size: 13px; color: #000;}
		.content_box form ul li label.step3 {  width: 260px; } 
		.content_box form ul li label.step3-short {  width: 225px; }  
		.content_box form ul li span.url { color: #333; }
		.content_box form ul li input { width: 175px; }
		.content_header {background: #4D6D9D url('images/dark_bar_bg.jpg') repeat-x;}
		.content_header img { margin: 0px; padding: 0px 7px 0px 0px; border: 0px;  vertical-align: middle; }
		.content_header h2 { line-height: 49px; color: #EFF3F8; font-size: 1.4em;}

		.error_box { margin: 20px; padding: 5px; background: #FBEADD;}
		.error_box h2 { margin-left: 75px; padding: 5px 0px; color: #C02A05; font-size: 0.9em; font-weight: bold;}
		.error_box p { margin-left: 75px; padding: 0px 0px 5px 0px; color: #C02A05; font-size: 0.85em; font-weight: none;}
		.error_box p.file_path {color: #691703; padding: 5px 0px;}
		.error_box p.note { color: #666; font-size: 0.7em; font-style: italic; margin-top: 10px;}

		.passed_box { margin: 20px; padding: 5px; background: #EAFBDD;}
		.passed_box h2 { margin-left: 75px; padding: 5px 0px; color: #395706; font-size: 0.9em; font-weight: bold;}
		.passed_box p { margin-left: 75px; padding: 0px 0px 5px 0px; color: #395706; font-size: 0.85em; font-weight: none;}
		.passed_box p.note { color: #666; font-size: 0.7em; font-style: italic; margin-top: 10px;}

		.submit_button { position: relative; left: 520px;}
		h3 {
			margin-bottom: 0px; }
	</style>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.16/jquery-ui.min.js"></script>    
</head>
	<div id="top_bar">
	<p>NUMO Installation</p>
	<img src="images/logo.jpg" alt="NUMO" title="NUMO" />
	</div>
	<div class="content_box">
		<?php
		/************************************************
		INSTALLATION STEP 1
			--> ENTER LICENSE KEY
		************************************************/
		if($_POST['next_step'] == "1") {
			$modulesFolders = array();
			$modulesFolder = @opendir(MODULES_FOLDER_NAME);
			while ($moduleFolderName = readdir($modulesFolder)) {
						//ingore if item named with periods or starts with an underscore
						if($moduleFolderName == "." || $moduleFolderName == ".." || substr($moduleFolderName, 0, 1) == "_" || $moduleFolderName == "settings" || $moduleFolderName == "accounts"){
							continue;
						}
						$modulesFolders[]  = $modulesFolderName;
			}
		?>
		<div class="content_header"><img src="images/install_key_icon.jpg" align="left" /><h2>Step 1: Enter License Number</h2></div>
		<p>Enter the product license number<?php if (sizeof($modulesFolders) > 1) { ?>(s)<?php } ?> below. </p>
        <?php if (sizeof($modulesFolders) > 1) { ?>
        <p>
         If you purchased a bundled web template product, you may only need to enter the same license key for multiple modules.
        </p>
        <?php } ?>
		<?php
		if($installError) {
		?>
		<div class="error_box">
		<img src="images/install_key_error_icon.jpg" align="left" />
		<p>The license number entered is invalid or no longer available.  Please double check that the correct license number has been entered.</p>
		<p class="note">If you continue to have troubles please contact Lucky Marble support for further information.</p>
		</div>
		<?php
		}
		?>

		<form method="post">
			<ul>
				<?php
				//cycle through module folder and
				if ($modulesFolder = @opendir(MODULES_FOLDER_NAME)){
					//cycle thru each file in the MODULES folder
					while ($moduleFolderName = readdir($modulesFolder)) {
						//ingore if item named with periods or starts with an underscore
						if($moduleFolderName == "." || $moduleFolderName == ".." || substr($moduleFolderName, 0, 1) == "_" || $moduleFolderName == "settings" || $moduleFolderName == "accounts"){
							continue;
						}

						?>
							<li><label for="product_key__<?=$moduleFolderName?>"><?=ucwords(str_replace("_"," ",$moduleFolderName))?> Product Key:</label>
                            <input type="text" id="product_key__<?=$moduleFolderName?>" name="product_key__<?=$moduleFolderName?>" value="<?=$_POST['product_key__'.$moduleFolderName]?>" /><?=$productKeyErrors[$moduleFolderName]?></li>
						<?php
					}
				}
				?>
			</ul>
			<input type="hidden" name="next_step" value="2" />
			<input type="hidden" name="cmd" value="numo_install" />
			<input class="submit_button" type="image" src="images/next_arrow_icon.jpg" name="nocmd" alt="Next" />
		</form>

		<?php
		/************************************************
		INSTALLATION STEP 2
			--> ENTER DATABASE INFO
		************************************************/
		} else if($_POST['next_step'] == "2") {
		?>
		<div class="content_header"><img src="images/install_database_icon.jpg" align="left" /><h2>Step 2: Enter Database Connection Information</h2></div>
		<p>Enter the connection details for your MySQL database.</p>

		<?php
		if($installError) {
		?>
		<div class="error_box">
		<img src="images/install_database_error_icon.jpg" align="left" />
		<p>This Numo system was unable to connect to the MySQL database using the connection details previously provided.  Please double check your connection information and try again.</p>
		<p>Error Returned: <br/><b><?php print $dbObj->error; ?></b></p>
		<p class="note">If you continue to have troubles please contact your hosting company to confirm your database connection information is correct and setup properly.</p>
		</div>
		<?php 
		}
		?>

		<form method="post">
			<ul>
				<li><label class='step2' for="database_host">Database Host:</label> <input type="text" id="database_host" name="database_host" value="<?=$_POST['database_host']?>" /></li>
				<li><label class='step2' for="database_name">Database Name:</label><input type="text" id="database_name" name="database_name" value="<?=$_POST['database_name']?>" /></li>
				<li><label class='step2' for="database_username">Database Username:</label><input type="text" id="database_username" name="database_username" value="<?=$_POST['database_username']?>" /></li>
				<li><label class='step2' for="database_password">Database Password:</label><input type="password" id="database_password" name="database_password" value="<?=$_POST['database_password']?>" /></li>
			</ul>
			<?php foreach ($_POST as $key => $value) {
				$pkInfo = explode("__", $key);
				if ($pkInfo[0] == "product_key") {
				  print "<input type='hidden' name='{$key}' value='{$value}' />";
				}
			}
			?>
			<input type="hidden" name="next_step" value="3" />
			<input type="hidden" name="cmd" value="numo_install" />
			<input class="submit_button" type="image" src="images/next_arrow_icon.jpg" name="nocmd" alt="Next" />
		</form>
		<?php
		/************************************************
		INSTALLATION STEP 3
			--> CREATE ADMINISTRATIVE ACCOUNT
		************************************************/
		} else if($_POST['next_step'] == "3" || $_POST['next_step'] == "0") {
		?>
		<div class="content_header"><img src="images/install_user_icon.jpg" align="left" /><h2>Step 3: Initialization Details</h2></div>
		
        <form method="post">
        <h3>Create Master Administrator Account</h3> 
        		<p>Enter your information for the administrative account.  The username and password you set here is what you will need to use in order to login to the administrative area once you have completed the installation.</p>

			<ul>
				<li><label for="account_name">Name:</label><input type="text" id="account_name" name="account_name" value="<?=$_POST['account_name']?>" /></li>
				<li><label for="account_email">Email Address:</label><input type="text" id="account_email" name="account_email" value="<?=$_POST['account_email']?>" /></li>
				<li><label for="account_username">Username:</label><input type="text" id="account_username" name="account_username" value="<?=$_POST['account_username']?>" /></li>
				<li><label for="account_password">Password:</label><input type="password" id="account_password" name="account_password" value="<?=$_POST['account_password']?>" /></li>
			</ul>
		<h3>Security Preferences</h3>		
        <p>These settings can be later changed.</p>
        <p>Please note, if you are are uncertain as to whether your SSL Address is valid (as in, if you have a valid SSL certificate), you should not secure your administration area or user logins as you will get a web browser warning every time you attempt to load a secured page if the SSL certificate is invalid.</p>
			<ul>
				<li><label class='step3-short' for="system_setting_ssl_url">SSL Address:</label><span class='url'>https://</span><input type="text" id="system_setting_ssl_url" name="system_setting_ssl_url" value="<?=$_SERVER['HTTP_HOST']?>" /></li>
				<li><label class='step3' for="system_setting_secure_backend_via_ssl">Admin Area Secured via SSL:</label>
                    <select id="system_setting_secure_backend_via_ssl" name="system_setting_secure_backend_via_ssl">
                        <option value='0'>No</option>
                        <option value='1'>Yes</option>
                    </select>
                </li>
				<li><label class='step3' for="system_setting_secure_frontend_via_ssl">User Logins Secured via SSL:</label>
                    <select id="system_setting_secure_frontend_via_ssl" name="system_setting_secure_frontend_via_ssl">
                        <option value='0'>No</option>
                        <option value='1'>Yes</option>
                    </select>                
                </li>
			</ul>
        
			<input type="hidden" name="database_host" value="<?=$_POST['database_host']?>" />
			<input type="hidden" name="database_name" value="<?=$_POST['database_name']?>" />
			<input type="hidden" name="database_username" value="<?=$_POST['database_username']?>" />
			<input type="hidden" name="database_password" value="<?=$_POST['database_password']?>" />
			<?php foreach ($_POST as $key => $value) {
				$pkInfo = explode("__", $key);
				if ($pkInfo[0] == "product_key") {
				  print "<input type='hidden' name='{$key}' value='{$value}' />\n";
				}
			}
			?>
			<input type="hidden" name="next_step" value="done" />
			<input type="hidden" name="cmd" value="numo_install" />
			<input class="submit_button" type="image" src="images/next_arrow_icon.jpg" name="nocmd" alt="Next" />
		</form>

		<?php
		/************************************************
		INSTALLATION START
			--> CHECK INSTALLATION ENVIRONMENT
		************************************************/
		} else {
			$systemCheckError = false;

		?>
			<div class="content_header"><img src="images/install_check_icon.jpg" align="left" /><h2>System Check</h2></div>
			<p>Check to make sure server settings will allow the installation to be completed.</p>

			<?php
			/******** CHECK TO ENSURE THAT THE SYSTEM IS DEPLOYED TO A UNIX/LINUX ENVIRONMENT *********/

			$operatingSystem = "Unknown";
			if ($PHP_OS != "") {
				//print "php_os: $PHP_OS"; 
			  $operatingSystem = $PHP_OS;
			} else if (php_uname('s') != "") {
				//print "php_uname: ".php_uname('s');
			  $operatingSystem = php_uname('s');
			} else if (DIRECTORY_SEPARATOR == "/") {
				$operatingSystem = "Unix/Linux";
				
			} else if (DIRECTORY_SEPARATOR == '\\') {
				$operatingSystem = "WIN";
			}
 
 			$localhostRunningApache = $_SERVER['HTTP_HOST'] == "localhost" && strstr($_SERVER['SERVER_SOFTWARE'], "Apache");
			if ($operatingSystem == "Unknown") {
			?>
							<div class="passed_box">
							<img src="images/install_check_passed_icon.jpg" align="left" />
							<h2>Operating System</h2>
							<p>The system could not detect your operating system.<br/><br/>  Ensure that your website is hosted on a Unix/Linux web server with php/mysql and .htaccess files enabled (and no FrontPage Extensions running).</p>
							</div>
            <?php
			} else if(strtoupper(substr($operatingSystem, 0, 3)) !== 'WIN') {
			?>
				<div class="passed_box">
				<img src="images/install_check_passed_icon.jpg" align="left" />
				<h2>Operating System</h2>
				<p>The system has detected that your operating system is "<?=$operatingSystem?>".</p>
				</div>
			<?php
			} else {
				if ($localhostRunningApache) {
				$systemCheckError = false;
				$failOS = false;
			?>
				<div class="passed_box">
				<img src="images/install_check_passed_icon.jpg" align="left"  />
				<h2>Operating System</h2>
				<p>The system has detected that you are attempting to run it on a Windows server (<?=$operatingSystem?>) with Apache in a "localhost" environment.</p>
				<p>As per the requirements stated <a href='http://www.i3dthemes.com/website-plugins/' target='_blank'>at the point of sale</a>, this system is designed and tested on a Unix/Linux web server running php/mysql and .htaccess files enabled (without MSFP Extensions running).</p>
                <p>However, we have determined that in all likelyhood, if you are running WAMP on a Windows Machine, the Numo system should operate as expected.</p>
				<p>As we can only troubleshoot issues with LIVE servers, however, if you encounter any bugs or errors while testing on yoru local machine via "localhost", we will be unable to provide
                any support for those issues.</p>
				</div>
<?php					
				} else {
					
					
				$systemCheckError = true;
				$failOS = true;
			?>
				<div class="error_box">
				<img src="images/install_check_error_icon.jpg" align="left"  />
				<h2>Operating System</h2>
				<p>The system has detected that you are attempting to run it on a Windows server (<?=$operatingSystem?>).</p>
				<p>As per the requirements stated <a href='http://www.i3dthemes.com/website-plugins/' target='_blank'>at the point of sale</a>, this system requires a Unix/Linux web server running php/mysql and .htaccess files enabled (without MSFP Extensions running).</p>
				<p>In order to continue with the installation, please have your hosting company switch you over to a Linux/Unix web server with the requirements listed above.</p>
				</div>
			<?php
				}
			}

            if (!$failOS) {
				/******** CHECK TO ENSURE THAT PHP SHORT TAGS ARE ENABLED *********/

				$shortTagsEnabled = ini_get("short_open_tag");

				if($shortTagsEnabled) {
				?>
					<div class="passed_box">
					<img src="images/install_check_passed_icon.jpg" align="left" />
					<h2>Short Tags Enabled</h2>
					<p>The system has detected that PHP Short Tags are Enabled.</p>
					</div>
				<?php
				} else {
					$systemCheckError = true;
				?>
					<div class="error_box">
					<img src="images/install_check_error_icon.jpg" align="left" />
					<h2>Short Tags Not Enabled</h2>
					<p>The system has detected that PHP short tags are not enabled..</p>
					<p>You will need to add a line to your php.ini file (or contact your web host) and have the following parameter added or changed:</p>
					<p class="file_path">short_open_tag	= On</p>
					</div>
				<?php
				}


				/******** CHECK TO ENSURE THAT THE DATABASE CONNECTION INFORMATION FILE IS WRITABLE *********/
				$databaseConfigurationFile = "configuration/database_connection_information.php";

				// attempt to change the permissions
				if (!is_writable($databaseConfigurationFile)) {
				  @chmod ($databaseConfigurationFile, 0777);
				}         
				
				if(is_writable($databaseConfigurationFile)) {
				?>
					<div class="passed_box">
					<img src="images/install_check_passed_icon.jpg" align="left" />
					<h2>Write Permissions</h2>
					<p>The system has detected that it is able to write to the configuration file.</p>
					</div>
				<?php
				} else {
					$systemCheckError = true;
				?>
					<div class="error_box">
					<img src="images/install_check_error_icon.jpg" align="left" />
					<h2>Write Permissions</h2>
					<p>The system has detected that it is unable to write to files on the server.  To continue with the installation please update the permissions on the following file to allow all 3 groups at least read and write permissions.</p>
					<p class="file_path"><b>File:</b> numo/<?=$databaseConfigurationFile?></p>
					<p class="note">Note: File permissions can generally be updated through your hosting control panel or FTP client.</p>
					</div>
				<?php
				}
				?>


				<?php
				
				if(true || @is_writable(session_save_path()) || @is_writable($secondarySavePath)) {
				?>
					<div class="passed_box">
					<img src="images/install_check_passed_icon.jpg" align="left" />
					<h2>Server Sessions</h2>
					<p>The module has detected that it is able to save session information.</p>
					</div>
				<?php
				} else {
					$systemCheckError = true;
				?>
					<div class="error_box">
					<img src="images/install_check_error_icon.jpg" align="left" />
					<h2>Server Sessions</h2>
					<p>This system has detected that it is unable to save session information to the default location set by your hosting company.  To continue with the installation please contact your hosting company so that they can look into why you are unable to save session information on the server.  The location your hosting company has set for session information to be stored in is:</p>
					<p class="file_path"><b>Location:</b> <?=session_save_path()?></p>
					<p class="note">Note: File permissions can generally be updated through your hosting control panel or FTP client.</p>
                   
    
                        <h2>OR</h2>
                        <p>You can optionally use internal session handling.  To set this up, enable write permissions on the following folder:</p>
                        <p class="file_path"><b>Location:</b> "numo/sessions"</p></p>
                       
                    </div>
				<?php
				}
				
				//php_value session.save_path "/root"

                 //print session_save_path();
				if(!$systemCheckError) {
				?>
				<form method="post">
					<input type="hidden" name="next_step" value="1" />
					<input type="hidden" name="cmd" value="numo_install" />
					<input class="submit_button" type="image" src="images/next_arrow_icon.jpg" name="nocmd" alt="Next" />
				</form>
			<?php
				}
			}
		}
		?>
	</div>
</body>
</html>