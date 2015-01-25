<style>
ul.form_display li label{ width:230px; padding-right: 5px;}
ul.form_display li {line-height: 20px; vertical-align: bottom;}
.checkbox_list {padding-left: 5px;}
.error {color: #900; font-weight: bold;}
ul.import_report {list-style-type: none; margin:0px; padding: 0px;}
.import_report li {font-size: 11px;}
</style>
<h2>Import Users</h2>
<?php
$step = 1;
$processSuccessCount = 0;
$processErrorCount = 0;

$csvFileName = "";
$simpleImportReport = "";
$detailedImportReport = "<li>Line 1: CSV file headers</li>";
$csvFileHeadingOptions = "";

if($_POST['process'] == "csv_file" && $_FILES['file']) {
	
			if (REMOTE_SERVICE === true) {
			  $uploadsDir = ABSOLUTE_ROOT_PATH."dashboard/uploads/modules/accounts";
			} else {
			  $uploadsDir = "modules/accounts/uploads";
			}
	
	
	$csvFileName = "{$uploadsDir}/u.".$_SESSION['account_id'].".".time().".".$_FILES['file']['name'];
	move_uploaded_file($_FILES['file']['tmp_name'], $csvFileName);
	$csvContents = file($csvFileName,FILE_SKIP_EMPTY_LINES);
	$fileHeadings = explode(",",$csvContents[0]);

	foreach($fileHeadings as $position => $heading) {
		$csvFileHeadingOptions .= '<option value="'.$position.'">'.$heading.'</option>';
	}

	$step = 2;
} else if($_POST['cmd'] == "import") {
	//load in csv file
	$csvContents = file($_POST['csv_file_name'],FILE_SKIP_EMPTY_LINES);


	//remove the first line from the file as it identifies the following rows and shouldn't be processed
	unset($csvContents[0]);
	
	// get product fields
	$slotMap = array();
	foreach ($_POST as $key => $value) {
		//print $key."=".$value."<br>";
		if ($value != "" && strstr($key, "_header_position")) {
		  $slot = str_replace("_header_position", "", $key);	
		  //$slot = str_replace("slot_", "", $slot);
		  $slotMap["$slot"] = $value;
		  //print $slot."=".$value."<br>";
		}
	}
	//exit;

	foreach($csvContents as $lineNumber => $line) {
		$line = trim($line);

		//split string by comma (,) but ignore commas surrounded by double quotes
		$listing = preg_split('/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/', $line, 0, PREG_SPLIT_DELIM_CAPTURE);
       // var_dump($listing);
		foreach($listing as $key => $value) {
			$value = trim($value);

			if(substr($value,0,1) == '"') {
				$value = str_replace('""','"',substr($value, 1, -1));
			}
			
			

			$listing[$key] = $value;
		}

		$insertStr = "";
		$insertKeyString = "INSERT INTO accounts (type_id, pending, activated";
		$insertValueString = ") VALUES ('{$_POST['account_type_id']}', '{$_POST['pending']}','{$_POST['activated']}'";
		
		foreach ($slotMap as $slot => $headerPosition) {
			$insertKeyString .= ", {$slot}";
			if ($slot == "slot_2") {
			  $insertValueString .= ", '".crypt($listing[$headerPosition])."'";
				
			} else {
			  $insertValueString .= ", '".$listing[$headerPosition]."'";
			}
		}
		$insertString = $insertKeyString.$insertValueString.")";
		$dbObj->query($insertString);
		//print $insertString."<br>";
		$listingID = mysql_insert_id();
		

		//add to process report strings
		$detailedImportReport .= '<li>Line '.($lineNumber+1).': Account "'.$listing[$slotMap['slot_1']].'" created.</li>';
		$processSuccessCount++;

	}

	$step = 3;
}


//Step 1: File Upload
if($step == 1) {
?>
<form method="post" enctype="multipart/form-data">
	<fieldset>
	<legend>Step 1: Upload CSV File</legend>
	<ul class="form_display">
		<li><label for="import_csv_file">File:</label><input type="file" name="file" id="import_csv_file" /></li>
		<li><label for="account_type_id">Import Account As:</label>
        <select name="account_type_id" id="account_type_id">
        <?php $query = "SELECT * FROM types WHERE site_id='".NUMO_SITE_ID."' ORDER BY name";
		$result = $dbObj->query($query);
		while ($accountTypeRecord = mysql_fetch_array($result)) { ?>
        <option value="<?php echo $accountTypeRecord['id']; ?>"><?php echo $accountTypeRecord['name']?></option>
        <?php } ?>
        </select>
        </li>
	</ul>
	<div style="clear: both;"><br /></div>
    <p>Please ensure that you are importing, minimally, the following fields: Name, Email Address, Username, Password</p>
	<input type="submit" name="nocmd" id="submit_input" style='margin-left: 240px;' class='btn btn-default btn-primary' value="Continue" />
	</fieldset>

	<input type="hidden" name="process" value="csv_file" />
  
</form>
<?php
//Step 2: Ask what field (email or name) their headers match to.  Request list(s) to import into as well
} else if($step == 2) {
?>
<form method="post">
<input type='hidden' name='account_type_id' value='<?php echo $_POST['account_type_id']; ?>' />
	<fieldset>
	<legend>Step 2: Import Mapping</legend>
	<ul class="form_display">
    <?php $query = "SELECT * FROM fields WHERE type_id='{$_POST['account_type_id']}' ORDER BY position";
	$result = $dbObj->query($query);
	while ($fieldRecord = mysql_fetch_array($result)) {
		?>
		<li><label for="import_slot_<?php echo $fieldRecord['slot']; ?>_heading"><?php echo $fieldRecord['name']; ?> <?php if ($fieldRecord['slot'] <= 4) { ?>*<?php } ?></label><select name="slot_<?php echo $fieldRecord['slot']; ?>_header_position" id="import_slot_<?php echo $fieldRecord['slot']; ?>_heading"><?php if ($fieldRecord['slot'] > 4) { ?><option value=''>-- skip -- </option><?php } ?><?=$csvFileHeadingOptions?></select></li>
    <?php } ?>
		
		<li><h3>General</h3></li>
	
			<li><label for='status'>Approval Status Once Imported</label><select id='pending' name='pending'><option value='1'>Pending</option><option value='0'>Approved</option></select></li>
			<li><label for='status'>Activation Status Once Imported</label><select id='activated' name='activated'><option value='0'>Pending</option><option value='1'>Activated</option></select></li>

	</ul>
	<div style="clear: both;"><br /></div>
	<input type="submit" name="nocmd" id="submit_input" class='btn btn-success' style='margin-left: 240px;' value="Import" />
	</fieldset>

	<input type="hidden" name="cmd" value="import" />
	<input type="hidden" name="csv_file_name" value="<?=$csvFileName?>" />
</form>
<?php
//Step 3: Display report of any error messages and a tally of added subscribers
} else if($step == 3) {
?>
<fieldset>
<legend>Finished: Account Import Results</legend>
<p>Accounts Successfully Imported: <?=$processSuccessCount?></p>
<?php
	$errorReportString = '';

	if($processErrorCount > 0) {
		$errorReportString = '<h2>Import Errors</h2><ul class="import_report">'.$simpleImportReport.'</ul>';
	}

  //  print "<p>Please note: you will still need to upload photos via the listing management page</p>";
	print '<div id="simple_import_report">'.$errorReportString.'<input type="button" name="nocmd" class="btn btn-default" value="Show Detailed Report"	onclick="document.getElementById(\'detailed_import_report\').style.display =\'block\'; document.getElementById(\'simple_import_report\').style.display =\'none\';" /></div>';
	print '<div id="detailed_import_report" style="display: none;"><h2>Detailed Import Report</h2><ul class="import_report">'.$detailedImportReport.'</ul><input class="btn btn-default" type="button" name="nocmd" value="Return To Summary"	onclick="document.getElementById(\'simple_import_report\').style.display =\'block\'; document.getElementById(\'detailed_import_report\').style.display =\'none\';" /></div>';
?>
</fieldset>
<?php
}
?>