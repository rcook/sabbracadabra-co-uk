<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/shopping_cart/customer-orders/">Shopping Cart</a> <span class="divider">/</span></li>
  <li class="active">Product Categories</li>
</ul>
<h3>Product Categories</h3>
<?php
$parentId = 0;



$sql = "SELECT * FROM `shopping_cart_settings` WHERE site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

$settings = mysql_fetch_array($result);

if($_POST['cmd'] == "update") {
/**************************************/
/*         REMOVE CATEGORY(s)         */
/**************************************/
	//field order value will be IDs separated by a comma.  Use explode function to break value apart into array
	$removeArr = explode(',', $_POST['category_remove']);

	//loop thru field ids and remove field entries
	foreach($removeArr as $key => $id) {
		if($id != "" && !strstr($id, "new")) {
			//remove the category
			$sql = "DELETE FROM `shopping_cart_categories` WHERE id='".$id."'";
			//print $sql."<br>";
			$dbObj->query($sql);

			$sql = "DELETE FROM `shopping_cart_category_permissions` WHERE category_ids='".$id."'";
			//print $sql."<br>";
			$dbObj->query($sql);


			//remove the sub-categories ... JUST CLEARS ONE LEVEL
			$sql = "DELETE FROM `shopping_cart_categories` WHERE parent_id='".$id."'";
			//print $sql."<br>";
			$dbObj->query($sql);
		}
	}

/**************************************/
/*         UPDATE CATEGORY(s)         */
/**************************************/
	//field order value will be IDs separated by a comma.  Use explode function to break value apart into array
	$orderArr = explode(',', $_POST['category_order']);

	//set starting position value
	$position = 1;

	//loop thru field id and save order
	foreach($orderArr as $key => $id) {
		//make copy of the id incase a new field is being created.
		$idNum = $id;

		/**************************************/
		/*         CREATE CATEGORY(s)         */
		/**************************************/
		if(substr($id, 0, 3) == "new") {
			//insert basic field information
			$sql = "INSERT INTO `shopping_cart_categories` (site_id,parent_id) VALUE ('".NUMO_SITE_ID."','".$_POST['current_parent']."')";
			//print $sql."<br>";
			$dbObj->query($sql);
			
			
			//get the ID for the field just inserted in the database
			$sql = "SELECT LAST_INSERT_ID() as 'id'";
			//print $sql."<br>";
			$result = $dbObj->query($sql);
 
			if($category = mysql_fetch_array($result)) {
				//assign ID to idNum variable to be used in update commands lower down
				$idNum = $category['id'];
			//	$id = $idNum;
			}
			$idData = explode("__", $id);
			$newID = array_shift($idData);
			$_POST["{$idNum}__allowed_user_groups"] = $_POST["{$newID}__allowed_user_groups"];
			$_POST["{$id}__keywords"] = "[Product Names]";
			//print "$idNum from $newID<br>";
		}

		//default update query
		$sql = "UPDATE `shopping_cart_categories` SET description='".$_POST[$id.'__description']."', keywords='".$_POST[$id.'__keywords']."', position='".$position."',label='".$_POST[$id.'__label']."' WHERE id='".$idNum."'";
		//print $sql."<br>";
		$dbObj->query($sql);
 
		if ($settings['catalog_visibility'] == "1") {
			$delete = "DELETE FROM shopping_cart_category_permissions WHERE category_id='{$idNum}'";
				  $dbObj->query($delete);
		
			$allowedUserTypes = $_POST["{$idNum}__allowed_user_groups"];
			if (is_array($allowedUserTypes)) {
				foreach ($allowedUserTypes as $allowedTypeID) {
				  $insert = "INSERT INTO shopping_cart_category_permissions (category_id, account_type_id) VALUES ('{$idNum}', '{$allowedTypeID}')";
				  $dbObj->query($insert);
				}
			}
		}
		//increase position by 1
		$position++;
	}
}

