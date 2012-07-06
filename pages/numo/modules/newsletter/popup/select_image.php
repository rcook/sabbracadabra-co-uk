<?php
/********************************************************************
 * openImageLibrary addon Copyright (c) 2006 openWebWare.com
 * Contact us at devs@openwebware.com
 * This copyright notice MUST stay intact for use.
 ********************************************************************/
require('config.inc.php');
error_reporting(0);
session_start();

if((substr($imagebaseurl, -1, 1)!='/') && $imagebaseurl!='') $imagebaseurl = $imagebaseurl . '/';
if((substr($imagebasedir, -1, 1)!='/') && $imagebasedir!='') $imagebasedir = $imagebasedir . '/';
$leadon = $imagebasedir;
if($leadon=='.') $leadon = '';
if((substr($leadon, -1, 1)!='/') && $leadon!='') $leadon = $leadon . '/';
$startdir = $leadon;

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

$opendir = $leadon;
if(!$leadon) $opendir = '.';
if(!file_exists($opendir)) {
	$opendir = '.';
	$leadon = $startdir;
}

//define begin of file name for user account
$userFileStart = "u.".$_SESSION['account_id'].".";

clearstatcache();
if ($handle = opendir($opendir)) {
	while (false !== ($file = readdir($handle))) { 
		//first see if this file is required in the listing
		if ($file == "." || $file == "..")  continue;
		
		if (@filetype($leadon.$file) == "dir") {
			continue;
		}	else {
			$n++;
			if($_GET['sort']=="date") {
				$key = @filemtime($leadon.$file) . ".$n";
			}
			elseif($_GET['sort']=="size") {
				$key = @filesize($leadon.$file) . ".$n";
			}
			else {
				$key = $n;
			}
			
			if(substr($file,0,(strlen($userFileStart))) == $userFileStart) {
				$files[$key] = $file;
			}
		}
	}
	closedir($handle); 
}

//sort our files
if($_GET['sort']=="date") {
	@ksort($dirs, SORT_NUMERIC);
	@ksort($files, SORT_NUMERIC);
}
elseif($_GET['sort']=="size") {
	@natcasesort($dirs); 
	@ksort($files, SORT_NUMERIC);
}
else {
	@natcasesort($dirs); 
	@natcasesort($files);
}

//order correctly
if($_GET['order']=="desc" && $_GET['sort']!="size") {$dirs = @array_reverse($dirs);}
if($_GET['order']=="desc") {$files = @array_reverse($files);}
$dirs = @array_values($dirs); $files = @array_values($files);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
<title>openWYSIWYG | Select Image</title>
<style type="text/css">
html,body {padding: 0px; margin: 0px;}
a {
	font-family: Arial, verdana, helvetica; 
	font-size: 11px; 
	color: #000000;
	text-decoration: none;
}
a:hover {
	text-decoration: underline;
}
.selectableImage {padding: 5px; border-bottom: 2px solid #444;}
</style>
<script type="text/javascript">
	function selectImage(url) {
		if(parent) {
			parent.document.getElementById("src").value = url;
			parent.document.getElementById("preview").src = url;
		}
	}
	
	if(parent) {
		parent.document.getElementById("dir").value = '<?php echo $leadon; ?>';
	}
</script>
</head>
<body>
	<table border="0" width="100%">
		<tbody>
				  <?php
					$class = 'b';
					
					$arsize = sizeof($files);
					for($i=0;$i<$arsize;$i++) {
						$icon = 'unknown.png';
						$ext = strtolower(substr($files[$i], strrpos($files[$i], '.')+1));
						if(in_array($ext, $supportedextentions)) {
							
							$thumb = '';
							if($filetypes[$ext]) {
								$icon = $filetypes[$ext];
							}
							
							$filename = $files[$i];
							if(strlen($filename)>43) {
								$filename = substr($files[$i], 0, 40) . '...';
							}
							$fileurl = $leadon . $files[$i];
							$filedir = str_replace($imagebasedir, "", $leadon);
					?>
					<tr><td class="selectableImage"><a href="javascript:void(0)" onclick="selectImage('<?php echo $imagebaseurl.$filedir.$filename; ?>');"><img src="../images/<?php echo $icon; ?>" alt="<?php echo $icon; ?>" border="0" /> <strong><?php echo substr($filename,(strlen($userFileStart))); ?></strong></a></td></tr>
					<?php
							if($class=='b') $class='w';
							else $class = 'b';	
						}
					}	
					?>
		</tbody>
	</table>
</body>
</html>