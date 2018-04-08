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
  
  if (!is_dir("modules/{$_GET['mod']}")) {
	  if (file_exists("modules/{$_GET['mod']}.zip")) {					
			print "10\n";
			print "Fetching Files";
	  } else if (file_exists("modules/{$_GET['mod']}_completed.zip")) {		
			print "40\n";
			print "Unzipping Package";
	  } else {
			print "5\n";
			print "Starting";
	  }
	  
  } else {
	 if (file_exists("modules/{$_GET['mod']}_completed.zip")) {		
			print "80\n";
			print "Initializing";
	  } else {
			print "100\n";
			print "Done!";
	  }
	  

	  
  }
  exit;
}
?>