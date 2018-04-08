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
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/settings/general/">System</a> <span class="divider">/</span></li>
  <li class="active">Update System</li>
</ul>
<h3>Update System</h3>  
<div class="well">
<h4>System Update Status</h4>
<?php if ($_GET['fetch'] == "upgrades") { ?>
<div class='alert alert-success'><h4>Your upgrade is nearly complete!</h4>
<p>All you need to do now is run an update to grab the latest files from our server.</p>
</div>
<?php } ?>
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
<input class='form-submit btn btn-large btn-primary' id='update-submit' type='submit' value='Check For Updates' />
<? if ($lastUpdateTime != "0000-00-00 00:00:00") { ?>
<br/>
or
<br/>
<input class='form-submit btn btn-large' id='update-submit-all' name="cmd2" type='submit' value='Update All Files' />

<? } ?>
</center>
</form>
</div>

<script> 

jQuery("form#check-for-updates-form input[type=submit]").click(function() {
    jQuery("input[type=submit]", jQuery(this).parents("form")).removeAttr("clicked");
    jQuery(this).attr("clicked", "true");
});
jQuery("form#check-for-updates-form").submit(function(event) {
													var the_form = $(this);
													var data = the_form.serialize();
													var url = the_form.attr( 'action' );
													var button = event.originalEvent.explicitOriginalTarget;
													//alert(button);
													var value = jQuery("input[type=submit][clicked=true]").val();
													//alert(value);
												    $('input[type=submit]', this).attr('disabled', 'disabled');
													checkForUpdates(value); 

												  return false;
												  });