if(is_numeric($_POST['parent_id'])) {
	$parentId = $_POST['parent_id'];
}
?>
<style>
html { padding: 0px; margin: 0px; }
body { padding: 0px; margin: 0px; font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; }
div { padding: 0px; margin: 0px; }
.headings{ padding: 0px; margin: 0px 0px 5px 0px; border: 1px solid #ccc; auto;}
.headings ul {height: 28px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_fields_heading.jpg') repeat-x; list-style:none;}
.headings ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left; clear: none;}
.headings ul li.movable { padding: 0px; margin: 0px; display: block; height: 28px; width: 8px;border-right: 1px solid #CCC; }
.headings ul li h2 {line-height: 28px; display: inline-block; text-decoration: none; width: 250px; color: #333; font-size: 16px; font-weight: normal; text-align: left; padding: 0px; margin: 0px; text-indent: 10px; border-right: 1px solid #CCC; }
.headings ul li h3 {line-height: 28px; display: inline-block; text-decoration: none; width: 250px; color: #333; font-size: 16px; font-weight: normal; text-align: left; padding: 0px; margin: 0px; text-indent: 10px; border-right: 1px solid #CCC; }
.lineitem { padding: 0px; margin: 0px 0px 10px 0px; border: 1px solid #ccc; width: auto; background: #EDEDED; cursor: move;}
.lineitem ul {height: 44px; padding: 0px; margin: 0px; background: #E6E6E6 url('images/manage_field.jpg') repeat-x;}
.lineitem ul li {display: inline; padding: 0px; margin: 0px; font-size: 1em; float: left;}
.lineitem ul li img { padding: 0px; margin: 0px; display: block;}
.lineitem ul li div { height: 44px; display: table-cell; vertical-align: middle; width: 250px; font-size: 1em; text-align: center; padding: 0px; margin: 0px; border-right: 1px solid #CCC;}
.lineitem ul li div.permit { height: 44px; display: table-cell; vertical-align: middle; width: 200px; font-size: 1em; text-align: center; padding: 0px; margin: 0px; border-right: 1px solid #CCC;}
.lineitem ul li input { font-size: 1em; font-family: Tahoma, Verdana, Arial, Helvetica, sans-serif; width: 220px; padding: 2px; margin: 0px;}
.lineitem ul li select { margin-bottom: 0px; !important; }
.lineitem ul li a { margin: 5px;}
.lineitem label { margin: 0px; padding: 0px; vertical-align: top; display: inline-block; color: #333; font-size: 20px; font-weight: normal;}
.lineitem p { margin: 0px; padding: 5px 0px; color: #777; font-size: 12px; font-weight: normal;}
.bttm_submit_button { position: fixed; bottom: 0px; right: 0px; background: #aaaaaa;  width: 100%; height: 70px; padding: 0px 20px; margin: 0px;}
.bttm_submit_button input { margin: 10px 0px 10px 210px;}

html {padding-bottom: 70px;}
</style>
<script language="JavaScript" src="javascript/prototype.js"></script>
<script language="JavaScript" src="javascript/effects.js"></script>
<script language="JavaScript" src="javascript/dragdrop.js"></script>

<script>
var fieldCount = 1;
var changeMade = 0;

function manageCategory(catId) {
	var frm = document.getElementById("category_form");

	//prompt to save if changes detected
	if(changeMade == 1) {
		if(confirm("Any changes made will be saved.  Would you like to continue?")) {
			document.getElementById("parent_id").value = catId;

			getGroupOrder(frm);
		}

	//if no changes detected continue without prompt
	} else {
			document.getElementById("parent_id").value = catId;
			frm.submit();
	}
	//this would allow them to proceed without saving
	/*else {
		document.getElementById("parent_id").value = catId;
		frm.submit();
	}*/
}

function returnToParentCategory() {
	<?php
	$sql = "SELECT parent_id FROM `shopping_cart_categories` WHERE id=".$parentId;
	//print $sql."<br>";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
	?>
	var frm = document.getElementById("category_form");

	//prompt to save if changes detected
	if(changeMade == 1) {
		if(confirm("Any changes made will be saved.  Would you like to continue?")) {
			document.getElementById("parent_id").value = <?=$row['parent_id']?>;

			getGroupOrder(frm);
		}

	//if no changes detected continue without prompt
	} else {
			document.getElementById("parent_id").value = <?=$row['parent_id']?>;
			frm.submit();
	}
	<?php
	}
	?>
}


function removeCategory(catId) {
	if(confirm("Are you sure you would like to remove this Category?")) {
		changeMade = 1;

		//get hidden field that stores removed id values
		categoryRemoved = document.getElementById("category_remove");

		//check to see if list is empty or not
		if(categoryRemoved.value == "") {
			categoryRemoved.value = catId;
		} else {
			categoryRemoved.value = categoryRemoved.value + "," + catId;
		}

		//get containing div element (container)
		var container = document.getElementById('group_categories');

		//get div element to remove
		var olddiv = document.getElementById('item_'+catId);

		//remove the div element from the container
		container.removeChild(olddiv);
  }
}

function getGroupOrder(frm) {
	var order = Sortable.serialize("group_categories");

	catOrder = document.getElementById("category_order");
	catOrder.value = Sortable.sequence("group_categories");

	document.getElementById("submit_cmd").value = "update";

	frm.submit();
	return true;
}

function addItem() {
	changeMade = 1;

	var currentTime = new Date();

	/*generate new div ID*/
	var divIdName = 'new'+fieldCount+'-'+currentTime.getTime();

	/*get containing div element (container)*/
	var container = document.getElementById('group_categories');

	/*create new div*/
	var newdiv = document.createElement('div');

	/*set new div ID*/
	newdiv.setAttribute('id','item_'+divIdName);

	/*set new div ID*/
	newdiv.className = 'lineitem';

	/*set content of new div*/
	var newHTML = '<ul><li><img src="modules/shopping_cart/images/moveable.jpg" /></li><li><div><input type="text" name="'+divIdName+'__label" value="Enter Category Name" onblur="checkFieldValue(this)" onclick="checkFieldValue(this)" /></div></li>';
<?php if ($settings['catalog_visibility'] == "1") { ?>
		newHTML = newHTML + '<li><div class="permit"><select name="' + divIdName + '__allowed_user_groups[]" multiple="multiple" size="2">';
		
        <?php
			$sql = "SELECT t.name, t.id FROM `types` t WHERE t.site_id=".NUMO_SITE_ID;
			
			$actQuery = $dbObj->query($sql);
			while ($actTypeRec = mysql_fetch_array($actQuery)) {
			 $sql2 = "SELECT * FROM `shopping_cart_category_permissions`  WHERE account_type_id='{$actTypeRec['id']}' AND category_id='{$category['id']}'";
			//print $sql2;
			 $actQuery2 = $dbObj->query($sql2);
			 $selected = mysql_num_rows($actQuery2) > 0;
			 $actTypeID = $actTypeRec['id'];
			 ?>
				newHTML = newHTML + '<option <?php if ($selected) { print "selected"; } ?> value="<?php print $actTypeRec['id']; ?>"><?php print $actTypeRec['name']; ?></option>';
			 <?php
			}

		?>    
  		newHTML = newHTML + '</select></div></li>';

        
<?php } ?>	
	
	newHTML = newHTML + '<li><a class="btn" href="javascript:manageCategory(\''+divIdName+'\')">Manage</a><a  class="btn btn-danger" href="javascript:removeCategory(\''+divIdName+'\')">Remove</a></li></ul>';
	
	newdiv.innerHTML = newHTML;
	//newdiv.innerHTML = "new item";

	/*add new div to list*/
	container.appendChild(newdiv);

	/*add one to new element counter*/
	fieldCount++;

	Sortable.destroy("group_categories");

	Sortable.create('group_categories',{tag:'div',dropOnEmpty: true, only:'lineitem'});
}

function checkFieldValue(field) {
	changeMade = 1;

	if(field.value == "Enter Category Name") {
		field.value = "";
	} else if(field.value == "") {
		field.value = "Enter Category Name";
	}
}
</script>
<?php
			
				?>
<form method="post" id="category_form">
	<div class="headings">
		<ul>
			<li class="movable">&nbsp;</li>

			<li><h2><?php if($parentId != 0) { print "Sub-"; } ?>Category Name</h2></li>
			<li><h2><?php if($parentId != 0) { print "Sub-"; } ?>Category Meta-Description</h2></li>
			<li><h2><?php if($parentId != 0) { print "Sub-"; } ?>Category Meta-Keywords</h2></li>
<?php if ($settings['catalog_visibility'] == "1") { ?>
			<li><h3>Permit Access To</h3></li>
<?php } ?>			<li>&nbsp;</li>
		</ul>
	</div>
	<?php
	//load field information for accounts group
	$sql = "SELECT * FROM `shopping_cart_categories` WHERE `site_id`='".NUMO_SITE_ID."' AND `parent_id`='".$parentId."' ORDER BY `position`,`label`";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	echo '<div id="group_categories">';

	while($category = mysql_fetch_array($results)) {
	?>
		<div id="item_<?=$category['id']?>" class="lineitem">
			<ul>
				<li><img src="modules/shopping_cart/images/moveable.jpg" /></li>
				<li><div><input type="text" name="<?=$category['id']?>__label" value="<?=$category['label']?>" onblur="checkFieldValue(this)" onclick="checkFieldValue(this)" /></div></li>
				<li><div><input type="text" name="<?=$category['id']?>__description" value="<?=$category['description']?>"  /></div></li>
				<li><div><input type="text" name="<?=$category['id']?>__keywords" value="<?=$category['keywords']?>" /></div></li>
<?php if ($settings['catalog_visibility'] == "1") { ?>
		<li><div class='permit'><select name="<?=$category['id']?>__allowed_user_groups[]" multiple='multiple' size='2'>
        <?php
			$sql = "SELECT t.name, t.id FROM `types` t WHERE t.site_id=".NUMO_SITE_ID;
			
			$actQuery = $dbObj->query($sql);
			while ($actTypeRec = mysql_fetch_array($actQuery)) {
			 $sql2 = "SELECT * FROM `shopping_cart_category_permissions`  WHERE account_type_id='{$actTypeRec['id']}' AND category_id='{$category['id']}'";
			//print $sql2;
			 $actQuery2 = $dbObj->query($sql2);
			 $selected = mysql_num_rows($actQuery2) > 0;
			 $actTypeID = $actTypeRec['id'];
			 
			 print "<option ".($selected ? "selected" : "")." value='".$actTypeRec['id']."'>".$actTypeRec['name']."</option>";
			}

		?>    
  
        </select></div></li>
<?php } ?>
				<li><a class="btn" href="javascript:manageCategory('<?=$category['id']?>')">Manage</a><a class="btn btn-danger" href="javascript:removeCategory('<?=$category['id']?>')">Remove</a></li>
			</ul>
		</div>
	<?php
	}

	echo '</div>';

	if($parentId != 0) {
	?><input type="button" name="nocmd2" class="btn" value="Back" onClick="returnToParentCategory()" /><?php } ?> <input type="button" name="nocmd2" class="btn" value="Add New Category" onClick="addItem()" />

	<input type="hidden" name="category_order" id="category_order" value="" />
	<input type="hidden" name="parent_id" id="parent_id" value="<?=$parentId?>" />
	<input type="hidden" name="current_parent" id="current_parent" value="<?=$parentId?>" />
	<input type="hidden" name="category_remove" id="category_remove" value="" />
	<input type="hidden" name="cmd" id="submit_cmd" value="update" />
<br /><br /><br /><br />
	<div class="bttm_submit_button">
	<input type="button" name="nocmd"  class="btn btn-large btn-success"  value="Save" onClick="getGroupOrder(this.form)" />
	</div>
</form>
<script type="text/javascript">
	// <![CDATA[
	Sortable.create('group_categories',{tag:'div',dropOnEmpty: true, only:'lineitem'});
	// ]]>
</script>