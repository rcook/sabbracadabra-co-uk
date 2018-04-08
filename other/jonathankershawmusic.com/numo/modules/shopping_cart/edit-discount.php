<?php


if(!isset($_GET['id'])) {

		//create offer
		$sql = "INSERT INTO `shopping_cart_discount` (`site_id`,`discount_name`,`when_created`,`start_date`,`end_date`,`created_by`) VALUES ('".NUMO_SITE_ID."','New Offer','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."','".date("Y-m-d H:i:s")."','{$_SESSION['account_id']}')";
		print $sql."<br>";
		$dbObj->query($sql);
		print mysql_error();
		$sql = "SELECT LAST_INSERT_ID() as 'new_id'";
		print $sql."<br>";
		
		$result = $dbObj->query($sql);

		$row = mysql_fetch_array($result);
		//print $row['new_id'];
		header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/edit-discount/?id='.$row['new_id']);

	
			exit();

	//}
}


//get all categories for site
$sql = "SELECT * FROM `shopping_cart_categories` WHERE `site_id`='".NUMO_SITE_ID."' ORDER BY `position`";
$results = $dbObj->query($sql);

$categories = array();

while($row = mysql_fetch_array($results)) {
	$categories[$row['id']] = array('label' => $row['label'],'parent_id' => $row['parent_id']);
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

if($_POST['cmd'] == "update") {
    $discountObj = new Discount($_POST['discount_id']);
	$discountObj->update($_POST);

		header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/discounts-and-sales/');
	exit();
}
?>
<style>
.error {color: #900; font-weight: bold;}
ul.checkbox_display_options {margin:0; padding:0; float: left; }
hr {padding: 0px; margin: 3px 0px; border: 1px dashed #DDD;}
.listing_image_thumb {width: 64px; border: 1px solid #CCC;}
textarea.image_description_textarea {width: 400px; height: 50px; color:#000;}
textarea.image_description_textarea_inactive {width: 400px; height: 50px; color:#666; font-style: italic;}

.headings{ padding: 0px; margin: 0px 0px 5px 0px; border: 1px solid #ccc; width: 680px;}
.headings ul {height: 28px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_fields_heading.jpg') repeat-x; list-style:none;}
.headings ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left; clear: none;}
.headings ul li img { padding: 0px; margin: 0px; display: block; height: 28px; }
.headings ul li h2 {line-height: 28px; display: inline-block; width: 170px; color: #333; font-size: 20px; font-weight: normal; text-align: center; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_fields_heading_background.jpg') top right; }

.lineitem { padding: 0px; margin: 0px 0px 5px 0px; border: 1px solid #ccc; width: 680px; background: #EDEDED; cursor: move;}
.lineitem ul {height: 44px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_field.jpg') repeat-x;}
.lineitem ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left;}
.lineitem ul li img { padding: 0px; margin: 0px; display: block;}
.lineitem ul li div { height: 44px; display: table-cell; vertical-align: middle; width: 170px; font-size: 1em; text-align: center; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_field_background.jpg') no-repeat top right; }
.lineitem ul li div.short-field { width: 65px; }
div.color-picker { position: absolute; left: 850px; z-index: 99; }

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
html {padding-bottom: 50px;}
ul.form_display li input.text_input {width: 400px;margin: 0px;}
ul.form_display li textarea {width: 400px; height: 100px;margin: 0px;}
ul.form_display li {margin: 3px; padding: 0px;}
input.colorwell {
	font-size: 8pt !important;
	width: 50px !important;
	height: 18px !important;
}
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

<script type="text/javascript" src="styles/bootstrap/js/bootstrap-datetimepicker.min.js"></script>
 <link rel="stylesheet" href="styles/bootstrap/css/bootstrap-datetimepicker.min.css" type="text/css" />
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/shopping_cart/customer-orders/">Shopping Cart</a> <span class="divider">/</span></li>
  <li><a href="module/shopping_cart/discounts-and-sales/">Manage Discounts &amp; Sales</a> <span class="divider">/</span></li>
  <li class="active">Edit Special Offer</li>
</ul>

<script type="text/javascript">
function toggleCouponCode(selectBox) {
		jQuery("#coupon-code-li").css("display", "none");
		jQuery("#user-offer-li").css("display", "none");
		jQuery("#user-group-offer-li").css("display", "none");
	
	if (selectBox.options[selectBox.selectedIndex].value == 2) {
		jQuery("#coupon-code-li").css("display", "block");
	} else	if (selectBox.options[selectBox.selectedIndex].value == 3) {
		jQuery("#user-offer-li").css("display", "block");
	
	} else	if (selectBox.options[selectBox.selectedIndex].value == 4) {
		jQuery("#user-group-offer-li").css("display", "block");

	} 
}


function changeScope(selectBox) {
	jQuery("#scope_extension_id__categories-li").css("display", "none");
	jQuery("#scope_extension_id__products-li").css("display", "none");
	
	if (selectBox.options[selectBox.selectedIndex].value == 1) {
		jQuery("#scope_extension_id__categories-li").css("display", "block");
	} else	if (selectBox.options[selectBox.selectedIndex].value == 2) {
		jQuery("#scope_extension_id__products-li").css("display", "block");
	
	}
}
function toggleCompounding(selectBox) {
	
	if (selectBox.options[selectBox.selectedIndex].value == 1) {
		jQuery("#compounding-priority-li").css("display", "block");
	} else {
		jQuery("#compounding-priority-li").css("display", "none");
		
	}
}

</script>
<h3>Manage Special Offer</h3>
<?php

//load account information
$sql = "SELECT l.* FROM `shopping_cart_discount` l WHERE l.id='".$_GET['id']."'  AND l.site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);
if($row = mysql_fetch_array($result)) {
?>
<form method="post" enctype="multipart/form-data">
	<fieldset>
	<legend>Offer Details</legend>
	<ul class="form_display">
      <li><label>When Created</label><input disabled type="text" value="<?php echo $row['when_created']; ?>" name="when_created" /></li>
      <li><label>Offer Name</label><input type="text" value="<?php echo $row['discount_name']; ?>" name="discount_name" /></li>
      <li><label>Starts</label>
      <div id="datetimepicker1" class="input-append date"><input data-format="MM/dd/yyyy hh:mm:ss"  value="<?php echo date("m/d/Y H:i:s", strtotime($row['start_date'])); ?>" type="text" name="start_date"></input>
      <span class="add-on">
      <i data-time-icon="icon-time" data-date-icon="icon-calendar">
      </i></span></div>
    </li>
      <li><label>Ends</label><div id="datetimepicker2" class="input-append date"><input data-format="MM/dd/yyyy hh:mm:ss" type="text" value="<?php echo date("m/d/Y H:i:s", strtotime($row['end_date'])); ?>" name="end_date" /><span class="add-on">
      <i data-time-icon="icon-time" data-date-icon="icon-calendar">
      </i>
    </span></div></li>
       <li style='display: none;'><label for='compounding'>Can Be Combined</label>
        <select id='compounding' name='compounding' onchange='toggleCompounding(this)'>
        <?php print generate_list_options(array(0 => "No", 1 => "Yes"), $row['compounding']); ?>
        </select>
      </li>
      <li  id='compounding-priority-li' <?php if ($row['compounding'] != 2) { print "style='display: none;'"; } ?>><label for='compounding_priority'>Compounding Priority</label>
        <select id='compounding_priority' name='compounding_priority'>
        <?php print generate_list_options(array(0 => "1st", 1 => "2nd", 2 => "3rd", 3 => "4th", 4 => "5th", 5 => "6th", 6 => "7th", 7 => "8th", 8 => "9th", 9 => "10th"), $row['compounding']); ?>
        </select>
      </li>
  
      <li><label for='visibility'>Type</label>
        <select id='visibility' name='visibility' onchange='toggleCouponCode(this)'>
        <?php print generate_list_options(array(1 => "Public Offer", 2 => "Coupon Code"), $row['visibility']); ?>
        <?php // print generate_list_options(array(1 => "Public Offer", 2 => "Coupon Code", 3 => "User Offer", 4 => "User Group Offer"), $row['visibility']); ?>
        </select>
      </li>
       <li id='coupon-code-li' <?php if ($row['visibility'] != 2) { print "style='display: none;'"; } ?>><label for="access_qualifier">Coupon Code</label><input id="access_qualifier" type="text" value="<?php if ($row['visibility'] == "2") { print $row['access_qualifier']; } ?>" name="access_qualifier-coupon" /></li>
       <li id='user-offer-li' <?php if ($row['visibility'] != 3) { print "style='display: none;'"; } ?>><label for="access_qualifier">Users</label>user stuff goes here</li>
       <li id='user-group-offer-li' <?php if ($row['visibility'] != 4) { print "style='display: none;'"; } ?>><label for="access_qualifier">User Groups</label>user stuff goes here</li>
   
    </ul>

    <legend>Rebate</legend>
	<ul class="form_display">
      <li><label for='amount_type'>Type</label>
        <select id='amount_type' name='amount_type'>
        <?php print generate_list_options(array(0 => "\$", 1 => "%"), $row['amount_type']); ?>
        </select>
      </li>
      <li><label for='amount'>Amount</label>
		<input type="text" value="<?php echo $row['amount']; ?>" name="amount" />
      </li>
      <li style='display: none;'><label for='discount_scope'>Applies To</label>
        <select id='discount_scope' name='discount_scope'>
        <?php print generate_list_options(array(0 => "Order", 1 => "Shipping"), $row['discount_scope']); ?>
        </select>
      </li>
      </ul>
 <?php
 
$sql = "SELECT * FROM `shopping_cart_categories` WHERE `site_id`='".NUMO_SITE_ID."' ORDER BY `position`";
$results = $dbObj->query($sql);
$categories = array();

while($catRow = mysql_fetch_array($results)) {
	$categories[$catRow['id']] = array('label' => $catRow['label'],'parent_id' => $catRow['parent_id']);
}


$sql = "SELECT * FROM `shopping_cart_products` WHERE `site_id`='".NUMO_SITE_ID."' ORDER BY `slot_1`";
$results = $dbObj->query($sql);
$products = array();

while($prodRow = mysql_fetch_array($results)) {
	$products[$prodRow['id']] = array('label' => $prodRow['slot_1'],'parent_id' => 0);
}

$productCategories = array();
$cats = array();
if ($row['qualifier_scope'] == "1") {
	$cats = explode(",", $row['scope_extension_id']);
} 
foreach ($cats as $categoryID) {
  $productCategories["$categoryID"] = $categoryID;	
}

$discountProducts = array();
$prods = array();
if ($row['qualifier_scope'] == "2") {
	$prods = explode(",", $row['scope_extension_id']);
} 
foreach ($prods as $productID) {
  $discountProducts["$productID"] = $productID;	
}




?>
    <div class="row-fluid">
      <div class="span5">  
    <legend>Qualifications</legend>
      <ul class="form_display">
      <li><label for='qualifier_scope'>Scope</label>
        <select id='qualifier_scope' name='qualifier_scope' onchange='changeScope(this)'>
        <?php //print generate_list_options(array(0 => "Entire Order", 1 => "Product Category", 2 => "Specific Product"), $row['qualifier_scope']); ?>
        <?php print generate_list_options(array(0 => "Entire Order",  2 => "Specific Product"), $row['qualifier_scope']); ?>
        </select>
      </li>
      <li id="scope_extension_id__categories-li" <?php if ($row['qualifier_scope'] != "1") { print "style='display: none;'"; } ?>><label for='scope_extension_id__categories'>&nbsp;</label>
        <select  name="scope_extension_id__categories[]" id="scope_extension_id__categories" multiple>
		<?php print display_shopping_cart_category_links(0,$categories,0); ?>
		</select>
      </li>
      <li id="scope_extension_id__products-li" <?php if ($row['qualifier_scope'] != "2") { print "style='display: none;'"; } ?>><label for='scope_extension_id__products'>&nbsp;</label>
        <select name="scope_extension_id__products[]" id="scope_extension_id__products" multiple>
		<?php print display_shopping_cart_product_links(0,$products,0); ?>
		</select>
      </li>
      <li><label for='discount_type'>Scope Qualifier</label>
        <select id='discount_type' name='discount_type'>
        <?php print generate_list_options(array(0 => "Value", 1 => "Quantity"), $row['discount_type']); ?>
        </select>
      </li>
       <li><label for='scope_quantifier'>Scope Quantifier</label>
        <input type="text" value="<?php echo $row['scope_quantifier']; ?>" name="scope_quantifier" />
      </li>
    </ul>
    </div>
    <div class="span7">
        <legend>Qualification Examples</legend>

    <h4>Order Level Scope Example</h4>
    <p>If the ENTIRE ORDER (before shipping) has a VALUE/QUANTITY equal to or greater than the QUANTIFIER, then the specified REBATE would be applied.  If the rebate is a percentage, then it applies to the total value of the order.</p>
    <h4>Category Level Scope Example</h4>
    <p>If the customer has items from a specific PRODUCT CATEGORY with a VALUE/QUANTITY equal to or greater than the QUANTIFIER, then the REBATE would be applied.  If the rebate is a percentage, then it applies to the total value of these products.</p>
    <h4>Product Level Scope Example</h4>
    <p>If the customer have a SPECIFIC PRODUCT with a VALUE/QUANTITY equal to or greater than the QUANTIFIER, then the REBATE would be applied.  If the rebate is a percentage, then it is based solely on the value of this product.</p>
    </div>
    </div>
	</fieldset>


	

	<input type="hidden" name="discount_id" value="<?=$row['id']?>" />
	<input type="hidden" name="cmd" value="update" />
	<br /><br /><br />

	<div class="bttm_submit_button">  
	<input type="submit" name="nocmd" class='btn btn-large btn-success' value="Update" /> <a href="module/shopping_cart/discounts-and-sales/" class='btn btn-large'>Cancel</a>
	</div>
</form>

<script type="text/javascript">
jQuery("#datetimepicker1").datetimepicker({language: 'en', pick12HourFormat: true, pickSeconds: false, maskInput: true});
jQuery("#datetimepicker2").datetimepicker({language: 'en', pick12HourFormat: true, pickSeconds: false, maskInput: true});
</script>
<?php

mysql_free_result($result);
} else {
	print '<p>Could not locate discount.</p>';
}


function display_yes_no_options($value) {
	if($value == 1) {
		return "<option value=\"1\" selected=\"selected\">Yes</option><option value=\"0\">No</option>";
	} else {
		return "<option value=\"1\">Yes</option><option value=\"0\" selected=\"selected\">No</option>";
	}
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

function get_product_optional_attribute_count() {
	global $dbObj;
	global $_GET;

	$sql = "SELECT COUNT(*) as 'count' FROM `shopping_cart_optional_product_attributes` WHERE product_id='".$_GET['id']."'";
	//print $sql."<br>";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		return $row['count'] + 1;
	}

	return 0;
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


function display_shopping_cart_product_links($parent,$categories,$pos) {
	$hasChildren = false;

	global $discountProducts;

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

			if(array_key_exists($key, $discountProducts)) {
				print ' selected="selected"';
			}

			print '>'.$alignmentStr.$value['label'].'</option>'."\r\n";

			display_shopping_cart_product_links($key,$categories,($pos+1));

			//call function again to generate nested list for subcategories belonging to this category
			//print '</li>'."\r\n";
		}
	}

	//if ($hasChildren) print '</ul>'."\r\n";
}

?>
