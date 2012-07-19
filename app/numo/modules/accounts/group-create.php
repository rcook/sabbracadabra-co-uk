<?php
	$result = $dbObj->query("SHOW COLUMNS FROM `shopping_cart_settings");
	$shoppingCartExists = (@mysql_num_rows($result))?TRUE:FALSE;
	
	$result = $dbObj->query("SHOW COLUMNS FROM `listing_contributors`");
	$listingServiceExists = (@mysql_num_rows($result))?TRUE:FALSE; 
	
?>
<h2>Create Group</h2>
<?php
if($_POST['cmd'] == "create") {
	if($_POST['name'] != "") {
		if ($shoppingCartExists) {
		  $discountFieldName = ",shopping_cart_discount,show_original_price";
		  $discountFieldValue = ",'{$_POST['shopping_cart_discount']}','{$_POST['show_original_price']}'";
		}
		if ($listingServiceExists) {
		  $listingServiceOverrideFields = ",listing_override_max_posts,listing_override_post_life,listing_override_require_approval";
		  $listingServiceOverrideValues = ",'{$_POST['listing_override_max_posts']}','{$_POST['listing_override_post_life']}','{$_POST['listing_override_require_approval']}'";
		}
		$sql = "INSERT INTO `types` (site_id,available_slots,name,allow_registration,require_approval,require_activation{$discountFieldName}{$listingServiceOverrideFields}) ".
					 "VALUES (".NUMO_SITE_ID.",'5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,30','".$_POST['name']."','".$_POST['allow_registration']."','".$_POST['require_approval']."','".$_POST['require_activation']."'{$discountFieldValue}{$listingServiceOverrideValues})";
		$dbObj->query($sql);
		//print $sql;
       // print mysql_error();
		//exit;
		$sql = "SELECT LAST_INSERT_ID() as 'type_id'";
		$result = $dbObj->query($sql);

		if($row = mysql_fetch_array($result)) {
			//add default field information to database
			$sql = "INSERT INTO `fields` (`type_id`,`name`,`slot`,`position`,`required`,`locked`,`show_on_registration`,`input_type`,`input_options`,`regex`) VALUES (".$row['type_id'].",'Username',1,3,1,1,1,'text','',''),(".$row['type_id'].",'Password',2,4,1,1,1,'password','',''),(".$row['type_id'].",'Email Address',3,2,1,1,1,'text','','^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$'),(".$row['type_id'].",'Name',4,1,1,1,1,'text','','')";
			$dbObj->query($sql);

			//redirect to edit page for new account group
			header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/group-edit/?id='.$row['type_id']);
		}
	} else {
		print "Error: Please enter a group name";
	}
}
?>
<style>
form fieldset {border: 1px solid #2A61BD; padding: 5px;}
form fieldset legend {font-weight: bold; color: #2A61BD;}
</style>
<form method="post">
		<fieldset>
			<legend>Settings</legend>
			<ul class="form_display">
				<li><label for="name">Name:</label><input type="text" name="name" value="<?=$_POST['name']?>" /></li>
				<li><label for="allow_registration">Allow Registration:</label><select name="allow_registration"><?=display_yes_no_options($_POST['allow_registration'])?></select></li>
			</ul>
			<fieldset>
				<legend>New Accounts</legend>
					<ul class="form_display">
						<li><label for="require_approval">Require Approval:</label><select name="require_approval"><?=display_yes_no_options($_POST['require_approval'])?></select></li>
						<li><label for="require_activation">Require Activation:</label><select name="require_activation"><?=display_yes_no_options($_POST['require_activation'])?></select></li>
					</ul>
			</fieldset>
<?php if ($shoppingCartExists) {
	if ($_POST['shopping_cart_discount'] == "") {
		$_POST['shopping_cart_discount'] = "0.00";
	}?>
			<fieldset>
				<legend>Shopping Cart</legend>
					<ul class="form_display">
						<li><label for="shopping_cart_discount">Group Discount %:</label><input type='text' name="shopping_cart_discount" value="<?=$_POST['shopping_cart_discount']?>" /></li>
					</ul>
					<ul class="form_display">
						<li><label for="show_original_price">Show Original Price:</label><select name="show_original_price"><?=display_yes_no_options($_POST['show_original_price'])?></select></li>
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
	<input type="hidden" name="cmd" value="create" />
	<input type="submit" name="nocmd" value="Create" />
</form>

<?php
function display_yes_no_options($value) {
	if($value == 1) {
		return "<option value='1' selected='selected'>Yes</option><option value='0'>No</option>";
	} else {
		return "<option value='1'>Yes</option><option value='0' selected='selected'>No</option>";
	}
}
?>