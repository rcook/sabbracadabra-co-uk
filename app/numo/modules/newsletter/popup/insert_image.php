<?php
/********************************************************************
 * openImageLibrary addon Copyright (c) 2006 openWebWare.com
 * Contact us at devs@openwebware.com
 * This copyright notice MUST stay intact for use.
 ********************************************************************/

require('config.inc.php');
error_reporting(0);
require("../../../classes/functions.php");

// start session
numo_session_start();
//session_start();

if(!isset($_SESSION['account_id']) || $_SESSION['is_admin'] != "1") {
	print "<p>Error: You are currently not logged into an account.  Please close this window and refresh your page to login again.</p>";
	exit;
}

$updatePreviewImageStr = "";

// get the identifier of the editor
$wysiwyg = $_GET['wysiwyg']; 
// set image dir
$leadon = $rootdir.$imagebasedir;

if($leadon=='.') $leadon = '';
if((substr($leadon, -1, 1)!='/') && $leadon!='') $leadon = $leadon . '/';
$startdir = $leadon;

// validate the directory
$_GET['dir'] = $_POST['dir'] ? $_POST['dir'] : $_GET['dir'];
if($_GET['dir']) {
	if(substr($_GET['dir'], -1, 1)!='/') {
		$_GET['dir'] = $_GET['dir'] . '/';
	}
	$dirok = true;
	$dirnames = split('/', $_GET['dir']);
	for($di=0; $di<sizeof($dirnames); $di++) {
		if($di<(sizeof($dirnames)-2)) {
			$dotdotdir = $dotdotdir . $dirnames[$di] . '/';
		}
	}
	if(substr($_GET['dir'], 0, 1)=='/') {
		$dirok = false;
	}

	if($_GET['dir'] == $leadon) {
		$dirok = false;
	}
	
	if($dirok) {
		$leadon = $_GET['dir'];
	}
}

// upload file
if($allowuploads && $_FILES['file']) {
	$upload = true;
	if(!$overwrite) {
		if(file_exists($leadon.$_FILES['file']['name'])) {
			$upload = false;
		}
	}
	$ext = strtolower(substr($_FILES['file']['name'], strrpos($_FILES['file']['name'], '.')+1));
	if(!in_array($ext, $supportedextentions)) {
		$upload = false;
	}
	if($upload) {
		move_uploaded_file($_FILES['file']['tmp_name'], $leadon . "u.".$_SESSION['account_id'].".".$_FILES['file']['name']);
		$updatePreviewImageStr = '<script>document.getElementById("src").value = "'.$imagebaseurl."u.".$_SESSION['account_id'].".".$_FILES['file']['name'].'"; document.getElementById("preview").src = "'.$imagebaseurl."u.".$_SESSION['account_id'].".".$_FILES['file']['name'].'";</script>';
	}
}

if($allowuploads) {
	$phpallowuploads = (bool) ini_get('file_uploads');		
	$phpmaxsize = ini_get('upload_max_filesize');
	$phpmaxsize = trim($phpmaxsize);
	$last = strtolower($phpmaxsize{strlen($phpmaxsize)-1});
	switch($last) {
		case 'g':
			$phpmaxsize *= 1024;
		case 'm':
			$phpmaxsize *= 1024;
	}
}

?>


