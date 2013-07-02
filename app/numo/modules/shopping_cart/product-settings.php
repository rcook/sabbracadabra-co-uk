<?php
if($_POST['cmd'] == "update") {
/************************************/
/*         REMOVE FIELD(s)         */
/**********************************/
	//field order value will be IDs separated by a comma.  Use explode function to break value apart into array
	$fieldRemoveArr = explode(',', $_POST['field_remove']);

	//loop thru field ids and remove field entries
	foreach($fieldRemoveArr as $key => $id) {
		if($id != "") {
			$sql = "SELECT slot FROM `shopping_cart_fields` WHERE id='".$id."'";
			$result = $dbObj->query($sql);

			if($row = mysql_fetch_array($result)) {
				$sql = "DELETE FROM `shopping_cart_fields` WHERE id='".$id."'";
				$dbObj->query($sql);

				$sql = "UPDATE `shopping_cart_settings` SET available_slots=CONCAT('".$row['slot'].",',available_slots)  WHERE site_id='".NUMO_SITE_ID."'";
				$dbObj->query($sql);
			}
		}
	}


/************************************/
/*         UPDATE FIELD(s)         */
/**********************************/
	// field order value will be IDs separated by a comma.  Use explode function to break value apart into array
	$fieldOrderArr = explode(',', $_POST['field_order']);

	// set starting position value
	$position = 1;

	// loop thru field id and save order
	foreach($fieldOrderArr as $key => $id) {
		// make copy of the id in case a new field is being created.
		$idNum = $id;

		/************************************/
		/*         CREATE FIELD(s)         */
		/**********************************/
		if(substr($id, 0, 3) == "new") {
			//select the available slots value for the TYPE
			$sql = "SELECT available_slots FROM `shopping_cart_settings` WHERE site_id='".NUMO_SITE_ID."'";
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
					$sql = "UPDATE `shopping_cart_settings` SET available_slots='".$availableSlots."' WHERE site_id='".NUMO_SITE_ID."'";
					//print $sql."<br>";
					$dbObj->query($sql);

					//insert basic field information
					$sql = "INSERT INTO `shopping_cart_fields` (slot,locked,site_id) VALUE ('".$slotNumber."','0','".NUMO_SITE_ID."')";
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
		
		//__input_search_options
		//if($_POST[$id.'__type'] == "number" || $_POST[$id.'__type'] == "money") {
		//	$_POST[$id.'__input_options'] = $_POST[$id.'__input_search_options'];
		//}

		//default update query
		$sql = "UPDATE `shopping_cart_fields` SET orderable='".$_POST[$id.'__orderable']."',visibility='".$_POST[$id.'__visible']."',input_type='".$_POST[$id.'__type']."',position=".$position.",name='".htmlentities($_POST[$id.'__name'])."',input_options='".htmlentities($_POST[$id.'__input_options'])."' WHERE id='".$idNum."'";
		$dbObj->query($sql);

		// increase position by 1
		$position++;
	}
	
	
	

	if ($_POST['view_cart_page'] == "special") {
		$_POST['view_cart_page'] = $_POST['view_cart_page_special'];
	}
	//default update query
	$sql = "UPDATE `shopping_cart_settings` SET `require_account_at_checkout`='{$_POST['require_account_at_checkout']}', `show_breadcrumb`='{$_POST['show_breadcrumb']}', `show_order_chooser`='{$_POST['show_order_chooser']}', `order_by_field`='{$_POST['field_order_by']} {$_POST['field_order_by_direction']}', `view_cart_page`='{$_POST['view_cart_page']}', `catalog_visibility`='".$_POST['catalog_visibility']."', `tax_display_preference`='".$_POST['tax_display_preference']."', `default_account_group`='".$_POST['default_account_group']."',`request_shipping_details`='".$_POST['request_shipping_details']."',`store_mode`='".str_replace("'","&#39;",$_POST['store_mode'])."',`company_name`='".str_replace("'","&#39;",$_POST['company_name'])."',`packing_slip_address`='".str_replace("'","&#39;",$_POST['packing_slip_address'])."',`paypal_email`='".str_replace("'","&#39;",$_POST['paypal_email'])."' WHERE `site_id`='".NUMO_SITE_ID."'";



	//print $sql."<br>";
	$dbObj->query($sql);
	//print mysql_error();
    $taxesComplete = array();
    foreach ($_POST as $x => $y) {
		if (substr($x, 0, 6) == "newtax") {
		  $keyData = explode("__", $x);
		  $fieldName = $keyData[1];
		  $key = $keyData[0];
		  if (!$taxesComplete["$key"]) {

			  $taxName = $_POST["{$key}__name"];
			//  $taxType = $_POST["{$key}__type"];
			  $taxRate = $_POST["{$key}__rate"];
			  unset($_POST["{$key}__name"]);
			 // unset($_POST["{$key}__type"]);
			  unset($_POST["{$key}__rate"]);

			  $sql = "INSERT INTO shopping_cart_taxes (site_id, rate_name, tax_rate) VALUES ('".NUMO_SITE_ID."', '{$taxName}', '{$taxRate}')";
			  // print $sql;
			   $dbObj->query($sql);
			   $taxesComplete["$key"] = true;
		  }

		} else if (substr($x, 0, 3) == "tax") {
		  $keyData = explode("__", $x);
		  $fieldName = $keyData[1];
		  $key = $keyData[0];
		  if (!$taxesComplete["$key"]) {
			  $taxID = str_replace("tax", "", $key);
			  $taxName = $_POST["{$key}__name"];
			 // $taxType = $_POST["{$key}__type"];
			  $taxRate = $_POST["{$key}__rate"];
			//  print $key."<br>";
			  unset($_POST["{$key}__name"]);
			  //unset($_POST["{$key}__type"]);
			  unset($_POST["{$key}__rate"]);

			  $sql = "UPDATE shopping_cart_taxes SET rate_name='{$taxName}', tax_rate='{$taxRate}' WHERE site_id='".NUMO_SITE_ID."' AND tax_rate_id='{$taxID}'";
			 // print $sql."<br>";
			  $dbObj->query($sql);
			  $taxesComplete["$key"] = true;
		  }

		}
		//print $x."=".$y."<br>";
	}
	//header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage-type/');
}

function list_account_group_move_options($currentId = 0) {
	global $dbObj;

	$returnStr = "";

	$sql = "SELECT id, name FROM `types` WHERE site_id='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	while($row = mysql_fetch_array($results)) {
		if($currentId == $row['id']) {
			$returnStr .= "<option value=\"".$row['id']."\" selected=\"selected\">".$row['name']."</option>";
		} else {
			$returnStr .= "<option value=\"".$row['id']."\">".$row['name']."</option>";
		}
	}

	return $returnStr;
}
?>
<script type="text/javascript" src="javascript/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="javascript/jquery-ui-1.8.2.custom.min.js"></script>
<script type="text/javascript">
	jQuery(function(){
		jQuery('#tabs').tabs();
	});
