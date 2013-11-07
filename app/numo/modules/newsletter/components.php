<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li class="active"><a href="#">Newsletter</a>  <span class="divider">/</span></li>
  <li class="active">Components</li>
</ul>
<h2>Instructions to place a component into your web page:</h2>
<ol>
	<li>Open up the page where you wish to place your component in your HTML editor</li>
	<li>Copy the code below for the component you wish to use</li>
	<li>Place your cursor at the location you wish to have the component</li>
	<li>Paste the code for the component into your page</li>
</ol>
<div>
<h2>Available Components</h2>

<h3>Subscribe Box Component</h3>
<p>This component will show a box that allows visitors to subscribe/unsubscribe from your available subscription lists</p>
<textarea cols="50" rows="1">[NUMO.NEWSLETTER: SUBSCRIBE BOX]</textarea>

<h3>Latest Newsletter Component</h3>
<p>This component will show a copy of your latest 'active' newsletter message</p>
<textarea cols="50" rows="1">[NUMO.NEWSLETTER: VIEW]</textarea>

<h3>Links Component</h3>
<p>This component will display a list of links to each of your active newsletter messages</p>
<textarea cols="50" rows="1">[NUMO.NEWSLETTER: LINKS]</textarea>

<h3>RSS Button Component</h3>
<p>This component will show a little RSS link that will allow visitors to view and subscribe to your newsletter RSS feed using their RSS reader</p>
<textarea cols="50" rows="1">[NUMO.NEWSLETTER: RSS BUTTON]</textarea>
<br /><br />
<hr />
<br />
<h2>Display Individual Newsletter Message Component(s)</h2>
<?php
//load account information
$sql = "SELECT title, id FROM newsletter_messages WHERE site_id='".NUMO_SITE_ID."' ORDER BY title";
//print $sql."<br>";
$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
?>
<h3>'<?=$row['title']?>' Message</h3>
<p>Allows you to display the newsletter message directly in your page(s)</p>
<textarea cols="50" rows="1">[NUMO.NEWSLETTER: VIEW(id=<?=$row['id']?>)]</textarea>
<?php
}
?>