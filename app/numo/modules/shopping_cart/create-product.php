<?php
$result = $dbObj->query("SELECT * FROM `shopping_cart_fields` WHERE input_type='shipping type'");

$exists = (mysql_num_rows($result))?TRUE:FALSE;
if (!$exists) {
  $dbObj->query("INSERT INTO shopping_cart_fields (id, site_id, name, slot, position, required, locked, input_type, input_options) VALUES (4, ".NUMO_SITE_ID.", 'Technical Specs', 4, 8, 1, 1, 'text box', ''), (5, ".NUMO_SITE_ID.", 'Shipping Type', 5, 4, 1, 1, 'shipping type', '0'), (6, ".NUMO_SITE_ID.", 'Internal', 6, 5, 1, 1, 'upon completion action', ''),(7, ".NUMO_SITE_ID.", 'SKU/ID', 7, 2, 1, 1, 'text', ''),(8, ".NUMO_SITE_ID.", 'Tax Rate', 8, 6, 1, 1, 'tax rate', '')");
  $dbObj->query("UPDATE shopping_cart_fields SET position=3 WHERE input_type='money' AND site_id=".NUMO_SITE_ID);
  $dbObj->query("UPDATE shopping_cart_fields SET position=7 WHERE input_type='text box' AND position='3' AND site_id=".NUMO_SITE_ID);
  //print "done";
} else {
	//print "exists";
}

// 1 product name
// 2 product sku/id
// 3 product type
// 4 internal payment complete action
// 5 price
// 6 description
// 7 tech specs

$errorMsg = "";

//get all categories for site
$sql = "SELECT * FROM `shopping_cart_categories` WHERE `site_id`='".NUMO_SITE_ID."' ORDER BY `position`";
$results = $dbObj->query($sql);

$categories = array();

while($row = mysql_fetch_array($results)) {
	$categories[$row['id']] = array('label' => $row['label'],'parent_id' => $row['parent_id']);
}

