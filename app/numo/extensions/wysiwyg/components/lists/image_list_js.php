<?php
require("../../../../configuration/database_connection_information.php");
if (NUMO_FOLDER_PATH == 'NUMO_FOLDER_PATH') {
	require ("/var/www/vhosts/server-apps.com/subdomains/numo/httpdocs/remote/numo/configuration/database_connection_information.php");
}

require("../../../../classes/functions.php");

// start session
numo_session_start();
//session_start();


// This list may be created by a server logic page PHP/ASP/ASPX/JSP in some backend system.
// There images will be displayed as a dropdown in all image dialogs if the "external_link_image_url"
// option is defined in TinyMCE init.
?>
var tinyMCEImageList = new Array(
	// Name, URL
<?php

$supportedextentions = array(
	'gif',
	'png',
	'jpeg',
	'jpg',
	'bmp'
);

//$leadon = "../media/";
$opendir = $leadon;
if(!$leadon) $opendir = '../../uploads/';
if(!file_exists($opendir)) {
	$opendir = '../../uploads';
	$leadon = $startdir;
	//print "directory doesn't exist";
} else {
//	print "directory does exist";
}
 

//define begin of file name for user account
$userFileStart = "u.".$_SESSION['account_id'].".";

clearstatcache();
if ($handle = opendir($opendir)) {
//	print "opened";
	while (false !== ($file = readdir($handle))) {
	//print $file."<br>";
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
							//if(strlen($filename)>43) {
							//	$filename = substr($files[$i], 0, 40) . '...';
							//}
							//$fileurl = $leadon . $files[$i];
							$filedir = str_replace($imagebasedir, "", $leadon);
					if ($gotOne) { print ","; } ?>
					["<?=$filename?>", "<?php echo NUMO_FOLDER_PATH; ?>extensions/wysiwyg/uploads/<?=$filename?>"]<?php
							if($class=='b') $class='w';
							else $class = 'b';
													$gotOne = true;

						}
					}
					?>
);
