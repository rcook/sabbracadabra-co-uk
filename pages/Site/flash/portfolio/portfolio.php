<?php
function luckymarble_show_flash_portfolio() {
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
	attributes.name  = "portfolio";
	attributes.align = "middle";
	swfobject.embedSWF(flashRootLocation + "Site/flash/portfolio.swf", "headerDivportfolio", "990", "500", "9.0.0", flashRootLocation + "Site/flash/js/expressInstall.swf", flashvars, params, attributes);
</script>

<!-- portfolio -->
<div id="portfolio_wrapper"><div id="portfolio"><div class="portfolio">

	<div id="headerDivportfolio"><img src="../../themed_images/portfolio-large-1.jpg" alt="Welcome" style="border:0px;" /></div>

</div></div></div>
<!-- /portfolio -->

<?php
}
?>