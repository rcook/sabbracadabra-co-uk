<h2>Instructions to place a component into your web page:</h2>
<ol>
	<li>Open up the page where you wish to place your component in your HTML editor</li>
	<li>Copy the code below for the component you wish to use</li>
	<li>Place your cursor at the location you wish to have the component</li>
	<li>Paste the code for the component into your page</li>
</ol>
<p style="font-style: italic; margin-left: 20px; width: 500px;">Important Note: Components will only appear when viewed on your LIVE server.  When viewing pages with components in them on your local computer the component code text will appear.</p>
<br />
<?php
$sql = "SELECT * FROM `guestbook_types` WHERE `site_id`='".NUMO_SITE_ID."'";
//print $sql."<br>";
$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
?>
<h2><?=$row['name']?></h2>
<div>
<h3>Display Component</h3>
<textarea cols="50" rows="1">[NUMO.GUESTBOOK: DISPLAY(id=<?=$row['id']?>&entry_saved=1)]</textarea>
</div>
<div>
<h3>Submit Form Component</h3>
<textarea cols="50" rows="1">[NUMO.GUESTBOOK: SUBMIT FORM(id=<?=$row['id']?>)]</textarea>
</div>
<div>
<h3>Combined Component</h3>
<p style="font-style: italic;">Displays both components.  The 'submit form' component will be shown below the 'display' component.</p>
<textarea cols="50" rows="1">[NUMO.GUESTBOOK: DISPLAY(id=<?=$row['id']?>)]</textarea>
</div>
<?php
}
?>