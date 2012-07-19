<?php
if ($_GET['display'] == "response_only") {
	//if ($_GET['cmd'] == "install") {
	//  callUpdateServer($_GET);	
	//} else {
	  callUpdateServer($_GET);
	  exit;
	//} 
}

$lastUpdateTime = $siteData['last_updated'];

?>
<style>
div.file-list { background-color: #ffffff; font-size: 9px; height: 200px; overflow: auto; width: 250px; border: 1px solid #cccccc; border-radius: 3px; -webkit-border-radius: 3px; -moz-border-radius: 3px; }
div.file-list ul {  border-left: 1px solid #ccc; list-style-type: none; margin: 0px; padding: 0px; padding-left: 7px; margin-left: 7px;}
div.file-list li { padding: 0px; margin: 0px; }
div.file-list span.file { margin-left: 0px; font-family: "Courier New", Courier, monospace; }
div.file-list span.folder { display: block; margin-left: 0px; font-family: "Courier New", Courier, monospace; }
ul.installed-modules { font-size: 8pt; }
ul.to-be-deployed-modules { font-size: 8pt; } 
ul.licensed-modules { font-size: 8pt; } 
.unlicensed-message { font-style: italic; font-size: 8pt; }
.form-submit { float: none !important; }
</style>
<h2>Update System</h2>  
<div class="settings-option-box">
<h2>System Update Status</h2>
<p>
<?php
if ($lastUpdateTime == "0000-00-00 00:00:00") { 
   print "This system has never been updated.  It is advised that you run an update to be sure you have the most current files.";
} else { 
  print "This system was last updated on ".date("F j, Y", strtotime($lastUpdateTime))."."; 
}
?></p>

<form method="get" id='check-for-updates-form'>
<center>
<input class='form-submit' id='update-submit' type='submit' value='Check For Updates' />
<? if ($lastUpdateTime != "0000-00-00 00:00:00") { ?>
<br/>
or
<br/>
<input class='form-submit' id='update-submit-all' name="cmd2" type='submit' value='Update All Files' />

<? } ?>
</center>
</form>
</div> 
<script> 
jQuery("form#check-for-updates-form").submit(function(event) {
													var the_form = $(this);
													var data = the_form.serialize();
													var url = the_form.attr( 'action' );
													var button = event.originalEvent.explicitOriginalTarget;
													//alert(button.value);
												    $('input[type=submit]', this).attr('disabled', 'disabled');
													checkForUpdates(button.value);

												  return false;
												  });
function checkForUpdates(submitValue) { 
	args = "";
	if (submitValue == "Update All Files") {
		var lastUpdateTime = "";
	} else {
		var lastUpdateTime = "<?php print $lastUpdateTime; ?>";
	}
		$.get("<?php print NUMO_FOLDER_PATH; ?>?display=response_only&m=settings&i=update", args + "last_update_time=" + lastUpdateTime, 
					function(data) {			
						$("#progress-data").html(data);		
		  			    $(".form-submit").attr("disabled", false); 

						   $(".settings-update-info-box").each(function() {
																		if ($(this).height() > maxDivHeight) {
																			maxDivHeight = $(this).height();
																			//alert($(this).height());
																		}
						  
						   });
						    $(".settings-update-info-box").height(maxDivHeight);
						  				
                       // alert(data);

					}
					);
}

var maxDivHeight = 0;
$(document).ready(function() {
						   $(".settings-update-info-box").each(function() {
																		if ($(this).height() > maxDivHeight) {
																			maxDivHeight = $(this).height();
																		}
						  
						   });
						    $(".settings-update-info-box").height(maxDivHeight);
						   });
</script>
<br/>
<div id='progress-data' style='display: inline-block; vertical-align: top;'></div>
<div id='progress-data-2' style='display: inline-block; vertical-align: top;'></div>

<?php
function callUpdateServer($data) {
	global $dbObj; 
	
	$query = "SELECT * FROM modules WHERE site_id='".NUMO_SITE_ID."'";
	$moduleKeyResponse = $dbObj->query($query);
	$licenseKeys = "";
	$modules = "";
	while ($moduleKeyRecord = mysql_fetch_array($moduleKeyResponse)) { 
	    $licenseKeys .= ($moduleKeyRecord['license_key'] != "" && !strstr($licenseKeys, $moduleKeyRecord['license_key']) ? $moduleKeyRecord['license_key']."," : "");	  
	    $modules     .= (!strstr($modules, $moduleKeyRecord['name']) ? $moduleKeyRecord['name']."," : "");	  
	
	}
	$licenseKeys = rtrim($licenseKeys, ",");
	$modules = rtrim($modules, ",");
	if ($_GET['cmd2'] == "Update All Files") {
		//print "x";
		$_GET['last_update_time'] = "";
	}
	if ($_GET['cmd'] == "install") {
	  $args = "&cmd=install"; 
	 // $args .= "&; 
	  if ($_GET['step'] != "") {
		  $args .= "&step=".$_GET["step"];
		  $args .= "&error=".$_GET["error"];
		  $args .= "&h=".$_GET["h"];
		  $args .= "&u=".$_GET["u"];
		  $args .= "&p=".$_GET["p"];  
		  $args .= "&lpk_v=2";  
		  $args .= "&document_root=".$_GET["document_root"]; 
		  $args .= "&numo_folder_path=".$_GET["numo_folder_path"]; 
	  } 
	} 
	
	$ch = curl_init(); //init

	curl_setopt($ch, CURLOPT_URL, 'http://numo.server-apps.com/update/'); //setup request to website to check license key
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // return the response
	curl_setopt($ch, CURLOPT_POST, 1); //transfer information as a POST request
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'lpk='.urlencode($licenseKeys).'&modules='.urlencode($modules).'&domain='.urlencode($_SERVER["HTTP_HOST"])."&last_update_time=".$_GET["last_update_time"].$args); //pass product license key and domain name along to be checked

	//send request and save response to variable
	$response = @curl_exec($ch);
	$response = str_replace('NUMO_FOLDER_PATH', NUMO_FOLDER_PATH, $response);  
	$response = str_replace('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'], $response);  

	/*

	------------------------
	|||  RESPONSE CODES  |||
	------------------------

	-1 = License key is not valid for the product it was entered for (i.e. license code for members module provided when installing a blog module)
	-2 = License already used for an installation on a different domain name
	-3 = Invalid domain name passed along
	 0 = Product license key does not exist (could not be found)
	 1 = License already used for an installation for THIS domain name
	 2 = License never used, record created at LM

	*/

	//check to see if the curl request completed without error
	if(curl_errno($ch)) {

	//error with license key provided 
	} else {
		print $response;
		//print "step:".$_GET['step'];
		if ($_GET['step'] == 6) {
		  $update = "UPDATE sites SET last_updated='".date("Y-m-d H:i:s")."' WHERE id='".NUMO_SITE_ID."'";
		 // print $update;
		  $dbObj->query($update);
		 // print mysql_error();
		}
		//curl_close($ch); //close curl connection

	}

	curl_close($ch); //close curl connection
	return ""; //success	
}
?>