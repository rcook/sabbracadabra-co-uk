<?php
//if no id specified create a new guestbook
if(!isset($_GET['id'])) {
	/*

		$sql = "SELECT `id` FROM `guestbook_types` WHERE `site_id`='".NUMO_SITE_ID."'";
		//print $sql."<br>";
		$result = $dbObj->query($sql);


	if($row = mysql_fetch_array($result) && $_POST['cmd'] != "create") {
		header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage/?id='.$row['id']);
		exit();
	} else {
		*/
		//create guestbook
		$sql = "INSERT INTO `guestbook_types` (`site_id`,`available_slots`,`name`,`restrict_access`,`show_registration`,`default_group`,`confirmation_type`,`send_notification`,`confirmation_value`,`notification_email`,`include_form_info`) VALUES ('".NUMO_SITE_ID."','3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30','Guestbook',0,0,0,1,0,'Thank-you for your response!','',0)";
		//print $sql."<br>";
		$dbObj->query($sql);

		$sql = "SELECT LAST_INSERT_ID() as 'guestbook_id'";
		//print $sql."<br>";
		$result = $dbObj->query($sql);

		if($row = mysql_fetch_array($result)) {
			//add default fields
			$sql = "INSERT INTO `guestbook_fields` (`type_id`,`name`,`slot`,`position`,`required`,`locked`,`input_type`,`input_options`,`regex`) VALUES ('".$row['guestbook_id']."','Name',1,1,1,1,'text','',''), ('".$row['guestbook_id']."','Comments',2,2,1,0,'textarea','','')";
			//print $sql."<br>";
			$dbObj->query($sql);

			//redirect to edit type page
			header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage/?id='.$row['guestbook_id']);
			exit();
		}
	//}
}

