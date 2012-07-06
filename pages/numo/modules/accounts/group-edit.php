<?php
	$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings`");
	$shoppingCartExists = (@mysql_num_rows($result))?TRUE:FALSE;
	
	$result = $dbObj->query("SHOW COLUMNS FROM `listing_contributors`");
	$listingServiceExists = (@mysql_num_rows($result))?TRUE:FALSE; 
	
?>
<script language="JavaScript" src="javascript/prototype.js"></script>
<script language="JavaScript" src="javascript/effects.js"></script>
<script language="JavaScript" src="javascript/dragdrop.js"></script>
<script language="JavaScript">
var fieldCount = <?=get_type_field_count()?>;

function getGroupOrder(frm) {
	var order = Sortable.serialize("group_fields");
	//alert(alerttext);

	fieldOrder = document.getElementById("field_order");
	fieldOrder.value = Sortable.sequence("group_fields");
	//alert(fieldOrder.value);
	frm.submit();
	return true;
}

function removeItem(id) {
	//get hidden field that stores removed id values
	fieldsRemoved = document.getElementById("field_remove");

	//check to see if list is empty or not
	if(fieldsRemoved.value == "") {
		fieldsRemoved.value = id;
	} else {
		fieldsRemoved.value = fieldsRemoved.value + "," + id;
	}

	//get containing div element (container)
  var container = document.getElementById('group_fields');

  //get div element to remove
  var olddiv = document.getElementById('item_'+id);

  //remove the div element from the container
  container.removeChild(olddiv);

  fieldCount--;
}

function addItem() {
	if(fieldCount <= 30) {
		var currentTime = new Date();

		/*generate new div ID*/
		var divIdName = 'new'+fieldCount+'-'+currentTime.getTime();

		/*get containing div element (container)*/
		var container = document.getElementById('group_fields');

		/*create new div*/
		var newdiv = document.createElement('div');

		/*set new div ID*/
		newdiv.setAttribute('id','item_'+divIdName);

		/*set new div ID*/
		newdiv.className = 'lineitem';

		/*set content of new div*/
		newdiv.innerHTML = '<ul><li><img src="images/unlocked.jpg" alt="field unlocked" /></li><li><div><input type="text" name="'+divIdName+'__name" value="Enter Field Name" onblur="checkFieldValue(this)" onclick="checkFieldValue(this)" /></div></li><li><div><select onchange="checkTypeSelection(this.value,\''+divIdName+'\')" name="'+divIdName+'__type"><?=display_field_type_options("","")?></select></div></li><li><div><select name="'+divIdName+'__required" id="'+divIdName+'__required"><?=display_yes_no_options("")?></select></div></li><li><div><select name="'+divIdName+'__show_on_registration" id="'+divIdName+'__show_on_registration"><?=display_yes_no_options("")?></select></div></li><li><a href="javascript:removeItem(\''+divIdName+'\')"><img src="images/close.jpg" alt="X" border="0" /></a></li></ul><div id="'+divIdName+'_field_input_options_display" class="field_optionals" style="display: none;"><div><label for="'+divIdName+'__input_options">Options</label><p>Place each drop down option on a new line</p></div><textarea name="'+divIdName+'__input_options" id="'+divIdName+'__input_options"></textarea></div>';
		//newdiv.innerHTML = "new item";

		/*add new div to list*/
		container.appendChild(newdiv);

		/*add one to new element counter*/
		fieldCount++;

		Sortable.destroy("group_fields");

		Sortable.create('group_fields',{tag:'div',dropOnEmpty: true, only:'lineitem'});
	} else {
		alert('All available field slots are currently in use');
	}
}

function checkFieldValue(field) {
	if(field.value == "Enter Field Name") {
		field.value = "";
	} else if(field.value == "") {
		field.value = "Enter Field Name";
	}
}

function checkTypeSelection(value, id) {
	var optionalDisplay = document.getElementById(id+'_field_input_options_display');

	if(value == "dropdown list") {
		optionalDisplay.style.display = "block";
	} else if (value == "captcha") {
		document.getElementById(id+'__required').selectedIndex             = 0;
		document.getElementById(id+'__show_on_registration').selectedIndex = 0;
		optionalDisplay.style.display = "none";
	} else {
		optionalDisplay.style.display = "none";
	}
}
</script>
<style>
	html { padding: 0px; margin: 0px; }
	body { padding: 0px; margin: 0px; font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; }
	div { padding: 0px; margin: 0px; }
	.headings{ padding: 0px; margin: 0px 0px 5px 0px; border: 1px solid #ccc; width: 750px;}
	.headings ul {height: 28px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_fields_heading.jpg') repeat-x; list-style:none;}
	.headings ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left; clear: none;}
	.headings ul li img { padding: 0px; margin: 0px; display: block; height: 28px; }
	.headings ul li h2 {line-height: 28px; display: inline-block; width: 170px; color: #333; font-size: 20px; font-weight: normal; text-align: center; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_fields_heading_background.jpg') top right; }

	.lineitem { padding: 0px; margin: 0px 0px 5px 0px; border: 1px solid #ccc; width: 750px; background: #EDEDED; cursor: move;}
	.lineitem ul {height: 44px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_field.jpg') repeat-x;}
	.lineitem ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left;}
	.lineitem ul li img { padding: 0px; margin: 0px; display: block;}
	.lineitem ul li div { height: 44px; display: table-cell; vertical-align: middle; width: 170px; font-size: 1em; text-align: center; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_field_background.jpg') no-repeat top right; }
	.lineitem ul li input, .lineitem ul li select { font-size: 1em; font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; width: 150px; padding: 2px; margin: 0px;}
	.lineitem ul li a {line-height: 44px; text-align: center; width: 24px; color: #aaa; text-decoration: none; display: block;}
	.lineitem label { margin: 0px; padding: 0px; vertical-align: top; display: inline-block; color: #333; font-size: 20px; font-weight: normal;}
	.lineitem p { margin: 0px; padding: 5px 0px; color: #777; font-size: 12px; font-weight: normal;}
	.lineitem textarea { vertical-align: top; width: 515px; margin: 0px; height: 70px;}
	.lineitem div.field_optionals { padding: 10px; border-top: 1px solid #ccc;}
	.lineitem div div { margin: 0px 0px 0px 30px; width: 175px; float: left;}
</style><!--[if lte IE 8]>
<style>
	.lineitem ul li div { height: 34px; padding: 10px 0px 0px 0px; }
</style>
<![endif]-->
<h2>Edit Group</h2>
<?php
if($_POST['cmd'] == "update") {

    if ($shoppingCartExists) {
		  $setDiscount = ",shopping_cart_discount='{$_POST['shopping_cart_discount']}',show_original_price='{$_POST['show_original_price']}'";
		
	}
	
    if ($listingServiceExists) {
		  $setListingSettings = ",listing_override_post_life='{$_POST['listing_override_post_life']}',listing_override_require_approval='{$_POST['listing_override_require_approval']}',listing_override_max_posts='{$_POST['listing_override_max_posts']}'";
		
	}	
	$sql = "UPDATE `types` SET name='".$_POST['name']."',allow_registration='".$_POST['allow_registration']."',require_approval='".$_POST['require_approval']."',require_activation='".$_POST['require_activation']."' {$setDiscount} {$setListingSettings} WHERE id='".$_GET['id']."' AND site_id='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	$dbObj->query($sql);

/************************************/
/*         REMOVE FIELD(s)         */
/**********************************/
	//field order value will be IDs separated by a comma.  Use explode function to break value apart into array
	$fieldRemoveArr = explode(',', $_POST['field_remove']);

	//loop thru field ids and remove field entries
	foreach($fieldRemoveArr as $key => $id) {
		if($id != "") {
			$sql = "SELECT slot FROM `fields` WHERE id='".$id."'";
			//print $sql."<br>";
			$result = $dbObj->query($sql);

			if($row = mysql_fetch_array($result)) {
				$sql = "DELETE FROM `fields` WHERE id='".$id."'";
				//print $sql."<br>";
				$dbObj->query($sql);

				$sql = "UPDATE `types` SET available_slots=CONCAT('".$row['slot'].",',available_slots)  WHERE id='".$_GET['id']."'";
				//print $sql."<br>";
				$dbObj->query($sql);
			}
		}
	}

/************************************/
/*         UPDATE FIELD(s)         */
/**********************************/
	//field order value will be IDs separated by a comma.  Use explode function to break value apart into array
	$fieldOrderArr = explode(',', $_POST['field_order']);

	//set starting position value
	$position = 1;

	//loop thru field id and save order
	foreach($fieldOrderArr as $key => $id) {
		//make copy of the id incase a new field is being created.
		$idNum = $id;

		/************************************/
		/*         CREATE FIELD(s)         */
		/**********************************/
		if(substr($id, 0, 3) == "new") {
			//select the available slots value for the TYPE
			$sql = "SELECT available_slots FROM `types` WHERE id='".$_GET['id']."' AND site_id='".NUMO_SITE_ID."'";
			//print $sql."<br>";
			$result = $dbObj->query($sql);

			if($row = mysql_fetch_array($result)) {
				//If no available slots skip add/update
				if($row['available_slots'] == "" || $row['available_slots'] == ",") {
					continue;
				} else {
					//split the available slots value.  put the first value in slotNumber variable and the rest in the availableSlots variable
					list($slotNumber,$availableSlots) = explode(",", $row['available_slots'], 2);

					//update available slots value to new value (availableSlots)
					$sql = "UPDATE `types` SET available_slots='".$availableSlots."' WHERE id='".$_GET['id']."' AND site_id='".NUMO_SITE_ID."'";
					//print $sql."<br>";
					$dbObj->query($sql);

					//insert basic field information
					$sql = "INSERT INTO `fields` (type_id,slot,locked) VALUE ('".$_GET['id']."',".$slotNumber.",0)";
					//print $sql."<br>";
					$dbObj->query($sql);

					//get the ID for the field just inserted in the database
					$sql = "SELECT LAST_INSERT_ID() as 'id'";
					//print $sql."<br>";
					$fieldResult = $dbObj->query($sql);

					if($field = mysql_fetch_array($fieldResult)) {
						//assign ID to idNum variable to be used in update commands lower down
						$idNum = $field['id'];
					}
				}
			}
		}

		//default update query
		$sql = "UPDATE `fields` SET position=".$position.",name='".$_POST[$id.'__name']."',input_type='".$_POST[$id.'__type']."',input_options='".$_POST[$id.'__input_options']."',required='".$_POST[$id.'__required']."',show_on_registration='".$_POST[$id.'__show_on_registration']."' WHERE id='".$idNum."'";

		//if field locked limit update query
		if($_POST[$id.'__required'] == null || $_POST[$id.'__required'] == '') {
			$sql = "UPDATE `fields` SET position='".$position."',name='".$_POST[$id.'__name']."' WHERE id='".$idNum."'";
		}

		//print $sql."<br>";
		$dbObj->query($sql);

		//increase position by 1
		$position++;
	}
}

//load account information
$sql = "SELECT * FROM `types` WHERE id='".$_GET['id']."' AND site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
?>
<form method="post">
		<fieldset>
			<legend>Settings</legend>
			<ul class="form_display">
				<li><label for="name">Name:</label><input type="text" name="name" id="name" value="<?=$row['name']?>" /></li>
				<li><label for="allow_registration">Allow Registration:</label><select name="allow_registration" id="allow_registration"><?=display_yes_no_options($row['allow_registration'])?></select></li>
			</ul>
			<fieldset>
				<legend>New Accounts</legend>
					<ul class="form_display">
						<li><label for="require_approval">Require Approval:</label><select name="require_approval" id="require_approval"><?=display_yes_no_options($row['require_approval'])?></select></li>
						<li><label for="require_activation">Require Activation:</label><select name="require_activation" id="require_activation"><?=display_yes_no_options($row['require_activation'])?></select></li>
					</ul>
			</fieldset>
<?php if ($shoppingCartExists) { ?>
			<fieldset>
				<legend>Shopping Cart</legend>
					<ul class="form_display">
						<li><label for="shopping_cart_discount">Group Discount %:</label><input type='text' name="shopping_cart_discount" value="<?=$row['shopping_cart_discount']?>" /></li>
					</ul>
					<ul class="form_display">
						<li><label for="show_original_price">Show Original Price:</label><select name="show_original_price"><?=display_yes_no_options($row['show_original_price'])?></select></li>
					</ul>
			</fieldset>

<?php } ?>
<?php if ($listingServiceExists) { ?>
			<fieldset> 
				<legend>Listing Service Contributors</legend>
					<ul class="form_display">
						<li><label style='width: 170px' for="listing_override_max_posts">Max Posts:</label><select name="listing_override_max_posts"><?=generate_list_options(array('-1' => 'Default Global Setting',
																																									   '0' => 'Unlimited', 
																																									   '1' => '1', 
																																									   '2' => '2',
																																									   '3' => '3',
																																									   '4' => '4',
																																									   '5' => '5',
																																									   '6' => '6',
																																									   '7' => '7',
																																									   '8' => '8',
																																									   '9' => '9',
																																									   '10' => '10',
																																									   '11' => '11',
																																									   '12' => '12',
																																									   '13' => '13',
																																									   '14' => '14',
																																									   '15' => '15',
																																									   '20' => '20',
																																									   '25' => '25',
																																									   '30' => '30',
																																									   '40' => '40',
																																									   '50' => '50'),$row['listing_override_max_posts']); ?></select></li>
					</ul>
					<ul class="form_display">
						<li><label style='width: 170px' for="listing_override_post_life">Listing Post Life:</label><select name="listing_override_post_life"><?=generate_list_options(array('-1' => 'Default Global Setting',
																																									   '1' => '1', 
																																									   '2' => '2',
																																									   '3' => '3',
																																									   '4' => '4',
																																									   '5' => '5',
																																									   '6' => '6',
																																									   '7' => '7',
																																									   '8' => '8',
																																									   '9' => '9',
																																									   '10' => '10',
																																									   '11' => '11',
																																									   '12' => '12',
																																									   '13' => '13',
																																									   '14' => '14',
																																									   '15' => '15',
																																									   '20' => '20',
																																									   '25' => '25',
																																									   '30' => '30',
																																									   '40' => '40',
																																									   '50' => '50',
																																									   '75' => '75',
																																									   '100' => '100',
																																									   '150' => '150',
																																									   '200' => '200',
																																									   '250' => '250',
																																									   '300' => '300',
																																									   '365' => '365'),$row['listing_override_post_life']); ?></select></li>
					</ul>
					<ul class="form_display">
						<li><label style='width: 170px' for="listing_override_require_approval">Listing Requires Approval:</label><select name="listing_override_require_approval"><?=generate_list_options(array('-1' => 'Default Global Setting', '0' => 'No', '1' => 'Yes'),$row['listing_override_require_approval']); ?></select></li>
					</ul>
			</fieldset>

<?php } ?>
            
		</fieldset>

		<fieldset>
			<legend>Fields</legend>
			<div class="headings">
				<ul>
					<li><img src="images/manage_fields_heading_locks.jpg"></li>
					<li><h2>Name</h2></li>
					<li><h2>Type</h2></li>
					<li><h2>Required</h2></li>
					<li><h2>Visible</h2></li>
					<li>&nbsp;</li>
				</ul>
			</div>
			<?php
			//load field information for accounts group
			$sql = "SELECT * FROM `fields` WHERE type_id='".$row['id']."' ORDER BY `position`,`name`";
			//print $sql."<br>";
			$results = $dbObj->query($sql);

			echo '<div id="group_fields">';

			while($field = mysql_fetch_array($results)) {
			?>
				<div id="item_<?=$field['id']?>" class="lineitem">
					<ul>
						<li><img src="images/<?php if($field['locked'] == "1") { print 'locked'; } else { print 'unlocked'; } ?>.jpg" alt="field <?php if($field['locked'] == "1") { print 'locked'; } else { print 'unlocked'; } ?>" /></li>
						<li><div><input type="text" name="<?=$field['id']?>__name" value="<?=$field['name']?>" /></div></li>
						<li><div><select onchange="checkTypeSelection(this.value,'<?=$field['id']?>')" <?php if($field['locked'] == "1") { print 'disabled="disabled"'; } ?> name="<?=$field['id']?>__type"><?=display_field_type_options($field['input_type'],$field['locked'])?></select></div></li>
						<li><div><select <?php if($field['locked'] == "1") { print 'disabled="disabled"'; } ?> name="<?=$field['id']?>__required" id="<?=$field['id']?>__required"><?=display_yes_no_options($field['required'])?></select></div></li>
						<li><div><select <?php if($field['locked'] == "1") { print 'disabled="disabled"'; } ?> name="<?=$field['id']?>__show_on_registration" id="<?=$field['id']?>__show_on_registration"><?=display_yes_no_options($field['show_on_registration'])?></select></div></li>
						<li><?php if($field['locked'] == "0") { print '<a href="javascript:removeItem(\''.$field['id'].'\')"><img src="images/close.jpg" alt="X" border="0" /></a>'; } ?></li>
					</ul>
					<?php if($field['locked'] == "0") { ?>
					<div id="<?=$field['id']?>_field_input_options_display" class="field_optionals" <?php if($field['input_type'] != "dropdown list") { print 'style="display: none;"'; } ?>>
						<div><label for="<?=$field['id']?>__input_options">Options</label><p>Place each drop down option on a new line</p></div>
						<textarea name="<?=$field['id']?>__input_options" id="<?=$field['id']?>__input_options"><?=$field['input_options']?></textarea>
					</div>
					<!--<div id="field_regex_display" class="field_optionals" <?php if($field['input_type'] != "text") { print 'style="display: none;"'; } ?>>
						<div><label for="<?=$field['id']?>__regex">Regular Expression</label><p>Optional</p></div>
						<textarea name="<?=$field['id']?>__regex" id="<?=$field['id']?>__regex"><?=$field['regex']?></textarea>
					</div>-->
					<?php } ?>
				</div>
			<?php
				//echo '<tr><td>'.$field['locked'].'</td><td>'.$field['name'].'</td><td>'.$field['input_type'].'</td><td>'.$field['required'].'</td><td><a href="module/'.$_GET['m'].'/field-edit/?id='.$field['id'].'">Edit</a></td></tr>';
			}

			echo '</div>';
			?>
			<input type="button" name="nocmd2" value="Add New Field" onClick="addItem()" />
		</fieldset>
	<input type="hidden" name="type_id" value="<?=$row['id']?>" />
	<input type="hidden" name="cmd" value="update" />
	<input type="hidden" name="field_order" id="field_order" value="" />
	<input type="hidden" name="field_remove" id="field_remove" value="" />
	<input type="button" name="nocmd" value="Save" onClick="getGroupOrder(this.form)" />
</form>
<?php
mysql_free_result($result);
mysql_free_result($results);
} else {
	print '<p>Could not locate account group.</p>';
}

function display_yes_no_options($value) {
	if($value == 1) {
		return "<option value=\"1\" selected=\"selected\">Yes</option><option value=\"0\">No</option>";
	} else {
		return "<option value=\"1\">Yes</option><option value=\"0\" selected=\"selected\">No</option>";
	}
}

function display_field_type_options($currentValue, $locked) {
	if($locked == 1) {
		return "<option value=\"".$currentValue."\">".ucfirst($currentValue)."</option>";
	} else {
		//$fieldTypes = array ("text","number","dropdown list","checkbox");
		$fieldTypes = array ("text","number","dropdown list","captcha");
		$returnStr  = "";

		foreach($fieldTypes as $key => $value) {
			if(ucwords($value) == ucwords($currentValue)) {
				$returnStr .= "<option value=\"".$value."\" selected=\"selected\">".ucfirst($value)."</option>";
			} else {
				$returnStr .= "<option value=\"".$value."\">".ucfirst($value)."</option>";
			}
		}

		return $returnStr;
	}
}

function get_type_field_count() {
	global $dbObj;
	global $_GET;

	$sql = "SELECT COUNT(*) as 'field_count' FROM `fields` WHERE type_id='".$_GET['id']."'";
	//print $sql."<br>";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		return $row['field_count'] + 1;
	}

	return 5;
}
?>
<script type="text/javascript">
	// <![CDATA[
	Sortable.create('group_fields',{tag:'div',dropOnEmpty: true, only:'lineitem'});
	// ]]>
</script>