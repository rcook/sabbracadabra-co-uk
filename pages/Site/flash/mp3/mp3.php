<?php
function luckymarble_show_flash_music() {
	$musicFiles = get_option('luckymarble_flash_music');
	
	//if no music set then don't show the component
	if(count($musicFiles) > 0) {
?>
<script type="text/javascript"> 
	var flashvars            = {};
	flashvars.configLocation = flashConfigLocation;
	flashvars.configType     = flashConfigType;
	flashvars.playMusic		 = playMusic;
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
	attributes.name  = "mp3";
	attributes.align = "middle";
	swfobject.embedSWF(flashRootLocation + "Site/flash/mp3.swf", "headerDivmp3", "300", "90", "9.0.0", flashRootLocation + "Site/flash/js/expressInstall.swf", flashvars, params, attributes);
</script> 

<!-- mp3 -->
<div id="mp3_wrapper"><div id="mp3"><div class="mp3">

	<div id="headerDivmp3"></div>

</div></div></div>
<!-- /mp3 -->
<?php	
	}
}
?>