if($_POST['cmd'] == "update") {
	//if checkbox checked then set on
	if(isset($_POST['send_notification'])) {
		$_POST['send_notification'] = 1;
	//uncheck, set off
	} else {
		$_POST['send_notification'] = 0;
	}

	//if checkbox checked then set on
	if(isset($_POST['restrict_access'])) {
		$_POST['restrict_access'] = 1;
	//uncheck, set off
	} else {
		$_POST['restrict_access'] = 0;
	}

	//if checkbox checked then set on
	if(isset($_POST['show_registration'])) {
		$_POST['show_registration'] = 1;
	//uncheck, set off
	} else {
		$_POST['show_registration'] = 0;
	}

	//if checkbox checked then set on
	if(isset($_POST['include_form_info'])) {
		$_POST['include_form_info'] = 1;
	//uncheck, set off
	} else {
		$_POST['include_form_info'] = 0;
	}

	//if checkbox checked then set on
	if(isset($_POST['require_review'])) {
		$_POST['require_review'] = 1;
	//uncheck, set off
	} else {
		$_POST['require_review'] = 0;
	}
	$sql = "UPDATE `guestbook_types` SET `name`='".$_POST['name']."',`include_form_info`='".$_POST['include_form_info']."',`restrict_access`='".$_POST['restrict_access']."',`show_registration`='".$_POST['show_registration']."',`default_group`='".$_POST['default_group']."',`confirmation_type`='".$_POST['confirmation_type']."',`send_notification`='".$_POST['send_notification']."',`notification_email`='".$_POST['notification_email']."',`confirmation_value`='".$_POST['confirmation_value']."',`require_review`='".$_POST['require_review']."' WHERE `id`='".$_GET['id']."' AND `site_id`='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	//exit;
	$dbObj->query($sql);
  //print mysql_error();
/************************************/
/*         REMOVE FIELD(s)         */
/**********************************/
	//field order value will be IDs separated by a comma.  Use explode function to break value apart into array
	$fieldRemoveArr = explode(',', $_POST['field_remove']);

	//loop thru field ids and remove field entries
	foreach($fieldRemoveArr as $key => $id) {
		if($id != "") {
			$sql = "SELECT slot FROM `guestbook_fields` WHERE id='".$id."'";
			//print $sql."<br>";
			$result = $dbObj->query($sql);

			if($row = mysql_fetch_array($result)) {
				$sql = "DELETE FROM `guestbook_fields` WHERE id='".$id."'";
				//print $sql."<br>";
				$dbObj->query($sql);

				$sql = "UPDATE `guestbook_types` SET available_slots=CONCAT('".$row['slot'].",',available_slots)  WHERE id='".$_GET['id']."'";
				//print $sql."<br>";
				$dbObj->query($sql);
			}
		}
	}

/************************************/
/*         UPDATE FIELD(s)         */
/***********************************/
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
			$sql = "SELECT available_slots FROM `guestbook_types` WHERE id='".$_GET['id']."' AND site_id='".NUMO_SITE_ID."'";
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
					$sql = "UPDATE `guestbook_types` SET available_slots='".$availableSlots."' WHERE id='".$_GET['id']."' AND site_id='".NUMO_SITE_ID."'";
					//print $sql."<br>";
					$dbObj->query($sql);

					//insert basic field information
					$sql = "INSERT INTO `guestbook_fields` (type_id,slot,locked) VALUE ('".$_GET['id']."','".$slotNumber."',0)";
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

		//__input_label_options
		if($_POST[$id.'__type'] == "label") {
			$_POST[$id.'__input_options'] = $_POST[$id.'__input_label_options'];
		}

		//__input_label_options
		if($_POST[$id.'__type'] == "heading") {
			$_POST[$id.'__input_options'] = $_POST[$id.'__input_heading_options'];
		}

		//default update query
		$sql = "UPDATE `guestbook_fields` SET position='".$position."',name='".htmlentities($_POST[$id.'__name'])."',input_type='".$_POST[$id.'__type']."',input_options='".htmlentities($_POST[$id.'__input_options'])."',required='".$_POST[$id.'__required']."' WHERE id='".$idNum."'";

		//if field locked limit update query
		if($_POST[$id.'__type'] == null || $_POST[$id.'__type'] == '') {
			$sql = "UPDATE `guestbook_fields` SET position='".$position."',name='".htmlentities($_POST[$id.'__name'])."',required='".$_POST[$id.'__required']."' WHERE id='".$idNum."'";

		}

		//print $sql."<br>";
		$dbObj->query($sql);

		//increase position by 1
		$position++;
	}
	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage/');

	//echo '<p style="border: 2px solid #E4BD71; background: #FAFFBD; color: #333; font-weight: bold; padding: 10px; margin: 10px 5px; max-width: 700px;">Changes Saved.</p>';
}
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
		newdiv.innerHTML = '<ul><li><img src="images/unlocked.jpg" alt="field unlocked" /></li><li><div><input type="text" name="'+divIdName+'__name" value="Enter Field Name" onblur="checkFieldValue(this)" onclick="checkFieldValue(this)" /></div></li><li><div><select onchange="checkTypeSelection(this.value,\''+divIdName+'\')" name="'+divIdName+'__type" id="'+divIdName+'__type"><?=display_field_type_options("","")?></select></div></li><li><div><select id="'+divIdName+'__required" name="'+divIdName+'__required"><?=display_yes_no_options("")?></select></div></li><li><a href="javascript:removeItem(\''+divIdName+'\')"><img src="images/close.jpg" alt="X" border="0" /></a></li></ul><div id="'+divIdName+'_field_input_options_display" class="field_optionals" style="display: none;"><div><label for="'+divIdName+'__input_options">Options</label><p>Place each option on a new line</p></div><textarea name="'+divIdName+'__input_options" id="'+divIdName+'__input_options"></textarea></div><div id="'+divIdName+'_field_input__label_options_display" class="field_optionals" style="display: none;"><div><label for="'+divIdName+'__input_options">Label</label><p>Enter the label you would like to appear</p></div><textarea name="'+divIdName+'__input_label_options" id="'+divIdName+'__input_label_options"></textarea></div><div id="'+divIdName+'_field_input__heading_options_display" class="field_optionals" style="display: none;"><div><label for="'+divIdName+'__input_options">Heading</label><p>Enter the heading you would like to appear</p></div><input type="text" name="'+divIdName+'__input_heading_options" id="'+divIdName+'__input_heading_options" /></div>';
		//newdiv.innerHTML = "new item";

		/*add new div to list*/
		container.appendChild(newdiv);

		/*add one to new element counter*/
		fieldCount++;

		Sortable.destroy("group_fields");

		Sortable.create('group_fields',{tag:'div',dropOnEmpty: true, only:'lineitem'});
	} else {
		alert('All available fields are currently in use');
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
	var labelOptionDisplay = document.getElementById(id+'_field_input__label_options_display');
	var headingOptionDisplay = document.getElementById(id+'_field_input__heading_options_display');
	var requiredDisplay = document.getElementById(id+'__required');

	if(value == "dropdown list" || value == "checkbox" || value == "radio" || value == "multiple select") {
		optionalDisplay.style.display = "block";
		labelOptionDisplay.style.display = "none";
		headingOptionDisplay.style.display = "none";
	} else if(value == "label") {
		optionalDisplay.style.display = "none";
		labelOptionDisplay.style.display = "block";
		headingOptionDisplay.style.display = "none";
	} else if(value == "heading") {
		optionalDisplay.style.display = "none";
		labelOptionDisplay.style.display = "none";
		headingOptionDisplay.style.display = "block";
 <?php if ($numo->extensions['captcha']) { ?>
  } else if (value == "captcha") {
		optionalDisplay.style.display = "none";
		labelOptionDisplay.style.display = "none";
		headingOptionDisplay.style.display = "none";
		requiredDisplay.selectedIndex = 0;
 <? } ?>
	} else {
		optionalDisplay.style.display = "none";
		labelOptionDisplay.style.display = "none";
		headingOptionDisplay.style.display = "none";
	}
}

