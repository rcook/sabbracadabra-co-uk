<style>
ul.form_display li label{ width:200px; padding-right: 5px;}
ul.form_display li {line-height: 20px; vertical-align: bottom;}
.checkbox_list {padding-left: 5px;}
.error {color: #900; font-weight: bold;}
ul.import_report {list-style-type: none; margin:0px; padding: 0px;}
.import_report li {font-size: 11px;}
</style>
<h2>Import Products</h2>
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
			  $uploadsDir = ABSOLUTE_ROOT_PATH."dashboard/uploads/modules/shopping_cart";
			} else {
			  $uploadsDir = "modules/shopping_cart/uploads";
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
		$product = preg_split('/,(?=(?:[^\"]*\"[^\"]*\")*(?![^\"]*\"))/', $line, 0, PREG_SPLIT_DELIM_CAPTURE);

		foreach($subscriber as $key => $value) {
			$value = trim($value);

			if(substr($value,0,1) == '"') {
				$value = str_replace('""','"',substr($value, 1, -1));
			}

			$subscriber[$key] = $value;
		}

		$insertStr = "";
		$insertKeyString = "INSERT INTO shopping_cart_products (site_id, when_created, status";
		$insertValueString = ") VALUES ('".NUMO_SITE_ID."', '".date("Y-m-d H:i:s")."', '{$_POST['status']}'";

		foreach ($slotMap as $slot => $headerPosition) {
			$insertKeyString .= ", {$slot}";
			//$insertValueString .= ", '".($product[$headerPosition])."'";
			$insertValueString .= ", '".addslashes($product[$headerPosition])."'";
		}
		$insertString = $insertKeyString.$insertValueString.")";
		$dbObj->query($insertString);
		//print $insertString."<br>";
		//print mysql_error()."<br>";
		$productID = mysql_insert_id();
		if (!mysql_error()) {
			foreach($_POST['categories'] as $key => $value) {
			  $sql = "INSERT INTO shopping_cart_product_categories (product_id, category_id) VALUES ('".$productID."','".$value."')";
			  $dbObj->query($sql);
			// print $sql."<br>";
			}

			//add to process report strings
			$detailedImportReport .= '<li>Line '.($lineNumber+1).': Product "'.$product[$slotMap['slot_1']].'" created.</li>';

		  $processSuccessCount++;
		} else {
		  $processErrorCount++;
			$simpleImportReport .= '<li>Line '.($lineNumber+1).': Product "'.$product[$slotMap['slot_1']].'" failed.</li>';
		}


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
	</ul>
	<div style="clear: both;"><br /></div>
	<input type="submit" name="nocmd" id="submit_input" value="Continue" />
	</fieldset>

	<input type="hidden" name="process" value="csv_file" />
    <p>Please include, minimally, the following fields: Product Name, Price, Short Description</p>
    <p>Please also note that images cannot be uploaded via CSV file, and will need to be manually uploaded via the product management page once the import procedure has completed.</p>

</form>
<?php
//Step 2: Ask what field (email or name) their headers match to.  Request list(s) to import into as well
} else if($step == 2) {
?>
<form method="post">
	<fieldset>
	<legend>Step 2: Import Mapping</legend>
	<ul class="form_display">
    <?php $query = "SELECT * FROM shopping_cart_fields WHERE slot<>'5' AND slot<>'6' AND site_id='".NUMO_SITE_ID."' ORDER BY position";
	$result = $dbObj->query($query);
	while ($fieldRecord = mysql_fetch_array($result)) {
		?>
		<li><label for="import_slot_<?php echo $fieldRecord['slot']; ?>_heading"><?php echo $fieldRecord['name']; ?></label><select name="slot_<?php echo $fieldRecord['slot']; ?>_header_position" id="import_slot_<?php echo $fieldRecord['slot']; ?>_heading"><option value=''>-- skip -- </option><?=$csvFileHeadingOptions?></select></li>
    <?php } ?>
		<li><h3>Categories</h3></li>
		<?php
		$sql = "SELECT id, label FROM shopping_cart_categories WHERE site_id='".NUMO_SITE_ID."' ORDER BY parent_id, position";
		$results = $dbObj->query($sql);

		while($row = mysql_fetch_array($results)) {
		?>
			<li><input type="checkbox" name="categories[]" id="category_<?=$row['id']?>" value="<?=$row['id']?>" /><label class="checkbox_list" for="category_<?=$row['id']?>"><?=$row['label']?></label></li>
		<?php
		}
		?>
		<li><h3>General</h3></li>

			<li><label for='status'>Status Once Uploaded</label><select id='status' name='status'><option value='0'>Offline</option><option value='1'>Online</option></select></li>

	</ul>
	<div style="clear: both;"><br /></div>
	<input type="submit" name="nocmd" id="submit_input" value="Import" />
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
<p>Products successfully imported: <?=$processSuccessCount?></p>
<br/>
<?php
	$errorReportString = '';

	if($processErrorCount > 0) {
		$errorReportString = '<h2>Import Errors</h2><ul class="import_report">'.$simpleImportReport.'</ul>';
	}

    print "<p>Please note: you will still need to upload photos via the product management page</p>";
	print '<div id="simple_import_report">'.$errorReportString.'<input type="button" name="nocmd" value="Show Detailed Report"	onclick="document.getElementById(\'detailed_import_report\').style.display =\'block\'; document.getElementById(\'simple_import_report\').style.display =\'none\';" /></div>';
	print '<div id="detailed_import_report" style="display: none;"><h2>Detailed Import Report</h2><ul class="import_report">'.$detailedImportReport.'</ul><input type="button" name="nocmd" value="Return To Summary"	onclick="document.getElementById(\'simple_import_report\').style.display =\'block\'; document.getElementById(\'detailed_import_report\').style.display =\'none\';" /></div>';
?>
</fieldset>
<?php
}
?>