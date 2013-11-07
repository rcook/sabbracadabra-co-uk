<?php include ("upgrade.php"); ?>
<div class="module_install_settings">
<img class='icon' src="images/configuration.png" />
<a href="http://www.i3dthemes.com/support/numo_settings/" target="_blank"><img alt='Help' title='Help' class='help-icon' src="images/help.png" /></a>

<h2>System Settings</h2>
<hr />
<p>Your server timezone is currently set to <?php echo date("e"); ?>.</p>
</div>
<?php if ($adminVersion != "3") { ?>
<br/>
<?php } ?>

<div class="module_install_settings">
<img class='icon' src="images/updates.png" />

<h2>Recent Updates</h2>
<hr />
<iframe width='100%' style='border: 0px;' height='200px' src='<?php if ($HTTPS == "on" || $_SERVER['SERVER_PORT'] == "443") { print "https://secure.server-apps.com/numo/"; } else { print "http://numo.server-apps.com/"; } ?>update/view-updates/?date=<?php echo $siteData['last_updated']; ?>&modules=<?php print implode(",", $modules); ?>&v=2'></iframe>
</div>