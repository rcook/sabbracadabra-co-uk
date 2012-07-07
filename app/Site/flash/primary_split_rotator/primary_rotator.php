<?php
function luckymarble_show_flash_primary_rotator() {
?>
<script type="text/javascript">
	var flashvars            = {};
	flashvars.configLocation = flashConfigLocation;
	flashvars.configType     = flashConfigType;
	var params               = {};
	params.play              = "true";
	params.loop              = "true";
	params.quality           = "high";
	params.scale             = "showall";
	params.wmode             = "transparent";
	params.devicefont        = "false";
	params.swliveconnect     = "false";
	params.allowfullscreen   = "false";
	params.allowscriptaccess = "sameDomain";
	params.base              = flashRootLocation + "Site/flash/";
	var attributes   = {};
	attributes.id    = "flashheader";
	attributes.name  = "primary_split_rotator";
	attributes.align = "middle";
	swfobject.embedSWF(flashRootLocation + "Site/flash/primary_split_rotator.swf", "headerDivpsr", "600", "380", "9.0.0", flashRootLocation + "Site/flash/js/expressInstall.swf", flashvars, params, attributes);
</script>

<!-- primary_split_rotator -->
<div id="primary_split_rotator_wrapper"><div id="primary_split_rotator"><div class="primary_split_rotator">

	<div id="headerDivpsr"><img src="../../graphics/FL_primarySplitRotatorCover.png" alt="Welcome" style="border:0px;" /></div>

</div></div></div>
<!-- /primary_split_rotator -->
<?php
}
?>