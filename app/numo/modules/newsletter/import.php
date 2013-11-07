<style>
ul.form_display li label{width:auto; padding-right: 5px;}
ul.form_display li {line-height: 20px; vertical-align: bottom;}
.checkbox_list {padding-left: 5px;}
.error {color: #900; font-weight: bold;}
ul.import_report {list-style-type: none; margin:0px; padding: 0px;}
.import_report li {font-size: 11px;}
</style>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li class="active"><a href='module/newsletter/manage-subscribers/'>Newsletter Subscribers</a> <span class="divider">/</span></li>
  <li class="active">Import Subscribers</li>
</ul>

<h2>Import Subscribers</h2>
<?php
$step = 1;
$processSuccessCount = 0;
$processErrorCount = 0;

$csvFileName = "";
$simpleImportReport = "";
$detailedImportReport = "<li>Line 1: CSV file headers</li>";
$csvFileHeadingOptions = "";

if($_POST['process'] == "csv_file" && $_FILES['file']) {
	$csvFileName = "modules/newsletter/uploads/u.".$_SESSION['account_id'].".".time().".".$_FILES['file']['name'];
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

	$sql = "SELECT default_account_group FROM newsletter_settings WHERE site_id='".NUMO_SITE_ID."'";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		$defaultSubscriberGroup = $row['default_account_group'];
	}

	//remove the first line from the file as it identifies the following rows and shouldn't be processed
	unset($csvContents[0]);

	foreach($csvContents as $lineNumber => $line) {
		$line = trim($line);

		//split string by comma (,) but ignore commas surrounded by double quotes
		$subscriber = preg_split('/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/', $line, 0, PREG_SPLIT_DELIM_CAPTURE);

		foreach($subscriber as $key => $value) {
			$value = trim($value);

			if(substr($value,0,1) == '"') {
				$value = str_replace('""','"',substr($value, 1, -1));
			}

			$subscriber[$key] = $value;
		}

		//check to see if the email address value is valid
		if(isValidEmail($subscriber[$_POST['email_header_position']])) {
			//check to see if an account already exists with the email address
			$sql = "SELECT a.id FROM accounts a, `types` t WHERE a.slot_3='".$subscriber[$_POST['email_header_position']]."' AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
			//print $sql."<bR>";
			$result = $dbObj->query($sql);

			if($row = mysql_fetch_array($result)) {
				//ensure account is subscribed to requested lists
				foreach($_POST['lists'] as $key => $value) {
					$sql = "SELECT id FROM newsletter_subscribers WHERE account_id='".$row['id']."' AND subscription_list_id='".$value."'";
					//print $sql."<br>";
					$subscriptionCheck = $dbObj->query($sql);

					if($subscription = mysql_fetch_array($subscriptionCheck)) {
						//already subscribed... do nothing.
					} else {
						$sql = "INSERT INTO newsletter_subscribers (account_id,subscription_list_id) VALUES ('".$row['id']."','".$value."')";
						//print $sql."<br>";
						$dbObj->query($sql);
					}
				}

				//add to process report strings
				$detailedImportReport .= '<li>Line '.($lineNumber+1).': Subscriber "'.$subscriber[$_POST['name_header_position']].'" already has an account using the email address "'.$subscriber[$_POST['email_header_position']].'".  The account is now subscribing to the selected list(s).</li>';
				$processSuccessCount++;

			//no account found with email address continue setting up a new subscriber
			} else {
				//create partial account for subscriber in the system
				$sql = "INSERT INTO accounts (type_id,pending,slot_1,slot_2,slot_3,slot_4) VALUES ('".$defaultSubscriberGroup."',3,'','".crypt(time())."','".$subscriber[$_POST['email_header_position']]."','".$subscriber[$_POST['name_header_position']]."')";
				//print $sql."<br>";
				$dbObj->query($sql);

				//lookup account id
				$sql = "SELECT LAST_INSERT_ID() as 'account_id'";
				//print $sql."<br>";
				$result = $dbObj->query($sql);

				if($row = mysql_fetch_array($result)) {
					if(isset($_POST['lists'])) {
						//add account to subscription lists requested
						foreach($_POST['lists'] as $key => $value) {
							$sql = "INSERT INTO newsletter_subscribers (account_id,subscription_list_id) VALUES ('".$row['account_id']."','".$value."')";
							//print $sql."<br>";
							$dbObj->query($sql);
						}
					}
				}

				//add to process report strings
				$detailedImportReport .= '<li>Line '.($lineNumber+1).': Subscriber "'.$subscriber[$_POST['name_header_position']].'" has been successfully created and added to the list(s).</li>';
				$processSuccessCount++;
			}
		//invalid email address provided
		} else {
			//add to process report strings
			$simpleImportReport .= '<li class="error">Line '.($lineNumber+1).': Subscriber "'.$subscriber[$_POST['name_header_position']].'" could not be created because the email address "'.$subscriber[$_POST['email_header_position']].'" provided is not valid.</li>';
			$detailedImportReport .= '<li class="error">Line '.($lineNumber+1).': Subscriber "'.$subscriber[$_POST['name_header_position']].'" could not be created because the email address "'.$subscriber[$_POST['email_header_position']].'" provided is not valid.</li>';
			$processErrorCount++;
		}
	}

	$step = 3;
}
?>

