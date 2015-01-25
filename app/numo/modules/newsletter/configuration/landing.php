<?php
//check and look for any accounts that are not linked to an account and delete their subscriber entry
$sql = "DELETE FROM newsletter_subscribers where account_id NOT IN (SELECT id FROM accounts WHERE id=account_id)";
$dbObj->query($sql);
?>
<div class="module_install_completed">

          <span class='fa-stack fa-1x pull-left' style='margin-right: 10px;'> 
            <i class='fa fa-circle fa-stack-2x'></i>
            <i class='fa fa-comment fa-stack-1x fa-inverse'></i>
          </span> 
<!--<img class='icon' src="images/newsletter.png" />
<a href="http://www.i3dthemes.com/support/numo_newsletter/" target="_blank"><img alt='Help' title='Help' class='help-icon' src="images/help.png" /></a>-->
<a class='pull-right' href="http://www.i3dthemes.com/support/numo_newsletter/" target="_blank" style='margin-top: 5px; border-bottom: 0px; color: #336699;'><i title='Help' class='fa fa-question-circle'></i></a>

<?php if ($moduleRecord['status'] == 1) { ?>
  <a class='label label-info pull-right' style='margin-right: 10px;margin-top:4px;' href="javascript:changeModuleStatus('newsletter', 0);" title='All ACCESS CONTROL related components are currently ONLINE'>online</a> 
<?php } else { ?>
  <a class='label label-important pull-right'  style='margin-right: 10px;margin-top:4px; ' href="javascript:changeModuleStatus('newsletter', 1);" title='All ACCESS CONTROL related components are now OFFLINE'>offline</a> 
<?php } ?>

<h2 style='line-height: 30px;'>Newsletter</h2>

<hr />

<h3 style='margin-bottom: 0px;margin-top:0px; line-height: 20px; padding-top: 20px;'>Latest Newsletters</h3>
<ul style='padding-left: 0px; margin-left: 0px;'>
<?php
$sql = "SELECT title, status, id FROM newsletter_messages WHERE site_id='".NUMO_SITE_ID."' ORDER BY id desc LIMIT 5";
//print $sql."<br>";
$results = $dbObj->query($sql);

$statusLabel = "";

while($row = mysql_fetch_array($results)) {
	if($row['status'] == "1") {
		$statusLabel = "<span class='label label-info pull-right' style='margin-top:2px;'>Online</span>";
	} else {
		$statusLabel = "<span class='label label-default pull-right' style='margin-top:2px;'>Offline</span>";
	}

	print '<li style="line-height: 23px; border-top: 1px solid #cccccc; padding-bottom: 2px;">'
		?>
    <a class='btn btn-default btn-mini' href="<?php echo NUMO_FOLDER_PATH?>module/newsletter/compose/?id=<?php print $row['id']; ?>"><i class='fa fa-pencil'></i>&nbsp;</a>
    <?php
	print $row['title'].' '.$statusLabel.' ';

	print '</li>';
}
?>
</ul>
<h3 style='margin-bottom: 0px;margin-top:0px; line-height: 20px; padding-top: 20px;'>Shortcuts</h3>

<a class='btn btn-default btn-small' href="<?php echo NUMO_FOLDER_PATH?>module/newsletter/compose-new/"><i class='fa fa-asterisk'></i> Create New Newsletter</a>
<a class='btn btn-default btn-small' href="<?php echo NUMO_FOLDER_PATH?>module/newsletter/manage/"><i class='fa fa-edit'></i> View All</a>

</div>