</script>
<style>
html { padding: 0px; margin: 0px; }
body { padding: 0px; margin: 0px; font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; }
div { padding: 0px; margin: 0px; }
fieldset {border: 1px solid #ccc; width: 585px; clear: both; display: block;}
fieldset legend {color: #222;}
fieldset div.form_setting { margin: 5px 0px; padding: 5px; border: 1px dotted #ddd; width: 575px; display: inline-block;}
.headings{ padding: 0px; margin: 0px 0px 5px 0px; border: 1px solid #ccc; width: 580px;}
.headings ul {height: 28px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_fields_heading.jpg') repeat-x; list-style:none;}
.headings ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left; clear: none;}
.headings ul li img { padding: 0px; margin: 0px; display: block; height: 28px; }
.headings ul li h2 {line-height: 28px; display: inline-block; width: 170px; color: #333; font-size: 20px; font-weight: normal; text-align: center; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_fields_heading_background.jpg') top right; }

.lineitem { padding: 0px; margin: 0px 0px 5px 0px; border: 1px solid #ccc; width: 580px; background: #EDEDED; cursor: move;}
.lineitem ul {height: 44px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_field.jpg') repeat-x;}
.lineitem ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left;}
.lineitem ul li img { padding: 0px; margin: 0px; display: block;}
.lineitem ul li div { height: 44px; display: table-cell; vertical-align: middle; width: 170px; font-size: 1em; text-align: center; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_field_background.jpg') no-repeat top right; }
.lineitem ul li input, .lineitem ul li select { font-size: 1em; font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; width: 150px; padding: 2px; margin: 0px;}
.lineitem ul li a {line-height: 44px; text-align: center; width: 24px; color: #aaa; text-decoration: none; display: block;}
.lineitem label { margin: 0px; padding: 0px; vertical-align: top; display: inline-block; color: #333; font-size: 20px; font-weight: normal;}
.lineitem p { margin: 0px; padding: 5px 0px; color: #777; font-size: 12px; font-weight: normal;}
.lineitem textarea { vertical-align: top; width: 335px; margin: 0px; height: 70px; margin-left: 9px;}
.lineitem div.field_optionals { padding: 10px; border-top: 1px solid #ccc; min-height: 60px;}
.lineitem div.field_optionals input {width: 325px; padding: 5px; margin-left: 9px;}
.lineitem div div { margin: 0px 0px 0px 30px; width: 175px; float: left;}
.bttm_submit_button {position: fixed; bottom: 0px; right: 0px; background: #779FE1; border-top: 1px solid #2A61BD; width: 100%; height: 50px; padding: 0px 20px; margin: 0px;}
.bttm_submit_button input {background: #EEEEEE; color: #333; border: 1px solid #333; height: 30px; margin: 10px 0px 10px 210px;}
.bttm_submit_button input:hover {background: #bbb; color: #333; border: 1px solid #333; cursor: pointer;}
html {padding-bottom: 50px;}
</style><!--[if lte IE 8]>
<style>
	.lineitem ul li div { height: 34px; padding: 10px 0px 0px 0px; }
</style>
<![endif]-->
<?php
//load account information
$sql = "SELECT * FROM `guestbook_types` WHERE id='".$_GET['id']."' AND site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
?>
<form method="post">
	<fieldset>
		<ul class="form_display">
			<li><label for="name" style="width: 150px; font-weight: bold; display: inline-block; line-height: 30px;">Guestbook Title:</label><input type="text" name="name" style="width: 417px; padding: 5px;" value="<?=$row['name']?>" /></li>
		</ul>
	</fieldset>
<!--
	<fieldset>
		<legend>Layout</legend>
		<a href="module/<?=$_GET['m']?>/configure-layout/"><img src="modules/<?php echo $_GET['m']; ?>/images/manage_layout.jpg" alt="click to manage guestbook entry display" title="click to manage guestbook entry display" border="0" /></a>
	</fieldset>
-->
	<fieldset>
		<legend>Fields</legend>
		<div class="headings">
			<ul>
				<li><img src="images/manage_fields_heading_locks.jpg"></li>
				<li><h2>Name</h2></li>
				<li><h2>Type</h2></li>
				<li><h2>Required</h2></li>
				<li>&nbsp;</li>
			</ul>
		</div>
		<?php
		//load field information for accounts group
		$sql = "SELECT * FROM `guestbook_fields` WHERE type_id='".$row['id']."' ORDER BY `position`,`name`";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		echo '<div id="group_fields">';

		while($field = mysql_fetch_array($results)) {
		?>
			<div id="item_<?=$field['id']?>" class="lineitem">
				<ul>
					<li><img src="images/<?php if($field['locked'] == "1") { print 'locked'; } else { print 'unlocked'; } ?>.jpg" alt="field <?php if($field['locked'] == "1") { print 'locked'; } else { print 'unlocked'; } ?>" /></li>
					<li><div><input type="text" name="<?=$field['id']?>__name" value="<?=$field['name']?>" /></div></li>
					<li><div><select onchange="checkTypeSelection(this.value,'<?=$field['id']?>')" <?php if($field['locked'] == "1") { print 'disabled="disabled"'; } ?> id="<?=$field['id']?>__type" name="<?=$field['id']?>__type"><?=display_field_type_options($field['input_type'],$field['locked'])?></select></div></li>
					<li><div><select id="<?=$field['id']?>__required" name="<?=$field['id']?>__required"><?=display_yes_no_options($field['required'])?></select></div></li>
					<li><?php if($field['locked'] == "0") { print '<a href="javascript:removeItem(\''.$field['id'].'\')"><img src="images/close.jpg" alt="X" border="0" /></a>'; } ?></li>
				</ul>
				<?php if($field['locked'] == "0") { ?>
				<div id="<?=$field['id']?>_field_input_options_display" class="field_optionals" <?php if(!($field['input_type'] == "dropdown list" || $field['input_type'] == "multiple select" || $field['input_type'] == "checkbox" || $field['input_type'] == "radio")) { print 'style="display: none;"'; } ?>>
					<div><label for="<?=$field['id']?>__input_options">Options</label><p>Place each option on a new line</p></div>
					<textarea name="<?=$field['id']?>__input_options" id="<?=$field['id']?>__input_options"><?=html_entity_decode($field['input_options'])?></textarea>
				</div>
				<div id="<?=$field['id']?>_field_input__label_options_display" class="field_optionals" <?php if($field['input_type'] != "label") { print 'style="display: none;"'; } ?>>
					<div><label for="<?=$field['id']?>__input_label_options">Label</label><p>Enter the label you would like to appear</p></div>
					<textarea name="<?=$field['id']?>__input_label_options" id="<?=$field['id']?>__input_label_options"><?=html_entity_decode($field['input_options'])?></textarea>
				</div>
				<div id="<?=$field['id']?>_field_input__heading_options_display" class="field_optionals" <?php if($field['input_type'] != "heading") { print 'style="display: none;"'; } ?>>
					<div><label for="<?=$field['id']?>__input_heading_options">Heading</label><p>Enter the heading you would like to appear</p></div>
					<input type="text" name="<?=$field['id']?>__input_heading_options" id="<?=$field['id']?>__input_heading_options" value='<?=str_replace("'","&#39;",html_entity_decode($field['input_options']))?>' />
				</div>
				<?php } ?>
			</div>
		<?php
			//echo '<tr><td>'.$field['locked'].'</td><td>'.$field['name'].'</td><td>'.$field['input_type'].'</td><td>'.$field['required'].'</td><td><a href="module/'.$_GET['m'].'/field-edit/?id='.$field['id'].'">Edit</a></td></tr>';
		}

		echo '</div>';
		?>
		<input type="button" name="nocmd2" value="Add New Field" onClick="addItem()" />
	</fieldset>

	<fieldset>
		<legend>Settings</legend>
		<div class="form_setting">
		<h3>Confirmation Page</h3>
		<ul class="form_display">
			<li class="checkbox">
				<input type="radio" id="confirmation_type_1" name="confirmation_type" onclick="document.getElementById('confirmation_value_2').disabled='disabled'; document.getElementById('confirmation_value_1').disabled='';" value="1" <?=($row['confirmation_type'] == '1' ? 'checked="checked"' : '')?> />
				<label for="confirmation_type_1">Text Message</label>
			</li>
			<li><textarea id="confirmation_value_1" name="confirmation_value" <?=($row['confirmation_type'] == '1' ? '' : 'disabled="disabled"')?> style="width: 470px; margin-left: 20px;"><?=($row['confirmation_type'] == '1' ? $row['confirmation_value'] : 'Thank-you for your response!')?></textarea></li>
			<li class="checkbox">
				<input type="radio" id="confirmation_type_2" name="confirmation_type" onclick="document.getElementById('confirmation_value_1').disabled='disabled'; document.getElementById('confirmation_value_2').disabled='';" value="2" <?=($row['confirmation_type'] == '2' ? 'checked="checked"' : '')?> />
				<label for="confirmation_type_2">Redirect</label>
			</li>
			<li><input type="text" id="confirmation_value_2" name="confirmation_value" style="width: 460px; margin-left: 20px; padding: 5px;" value="<?=($row['confirmation_type'] == '2' ? $row['confirmation_value'] : 'http://')?>" <?=($row['confirmation_type'] == '2' ? '' : 'disabled="disabled"')?> /></li>
		</ul>
		</div>
		<div class="form_setting">
		<h3>Response Notification</h3>
		<ul class="form_display">
			<li class="checkbox">
				<input type="checkbox" id="send_notification" name="send_notification" onclick="if(this.checked) { document.getElementById('include_form_info_li').style.display='block'; document.getElementById('notification_email_li').style.display='block'; } else { document.getElementById('include_form_info_li').style.display='none'; document.getElementById('notification_email_li').style.display='none'; }" value="0" <?=($row['send_notification'] == '1' ? 'checked="checked"' : '')?> />
				<label for="send_notification">Send notification of new responses</label>
			</li>
			<li class="checkbox" id="include_form_info_li" <?=($row['send_notification'] == '1' ? '' : 'style="display: none;"')?>>
				<input type="checkbox" id="include_form_info" name="include_form_info" value="0" <?=($row['include_form_info'] == '1' ? 'checked="checked"' : '')?> />
				<label for="include_form_info">Include field responses in email notification</label>
			</li>
			<li id="notification_email_li" <?=($row['send_notification'] == '1' ? '' : 'style="display: none;"')?>><label style="margin-left: 30px; width: 50px; line-height: 30px; font-weight: bold;" for="default_group">Email:</label><input type="text" id="notification_email" name="notification_email" style="width: 460px; margin-left: 20px; padding: 5px;" value="<?=$row['notification_email']?>" /></li>
		</ul>
		</div>
		<div class="form_setting">
		<h3>Restrict Access</h3>
		<ul class="form_display">
			<li class="checkbox">
				<input type="checkbox" id="restrict_access" name="restrict_access" onclick="if(this.checked) { document.getElementById('show_registration_li').style.display='block'; if(document.getElementById('show_registration').checked) { document.getElementById('default_group_li').style.display='block'; } else { document.getElementById('default_group_li').style.display='none'; } } else {document.getElementById('show_registration_li').style.display='none'; document.getElementById('default_group_li').style.display='none'; }" value="0" <?=($row['restrict_access'] == '1' ? 'checked="checked"' : '')?> />
				<label for="restrict_access">Require visitors to be logged in an submit entry</label>
			</li>
			<li class="checkbox" id="show_registration_li" <?=($row['restrict_access'] == '1' ? '' : 'style="display: none;"')?>>
				<input type="checkbox" id="show_registration" name="show_registration" onclick="if(this.checked) { document.getElementById('default_group_li').style.display='block'; } else {document.getElementById('default_group_li').style.display='none'; }" value="0" <?=($row['show_registration'] == '1' ? 'checked="checked"' : '')?> />
				<label for="show_registration">Show registration component if the visitor is not logged in</label>
			</li>
			<li id="default_group_li" <?=($row['show_registration'] == '1' ? '' : 'style="display: none;"')?>><label style="margin-left: 30px; width: 50px; line-height: 30px; font-weight: bold;" for="default_group">Use:</label><select id="default_group" name="default_group" style="padding: 5px;"><?=list_account_group_move_options($row['default_group'])?></select></li>
		</ul>
		</div>
        
		<div class="form_setting">
		<h3>Submission Review</h3>
		<ul class="form_display">
			<li class="checkbox">
				<input type="checkbox" id="require_review" name="require_review" value="1" <?=($row['require_review'] == '1' ? 'checked="checked"' : '')?> />
				<label for="restrict_access">Require submissions to be reviewed before being visible to public</label>
			</li>
		</ul>
		</div>
	</fieldset>

	<input type="hidden" name="type_id" value="<?=$row['id']?>" />
	<input type="hidden" name="cmd" value="update" />
	<input type="hidden" name="field_order" id="field_order" value="" />
	<input type="hidden" name="field_remove" id="field_remove" value="" />

	<div class="bttm_submit_button">
	<input type="button" name="nocmd" value="Save" onClick="getGroupOrder(this.form)" />
	</div>
</form>
<!--[if IE]>
<div style='padding-top:35px;'>&nbsp;</div>
<![endif]-->
<?php
mysql_free_result($result);
mysql_free_result($results);
} else {
	print '<p>Could not locate account group.</p>';
}

function list_account_group_move_options($currentId = 0) {
	global $dbObj;

	$returnStr = "";

	$sql = "SELECT id, name FROM `types` WHERE site_id='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	while($row = mysql_fetch_array($results)) {
		if($currentId == $row['id']) {
			$returnStr .= "<option value=\"".$row['id']."\" selected=\"selected\">".$row['name']." Account Group</option>";
		} else {
			$returnStr .= "<option value=\"".$row['id']."\">".$row['name']." Account Group</option>";
		}
	}

	return $returnStr;
}

function display_yes_no_options($value) {
	if($value == 1) {
		return "<option value=\"1\" selected=\"selected\">Yes</option><option value=\"0\">No</option>";
	} else {
		return "<option value=\"1\">Yes</option><option value=\"0\" selected=\"selected\">No</option>";
	}
}

function display_allow_dis_options($value) {
	if($value == 1) {
		return "<option value=\"1\" selected=\"selected\">Allow By Default</option><option value=\"0\">Prevent By Default</option>";
	} else {
		return "<option value=\"1\">Allow By Default</option><option value=\"0\" selected=\"selected\">Prevent By Default</option>";
	}
}

function display_counting_options($value,$limit,$interval, $zeroLabel = "0", $label = "") {
	$optionStr = "";

	for($i = 0; $i <= $limit; ) {
		$numLabel = $i.$label;

		if($i == 0) {
			$numLabel = $zeroLabel;
		}

		if($value == $i) {
			$optionStr .= "<option selected='selected' value='".$i."'>".$numLabel."</option>";
		} else {
			$optionStr .= "<option value='".$i."'>".$numLabel."</option>";
		}

		$i = $i + $interval;
	}

	return $optionStr;
}

function display_field_type_options($currentValue, $locked) {
	global $numo;
	
	if($locked == 1) {
		return "<option value=\"".$currentValue."\">".ucfirst($currentValue)."</option>";
	} else {
		$fieldTypes = array ("text","textarea","radio","checkbox","dropdown list","label","heading","email","website address");
		if ($numo->extensions['captcha']) {
			$fieldTypes[] = "captcha";
		}
		//removed ,"multiple select","file upload"
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

	$sql = "SELECT COUNT(*) as 'field_count' FROM `guestbook_fields` WHERE type_id='".$_GET['id']."'";
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