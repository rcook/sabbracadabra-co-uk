<?php include ("upgrade.php"); ?>
<div class="module_install_settings animated fadeInRight">
<!--<a href="http://www.i3dthemes.com/support/numo_settings/" target="_blank"><img alt='Help' title='Help' class='help-icon' src="images/help.png" /></a>-->
<a class='pull-right' href="http://www.i3dthemes.com/support/numo_settings/" target="_blank" style='margin-top: 5px; border-bottom: 0px; color: #336699;'><i title='Help' class='fa fa-question-circle'></i></a>

          <span class='fa-stack fa-1x pull-left' style='margin-right: 10px;'> 
            <i class='fa fa-circle fa-stack-2x'></i>
            <i class='fa fa-gears fa-stack-1x fa-inverse'></i>
          </span> 
<h2 style='line-height: 30px;'>System Settings</h2>
<div style='clear: both; height: 10px;'></div>
<hr />
<!--<img class='icon' src="images/configuration.png" />-->

<p>Your server timezone is currently set to <?php echo date("e"); ?>.</p>
<ul style='margin-left: 0px; padding-left: 0px;'>
<?php 
foreach ($modules as $key => $moduleID) { 
  if ($moduleID != "settings") {
	$query = "SELECT * FROM modules WHERE name='{$moduleID}' AND site_id='".NUMO_SITE_ID."' AND status>=0";
	$modRes = $dbObj->query($query);
	$modRec = mysql_fetch_array($modRes);

  ?><li style='clear: both;'>

<?php if ($modRec['status'] == 1) { ?>
  <a class='label label-info pull-right' style='margin-right: 10px;margin-top:4px;' href="javascript:changeModuleStatus('<?php echo $moduleID; ?>', 0);" title='All related components are currently ONLINE'>online</a> 
<?php } else { ?>
  <a class='label label-important pull-right'  style='margin-right: 10px;margin-top:4px; ' href="javascript:changeModuleStatus('<?php echo $moduleID; ?>', 1);" title='All related components are now OFFLINE'>offline</a> 
<?php } ?>  
  <?php print ucwords(str_replace("handler", "wizard", str_replace("sections", "wizard", str_replace("_", " ", $modRec["name"])))); ?>
  </li>
<?php 
  }
} ?>
</ul>
<?php if ($adminVersion != "3") { ?>
<br/>
<?php } ?>
</div>

<?php if (true) {
	ob_start();
	?>
<div class="module_install_settings animated fadeInRight">
          <span class='fa-stack fa-1x pull-left' style='margin-right: 10px;'> 
            <i class='fa fa-circle fa-stack-2x'></i>
            <i class='fa fa-arrow-up fa-stack-1x fa-inverse'></i>
          </span> 
<h2 style='line-height: 30px; display: inline-block;'>Available Upgrades</h2>
<form method="post" style='display: inline;'>
<input type='hidden' name='cmd' value='hide-side-bar-offers' />
<button type='submit' class='btn btn-small btn-default' style='vertical-align: top; margin-top: 5px;'><i class='fa fa-times'></i></button>
</form>
<div style='clear: both; height: 10px;'></div>
<hr />
<ul style='margin-left: 0px; padding-left: 0px;'>
<?php 
$availableModules = array('access_control', 'blog', 'newsletter', 'listing_service', 'content_sections', 'form_handler', 'web_ballot', 'whois_online', 'help_desk', 'calendar', 'guestbook', 'shopping_cart');
asort($availableModules);
$availableModules = array_diff($availableModules, $modules);

foreach ($availableModules as $key => $moduleID) { 
  if ($moduleID != "settings") {
	$query = "SELECT * FROM modules WHERE name='{$moduleID}' AND site_id='".NUMO_SITE_ID."' AND status>=0";
	$modRes = $dbObj->query($query);
	$modRec = mysql_fetch_array($modRes);
    if ($modRec['name'] == "") { $modRec['name'] = $moduleID; } 
	
  ?><li style='clear: both;'>

<?php if ($modRec['status'] == "0" || $modRec['status'] == "1") { ?>
<?php } else if ($modRec['status'] == "2") {
	$displayedUpgrade = true; ?>
  <a class='label label-info pull-right' style='margin-right: 10px;margin-top:4px; width: 55px; text-align: center;' href="javascript:changeModuleStatus('<?php echo $moduleID; ?>', 0);" title='This has been unlocked, but needs to be installed.  Click to install.'><i class='fa fa-cloud-download'></i> install</a> 
  <?php print ucwords(str_replace("handler", "wizard", str_replace("sections", "wizard", str_replace("_", " ", $modRec["name"])))); ?>
<?php } else { 
$displayedUpgrade = true; ?>
  <a class='label label-default pull-right'  style='margin-right: 10px;margin-top:4px; width: 55px; text-align: center;' data-toggle="modal" href="module/settings/fetch-upgrades/?display=response_only" data-target="#unlockModal"><i class='fa fa-lock'></i> unlock</a> 
  <?php print ucwords(str_replace("handler", "wizard", str_replace("sections", "wizard", str_replace("_", " ", $modRec["name"])))); ?>
<?php } ?>  
  </li>
<?php 
  }
} ?>
</ul>
</div>

<!-- Modal -->
<div id="unlockModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
<h3 id="myModalLabel">Unlock Upgrades</h3>
</div>
<div class="modal-body">
<div class='text-center' style='padding: 100px 0px;'><i class='fa fa-cog fa-spin fa-4x'></i><br/>
Loading Special Prices, Please Wait</div> 
</div>
<div class="modal-footer">
<button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
</div>
</div>
<?php
	if ($displayedUpgrade && $siteData['show_side_bar_offers'] == "1") {
		ob_end_flush();
	} else {
		ob_end_clean();
	}
} ?>

<div class="module_install_settings animated fadeInRight">
          <span class='fa-stack fa-1x pull-left' style='margin-right: 10px;'> 
            <i class='fa fa-circle fa-stack-2x'></i>
            <i class='fa fa-cloud-download fa-stack-1x fa-inverse'></i>
          </span> 
<h2 style='line-height: 30px;'>Recent Updates</h2>
<div style='clear: both; height: 10px;'></div>
<hr />
<iframe width='100%' style='border: 0px;' height='200px' src='<?php if ($HTTPS == "on" || $_SERVER['SERVER_PORT'] == "443") { print "https://secure.server-apps.com/numo/"; } else { print "http://numo.server-apps.com/"; } ?>update/view-updates/?date=<?php echo $siteData['last_updated']; ?>&modules=<?php print implode(",", $modules); ?>&v=2'></iframe>
</div>