if($_POST['cmd'] == "create") {
	foreach ($_POST as $key => $value) {
    	$_POST["$key"] = sanitize_field($value);
  	}	
	
	//if status checkbox checked then dont display product in display components
	if(isset($_POST['status'])) {
		$_POST['status'] = 0;

	//uncheck, allow product to be displayed
	} else {
		$_POST['status'] = 1;
	}

	$productObj = new Product();
	$listingId = $productObj->create($_POST);
	if($listingId != null || $listingId != "") {
			if (REMOTE_SERVICE === true) {
			  $uploadsDir = ABSOLUTE_ROOT_PATH."numo/uploads/modules/shopping_cart";
			} else {
			  $uploadsDir = "modules/shopping_cart/uploads";
			}


		//upload image files
		foreach($_FILES as $fieldName => $fieldValue) {
			//upload new listing image
			if(substr($fieldName,0,11) == "new_image__") {
				if ($fieldValue['error'] == UPLOAD_ERR_OK) {
					$newImageId = substr($fieldName,11);

					//complete upload and move file
					$tmp_name = $_FILES[$fieldName]["tmp_name"];
					$name = "u".$_SESSION['account_id'].".".time().".".$_FILES[$fieldName]["name"];
					move_uploaded_file($tmp_name, "$uploadsDir/$name");

					//resize image if it is the right type of image
					/*$ext = explode(".",strtolower($_FILES[$fieldName]['name']));
					$max_width = 400;
					$max_height = 400;
					$src = "";
					$skipResize = false;
					$uploadPath = "$uploadsDir/$name";

					if($ext[1] == "jpg" || $ext[1] == "jpeg") {
					$src = imagecreatefromjpeg($uploadPath);
					} elseif ($ext[1] == "gif") {
					$src = imagecreatefromgif($uploadPath);
					} elseif ($ext[1] == "png") {
					$src = imagecreatefrompng($uploadPath);
					} else {
						//print "error";
						$skipResize = true;
					}

					if(!$skipResize) {
						list($width,$height)=getimagesize($uploadPath);

						$x_ratio = $max_width / $width;
						$y_ratio = $max_height / $height;

						if(($width <= $max_width) && ($height <= $max_height)){
							$tn_width = $width;
							$tn_height = $height;
						} elseif(($x_ratio * $height) < $max_height) {
							$tn_height = ceil($x_ratio * $height);
							$tn_width = $max_width;
						} else {
							$tn_width = ceil($y_ratio * $width);
							$tn_height = $max_height;
						}

						$tmp=imagecreatetruecolor($tn_width,$tn_height);
						imagecopyresampled($tmp,$src,0,0,0,0,$tn_width, $tn_height,$width,$height);

						imagejpeg($tmp,$uploadPath,100);

						imagedestroy($src);
						imagedestroy($tmp);
					}*/

					//update database records
					$sql = "INSERT INTO `shopping_cart_product_images` (listing_id,file_name,description) VALUES ('".$listingId."','".$name."','".$_POST['new_image_description__'.$newImageId]."')";
					//print $sql."<br>";
					$dbObj->query($sql);
				}
			}
		}

		/*******************************/
		/*   SAVE PRODUCT CATEGORIES   */
		/*******************************/
		$insertCategoryList = array();

		//loop thru selected categories
		foreach($_POST['categories'] as $key => $value) {
			//loop thru the parents of the category and add them to the list of categories
			while(isset($categories[$value])) {
				$insertCategoryList[$value] = $value;
				$value = $categories[$value]['parent_id'];
			}
		}

		$insertItems = "";

		//loop thru the list of categories to add for the product
		foreach($insertCategoryList as $key => $value) {
			$insertItems .= "('".$listingId."','".$value."'),";
		}

		//if insert items set, insert into the database
		if(strlen($insertItems) > 0) {
			$insertItems = substr($insertItems, 0, -1);
			$sql = "INSERT INTO `shopping_cart_product_categories` (product_id,category_id) VALUES ".$insertItems;
			//print $sql."<br>";
			$dbObj->query($sql);
		}

		/************************************/
		/*      UPDATE ATTRIBUTES(s)       */
		/***********************************/
		if($_POST['field_order'] != "") {
			//field order value will be IDs separated by a comma.  Use explode function to break value apart into array
			$fieldOrderArr = explode(',', $_POST['field_order']);

			//set starting position value
			$position = 1;

			//loop thru field id and save order
			foreach($fieldOrderArr as $key => $id) {
				//make copy of the id incase a new field is being created.
				$idNum = $id;

				/***********************************/
				/*      CREATE ATTRIBUTES(s)       */
				/***********************************/
				if(substr($id, 0, 3) == "new") {
					//insert basic field information
					$sql = "INSERT INTO `shopping_cart_optional_product_attributes` (product_id) VALUE ('".$listingId."')";
					//print $sql."<br>";
					$dbObj->query($sql);

					//get the ID for the field just inserted in the database
					$sql = "SELECT LAST_INSERT_ID() as 'id'";
					//print $sql."<br>";
					$attrResult = $dbObj->query($sql);

					if($attribute = mysql_fetch_array($attrResult)) {
						//assign ID to idNum variable to be used in update commands lower down
						$idNum = $attribute['id'];
					}
				}

				//update query
				$sql = "UPDATE `shopping_cart_optional_product_attributes` SET position='".$position."',type='".$_POST[$id.'__type']."',label='".htmlentities($_POST[$id.'__name'])."',required='".htmlentities($_POST[$id.'__required'])."' WHERE id='".$idNum."'";
				//print $sql."<br>";
				$dbObj->query($sql);

				/*************************************/
				/*   UPDATE OPTIONAL ATTRIBUTES(s)   */
				/*************************************/
				foreach($_POST[$id.'__input_options'] as $oKey => $oId) {
					$optionalLabel = $_POST[$id.'__input_options_item_label__'.$oId];
					$optionalCost  = $_POST[$id.'__input_options_item_cost__'.$oId];

					//if cost is not a number set to 0 value
					if(!is_numeric($optionalCost)) {
						$optionalCost = "0.00";
					}

					//insert new row for option
					if(substr($oId, 0, 3) == "new") {
						if($optionalLabel != "") {
							$sql = "INSERT INTO `shopping_cart_optional_product_attribute_options` (attribute_id,status,label,cost) VALUES ('".$idNum."',1,'".$optionalLabel."','".$optionalCost."')";
							//print $sql."<br>";
							$dbObj->query($sql);
						}

					//update existing option
					} else {
						//if label does not have a value set status of option to '0' to "remove" option
						if($optionalLabel == "") {
							$sql = "UPDATE `shopping_cart_optional_product_attribute_options` SET status=0 WHERE id='".$oId."'";
							//print $sql."<br>";
							$dbObj->query($sql);
						} else {
							$sql = "UPDATE `shopping_cart_optional_product_attribute_options` SET `label`='".$optionalLabel."',cost='".$optionalCost."' WHERE id='".$oId."'";
							//print $sql."<br>";
							$dbObj->query($sql);
						}
					}
				}

				//increase position by 1
				$position++;
			}
		}
		/*****************************/
		/*   REDIRECT TO EDIT PAGE   */
		/*****************************/
		header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage-products/?id='.$listingId);
	} else {
		print "<h2>System Error.  Unable to create new product.</h2>";
	}
}
?>
<style>
ul.checkbox_display_options {margin:0; padding:0; float: left; }
hr {padding: 0px; margin: 3px 0px; border: 1px dashed #DDD;}
.listing_image_thumb {width: 64px; border: 1px solid #CCC;}
textarea.image_description_textarea {width: 400px; height: 50px; color:#000;}
textarea.image_description_textarea_inactive {width: 400px; height: 50px; color:#666; font-style: italic;}
ul.form_display li input.text_input {width: 400px;margin: 0px;}
ul.form_display li textarea {width: 400px; height: 100px;margin: 0px;}
ul.form_display li {margin: 3px; padding: 0px;}
.headings{ padding: 0px; margin: 0px 0px 5px 0px; border: 1px solid #ccc; width: 610px;}
.headings ul {height: 28px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_fields_heading.jpg') repeat-x; list-style:none;}
.headings ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left; clear: none;}
.headings ul li img { padding: 0px; margin: 0px; display: block; height: 28px; }
.headings ul li h2 {line-height: 28px; display: inline-block; width: 170px; color: #333; font-size: 20px; font-weight: normal; text-align: center; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_fields_heading_background.jpg') top right; }

.lineitem { padding: 0px; margin: 0px 0px 5px 0px; border: 1px solid #ccc; width: 610px; background: #EDEDED; cursor: move;}
.lineitem ul {height: 44px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_field.jpg') repeat-x;}
.lineitem ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left;}
.lineitem ul li img { padding: 0px; margin: 0px; display: block;}
.lineitem ul li div { height: 44px; display: table-cell; vertical-align: middle; width: 170px; font-size: 1em; text-align: center; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_field_background.jpg') no-repeat top right; }
.lineitem ul li input, .lineitem ul li select { font-size: 1em; font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; width: 150px; padding: 2px; margin: 0px;}
.lineitem ul li a {line-height: 44px; text-align: center; width: 24px; color: #aaa; text-decoration: none; display: block;}
.lineitem label { margin: 0px; padding: 0px; vertical-align: top; display: inline-block; color: #333; font-size: 20px; font-weight: normal;}
.lineitem p { margin: 0px; padding: 5px 0px; color: #777; font-size: 12px; font-weight: normal;}
.lineitem div.field_optionals { padding: 10px; border-top: 1px solid #ccc; min-height: 80px;}
.lineitem div div { margin: 0px 0px 0px 30px; width: 175px; float: left;}
.lineitem textarea { vertical-align: top; width: 260px; margin: 0px; height: 70px;}
.item_cost {width: 50px;}
.item_add_button {margin-left: 210px;}
.bttm_submit_button { position: fixed; bottom: 0px; right: 0px; background: #aaaaaa;  width: 100%; height: 70px; padding: 0px 20px; margin: 0px;}
.bttm_submit_button input { margin: 10px 0px 10px 210px;}
html {padding-bottom: 70px;}
table.egood_config {
  border: 1px solid #cccccc;
  border-radius: 3px;
}
table.egood_config th {
	background: #E6E6E6 url('images/manage_fields_heading.jpg') repeat-x;
	font-size: 9pt;
	padding: 3px 10px;
	border-bottom: 1px solid #cccccc;

}
</style>
<script language="JavaScript" src="javascript/prototype.js"></script>
<script language="JavaScript" src="javascript/effects.js"></script>
<script language="JavaScript" src="javascript/dragdrop.js"></script>
<script>
var iteration = 3;

function checkCaptionFieldValue(field) {
	if(field.value == "Enter your image caption here") {
		field.value = "";
		field.className = "image_description_textarea";
	} else if(field.value == "") {
		field.value = "Enter your image caption here";
		field.className = "image_description_textarea_inactive";
	}
}
var fieldCount = 0;

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

function checkTypeSelection(value, id) {
	var optionalDisplay = document.getElementById(id+'_field_input_options_display');

	if(value == "dropdown list") {
		optionalDisplay.style.display = "block";
	} else {
		optionalDisplay.style.display = "none";
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
		newdiv.innerHTML = '<ul><li><img src="images/unlocked.jpg" alt="click and drag to move" /></li><li><div><input type="text" name="'+divIdName+'__name" value="Enter Attribute Name" onblur="checkFieldValue(this)" onclick="checkFieldValue(this)" /></div></li><li><div><select onchange="checkTypeSelection(this.value,\''+divIdName+'\')" name="'+divIdName+'__type" id="'+divIdName+'__type"><?=display_field_type_options("")?></select></div></li><li><div><select  name="'+divIdName+'__type" id="'+divIdName+'__required"><?=display_yes_no_options("")?></select></div></li><li><a href="javascript:removeItem(\''+divIdName+'\')"><img src="images/close.jpg" alt="X" border="0" /></a></li></ul><div id="'+divIdName+'_field_input_options_display" class="field_optionals" style="display: none;"><div><label for="'+divIdName+'__input_options">Options</label><p>Enter the label and price (optional) difference for each option</p></div><table id="'+divIdName+'__input_options_table"><tr><td><b>Label</b></td><td><b>Cost</b></td></tr><tr><td><input type="text" name="'+divIdName+'__input_options_item_label__new1" value="" /></td><td><input type="text" name="'+divIdName+'__input_options_item_cost__new1" class="item_cost" value="0.00" /><input type="hidden" name="'+divIdName+'__input_options[]"value="new1" /></td></tr><tr><td><input type="text" name="'+divIdName+'__input_options_item_label__new2" value="" /></td><td><input type="text" class="item_cost" name="'+divIdName+'__input_options_item_cost__new2" value="0.00" /><input type="hidden" name="'+divIdName+'__input_options[]" value="new2" /></td></tr></table><input type="button" class="item_add_button" name="addoptionalitem" value="Add New Option" onclick="javascript:addOptionalItem(\''+divIdName+'\')" /></div>';
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
	if(field.value == "Enter Attribute Name") {
		field.value = "";
	} else if(field.value == "") {
		field.value = "Enter Attribute Name";
	}
}

function addOptionalItem(id) {
	var tbl = document.getElementById(id+'__input_options_table');
	var lastRow = tbl.rows.length;

	// if theres no header row in the table, then iteration = lastRow + 1
	var iteration = lastRow+1;
	var row = tbl.insertRow(lastRow);

	// label cell
	var cell1 = row.insertCell(0);
	var el = document.createElement('input');
	el.type = 'text';
	el.name = id+'__input_options_item_label__new'+iteration;
	el.id = id+'__input_options_item_label__new'+iteration;

	cell1.appendChild(el);

	// cost cell
	var cell2 = row.insertCell(1);
	var el2 = document.createElement('input');
	el2.type = 'text';
	el2.name = id+'__input_options_item_cost__new'+iteration;
	el2.id = id+'__input_options_item_cost__new'+iteration;
	el2.value = "0.00";
	el2.className = "item_cost";

	var el3 = document.createElement('input');
	el3.type = 'hidden';
	el3.name = id+'__input_options[]';
	el3.value = "new"+iteration;

	cell2.appendChild(el2);
	cell2.appendChild(el3);
}

function change_egood_type(selectBox) {
  var selectedValue = selectBox.options[selectBox.selectedIndex].value;
  jQuery(".egood_settting_access_control").css("display", "none");
  jQuery(".egood_settting_newsletter").css("display", "none");
  jQuery(".egood_settting_listing_service").css("display", "none");
  jQuery(".egood_config").css("display", "block");


  if (selectedValue == "simple") {
	  jQuery(".egood_config").css("display", "none");
  } else if (selectedValue == "accounts") {
  } else if (selectedValue == "access_control") {
	  jQuery(".egood_settting_access_control").css("display", "block");
  } else if (selectedValue == "newsletter") {
	  jQuery(".egood_settting_newsletter").css("display", "block");
  } else if (selectedValue == "listing_service") {
    jQuery(".egood_settting_listing_service").css("display", "none");
  }
}

function change_shipping_type(selectBox) {
  var selectedValue = selectBox.options[selectBox.selectedIndex].value;

  if (selectedValue == 0) {
	  jQuery(".shipping_weight_label").css("display", "none");
	  jQuery("span.shipping_cost_label").css("display", "inline-block");
	  jQuery("li.shipping_cost_label").css("display", "block");
	  jQuery(".shipping_cost_label_list_item").css("display", "block");
	  jQuery("li.shipping_egood_config").css("display", "none");
  } else if (selectedValue == 1) {
	  jQuery("li.shipping_cost_label_list_item").css("display", "block");
	  jQuery("li.shipping_cost_label").css("display", "none");
	  jQuery("span.shipping_cost_label").css("display", "none");
	  jQuery("span.shipping_weight_label").css("display", "inline-block");
	  jQuery("li.shipping_egood_config").css("display", "none");
  } else if (selectedValue == 2) {
	  jQuery("li.shipping_cost_label_list_item").css("display", "none");
	  jQuery(".shipping_weight_label").css("display", "none");
	  jQuery(".shipping_cost_label").css("display", "none");
	  jQuery(".shipping_cost_label_list_item").css("display", "none");
	  jQuery("li.shipping_egood_config").css("display", "block");
  }
}
</script>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/shopping_cart/customer-orders/">Shopping Cart</a> <span class="divider">/</span></li>
  <li><a href="module/shopping_cart/manage-products/">Manage Products</a> <span class="divider">/</span></li>
  <li class="active">Create Product</li>
</ul>
<h3>Create Product</h3>
<?php
print $errorMsg;
?>
<? if ($_POST['existing_product'] == "") { ?>
<form method="post" enctype="multipart/form-data">
  <input type='hidden' name='copy_from' value='existing_product' />
	<fieldset>
	<legend>Copy from Existing Product</legend>
    <select name="existing_product" onchange="this.form.submit();">
      <option value="">-- choose product to duplicate --</option>
      <?php
		$sql = "SELECT l.id, l.status, l.slot_1, l.slot_2, l.when_created FROM `shopping_cart_products` l WHERE l.slot_1 LIKE '%".$_POST['product_name']."%' AND l.site_id='".NUMO_SITE_ID."' ORDER BY l.status desc,l.slot_1,l.id";
	    $result = $dbObj->query($sql);
		while ($record = mysql_fetch_array($result)) {?>
        <option value="<?php echo $record['id'];?>"><?php echo $record['slot_1']; ?></option>
        <?php } ?>
    </select>
    </fieldset>
</form>

<div style='padding-top: 10px; padding-bottom: 20px; width: 300px; text-align: center; font-weight: bold'>OR</div>

<?php } ?>
<?php
if ($_POST['copy_from'] == "existing_product") {
  $sql = "SELECT * FROM `shopping_cart_products` l WHERE l.id = '".$_POST['existing_product']."' AND l.site_id='".NUMO_SITE_ID."'";
  //print $sql;
  $result = $dbObj->query($sql);
  $originalProduct = mysql_fetch_array($result);

	$sql = "SELECT category_id FROM `shopping_cart_product_categories` WHERE `product_id`=".$_POST['existing_product'];
	$results = $dbObj->query($sql);

	$productCategories = array();

	while($row = mysql_fetch_array($results)) {
		$productCategories[$row['category_id']] = $row['category_id'];
	}


}

$taxRatesResult = @$dbObj->query("SELECT * FROM `shopping_cart_taxes` WHERE site_id='".NUMO_SITE_ID."' ORDER BY rate_name, tax_rate");
$taxRates = array();
if (@mysql_num_rows($taxRatesResult) > 0) {
	$taxRates[0] = "Tax Exempt";
	while ($taxRateRecord = mysql_fetch_array($taxRatesResult)) {
		$taxRateID = $taxRateRecord['tax_rate_id'];
		$taxRates["$taxRateID"] = $taxRateRecord['rate_name']." @ ".$taxRateRecord['tax_rate']."%";

	}
}

?>
<form method="post" enctype="multipart/form-data">
	<fieldset>
	<legend>Enter Product Details</legend>
	<ul class="form_display">
		<?php
		//load field information for accounts group
		$sql = "SELECT `name`,`slot`,`input_type`,`input_options` FROM `shopping_cart_fields` WHERE site_id='".NUMO_SITE_ID."' ORDER BY `position`,`name`";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($field = mysql_fetch_array($results)) {
			if ($_POST['copy_from'] == "existing_product") {

			  $fieldValue = html_entity_decode($originalProduct['slot_'.$field['slot']]);
			  if ($field['input_type'] == "text") {
				  $fieldValue .= " (Copy)";
			  }
			  if ($field['input_type'] == "money") {
				  $_POST['shipping']  = $originalProduct['shipping'];
				  $_POST['shipping2'] = $originalProduct['shipping2'];
			  }

			 // print "yup";
			} else {
			  $fieldValue = html_entity_decode($_POST['slot_'.$field['slot']]);
			}

			$fieldOptions = html_entity_decode($field['input_options']);

			if($field['input_type'] == "link") {
				print '<li><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><input class="text_input" type="text" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="http://" autocomplete="off" /></li>';

			} else if($field['input_type'] == "dropdown list") {

				print '<li>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<select id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'[]">'.generate_list_options($fieldOptions ,$fieldValue).'</select>
							</li>';

			} else if($field['input_type'] == "tax rate" ) {
				if (sizeof($taxRates) > 0) {
				print '<li>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<select id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'[]">'.generate_list_options($taxRates, $fieldValue).'</select>
							</li>';
				}
			} else if($field['input_type'] == "multiple select") {
				print '<li>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<select id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'[]" multiple="multiple">'.generate_list_options($fieldOptions ,explode(",", $fieldValue)).'</select>
							</li>';

			} else if($field['input_type'] == "checkbox") {
				print '<li>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<ul class="checkbox_display_options">'.generate_checkbox_options('slot_'.$field['slot'],$fieldOptions ,$fieldValue).'</ul>
							</li>';
			} else if($field['input_type'] == "text box" || $field['input_type'] == "text area") {
				print '<li>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<textarea id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">'.$fieldValue.'</textarea>
							</li>';

			} else if($field['input_type'] == "money") {
				print '<li><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><input class="text_input" type="text" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="'.number_format($fieldValue, 2, '.', ',').'" autocomplete="off" /></li>';



			} else if($field['input_type'] == "shipping type") {
				$fieldOptions = array(0 => 'Cost Based Shipping',
									  1 => 'Weight Based Shipping (Configured via PayPal)',
									  2 => 'Non-Shippable Electronic Good');
				print '<li>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<select onchange="change_shipping_type(this)" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">'.generate_list_options($fieldOptions ,$fieldValue).'</select>


							</li>';

				//add shipping input box
				print '<li class="shipping_cost_label_list_item"><label  for="product_shipping_cost"><span class="shipping_cost_label">Shipping Cost</span><span style="display: none" class="shipping_weight_label">Weight (lbs)</span>:</label><input class="text_input" type="text" id="product_shipping_cost" name="shipping" value="'.number_format($_POST['shipping'], 2, '.', ',').'" autocomplete="off" /></li>';


				//add shipping input box
				print '<li class="shipping_cost_label"><label for="product_shipping2_cost">Additional Shipping:</label><input class="text_input" type="text" id="product_shipping2_cost" name="shipping2" value="'.number_format($_POST['shipping2'], 2, '.', ',').'" autocomplete="off" /></li>';



			} else if($field['input_type'] == "upon completion action") {
				//add shipping input box
				$fieldOptions = array();
				$fieldOptions['simple'] = 'Simple Payment';
				$fieldOptions['accounts'] = "Account Upgrade";

				$accountTypeOptions = array(0 => "** No Change **");
				$result = $dbObj->query("SELECT * FROM `types` WHERE site_id='".NUMO_SITE_ID."' ORDER BY `id`");
				while ($rec = mysql_fetch_array($result)) {
					$accTypeId = $rec['id'];

					$accountTypeOptions["{$accTypeId}"] = $rec['name'];
				}
				mysql_free_result($result);


				$query = "SELECT * FROM modules WHERE name='access_control' AND site_id='".NUMO_SITE_ID."'";
				$result = $dbObj->query($query);
				$exists = (mysql_num_rows($result))?TRUE:FALSE;
				if ($exists) {
				  $fieldOptions['access_control']  = "- Access Upgrade";
					$accessTypeOptions = array();
					$result = $dbObj->query("SELECT * FROM `protected_files` WHERE site_id='".NUMO_SITE_ID."' ORDER BY `file_name`");
					//print mysql_error();
					while ($rec = mysql_fetch_array($result)) {
						$accTypeId = $rec['id'];

						$accessTypeOptions["{$accTypeId}"] = $rec['file_name'];
					}


				}
				$query = "SELECT * FROM modules WHERE name='newsletter' AND site_id='".NUMO_SITE_ID."'";
				$result = $dbObj->query($query);
				$exists = (mysql_num_rows($result))?TRUE:FALSE;
				if ($exists) {
				  $fieldOptions['newsletter']      = "- Newsletter Subscription";

					$newsletterListOptions = array();
									//$newsletterListOptions = array(0 => "** No Change **");

					$result = $dbObj->query("SELECT * FROM `newsletter_subscription_lists` WHERE site_id='".NUMO_SITE_ID."' ORDER BY `name`");
					//print mysql_error();
					while ($rec = mysql_fetch_array($result)) {
						$accTypeId = $rec['id'];

						$newsletterListOptions["{$accTypeId}"] = $rec['name'];
					}


				}

//								$query = "SELECT * FROM modules WHERE name='newsletter' AND site_id='".NUMO_SITE_ID."'";

				$result = $dbObj->query("SHOW COLUMNS FROM `listing_contributors`");
				$exists = (@mysql_num_rows($result))?TRUE:FALSE;

				if ($exists) {

				  $fieldOptions['listing_service'] = "- Listing Service Contributor";
				}

				// will need to parse out the field value information
				// $fieldValue example "accounts:2
				//                      access_control:page1.htm,page2.htm
				//                      newsletter:1
				//                      listing_service:1,3



				print '<li style="display:none" class="shipping_egood_config"><label for="slot_'.$field['slot'].'"">eGood Configuration:</label>
				<table>
				  <tr>
				    <td>
					  <select onchange="change_egood_type(this)" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">'.generate_list_options($fieldOptions ,$fieldValue).'</select>
				    </td>
				   </tr>
				   <tr class="egood_config" style="display:none">
					<td>
					  <table class="egood_config">
					    <tr>
						  <th>Set Account Type</th>
						  <th class="egood_settting_access_control">Grant Access</th>
						  <th class="egood_settting_newsletter">Subscription List</th>
						  <th class="egood_settting_listing_service">Contribute To</th>
						</tr>
					    <tr>
						  <td valign="top"><select name="egood_config_accounts">'.generate_list_options($accountTypeOptions,$fieldValue).'</select></td>
						  <td class="egood_settting_access_control" valign="top"><select name="egood_config_access_control[]" multiple="multiple">'.generate_list_options($accessTypeOptions,$fieldValue).'</select></td>
						  <td class="egood_settting_newsletter" valign="top"><select name="egood_config_newsletter">'.generate_list_options($newsletterListOptions,$fieldValue).'</select></td>
						  <td class="egood_settting_listing_service" valign="top"><select name="egood_config_listing_service[]" multiple="multiple">'.generate_list_options($listingTypeOptions,$fieldValue).'</select></td>
						</tr>
				      </table>
					</td>
			       </tr>
				 </table>


				</li>';
			} else {
				print '<li><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><input class="text_input" type="text" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="'.$fieldValue.'" /></li>';
			}
		}

		print '<li><label for="categories_select">Categories:</label><select name="categories[]" id="categories_select" multiple="multiple">';
		//generate starting with parent categories (that have a 0 parent)
		display_shopping_cart_category_links(0,$categories,0);
		print '</select></li>';

		print '<li class="checkbox">
					<input type="checkbox" id="status" name="status" value="0" '.($_POST['status'] == "0" ? 'checked="checked"' : '').' />
					<label for="status">Do not display product in catalog</label>
				</li>';
		?>
	</ul>
	</fieldset>

	<fieldset>
		<legend>Optional Attributes</legend>
		<div class="headings">
			<ul>
				<li><img src="images/manage_fields_heading_locks.jpg"></li>
				<li><h2>Name</h2></li>
				<li><h2>Type</h2></li>
				<li><h2>Required</h2></li>
				<li>&nbsp;</li>
			</ul>
		</div>
		<div id="group_fields">
		<?php
		// load field information for accounts group
		$sql = "SELECT * FROM `shopping_cart_optional_product_attributes` WHERE `product_id`='".$_POST['existing_product']."' ORDER BY `position`,`label`";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

        $newCount = 0;
		while($field = mysql_fetch_array($results)) {

			$myFieldID = $field['id'];
			$field['id'] = "new{$newCount}-".time();
			$newCount++;
		?>
			<div id="item_<?=$field['id']?>" class="lineitem">
				<ul>
					<li><img src="images/unlocked.jpg" alt="click and drag to move" /></li>
					<li><div><input type="text" id="<?=$field['id']?>__name" name="<?=$field['id']?>__name" value="<?=$field['label']?>" /></div></li>
					<li><div><select onchange="checkTypeSelection(this.value,'<?=$field['id']?>')" id="<?=$field['id']?>__type" name="<?=$field['id']?>__type"><?=display_field_type_options($field['type'])?></select></div></li>
					<li><div><select id="<?=$field['id']?>__required" name="<?=$field['id']?>__required"><?=display_yes_no_options($field['required'])?></select></div></li>
					<li><a href="javascript:removeItem('<?=$field['id']?>')"><img src="images/close.jpg" alt="X" border="0" /></a></li>
				</ul>
				<div id="<?=$field['id']?>_field_input_options_display" class="field_optionals" <?php if($field['type'] != "dropdown list") { print 'style="display: none;"'; } ?>>
					<div><label for="<?=$field['id']?>__input_options">Options</label><p>Enter the label and price (optional) difference for each option</p></div>
					<table id="<?=$field['id']?>__input_options_table">
					<tr><td><b>Label</b></td><td><b>Cost</b></td></tr>
					<?php
					//load field information for accounts group
					$sql = "SELECT * FROM `shopping_cart_optional_product_attribute_options` WHERE `attribute_id`='".$myFieldID."' AND `status`=1 ORDER BY `id`";
					//print $sql."<br>";
					$optionals = $dbObj->query($sql);

					  $newOptionCount = 0;

					while($option = mysql_fetch_array($optionals)) {
					  $option['id'] = "new{$newOptionCount}";
					  $newOptionCount++;

					?>
					<tr><td><input type="text" name="<?=$field['id']?>__input_options_item_label__<?=$option['id']?>" value="<?=$option['label']?>" /></td><td><input type="text" name="<?=$field['id']?>__input_options_item_cost__<?=$option['id']?>" class="item_cost" value="<?=number_format($option['cost'], 2, '.', ',')?>" /><input type="hidden" name="<?=$field['id']?>__input_options[]" value="<?=$option['id']?>" /></td></tr>
					<?php
					}
					?>
					</table>
					<input type="button" class="btn" name="addoptionalitem" value="Add New Option" onclick="javascript:addOptionalItem('<?=$field['id']?>')" />
				</div>
			</div>
		<?php
		}

		?>		</div>

    	<input type="button" name="nocmd2" class='btn' value="Add New Attribute" onClick="addItem()" />
	</fieldset>

	<?php
	$maxImages = 6;
	if($maxImages > 0) {
	?>
		<fieldset>
		<legend>Product Images</legend>
	<?php
		$counter = 0;

		for(; $counter < $maxImages; $counter++) {
	?>
		<table border="0"><tr><td valign="top"><image src="modules/<?=$_GET['m']?>/images/na.jpg" class="listing_image_thumb" /></td><td valign="top"><table cellpadding="0" cellspacing="0"><tr><td><textarea onblur="checkCaptionFieldValue(this)" onclick="checkCaptionFieldValue(this)" class="image_description_textarea_inactive" name="new_image_description__<?=$counter?>">Enter your image caption here</textarea></td></tr><tr><td>Upload: <input type="file" name="new_image__<?=$counter?>" value="" /></td></tr></table></td></tr></table><hr />
	<?php
		}
	?>
		</fieldset>
	<?php
	}
	?>

	<input type="hidden" name="cmd" value="create" />
	<input type="hidden" name="field_order" id="field_order" value="" />
	<input type="hidden" name="field_remove" id="field_remove" value="" />
	<div class="bttm_submit_button">
	<input type="button" name="nocmd" class='btn btn-large btn-success' value="Create" onClick="getGroupOrder(this.form)" /> <a href="module/shopping_cart/manage-products/" class='btn btn-large'>Cancel</a>
	</div>
</form>
<?php

function generate_checkbox_options($name, $options, $currentValue = "", $sep = "\r\n") {
	$returnStr   = "";
	$count = 0;

	if(is_array($currentValue)) {
		$listOptions = explode($sep, trim($options));

		foreach ($listOptions as $key => $value) {
			if(in_array($value, $currentValue)) {
				$returnStr .= '<li><input type="checkbox" name="'.$name.'[]" id="'.$name.'-'.$count.'" value="'.$value.'" checked="checked" /><label class="checkbox" for="'.$name.'-'.$count++.'">'.$value.'</label></li>';
			} else {
				$returnStr .= '<li><input type="checkbox" name="'.$name.'[]" id="'.$name.'-'.$count.'" value="'.$value.'" /><label class="checkbox" for="'.$name.'-'.$count++.'">'.$value.'</label></li>';
			}
		}
	} else {
		$listOptions = explode($sep, trim($options));

		foreach ($listOptions as $key => $value) {
			if($currentValue == $value) {
				$returnStr .= '<li><input type="checkbox" name="'.$name.'[]" id="'.$name.'-'.$count.'" value="'.$value.'" checked="checked" /><label class="checkbox" for="'.$name.'-'.$count++.'">'.$value.'</label></li>';
			} else {
				$returnStr .= '<li><input type="checkbox" name="'.$name.'[]" id="'.$name.'-'.$count.'" value="'.$value.'" /><label class="checkbox" for="'.$name.'-'.$count++.'">'.$value.'</label></li>';
			}
		}
	}

	return $returnStr;
}

function display_field_type_options($currentValue) {
	$fieldTypes = array ("text","dropdown list","text area","date", "section break");
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

//recursive function that prints categories as a nested html unorderd list
function display_shopping_cart_category_links($parent,$categories,$pos) {
	$hasChildren = false;

	global $productCategories;

	$alignmentStr = "";

	for($i=$pos; $i > 0; $i--) {
		$alignmentStr .= "...";
	}

	foreach($categories as $key => $value) {
		//print $value['label']." (".$key."): ".$value['parent_id']." --> ".$parent."<br>";
		if($value['parent_id'] == $parent) {
			//if this is the first child print '<ul>'
			if (!$hasChildren) {
				//don't print '<ul>' multiple times
				$hasChildren = true;

				//print '<ul>'."\r\n";
			}

			print '<option value="'.$key.'"';

			if(array_key_exists($key, $productCategories)) {
				print ' selected="selected"';
			}

			print '>'.$alignmentStr.$value['label'].'</option>'."\r\n";

			display_shopping_cart_category_links($key,$categories,($pos+1));

			//call function again to generate nested list for subcategories belonging to this category
			//print '</li>'."\r\n";
		}
	}

	//if ($hasChildren) print '</ul>'."\r\n";
}


function display_yes_no_options($value) {
	if($value == 1) {
		return "<option value=\"1\" selected=\"selected\">Yes</option><option value=\"0\">No</option>";
	} else {
		return "<option value=\"1\">Yes</option><option value=\"0\" selected=\"selected\">No</option>";
	}
}

function sanitize_field($data) {
  $data = str_replace("&ldquo;", '"', $data);
  $data = str_replace("“", '"', $data);
  $data = str_replace("&#39;", "\'", $data);
  $data = str_replace("&rdquo;", '"', $data);
  $data = str_replace("”", '"', $data);
  $data = str_replace("&lsquo;", "\'", $data);
  $data = str_replace("‘", "\'", $data);
  $data = str_replace("&rsquo;", "\'", $data);
  $data = str_replace("’", "\'", $data);
  $data = str_replace("&ndash;", "--", $data);
  $data = str_replace("…", "...", $data);
  $data = str_replace("&hellip;", "...", $data);
  
  return $data;

}
?>
<script type="text/javascript">
	// <![CDATA[
	Sortable.create('group_fields',{tag:'div',dropOnEmpty: true, only:'lineitem'});
	// ]]>
</script>