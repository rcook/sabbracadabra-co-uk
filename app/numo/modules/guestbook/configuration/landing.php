<? 
	$result = $dbObj->query("SHOW COLUMNS FROM `guestbook_types` LIKE 'require_review'");
	$exists = (mysql_num_rows($result))?TRUE:FALSE;
	if (!$exists) {
		$dbObj->query("ALTER TABLE `guestbook_types` ADD `require_review` tinyint (4) default 0");
	}
?>
 <div class="module_install_completed">
          <span class='fa-stack fa-1x pull-left' style='margin-right: 10px;'> 
            <i class='fa fa-circle fa-stack-2x'></i>
            <i class='fa fa-book fa-stack-1x fa-inverse'></i> 
          </span> 
<a class='pull-right' href="http://www.i3dthemes.com/support/numo_guestbook/" target="_blank" style='margin-top: 5px; border-bottom: 0px; color: #336699;'><i title='Help' class='fa fa-question-circle'></i></a>

<?php if ($moduleRecord['status'] == 1) { ?>
  <a class='label label-info pull-right' style='margin-right: 10px;margin-top:4px;' href="javascript:changeModuleStatus('<?=$moduleRecord['name']?>', 0);" title='All ACCESS CONTROL related components are currently ONLINE'>online</a> 
<?php } else { ?>
  <a class='label label-important pull-right'  style='margin-right: 10px;margin-top:4px; ' href="javascript:changeModuleStatus('<?=$moduleRecord['name']?>', 1);" title='All ACCESS CONTROL related components are now OFFLINE'>offline</a> 
<?php } ?>
<h2 style='line-height: 30px;'>Guestbook</h2>
<hr />

<h3 style='margin-bottom: 0px;margin-top:0px; line-height: 20px; padding-top: 20px;'>Shortcuts</h3>

<a class='btn btn-default btn-small' href="<?php echo NUMO_FOLDER_PATH?>module/guestbook/manage/"><i class='fa fa-edit'></i> Guestbooks</a>
<a class='btn btn-default btn-small' href="<?php echo NUMO_FOLDER_PATH?>module/guestbook/responses/"><i class='fa fa-eye'></i> Submissions</a>

</div>