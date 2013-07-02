<? 
	$result = $dbObj->query("SHOW COLUMNS FROM `guestbook_types` LIKE 'require_review'");
	$exists = (mysql_num_rows($result))?TRUE:FALSE;
	if (!$exists) {
		$dbObj->query("ALTER TABLE `guestbook_types` ADD `require_review` tinyint (4) default 0");
	}
?>
 <div class="module_install_completed">
<img class='icon' src="images/guestbook.png" />
<a href="http://www.i3dthemes.com/support/numo_guestbook/" target="_blank"><img alt='Help' title='Help' class='help-icon' src="images/help.png" /></a>
<?php if ($moduleRecord['status'] == 1) { ?>
  <a class='status-online' href="javascript:changeModuleStatus('guestbook', 0);" title='All GUESTBOOK related components are currently ONLINE'>online</a> 
<?php } else { ?>
  <a class='status-offline' href="javascript:changeModuleStatus('guestbook', 1);" title='All GUESTBOOK related components are now OFFLINE'>offline</a> 
<?php } ?>

<h2>Guestbook</h2>
<hr />

<h3>Quicklinks</h3>
<ul>
  <li>Manage Guestbooks: <a href="<?php echo NUMO_FOLDER_PATH?>module/guestbook/manage/">manage</a></li>
  <li>View Guestbook Submissions: <a href="<?php echo NUMO_FOLDER_PATH?>module/guestbook/responses/">manage</a></li>
</ul>
</div>