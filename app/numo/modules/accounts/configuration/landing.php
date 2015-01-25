<?php include("upgrade.php"); ?>
<div class="module_install_accounts animated fadeInRight">
<!--
<img class='icon' src="images/accounts.png" />-->
          <span class='fa-stack fa-1x pull-left' style='margin-right: 10px;'> 
            <i class='fa fa-circle fa-stack-2x'></i>
            <i class='fa fa-users fa-stack-1x fa-inverse'></i>
          </span> 
<a class='pull-right' href="http://www.i3dthemes.com/support/numo_accounts/" target="_blank" style='margin-top: 5px; border-bottom: 0px; color: #336699;'><i title='Help' class='fa fa-question-circle'></i></a>
<?php if ($moduleRecord['status'] == 1) { ?>
  <a class='label label-info pull-right' style='margin-right: 10px; margin-top:4px;' href="javascript:changeModuleStatus('accounts', 0);" title='All ACCESS CONTROL related components are currently ONLINE'>online</a> 
<?php } else { ?>
  <a class='label label-important pull-right'  style='margin-right: 10px; margin-top:4px;' href="javascript:changeModuleStatus('accounts', 1);" title='All ACCESS CONTROL related components are now OFFLINE'>offline</a> 
<?php } ?>
<h2 style='line-height: 30px;'>Accounts</h2> 
<hr />
<h3 style='margin-bottom: 0px;margin-top:0px; line-height: 20px; padding-top: 0px;'>Shortcuts</h3>

<?php
$sql = "SELECT COUNT(*) as 'count' FROM accounts a, `types` t WHERE a.pending=1 AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
    ?>
   <p><a style='margin-top:-4px;' class='btn btn-default btn-small pull-right' href="<?php echo NUMO_FOLDER_PATH?>module/accounts/account-pending/"><i class='fa fa-user'></i> Manage</a>
   <span class='badge'><?php echo $row['count']; ?></span> Pending Accounts
   </p>
    <?php

}

$sql = "SELECT COUNT(*) as 'count' FROM accounts a, `types` t WHERE a.pending=0 AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
    ?>
    <p style='margin-bottom: 0px;'>
    <a  style='margin-top:-4px;'class='btn btn-default btn-small pull-right' href="<?php echo NUMO_FOLDER_PATH?>module/accounts/account-manage/"><i class='fa fa-user'></i> Manage</a>
   <span class='badge'><?php echo $row['count']; ?></span> Active Accounts
   </p>
<?php
}
?>

</div>