</script>

<script language="JavaScript" src="javascript/prototype.js"></script>
<script language="JavaScript" src="javascript/effects.js"></script>
<script language="JavaScript" src="javascript/dragdrop.js"></script>
<script language="JavaScript">
var fieldCount = <?=get_type_field_count()?>;
var taxFieldCount = <?=get_tax_field_count()?>;

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


function getGroupOrder(frm) {
	var order = Sortable.serialize("group_fields");
	//alert(alerttext);

	fieldOrder = document.getElementById("field_order");
	fieldOrder.value = Sortable.sequence("group_fields");
	//alert(fieldOrder.value);
	frm.submit();
	return true;
}

function addRateItem() {
	if(taxFieldCount <= 5) {
		var currentTime = new Date();

		/*generate new div ID*/
		var divIdName = 'newtax'+fieldCount+'-'+currentTime.getTime();

		/*get containing div element (container)*/
		var container = document.getElementById('rate_fields');

		/*create new div*/
		var newdiv = document.createElement('div');

		/*set new div ID*/
		newdiv.setAttribute('id','item_'+divIdName);

		/*set new div ID*/
		newdiv.className = 'lineitem';

		/*set content of new div*/
		newdiv.innerHTML = '<ul><li><div><input type="text" id="'+divIdName+'__name" name="'+divIdName+'__name" value="VAT" /></div></li><li><div><input  class="tax_rate" type="text" id="'+divIdName+'__rate" name="'+divIdName+'__rate" value="0.00">%</div></li></ul></div>';
		//newdiv.innerHTML = "new item";

		/*add new div to list*/
		container.appendChild(newdiv);

		/*add one to new element counter*/
		fieldCount++;

		//Sortable.destroy("rate_fields");

		//Sortable.create('rate_fields',{tag:'div',dropOnEmpty: true, only:'lineitem'});
	} else {
		alert('All available field slots are currently in use');
	}
}
function checkTypeSelection(value, id) {
	//var optionalDisplay = document.getElementById(id+'_field_input_options_display');
	var currencyOptionDisplay = document.getElementById(id+'_field_input__label_options_display');
	//var headingOptionDisplay = document.getElementById(id+'_field_input__heading_options_display');
	//var dateOptionDisplay = document.getElementById(id+'_field_input__date_options_display');

	//if(value == "dropdown list" || value == "radio" || value == "multiple select") {
	//	optionalDisplay.style.display = "block";
	//	currencyrOptionDisplay.style.display = "none";
	//	headingOptionDisplay.style.display = "none";
	//	dateOptionDisplay.style.display = "none";
	if (value == "money") {
		//optionalDisplay.style.display = "none";
		currencyOptionDisplay.style.display = "block";
		//headingOptionDisplay.style.display = "none";
	//	dateOptionDisplay.style.display = "none";
/*	} else if(value == "heading") {
		optionalDisplay.style.display = "none";
		numberOptionDisplay.style.display = "none";
		headingOptionDisplay.style.display = "block";
		dateOptionDisplay.style.display = "none";
	} else if(value == "date") {
		optionalDisplay.style.display = "none";
		numberOptionDisplay.style.display = "none";
		headingOptionDisplay.style.display = "none";
		dateOptionDisplay.style.display = "block";*/
	} else {
		//optionalDisplay.style.display = "none";
		currencyOptionDisplay.style.display = "none";
		//headingOptionDisplay.style.display = "none";
		//dateOptionDisplay.style.display = "none";
	}
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
		newHTML = '<ul><li><img src="images/unlocked.jpg" alt="field unlocked" /></li>'
		newHTML = newHTML + '<li><div><input type="text" name="'+divIdName+'__name" value="Enter Field Name" onblur="checkFieldValue(this)" onclick="checkFieldValue(this)" /></div></li>';
		newHTML = newHTML + '<li><div><select onchange="checkTypeSelection(this.value,\''+divIdName+'\')" name="'+divIdName+'__type" id="'+divIdName+'__type"><?=display_field_type_options("","")?></select></div></li>';
		newHTML = newHTML + '<li><div><select name="'+divIdName+'__visible"><?=display_yes_no_options("")?></select></div></li>';
		newHTML = newHTML + '<li><div><select name="'+divIdName+'__orderable"><?=display_yes_no_options("")?></select></div></li>';
		newHTML = newHTML + '<li><a href="javascript:removeItem(\''+divIdName+'\')"><img src="images/close.jpg" alt="X" border="0" /></a></li>';
		newHTML = newHTML + '</ul>';
	//	newHTML = newHTML + '<div id="'+divIdName+'_field_input_options_display" class="field_optionals" style="display: none;">';
		//newHTML = newHTML + '<div><label for="'+divIdName+'__input_options">Options</label><p>Place each option on a new line</p></div><textarea name="'+divIdName+'__input_options" id="'+divIdName+'__input_options"></textarea></div>';
		newHTML = newHTML + '<div id="'+divIdName+'_field_input__label_options_display" class="field_optionals" style="display: none;"><div><label for="'+divIdName+'__input_options">Currency</label><p>Please select the currency you would like displayed</p></div>';
		newHTML = newHTML + '<select name="'+divIdName+'__input_options" id="'+divIdName+'__input_options"><?php print generate_list_options( array("AUD" => "Australian Dollar ($)","CAD" => "Canadian Dollar ($)","EUR" => "Euro (&#128;)","NZD" => "New Zealand Dollar ($)","GBP" => "Pound Sterling (&#163;)","USD" => "U.S. Dollar ($)"),$field['input_options'],","); ?></select>';
        newHTML = newHTML + '</div>';
		//newHTML = newHTML + '<div id="'+divIdName+'_field_input__heading_options_display" class="field_optionals" style="display: none;"><div><label for="'+divIdName+'__input_options">Heading</label><p>Enter the heading you would like to appear</p></div><input type="text" name="'+divIdName+'__input_heading_options" id="'+divIdName+'__input_heading_options" /></div>';
		//newHTML = newHTML + '<div id="'+divIdName+'_field_input__date_options_display" class="field_optionals" style="display: none;"><div><label for="'+divIdName+'__input_options">Search Logic</label><p>Timeframe</p></div>';
		//newHTML = newHTML + '<select name="'+divIdName+'__input_date_options_logic" id="'+divIdName+'__input_date_options_logic"><option value="equals">Exactly</option><option value="plusorminus">Plus or Minus</option><option value="withinlessthan">Within </option><option value="equaltoorgreater">Equal To, or Greater Than</option></select>';
		//newHTML = newHTML + '<br/><input type="text" style="width: 20px;"><select><option>Days</option><option>Months</option><option>Years</option></div>'
		newdiv.innerHTML = newHTML;
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

function changeViewCartPage(selectBox) {
	if (selectBox.selectedIndex == 0) {
		document.getElementById('view_cart_page_special').style.display = "none";
		
	} else {
		document.getElementById('view_cart_page_special').style.display = "block";
	}
}
</script>
<style>
	html { padding: 0px; margin: 0px; }
	body { padding: 0px; margin: 0px; font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; }
	div { padding: 0px; margin: 0px; }
	.headings{ padding: 0px; margin: 0px 0px 5px 0px; border: 1px solid #ccc; }
	.lineitem { padding: 0px; margin: 0px 0px 5px 0px; border: 1px solid #ccc;  background: #EDEDED; cursor: move;}
	#tabs-attributes .headings {
		width: 760px;
	}
	#tabs-attributes .lineitem {
		width: 760px;
	}
	#tabs-tax_rates .headings {
		width: 340px;
	}
	#tabs-tax_rates .lineitem {
		width: 340px;
	}	
	.headings ul { height: 28px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_fields_heading.jpg') repeat-x; list-style:none;}
	.headings ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left; clear: none;}
	.headings ul li img { padding: 0px; margin: 0px; display: block; height: 28px; }
	.headings ul li h2 {line-height: 28px; display: inline-block; width: 170px; color: #333; font-size: 20px; font-weight: normal; text-align: center; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_fields_heading_background.jpg') top right; }

	.lineitem ul {height: 44px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_field.jpg') repeat-x;}
	.lineitem ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left;}
	.lineitem ul li img { padding: 0px; margin: 0px; display: block;}
	.lineitem ul li div { height: 44px; display: table-cell; vertical-align: middle; width: 170px; font-size: 1em; text-align: center; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_field_background.jpg') no-repeat top right; }
	.lineitem ul li input, .lineitem ul li select { font-size: 1em; font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; width: 150px; padding: 2px; margin: 0px;}
	.lineitem ul li a {line-height: 44px; text-align: center; width: 24px; color: #aaa; text-decoration: none; display: block;}
	.lineitem label { margin: 0px; padding: 0px; vertical-align: top; display: inline-block; color: #333; font-size: 20px; font-weight: normal;}
	.lineitem p { margin: 0px; padding: 5px 0px; color: #777; font-size: 12px; font-weight: normal;}
	.lineitem div.field_optionals { padding: 10px; border-top: 1px solid #ccc; height: 80px;}
	.lineitem div div { margin: 0px 0px 0px 30px; width: 175px; float: left;}
	.lineitem textarea { vertical-align: top; width: 260px; margin: 0px; height: 70px;}
	ul.form_display li input {width: 400px;margin: 0px;}
	ul.form_display li textarea {width: 400px; height: 50px;margin: 0px;}
	ul.form_display li {margin: 3px; padding: 0px;}
	input.tax_rate { width: 50px; text-align: right; }

	.bttm_submit_button {position: fixed; bottom: 0px; right: 0px; background: #779FE1; border-top: 1px solid #2A61BD; width: 100%; height: 50px; padding: 0px 20px; margin: 0px;}
.bttm_submit_button input {background: #EEEEEE; color: #333; border: 1px solid #333; height: 30px; margin: 10px 0px 10px 210px;}
.bttm_submit_button input:hover {background: #bbb; color: #333; border: 1px solid #333; cursor: pointer;}
html {padding-bottom: 50px;}
ul.form_display li label { width: 205px !important; }

.ui-corner-top {
-webkit-border-top-left-radius: 3px;
-webkit-border-top-right-radius: 3px;
-moz-border-radius-topleft: 3px;
-moz-border-radius-topright: 3px;
border-top-left-radius: 3px;
border-top-right-radius: 3px;
}
.ui-helper-hidden { display: none; }
.ui-helper-hidden-accessible { position: absolute; left: -99999999px; }
.ui-helper-reset { margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 12px; list-style: none; }
.ui-helper-clearfix:after { content: "."; display: block; height: 0; clear: both; visibility: hidden; }
.ui-helper-clearfix { display: inline-block; }
/* required comment for clearfix to work in Opera \*/
* html .ui-helper-clearfix { height:1%; }
.ui-helper-clearfix { display:block; }
/* end clearfix */

.ui-widget-content { border: 0px solid #ccc; background: #F9F9F9; color: #333333; }
.ui-widget-content a { color: #333333; }
.ui-widget-header { border: 0px solid #2A61B3; background-color: #F9F9F9 !important; color: #ffffff; font-weight: bold; border-bottom: 1px solid #cccccc;}
.ui-widget-header a { color: #ffffff; }

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default { border: 1px solid #cccccc; background: #eee; font-weight: bold; color: #3473D1; }
.ui-state-default a, .ui-state-default a:link, .ui-state-default a:visited { color: #3473D1; text-decoration: none; }
.ui-state-hover, .ui-widget-content .ui-state-hover, .ui-widget-header .ui-state-hover, .ui-state-focus, .ui-widget-content .ui-state-focus, .ui-widget-header .ui-state-focus { border: 1px solid #3473D1; background: #DBE6F7; font-weight: bold; color: #3473D1; }
.ui-state-hover a, .ui-state-hover a:hover { color: #3473D1; text-decoration: none; }
.ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active { border: 1px solid #cccccc; background: #F9F9F9; font-weight: bold; color: #2A61B3; }
.ui-state-active a, .ui-state-active a:link, .ui-state-active a:visited { color: #2A61B3; text-decoration: none; }
.ui-widget :active { outline: none; }
.ui-tabs { position: relative; padding: 0em; zoom: 1; } /* position: relative prevents IE scroll bug (element with position: relative inside container with overflow: auto appear as "fixed") */
.ui-tabs .ui-tabs-nav { margin: 0; padding: .2em .2em 0; }
.ui-tabs .ui-tabs-nav li { list-style: none; float: left; position: relative; top: 1px; margin: 0 .2em 1px 0; border-bottom: 0 !important; padding: 0; white-space: nowrap; }
.ui-tabs .ui-tabs-nav li a { float: left; padding: .5em 1em; text-decoration: none; }
.ui-tabs .ui-tabs-nav li.ui-tabs-selected { margin-bottom: 0; padding-bottom: 1px; }
.ui-tabs .ui-tabs-nav li.ui-tabs-selected a, .ui-tabs .ui-tabs-nav li.ui-state-disabled a, .ui-tabs .ui-tabs-nav li.ui-state-processing a { cursor: text; }
.ui-tabs .ui-tabs-nav li a, .ui-tabs.ui-tabs-collapsible .ui-tabs-nav li.ui-tabs-selected a { cursor: pointer; } /* first selector in group seems obsolete, but required to overcome bug in Opera applying cursor: text overall if defined elsewhere... */
.ui-tabs .ui-tabs-panel { display: block; border-width: 0; padding: 1em 0em; background: none; }
.ui-tabs .ui-tabs-hide { display: none !important; }

.bttm_submit_button {position: fixed; bottom: 0px; right: 0px; background: #779FE1; border-top: 1px solid #2A61BD; width: 100%; height: 50px; padding: 0px 20px; margin: 0px;}
.bttm_submit_button input {background: #EEEEEE; color: #333; border: 1px solid #333; height: 30px; margin: 10px 0px 10px 210px;}
.bttm_submit_button input:hover {background: #bbb; color: #333; border: 1px solid #333; cursor: pointer;}
</style><!--[if lte IE 8]>
<style>
	.lineitem ul li div { height: 34px; padding: 10px 0px 0px 0px; }
</style>
<![endif]-->
<h2>Store Settings</h2>
<div id="tabs">
<ul>
  <li><a href="#tabs-store">General</a></li>
  <li><a href="#tabs-catalog">Catalog</a></li>
  <li><a href="#tabs-checkout">Checkout</a></li>
  <li><a href="#tabs-attributes">Product Attributes</a></li>
  <li><a href="#tabs-tax_rates">Tax Rates</a></li>
</ul>

<form method="post">
<div id="tabs-store">
		<fieldset>
			<legend>Settings</legend>
			<?php

			$sql = "SELECT * FROM `shopping_cart_settings` WHERE site_id='".NUMO_SITE_ID."'";
			//print $sql."<br>";
			$result = $dbObj->query($sql);

			if($row = mysql_fetch_array($result)) {
				
			?>
			<ul class="form_display">
				<li><label for="paypal_store_mode">Store Mode:</label><select name="store_mode" id="paypal_store_mode"><option value="1">Live</option><option value="0" <?php if($row['store_mode'] == 0) { print 'selected="selected"'; } ?>>Testing (PayPal Sandbox)</option></select></li>
				<li><label for="catalog_visibility">Store Visibility:</label><select name="catalog_visibility" id="catalog_visibility"><option value="0">Public (anyone can view)</option>
                	<option value="1" <?php if($row['catalog_visibility'] == 1) { print 'selected="selected"'; } ?>>Restricted</option>
                </select></li>			
                <li><label for="packing_slip_company_name">Company Name:</label><input type="text" name="company_name" id="packing_slip_company_name" value="<?=$row['company_name']?>" /></li>
				<li><label for="packing_slip_address">Company Address:</label><textarea name="packing_slip_address" id="packing_slip_address"><?=$row['packing_slip_address']?></textarea></li>

				</ul>
			<?php
			}
			?>
			<h2>Default Account Group for Checkout</h2>
			<p style='font-style: italic; color: #444;'>Please select the account group you would like new subscribers to be added to if they do not already have an account.</p>
			<ul class="form_display">
				<li><label for="default_account_group">Account Group:</label><select id="default_account_group" name="default_account_group"><option value="0">- SELECT -</option><?=list_account_group_move_options($row['default_account_group'])?></select></li>
			</ul>
		</fieldset>
</div>
<div id="tabs-catalog">
		<fieldset>
			<legend>Catalog</legend>
			<ul class="form_display">
				<li><label for="paypal_store_mode">View Cart Page:</label>
                <select style='float: left' onchange='changeViewCartPage(this)' name="view_cart_page" id="view_cart_page"><option value="">Default</option><option value="special" <?php if($row['view_cart_page'] != "") { print 'selected="selected"'; } ?>>Use Specific Page</option></select>
                <input <?php if ($row['view_cart_page'] == "") { ?>style='display: none;'<?php } ?> type="text" name="view_cart_page_special" id="view_cart_page_special" value="<?=$row['view_cart_page']?>" />
                </li>
				<li><label for="tax_display_preference">Tax Display Preferences:</label><select name="tax_display_preference" id="tax_display_preference"><option value="0">Show NET Price (default)</option>
                <option value="1" <?php if($row['tax_display_preference'] == 1) { print 'selected="selected"'; } ?>>Show NET Price and TAX value</option>
                <option value="2" <?php if($row['tax_display_preference'] == 2) { print 'selected="selected"'; } ?>>Show GROSS Price followed by 'Including TAX'</option>
                </select></li>
				<li><label for="field_order_by">Default Catalog Sort Order:</label><select name="field_order_by" id="field_order_by">
                <option value="when_created">When Created</option>
<?php
			$sql = "SELECT * FROM `shopping_cart_fields` WHERE site_id='".NUMO_SITE_ID."' AND orderable='1' ORDER BY `position`,`name`";
			$fieldResults = $dbObj->query($sql);
			$orderByField = array_shift(explode(" ", $row['order_by_field']));
			if ($orderByField == "") {
				$orderByField = "slot_1";
			}
			$orderByDirection = array_pop(explode(" ", $row['order_by_field']));
			if ($orderByDirection == "") {
				$orderByDirection = "ASC";
			}

			while ($fieldRecord = mysql_fetch_array($fieldResults)) { 
			if ($fieldRecord['orderable'] == "1") { 
			?>
			<option <?php if ($orderByField == "slot_".$fieldRecord['slot']) { print "selected"; } ?> value="slot_<?php echo $fieldRecord['slot']; ?>"><?php echo $fieldRecord['name']; ?></option>
			<?php	
			}
			} ?>
                </select>
                <select name="field_order_by_direction" id="field_order_by_direction">
                  <option <? if ($orderByDirection == "ASC") { print "selected"; } ?> value='ASC'>Ascending</option>
                  <option <? if ($orderByDirection == "DESC") { print "selected"; } ?> value='DESC'>Descending</option>
                </select>
                </li>
				<li><label for="field_order_by">Show Catalog Breadcrumb:</label>
                <select name="show_breadcrumb" id="show_breadcrumb">
			<?=display_yes_no_options($row['show_breadcrumb'])?>
            </select>

                </li>
				<li><label for="field_order_by" style='white-space: nowrap'>Show Catalog Order By Chooser:</label>
                <select name="show_order_chooser" id="show_order_chooser">
			<?=display_yes_no_options($row['show_order_chooser'])?>
            </select>

                </li>

                </ul>
				</fieldset>
</div>
<div id="tabs-checkout">
		<fieldset>
			<legend>Checkout</legend>
			<ul class="form_display">
				<li><label for="paypal_email">PayPal Account:</label><input type="text" name="paypal_email" id="paypal_email" value="<?=$row['paypal_email']?>" /></li>
				<li><label for="request_shipping_details">Shipping Details:</label><select name="request_shipping_details" id="request_shipping_details"><option value="1">Require Shipping Information</option><option value="0" <?php if($row['request_shipping_details'] == 0) { print 'selected="selected"'; } ?>>Do Not Request</option></select></li>
				<li><label for="paypal_store_ipn">PayPal IPN URL:</label><?php if (REMOTE_SERVICE === true) { ?>http://numo.server-apps.com/remote/component.numo?module=shopping_cart&component=process&nsid=<?php print $numo->strToHex(NUMO_SITE_ID); ?><?php } else { ?>http://<?php print $numo->getRootFolder(); ?>/component.numo?module=shopping_cart&component=process<? } ?></li>
				<li><label for="require_account_at_checkout">Require Account At Checkout:</label><select name="require_account_at_checkout" id="require_account_at_checkout"><option value="1">Yes</option><option value="0" <?php if($row['require_account_at_checkout'] == 0) { print 'selected="selected"'; } ?>>No</option></select></li>
</ul>	
</fieldset>			
</div>


<div id="tabs-attributes">
		<fieldset>
			<legend>Standard Product Attributes</legend>
			<div class="headings">
				<ul>
					<li><img src="images/manage_fields_heading_locks.jpg"></li>
					<li><h2>Name</h2></li>
					<li><h2>Type</h2></li>
					<li><h2>Visible</h2></li>
					<li><h2>Orderable</h2></li>
					<li>&nbsp;</li>
				</ul>
			</div>
			<?php
			//load field information for accounts group
			$sql = "SELECT * FROM `shopping_cart_fields` WHERE site_id='".NUMO_SITE_ID."' ORDER BY `position`,`name`";
			//print $sql."<br>";
			$results = $dbObj->query($sql);

			echo '<div id="group_fields">';

			while($field = mysql_fetch_array($results)) {
			?>
				<div id="item_<?=$field['id']?>" class="lineitem">
					<ul>
						<li><img src="images/<? if ($field['locked'] == "1") { print "locked"; } else { print "unlocked"; } ?>.jpg" alt="attribute locked" /></li>
						<li><div><input type="text" name="<?=$field['id']?>__name" value="<?=$field['name']?>" /></div></li>
						<li><div><select <? if ($field['locked'] == "1") { print "disabled='disabled'"; } ?> id="<?=$field['id']?>__type" name="<?=$field['id']?>__type"><?=display_field_type_options($field['input_type'],$field['locked'])?></select></div></li>
                        <li><div><select <? if ($field['locked'] == "1") { print "disabled='disabled'"; } ?> name="<?=$field['id']?>__visible"><?=display_yes_no_options($field['visibility'])?></select></div></li>
                        <li><div><select name="<?=$field['id']?>__orderable"><?=display_yes_no_options($field['orderable'])?></select></div></li>
                        <li><?php if($field['locked'] == "0") { print '<a href="javascript:removeItem(\''.$field['id'].'\')"><img src="images/close.jpg" alt="X" border="0" /></a>'; } else { ?><input type='hidden' name='<?php echo $field['id']; ?>__type' value='<?php echo $field['input_type']; ?>' /><input type='hidden' name='<?php echo $field['id']; ?>__visible' value='<?php echo $field['visibility']; ?>' /><? } ?></li>
                    </ul>
					<?php
					if($field['input_type'] == "money") {
						$currencyOptions = array("AUD" => "Australian Dollar ($)","CAD" => "Canadian Dollar ($)","EUR" => "Euro (&#128;)","NZD" => "New Zealand Dollar ($)","GBP" => "Pound Sterling (&#163;)","USD" => "U.S. Dollar ($)");
					?>
					<div id="<?=$field['id']?>_field_input_options_display" class="field_optionals">
						<div><label for="<?=$field['id']?>__input_options">Currency</label><p>Please select the currency you would like to use for your products</p></div>
						<select name="<?=$field['id']?>__input_options" id="<?=$field['id']?>__input_options"><?=generate_list_options($currencyOptions,$field['input_options'],",")?></select>
					</div>
					<?php
					} else if($field['input_type'] == "dropdown list" || $field['input_type'] == "multiple select" || $field['input_type'] == "checkbox") {
						$currencyOptions = array("AUD" => "Australian Dollar ($)","CAD" => "Canadian Dollar ($)","EUR" => "Euro (&#128;)","NZD" => "New Zealand Dollar ($)","GBP" => "Pound Sterling (&#163;)","USD" => "U.S. Dollar ($)");
					?>
					<div id="<?=$field['id']?>_field_input_options_display" class="field_optionals">
						<div><label for="<?=$field['id']?>__input_options">Options</label><p>Place each option on a new line</p></div>
						<textarea name="<?=$field['id']?>__input_options" id="<?=$field['id']?>__input_options"><?=html_entity_decode($field['input_options'])?></textarea>
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
	<input type="hidden" name="cmd" value="update" />
	<input type="hidden" name="field_order" id="field_order" value="" />
	<input type="hidden" name="field_remove" id="field_remove" value="" />
	</div>


<?php
mysql_free_result($results);

$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_taxes");
$exists = (@mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
  $dbObj->query("CREATE TABLE `shopping_cart_taxes` (
`tax_rate_id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`site_id` INT( 11 ) NOT NULL DEFAULT '1',
`rate_name` VARCHAR( 50 ) NOT NULL ,
`tax_rate` DOUBLE( 6, 2 ) NOT NULL ,
PRIMARY KEY ( `tax_rate_id` , `site_id` )
)");
}

?>
<div id="tabs-tax_rates">
	<fieldset>
    
		<legend>Tax Rates</legend>
		<div class="headings">
			<ul>
				<li><h2>Name</h2></li>
				<li><h2>Rate</h2></li>
			</ul>
		</div>
		<?php
		//load field information for accounts group
		$sql = "SELECT * FROM `shopping_cart_taxes` WHERE `site_id`='".NUMO_SITE_ID."' ORDER BY `rate_name`, `tax_rate`";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		echo '<div id="rate_fields">';

		while($field = mysql_fetch_array($results)) {
		?>
			<div id="item_<?=$field['tax_rate_id']?>" class="lineitem">
				<ul>
					<li><div><input type="text" id="tax<?=$field['tax_rate_id']?>__name" name="tax<?=$field['tax_rate_id']?>__name" value="<?=$field['rate_name']?>" /></div></li>
					<li><div><input class='tax_rate' type="text" id="tax<?=$field['tax_rate_id']?>__rate" name="tax<?=$field['tax_rate_id']?>__rate" value="<?=$field['tax_rate']?>">%</div></li>
				</ul>
			</div>
		<?php
		}

		echo '</div>';
		?>
		<input type="button" name="nocmd2" value="Add New Attribute" onClick="addRateItem()" />
	</fieldset>
    </div>
        <br /><br /><br />
    
	<div class="bttm_submit_button">
	<input type="button" name="nocmd" value="Save" onClick="getGroupOrder(this.form)" />
	</div>

</form>
  </div>
<br /><br /><br />
<?php

function display_yes_no_options($value) {
	if($value == 1) {
		return "<option value=\"1\" selected=\"selected\">Yes</option><option value=\"0\">No</option>";
	} else {
		return "<option value=\"1\">Yes</option><option value=\"0\" selected=\"selected\">No</option>";
	}
}

function display_rate_type_options($value) {
	if($value == 1) {
		return "<option value=\"0\">Not Included in Item Gross</option><option value=\"1\" selected=\"selected\">Included in Item Gross</option>";
	} else {
		return "<option value=\"0\" selected=\"selected\">Not Included in Item Gross</option><option value=\"1\">Included in Item Gross</option>";
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
	if($locked == 1) {
		return "<option value=\"".$currentValue."\">".ucfirst($currentValue)."</option>";
	} else {
		//$fieldTypes = array ("text","number","dropdown list","checkbox");
		$fieldTypes = array ("text","link","number","money","text box");
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

	$sql = "SELECT COUNT(*) as 'field_count' FROM `shopping_cart_fields` WHERE site_id='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		return $row['field_count'] + 1;
	}

	return 5;
}

function get_tax_field_count() {
	global $dbObj;
	global $_GET;

	$sql = "SELECT COUNT(*) as 'field_count' FROM `shopping_cart_taxes` WHERE site_id='".NUMO_SITE_ID."'";
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