<?php
// make curl connection to upgrade server
if (!fetch_pending_upgrades()) {
	fetch_available_upgrades();
}
function fetch_licenses_for_transaction($transactionID, $verificationID) {
	$ch = curl_init(); //init
    global $dbObj;

    $modules = array();
	curl_setopt($ch, CURLOPT_URL, 'http://numo.server-apps.com/upgrade/query-upgrades/'); //setup request to website to check license key
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // return the response
	curl_setopt($ch, CURLOPT_POST, 1); //transfer information as a POST request
	curl_setopt($ch, CURLOPT_POSTFIELDS, '&tid='.$transactionID.'&tos='.$verificationID); //pass product license key and domain name along to be checked

	//send request and save response to variable
	$response = @curl_exec($ch);
	//print "response: $response";
    $data = explode("\n", $response);
	foreach ($data as $line) {
	  parse_str($line, $license);	
	  $modules[] = $license;
	}
	//var_dump($modules);  
	return $modules;
   // print $response;
	//return ""; //success
}

function fetch_pending_upgrades() {
  global $dbObj;
  
  $query = "SELECT * FROM pending_upgrades WHERE site_id='".NUMO_SITE_ID."'";
  $result = $dbObj->query($query);
  
//  print mysql_num_rows($result);
  if (mysql_num_rows($result) > 0) {
	 
	  
	   while ($record = mysql_fetch_array($result)) {
		  // var_dump($record);
		  $licenses = fetch_licenses_for_transaction($record['transaction_id'], $record['verification_id']);
		  
		  foreach ($licenses as $licenseData) {
			$insert = "INSERT INTO modules (site_id, name, license_key, status) VALUES ('".NUMO_SITE_ID."', '{$licenseData['name']}', '{$licenseData['key']}', '2')";
			$dbObj->query($insert);
			//print $insert."<br>";
			//print mysql_error();
		  }
		  $dbObj->query("DELETE from pending_upgrades WHERE transaction_id='{$record['transaction_id']}'");
		  
		  //print "have ".$record[transaction_id]." :: ".$record['verification_id']."<br>"; 
	  }  
	  
  } 
  
  
  $query  = "SELECT * FROM modules WHERE status='2'"; 
  $result =  $dbObj->query($query);
   // print mysql_num_rows($result);

  if (mysql_num_rows($result) == 0) {
	  return false;
  }
  
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
  
  if (!$useFopen) {
	header("Location: ".NUMO_FOLDER_PATH."module/settings/update/?fetch=upgrades");
		   exit;
  }

  ?>
  <script>
  //alert("yup");
  var totalToGo = 0;
  function startFetch(module, key) {
	var now = new Date(); 
    totalToGo = totalToGo + 1;
	$.get("<?php print NUMO_FOLDER_PATH; ?>module/settings/configuration/fetch-upgrade/",  "display=response_only&mod=" + module + "&key=" + key + " +&t=" + now.getTime(), function(data){; });
	  
  }
  
  function pollStatus(module, key) {
	  //alert("polling");
	  		var now = new Date(); 

	$.get("<?php print NUMO_FOLDER_PATH; ?>module/settings/configuration/poll-upgrade-status/",  "display=response_only&mod=" + module + "&key=" + key + " +&t=" + now.getTime(), 
		 function(data) {
			// alert(data);
			 if (data == "DONE") {
				 jQuery('#' + module + "__progress").html('<div class="progress progress-success progress-striped"><div class="bar" style="width: 100%;"></div></div>');
				 jQuery('#' + module + "__status").html('Installation Complete');
				 totalToGo = totalToGo - 1;
				 
				 if (totalToGo == 0) {
					document.location = "<?php print NUMO_FOLDER_PATH; ?>";
				 }
			 } else {
				var newData = data.split("\n");
				jQuery('#' + module + "__progress").html('<div class="progress progress-info progress-striped active" style="margin-bottom: 5px;"><div class="bar" style="width: ' + newData[0] + '%;"></div></div>');
				 jQuery('#' + module + "__status").html(newData[1]);
									 
				setTimeout('pollStatus("' + module + '", "' + key + '")', 2000);
									
			  }
			}
	);  
  }
  </script>
  <table class='table table-bordered table-striped'>
    <tr>
      <td>Module</td>
      <td>Progress</td>
      <td>Status</td>
    </tr>
  <?php
  $query  = "SELECT * FROM modules WHERE status='2'"; 
  $result =  $dbObj->query($query);
  while ($record = mysql_fetch_array($result)) {
	  ?>
      <script>
	    
							setTimeout('pollStatus("<?php print $record['name']; ?>", "<?php print $record['license_key']; ?>")', 2000);  
							setTimeout('startFetch("<?php print $record['name']; ?>", "<?php print $record['license_key']; ?>")', 1000);;  
					

	  </script>
     <tr>
      <td><?php echo ucwords(str_replace(array("handler", "sections","_"), array("wizard", "wizard"," "), $record['name'])); ?></td>
      <td id="<?php echo $record['name']."__progress"; ?>"></td>
      <td id="<?php echo $record['name']."__status"; ?>"></td>
    </tr>     
      <?php
  }
  ?>
  </table>
  <?php
  return true;
}


?>