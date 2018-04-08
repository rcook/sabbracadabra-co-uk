<?
require("../../../../../../configuration/database_connection_information.php");

require('config.inc.php');
error_reporting(0);
//error_reporting(E_ALL);

require("../../../../../../classes/functions.php");

// start session
numo_session_start();
//session_start();


if(!isset($_SESSION['account_id'])) {
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
		foreach($_FILES['file'] as $x => $y) {
			//print $x."=".$y."<br>";
		}
		$yes = move_uploaded_file($_FILES['file']['tmp_name'], $leadon . "u.".$_SESSION['account_id'].".".$_FILES['file']['name']);
		$updatePreviewImageStr = '<script>document.getElementById("src").value = "'.$imagebaseurl."u.".$_SESSION['account_id'].".".$_FILES['file']['name'].'"; document.getElementById("preview").src = "'.$imagebaseurl."u.".$_SESSION['account_id'].".".$_FILES['file']['name'].'";</script>';
	//  print "yup $yes <br>".$_FILES['file']['tmp_name']." to ".$leadon . "u.".$_SESSION['account_id'].".".$_FILES['file']['name'];
	} else {
		//print "Nope";
	}
}

?>
<script>
window.parent.doneUpload();
</script>