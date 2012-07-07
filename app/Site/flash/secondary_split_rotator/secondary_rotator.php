<?php
function luckymarble_show_flash_secondary_rotator() {
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
	attributes.name  = "secondary_split_rotator";
	attributes.align = "middle";
	swfobject.embedSWF(flashRootLocation + "Site/flash/secondary_split_rotator.swf", "headerDivssr", "400", "240", "9.0.0", flashRootLocation + "Site/flash/js/expressInstall.swf", flashvars, params, attributes);
</script>

<!-- secondary_split_rotator -->
<div id="secondary_split_rotator_wrapper"><div id="secondary_split_rotator"><div class="secondary_split_rotator">

	<div id="headerDivssr"><img src="../../graphics/FL_secondarySplitRotatorCover.png" alt="Welcome" style="border:0px;" /></div>

</div></div></div>
<!-- /secondary_split_rotator -->
<?php
}
?>