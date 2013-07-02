<?php
if ($settings == "") {
		$sql = "SELECT * FROM `shopping_cart_settings` WHERE `site_id`='".NUMO_SITE_ID."'";
	$settings = $dbObj->query($sql);
	$settings = mysql_fetch_array($settings);
}
			$sql = "SELECT * FROM `shopping_cart_fields` WHERE site_id='".NUMO_SITE_ID."' AND orderable='1' ORDER BY `position`,`name`";
			$fieldResults = $dbObj->query($sql);

	if ($_GET['ob'] == "") {
		$_GET['ob'] = array_shift(explode(" ", $settings['order_by_field']));
		if ($_GET['ob'] == "") {
			$_GET['ob'] == "slot_1";
		}
	} 
	if ($_GET['obd'] == "") {
		$_GET['obd'] = array_pop(explode(" ", $settings['order_by_field']));
	} 	
	//print $_GET['ob'];
	//print $settings['order_by_field'];
?>
<div id='numo_catalog_order_by_surround'>
<div id='numo_catalog_order_by_box'>
<?php echo NUMO_SYNTAX_SHOPPING_CART_ORDER_BY; ?>: 
<select name='ob' id='ob' onchange='updateOrderBy(this)'>
<option <? if ($_GET['ob'] == "when_created") { print "selected"; } ?> value='when_created'>When Created</option>

<?php while ($fieldRecord = mysql_fetch_array($fieldResults)) { ?>
<option <? if ($_GET['ob'] == "slot_{$fieldRecord['slot']}") { print "selected"; } ?> value='slot_<?php print $fieldRecord['slot']?>'><?php print $fieldRecord['name']?></option>
<?php } ?>
</select>
</div>

<div id='numo_catalog_order_by_box_direction'>
<?php echo NUMO_SYNTAX_SHOPPING_CART_ORDER_BY_DIRECTION; ?>: 
<select name='obd' id='obd'  onchange='updateOrderByDirection(this)'>
<option <? if ($_GET['obd'] == "ASC") { print "selected"; } ?> value='ASC'><?php echo NUMO_SYNTAX_SHOPPING_CART_ORDER_BY_DIRECTION_ASCENDING; ?></option>
<option <? if ($_GET['obd'] == "DESC") { print "selected"; } ?> value='DESC'><?php echo NUMO_SYNTAX_SHOPPING_CART_ORDER_BY_DIRECTION_DESCENDING; ?></option>
</select>
</div>
</div>
<script type='text/javascript'>
function updateOrderBy(selectBox) {
  docLocation = document.location.href;
  if (docLocation.search("&ob=") > -1) {
	   if (docLocation.search("&ob=(.*)&") > -1) {
	     docLocation = docLocation.replace(/&ob=(.*)&/, "&ob=" + selectBox.options[selectBox.selectedIndex].value + "&");
	   } else {
		   
	     docLocation = docLocation.replace(/&ob=(.*)/, "&ob=" + selectBox.options[selectBox.selectedIndex].value);
	   }
  } else {
	  if (docLocation.search(/\?/) <0) {
		  docLocation = docLocation + "?";
	  } else {
		  docLocation = docLocation + "&";
	  
	  }
	  docLocation = docLocation + "ob=" + selectBox.options[selectBox.selectedIndex].value;
  }
  document.location.href = docLocation;
}

function updateOrderByDirection(selectBox) {
  docLocation = document.location.href;
  if (docLocation.search("&obd=") > -1) {
	   if (docLocation.search("&obd=(.*)&") > -1) {
	     docLocation = docLocation.replace(/&ob=(.*)&/, "&obd=" + selectBox.options[selectBox.selectedIndex].value + "&");
	   } else {
		   
	     docLocation = docLocation.replace(/&ob=(.*)/, "&obd=" + selectBox.options[selectBox.selectedIndex].value);
	   }
  } else {
	  if (docLocation.search(/\?/) <0) {
		  docLocation = docLocation + "?";
	  } else {
		  docLocation = docLocation + "&";
	  
	  }
	  docLocation = docLocation + "obd=" + selectBox.options[selectBox.selectedIndex].value;
  }
  document.location.href = docLocation;
}
</script>