<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<title>Insert Image</title>
<script language="JavaScript" type="text/javascript">
/* ---------------------------------------------------------------------- *\
  Function    : insertImage()
  Description : Inserts image into the WYSIWYG.
\* ---------------------------------------------------------------------- */
function insertImage() {
	// get values from form fields
	var src = document.getElementById('src').value;
	var alt = document.getElementById('alt').value;
	var width = document.getElementById('width').value;
	var height = document.getElementById('height').value;
	
	// insert image
	window.opener.insert_image(src, width, height, alt);
  window.close();
}
</script>
<style>
html, body,iframe {padding: 0px; margin: 0px;}
img#preview {max-width: 250px; max-height: 150px; width: expression(this.width > 250 ? 250: true); height: expression(this.height > 150 ? 150: true);}
.selectedImagePreview { border: 1px solid #999; text-align: center; vertical-align: middle; height: 160px; width: 260px;}
table tr td {padding-top: 0px;padding-bottom: 2px;font-family: arial, tahoma; font-size: 11px; vertical-align: top; }
</style>
</head>
<body bgcolor="#ffffff" marginwidth="0" marginheight="0" topmargin="0" leftmargin="0">
<form method="post" enctype="multipart/form-data">
<input type="hidden" id="dir" name="dir" value="">
<table width="100%" border="0" cellpadding="0" cellspacing="0" style="border: 2px solid #000; padding: 1px; margin-bottom: 4px;">
	<tr>
		<td style="width: 300px;">
		<span style="font-family: arial, verdana, helvetica; font-size: 11px; font-weight: bold;">Select An Image:</span>
		<iframe id="chooser" frameborder="0" style="height:270px;width: 300px;" src="select_image.php?dir=<?php echo $leadon; ?>"></iframe></td>
		</td>
		<td>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #fff; border: 1px solid #666; padding: 5px; margin-right: 5px;">
			<tr>
			<td>
				<span style="font-family: arial, verdana, helvetica; font-size: 11px; font-weight: bold;">Preview:</span>
			</tr>
			<tr>
			<td class="selectedImagePreview">
				<img src="/webs/NUMO_NEWSLETTER/numo/modules/newsletter/images/image_upload_preview_default.jpg" id="preview" /></td>
			</tr>			
			<tr>
			<td>
			<table width="100%" border="0" cellpadding="0" cellspacing="0" style="padding: 5px; margin-right: 5px;">
				<tr>
					<td style="padding-bottom: 2px; padding-top: 0px; font-family: arial, verdana, helvetica; font-size: 11px; width: 100px;">Alternate Text:</td>
					<td style="padding-bottom: 2px; padding-top: 0px;"><input type="text" name="alt" id="alt" value="" style="font-size: 10px; width: 100%;"></td>
				</tr>
				<tr>
					<td style="padding-bottom: 2px; padding-top: 0px; font-family: arial, verdana, helvetica; font-size: 11px; width: 100px;">Width:</td>
					<td style="padding-bottom: 2px; padding-top: 0px;"><input type="text" name="width" id="width" value="" style="font-size: 10px; width: 100%;"></td>
				</tr>
				<tr>
					<td style="padding-bottom: 2px; padding-top: 0px; font-family: arial, verdana, helvetica; font-size: 11px; width: 100px;">Height:</td>
					<td style="padding-bottom: 2px; padding-top: 0px;"><input type="text" name="height" id="height" value="" style="font-size: 10px; width: 100%;"></td>
				</tr>				
				<tr>
					<td style=" font-size: 11px;">&nbsp;</td>
					<td><input type="submit" value="Insert" onClick="insertImage();return false;" style="font-size: 12px;"></td>
				</tr>	
			</table>	
			</td>
			</tr>
			</table>
		</td>
	</tr>	

</table>

<table width="100%" border="0" cellpadding="0" cellspacing="0" style="background-color: #eee; border: 2px solid #000; padding: 5px;">
	<?php
	if($allowuploads) {
		if($phpallowuploads) {
		
	?>
		<tr>
			<td style="width:110px;">Upload New Image:</td>
			<td><input type="file" name="file" size="30" style="font-size: 10px; width: 100%;" /></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td style="font-size: 9px;">(Max Filesize: <?php echo $phpmaxsize; ?>KB)</td>
		</tr>
	<?php
		}
		else {
	?>
		<tr>
			<td style="font-size: 11px;" colspan="2">
				File uploads are disabled in your php.ini file. Please enable them.
			</td>
		</tr>
	<?php
		}
	}
	?>
	<?php if ( $allowuploads ) { ?> 
		<tr>
		<td style="font-size: 11px;" width="80">&nbsp;</td>
		<td><input type="submit" value="  Upload  " style="font-size: 12px;"></td>
		</tr>
	<?php } ?> 		
</table>
<input type="hidden" name="src" id="src" value="">
</form>
<?=$updatePreviewImageStr?>
</body>
</html>
