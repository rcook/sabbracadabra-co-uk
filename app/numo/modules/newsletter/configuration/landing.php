<?php
//check and look for any accounts that are not linked to an account and delete their subscriber entry
$sql = "DELETE FROM newsletter_subscribers where account_id NOT IN (SELECT id FROM accounts WHERE id=account_id)";
$dbObj->query($sql);
?>
<div class="module_install_completed">
<img class='icon' src="images/newsletter.png" />
<a href="http://www.i3dthemes.com/support/numo_newsletter/" target="_blank"><img alt='Help' title='Help' class='help-icon' src="images/help.png" /></a>
<?php if ($moduleRecord['status'] == 1) { ?>
  <a class='status-online' href="javascript:changeModuleStatus('newsletter', 0);" title='All NEWSLETTER related components are currently ONLINE'>online</a> 
<?php } else { ?>
  <a class='status-offline' href="javascript:changeModuleStatus('newsletter', 1);" title='All NEWSLETTER related components are now OFFLINE'>offline</a> 
<?php } ?>

<h2>Newsletter</h2>
<!--
<p>Instruction: <a href="http://www.luckymarble.com/members/product/helpsys/numo/numo_newsletter_guide.pdf">View Manual</a></p>
-->
<hr />
<!--
<style>
.module_install_completed h3 {color: #4E720E; padding: 4px 0px; margin-left: 70px; text-decoration: underline;}
.module_install_completed ul {margin: 0px 0px 0px 90px; padding: 0px; list-style-image:url("modules/newsletter/images/point_icon.jpg");}
.module_install_completed li {margin: 0px; padding: 0px;color: #000; font-size: 0.8em;}
</style>
-->
<h3>Latest Newsletters</h3>
<ul>
<?php
$sql = "SELECT title, status, id FROM newsletter_messages WHERE site_id='".NUMO_SITE_ID."' ORDER BY id desc LIMIT 5";
//print $sql."<br>";
$results = $dbObj->query($sql);

$statusLabel = "";

while($row = mysql_fetch_array($results)) {
	if($row['status'] == "1") {
		$statusLabel = "Online";
	} else {
		$statusLabel = "Offline";
	}

	print '<li>'.$row['title'].' ('.$statusLabel.') <a href="module/newsletter/compose/?id='.$row['id'].'">[edit]</a></li>';
}
?>
</ul>
<p><br /><a href="module/newsletter/manage/">[View All]</a></p>
</div>