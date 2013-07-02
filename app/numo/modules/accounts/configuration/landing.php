<?php
include("upgrade.php");
?>
<div class="module_install_accounts">
<img class='icon' src="images/accounts.png" />
<a href="http://www.i3dthemes.com/support/numo_accounts/" target="_blank"><img alt='Help' title='Help' class='help-icon' src="images/help.png" /></a>
<?php if ($moduleRecord['status'] == 1) { ?>
  <a class='status-online' href="javascript:changeModuleStatus('accounts', 0);" title='All ACCOUNTS related components are currently ONLINE'>online</a> 
<?php } else { ?>
  <a class='status-offline' href="javascript:changeModuleStatus('accounts', 1);" title='All ACCOUNTS related components are now OFFLINE'>offline</a> 
<?php } ?>
<h2>Accounts</h2>
<hr />
<?php
$sql = "SELECT COUNT(*) as 'count' FROM accounts a, `types` t WHERE a.pending=1 AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
	print '<p>'.$row['count'].' Pending Accounts <a href="module/accounts/account-pending/">[manage]</a></p>';
}

$sql = "SELECT COUNT(*) as 'count' FROM accounts a, `types` t WHERE a.pending=0 AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
	print '<p>'.$row['count'].' Active Accounts <a href="module/accounts/account-manage/">[manage]</a></p>';
}
?>
</div>