function checkForUpdates(submitValue) { 
	args = "";
	if (submitValue == "Update All Files") {
		var lastUpdateTime = "";
	} else {
		var lastUpdateTime = "<?php print $lastUpdateTime; ?>";
	} 
		$.get("<?php print NUMO_FOLDER_PATH; ?>?display=response_only&m=settings&i=update", args + "updater_version=1&last_update_time=" + lastUpdateTime, 
					function(data) {			
					//alert("done");
					//alert(data);
		  			    jQuery("#update-submit").attr("disabled", false); 
						//alert("x");
		  			    jQuery("#update-submit-all").attr("disabled", false); 
						//alert("y");
						
						jQuery("#progress-data").html(data);		
//alert("next");
/*
					alert("done2"); 

						   jQuery(".settings-update-info-box").each(function() {
																		if (jQuery(this).height() > maxDivHeight) {
																			maxDivHeight = jQuery(this).height();
																			//alert($(this).height());
																		}
						  
						   });
					alert("done3"); 
						    jQuery(".settings-update-info-box").height(maxDivHeight);
					alert("done4"); 
						  				
										*/
                       // alert(data);
//alert("x");  
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
						   $(".settings-update-info-box-new").each(function() {
																		if ($(this).height() > maxDivHeight) {
																			maxDivHeight = $(this).height();
																		}
						  
						   });						   
						    $(".settings-update-info-box").height(maxDivHeight);
						    $(".settings-update-info-box-new").height(maxDivHeight);
						   });
</script>

<br/>
<div id='progress-data' style='display: inline-block; vertical-align: top;'></div>
<div id='progress-data-2' style='display: inline-block; vertical-align: top;'></div>

<?php
function callUpdateServer($data) {
	//var_dump($data); 
	global $dbObj; 

	  $useFopen = false;
	  $testFile = 'test-'.time();
	  $testFp = @fopen($testFile, 'w');
	  if ($testFp) {
		if (getmyuid() == @fileowner($testFile)) {
			$useFopen = true;
		}
		@fclose($testFp);
		@unlink($testFile);
	  }


	$query = "SELECT * FROM modules WHERE site_id='".NUMO_SITE_ID."'";
	$moduleKeyResponse = $dbObj->query($query);
	$licenseKeys = "";
	$modules = "";
	$pendingModules = "";
	while ($moduleKeyRecord = mysql_fetch_array($moduleKeyResponse)) { 

	   $licenseKeys .= ($moduleKeyRecord['license_key'] != "" && !strstr($licenseKeys, $moduleKeyRecord['license_key']) ? $moduleKeyRecord['license_key']."," : "");	  

		$modules     .= (!strstr($modules, $moduleKeyRecord['name']) ? $moduleKeyRecord['name']."," : "");
		if ($moduleKeyRecord['status'] == 2) {
	      $pendingModules .= (!strstr($pendingModules, $moduleKeyRecord['name']) ? $moduleKeyRecord['name']."," : "");	  
		}
		//print $moduleKeyRecord['name']."-".$moduleKeyRecord['status']."<br>";
	}
	$licenseKeys = rtrim($licenseKeys, ",");
	$modules = rtrim($modules, ",");
	$pendingModules = rtrim($pendingModules, ",");
	
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
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'lpk='.urlencode($licenseKeys).'&method='.($useFopen ? "direct" : "ftp").'&pending_modules='.urlencode($pendingModules).'&modules='.urlencode($modules).'&domain='.urlencode($_SERVER["HTTP_HOST"])."&updater_version=1&last_update_time=".$_GET["last_update_time"].$args); //pass product license key and domain name along to be checked

	//send request and save response to variable
	$response = @curl_exec($ch);
	//print "test";
	if ($useFopen && $_GET['cmd'] == "install") {
	//print $response; 
		//print strlen($response)."<br>";
		//print $response;  
	$saveFileName = "update-".time().".zip"; 
	//print realpath("../../".$saveFileName)."<br>";
	$fp = fopen("../{$saveFileName}", 'w'); 
	//if ($fp) { 
	//	print "file was succesfully opened<br>";
	//}
	fwrite($fp, $response);
	fclose($fp); 

    // $newFileName = $_GET['mod']."_completed.zip";
	//print "trying to rename {$saveFileName} to {$newFileName}<br/>";
    // rename("modules/{$saveFileName}", "modules/{$newFileName}");
	//sleep(1);
    // unzip
	$zip = new ZipArchive;
	$res = $zip->open("../".$saveFileName);
	$path = pathinfo(realpath("../".$saveFileName), PATHINFO_DIRNAME);
	//print $path."<br>";
	//print $res."<br>";
    if ($res === TRUE) {
      $zip->extractTo($path."/");
      $zip->close();
    //  echo 'woot!';
    } else {
    //  echo 'doh!';
    }
  // do cleanup
  @unlink("../{$saveFileName}");

  $pending = explode(",", $pendingModules);
  //var_dump($pending);
  // run install
  foreach ($pending as $pendingMod) {
	  $sqlLines = file("modules/{$pendingMod}/configuration/initialization.sql");
	  foreach ($sqlLines as $line) {
		  
		  if (!strstr($line, "INSERT INTO `modules`")) {
			  $line = str_replace("NUMO_SITE_ID", NUMO_SITE_ID, $line);
			// print $line.'<br>';
			$dbObj->query($line);	  
		  }
	  }
	  
	    // change status
	  $update = "UPDATE modules SET status=1 WHERE name='{$pendingMod}' AND site_id='".NUMO_SITE_ID."'";
	  //print $update;
	  $dbObj->query($update);
	  
  }
  
  print '<div class="settings-update-info-box-new well span4">
  <i class="fa fa-check-circle"></i>
  
  Done!</div>
  
  <script>
    function goreload() {
    document.location.href="'.NUMO_FOLDER_PATH.'";
	}
	setTimeout("goreload()", 2000); 
  </script>
  ';
  
  
		  $update = "UPDATE sites SET last_updated='".date("Y-m-d H:i:s")."' WHERE id='".NUMO_SITE_ID."'";
		 // print $update;
		  $dbObj->query($update);


	} else {
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
	  print curl_error($ch);

	//error with license key provided 
	} else {
		
	  $response = str_replace('NUMO_FOLDER_PATH', NUMO_FOLDER_PATH, $response);  
	  $response = str_replace('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'], $response);  
		
		
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
	}

	curl_close($ch); //close curl connection
	return ""; //success	
}
?>