<?php
$sql = "SELECT default_account_group FROM newsletter_settings WHERE site_id='".NUMO_SITE_ID."'";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
	//do nothing
} else {
 print "<p class='error'>IMPORT CURRENTLY UNAVAILABLE.  In order to use this feature you will need to set a default account group for new subscribers.  To set a default account group for new subscribers please go to the 'Configure' page under the 'Newsletters' menu.  On the configure page look for the section labeled 'Default Account Group', select an account group from the drop down list and click the 'Save Changes' button at the end of the page.</p>";
	exit();
}

//Step 1: File Upload
if($step == 1) {
?>
<form method="post" enctype="multipart/form-data">
	<fieldset>
	<legend>Step 1: Upload CSV File</legend>
	<ul class="form_display">
		<li><label for="import_csv_file">File:</label><input type="file" name="file" id="import_csv_file" /></li>
	</ul>
	<div style="clear: both;"><br /></div>
	<input type="submit" name="nocmd" id="submit_input" class='btn btn-primary btn-large' value="Continue" />
	</fieldset>

	<input type="hidden" name="process" value="csv_file" />
</form>
<?php
//Step 2: Ask what field (email or name) their headers match to.  Request list(s) to import into as well
} else if($step == 2) {
?>
<form method="post">
	<fieldset>
	<legend>Step 2: Import Properties</legend>
	<ul class="form_display">
		<li><label for="import_name_heading">Name:</label><select name="name_header_position" id="import_name_heading"><?=$csvFileHeadingOptions?></select></li>
		<li><label for="import_email_heading">Email:</label><select name="email_header_position" id="import_name_heading"><?=$csvFileHeadingOptions?></select></li>
		<li><h3>Subscription Lists</h3></li>
		<?php
		$sql = "SELECT id, name FROM newsletter_subscription_lists WHERE site_id='".NUMO_SITE_ID."' ORDER BY name";
		$results = $dbObj->query($sql);

		while($row = mysql_fetch_array($results)) {
		?>
			<li><input style='margin: 0px 0px 0px 0px;' type="checkbox" name="lists[]" id="list_<?=$row['id']?>" value="<?=$row['id']?>" /><label style='margin: 0px 0px 0px 10px; float: none; display: inline-block;' class="checkbox_list" for="list_<?=$row['id']?>"><?=$row['name']?></label></li>
		<?php
		}
		?>
	</ul>
	<div style="clear: both;"><br /></div>
	<input type="submit" name="nocmd" id="submit_input" class='btn btn-primary btn-large' value="Import" />
	</fieldset>

	<input type="hidden" name="cmd" value="import" />
	<input type="hidden" name="csv_file_name" value="<?=$csvFileName?>" />
</form>
<?php
//Step 3: Display report of any error messages and a tally of added subscribers
} else if($step == 3) {
?>
<fieldset>
<legend>Finished: Subscriber Import Results</legend>
<p>Subscribers successfully imported: <?=$processSuccessCount?></p>
<p>Subscribers NOT imported: <?=$processErrorCount?></p>
<br/>
<?php
	$errorReportString = '';

	if($processErrorCount > 0) {
		$errorReportString = '<h2>Import Errors</h2><ul class="import_report">'.$simpleImportReport.'</ul>';
	}

	print '<div id="simple_import_report">'.$errorReportString.'<input type="button" name="nocmd" value="Show Detailed Report"	onclick="document.getElementById(\'detailed_import_report\').style.display =\'block\'; document.getElementById(\'simple_import_report\').style.display =\'none\';" /></div>';
	print '<div id="detailed_import_report" style="display: none;"><h2>Detailed Import Report</h2><ul class="import_report">'.$detailedImportReport.'</ul><input type="button" name="nocmd" value="Return To Summary"	onclick="document.getElementById(\'simple_import_report\').style.display =\'block\'; document.getElementById(\'detailed_import_report\').style.display =\'none\';" /></div>';
?>
</fieldset>
<?php
}
?>