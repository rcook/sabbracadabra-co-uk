<?php
// query final status
$query = "SELECT * FROM modules WHERE name='{$_GET['mod']}' AND license_key='{$_GET['key']}'";
//print $query;
$result = $dbObj->query($query);
$record = mysql_fetch_array($result);

if ($record['status'] == "1") {
  print "DONE";
  exit;
} else if ($record['status'] == "2") { 
	$ch = curl_init(); //init 

  // invoke fetch
  	curl_setopt($ch, CURLOPT_URL, 'http://numo.server-apps.com/upgrade/fetch/'); //setup request to website to check license key
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // return the response
	curl_setopt($ch, CURLOPT_POST, 1); //transfer information as a POST request
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'type=single&license='.trim($_GET['key']).'&mod='.trim($_GET['mod'])); //pass product license key and domain name along to be checked
 
	//send request and save response to variable 
	$response = curl_exec($ch);
	//print curl_error($ch);
	
	$saveFileName = $_GET['mod'].".zip";
	//print realpath("../../".$saveFileName)."<br>";
	$fp = fopen("modules/{$saveFileName}", 'w'); 
	fwrite($fp, $response);
	fclose($fp); 
	//print $response;

    $newFileName = $_GET['mod']."_completed.zip";
	//print "trying to rename {$saveFileName} to {$newFileName}<br/>";
    rename("modules/{$saveFileName}", "modules/{$newFileName}");
	//sleep(1);
    // unzip
	$zip = new ZipArchive;
	$res = $zip->open("modules/".$newFileName);
	$path = pathinfo(realpath("modules/".$newFileName), PATHINFO_DIRNAME);
	//print $path."<br>";
	//print $res."<br>";
    if ($res === TRUE) {
      $zip->extractTo($path."/");
      $zip->close();
    //  echo 'woot!';
    } else {
    //  echo 'doh!';
    }

  
  // run install
  $sqlLines = file("modules/{$_GET['mod']}/configuration/initialization.sql");
  foreach ($sqlLines as $line) {
	  if (!strstr($line, "INSERT INTO `modules`")) {
		  $line = str_replace("NUMO_SITE_ID", NUMO_SITE_ID, $line);
	    $dbObj->query($line);	  
	  }
  }
  
  // do cleanup
  @unlink("modules/{$newFileName}");
  
  // change status
  $update = "UPDATE modules SET status=1 WHERE name='{$_GET['mod']}' AND license_key='{$_GET['key']}'";
  $dbObj->query($update);

}
?>