<h2>Instructions to place a component into your web page:</h2>
<ol>
	<li>Open up the page where you wish to place your component in your HTML editor</li>
	<li>Copy the code below for the component you wish to use</li>
	<li>Place your cursor at the location you wish to have the component</li>
	<li>Paste the code for the component into your page</li>
</ol>
<p>Important Note: Components will only appear when viewed on your LIVE server.  When viewing pages with components in them on your local computer the component code text will appear.</p>
<div>
<h3>Catalog Display</h3>
<textarea cols="50" rows="1">[NUMO.SHOPPING CART: CATALOG]</textarea>
</div>
<div>
<h3>View Cart Button</h3>
<textarea cols="50" rows="1">[NUMO.SHOPPING CART: VIEW CART LINK]</textarea>
</div>
<div>
<h3>Category Link List</h3>
<textarea cols="50" rows="1">[NUMO.SHOPPING CART: LIST]</textarea>
</div>
<div>
<h3>Breadcrumb Trail</h3>
<textarea cols="50" rows="1">[NUMO.SHOPPING CART: BREADCRUMB]</textarea>
</div>
<div>
<h3>Search Box</h3>
<textarea cols="50" rows="1">[NUMO.SHOPPING CART: SEARCH]</textarea>
</div>
<div>
<h3>Cart Contents</h3>
<textarea cols="50" rows="1">[NUMO.SHOPPING CART: CATALOG(view=cart)]</textarea>
</div>

<div>
<h3>Customer Purchase List</h3>
<p>Displays a list of past purchases that allows a customer to review their previous orders.</p>
<textarea cols="50" rows="1">[NUMO.SHOPPING CART: PURCHASES]</textarea>
</div>
<br />
<h2>Category Catalog Components:</h2>
<p>Displays the items within the category</p>
<br />
<?php
displayCatalogComponents(0);
?>
<?php
function displayCatalogComponents($parent) { 
global $dbObj;
$sql = "SELECT `label`,`id` FROM `shopping_cart_categories` WHERE `site_id`='".NUMO_SITE_ID."' AND `parent_id`='{$parent}' ORDER BY `position`";

$results = $dbObj->query($sql);

$categories = array();
?>
<div style='<?php if ($parent != 0) { ?>padding-left: 25px;<?php } ?>'>
<?php
while($row = mysql_fetch_array($results)) {
?>
<h3><?=$row['label']?></h3>
<textarea cols="50" rows="1">[NUMO.SHOPPING CART: CATALOG(cid=<?=$row['id']?>)]</textarea>
<?php
displayCatalogComponents($row['id']);


}
?>
</div>
<?php
}
?>