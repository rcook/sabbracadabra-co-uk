<?php
$errorMessage = "";

$query = "SELECT * FROM `sites` WHERE id='".NUMO_SITE_ID."'";
$results = $dbObj->query($query);
$siteData = mysql_fetch_array($results);
include_once("extensions/captcha/recaptchalib.php");

//process login request
if($_POST['cmd'] == "login") {
	$attemptLogin = true;
	
	if ($siteData['admin_require_captcha'] == 1) {
		
					$resp = recaptcha_check_answer ("6Ld1htoSAAAAAHc7SI-RwWI71aR0YaVSze77fczU",
                                $_SERVER["REMOTE_ADDR"],
                                $_POST["recaptcha_challenge_field"],
                                $_POST["recaptcha_response_field"]);

					  if (!$resp->is_valid) {
						$attemptLogin = false;
						// What happens when the CAPTCHA was entered incorrectly
						$recaptchaError = "Invalid CAPTCHA Provided";
					  } 
	}
    
	if($attemptLogin && !login($_POST['username'],$_POST['password'], false, $siteData['login_attempts_threshold'], $siteData['bad_login_freeze_period'])) {
		$errorLogin = "<p>Could not find account matching login information. Please try again.</p>";
	} else {
	  	include("configuration/sql-upgrade.php");
	}
}

//if not logged in show login box
if(!isset($_SESSION['type_id'])) {	
  if ($HTTP_HOST == "webs.my-demos.com") {
	  $adminLogin = "admin";
	  $adminPassword = "password";
  }
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
	<head>
		<title>Login</title>
        <?php if ($_SERVER['HTTPS'] == "on" || $_SERVER['SERVER_PORT'] == "443") { ?>
		<base href="https://<?php echo NUMO_SECURE_ADDRESS."".NUMO_FOLDER_PATH; ?>" />	
        <?php } else { ?>
		<base href="http://<?php echo NUMO_SERVER_ADDRESS."".NUMO_FOLDER_PATH; ?>" />	
        <?php } ?>	
		<style>		
		body {background: #f6f6f6;   padding: 0px; margin: 0px; font-family: Arial, sans-serif; color: #333; font-size: 0.9em;}
		div,h2,p { padding: 0px; margin: 0px; }
		#top_bar {
			height: 90px; text-align: left; color: #333333; font-weight: bold;
		}
		#top_bar img { float: left;}
		#top_bar p { margin: 0px; padding-right: 20px; line-height: 90px; float: right; font-size: 1.4em;}	
		#main_box {
			padding-top: 75px;
			padding-bottom: 75px;
			
			-moz-box-shadow: 0 0 5px #888;
			-webkit-box-shadow: 0 0 5px #888;
			box-shadow: 0 0 5px #888;
			background: #ffffff;
		}
		#bottom_bar {
		  position: fixed;
		  bottom: 0px;
		  text-align: center;
		  font-size: 8pt;
		  color: #666666;
		  width: 100%;
		  padding-bottom: 10px;
		}
		#bottom_bar a {
			color: #666666;
		}
		.content_box {
			
			 width: 600px; 
			 
			 margin: auto; 
			 text-align: left; 
			 /*
			 border: 1px solid #dddddd;
			 border-radius: 5px;
			 box-shadow: 0 4px 18px #cccccc;
			 */
	    }
		.content_box p { color: #999; font-size: 0.75em; padding: 10px 5px;}
		.content_box form {padding: 10px; margin: 0px; margin-left: 100px; margin-right: 100px;}
		.content_box form ul {padding: 0px; margin: 0px; list-style-type: none; }
		.content_box form ul li {font-weight: bold; padding: 2px 0px; font-size: 11px; color: #f00; padding-bottom: 10px;}
		.content_box form ul li label { float: left; width: 12em; font-weight: normal; font-size: 13px; color: #666;}
		.content_header { 

			
		}
		b {
			background-image: url(images/underline.png);
			background-position: center bottom;
			background-repeat: no-repeat;
			padding-bottom: 5px;
		}
		.content_header h2 { text-align: center; color: #333333; font-size: 34pt; text-decoration: none; margin: 0px; padding: 0px;}
		.content_header p {  text-align: center; color: #444444; font-size: 20pt; padding: 0px 5px 10px 5px;  }
		.content_box input[type=submit] { float: right;  		    
		
		     background: #E4E4E4 url('images/bar_bg.jpg') repeat-x; 
			 border: 1px solid #dddddd;
			 border-radius: 5px;
			 padding: 3px 7px 3px 7px;
			 font-weight: bold;
			 box-shadow: 1px 1px 3px #cccccc;
text-shadow: 1px 1px #ffffff;
font-size: 14pt;
cursor: pointer;
margin-right: 15px;
margin-top: 10px;
}
.content_box input[type=submit]:hover { 
  color: #F60;
}
		.content_box input[type=text], .content_box input[type=password] { width: 95%; height: 25px; font-size: 24px;  border: 1px solid #dddddd; background-color: #fafafafa; padding: 3px; color: #666; }
		.content_box a { color: #336699; font-size: 9pt; line-height: 20px; }
		.content_box a:hover { color: #F60; }
        </style>
        <!-- <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1"> -->
	</head>
    <body>
    <?php
	$messages[] = array("Welcome Back!", "Don't forget to <b>bookmark</b> this page.");
	$messages[] = array("Aloha!", "Another <b>beautiful</b> day in parardise.");
	$messages[] = array("You Look <b>Great</b>!", "Did you do something different with your hair?");
	$messages[] = array("Haven't you heard?", "People who log in using this page are<br/><b>SUPER-DUPER</b> administators.");
	$messages[] = array("Do you come here often?", "[Honestly, this isn't my <b>only</b> pick-up line]");
	$position = rand(0, sizeof($messages) - 1);
	
	?>
            <div id="top_bar">
                <p>Administrative Login</p>
                <img src="images/logo2013.png" alt="NUMO" title="NUMO" />
            </div>
            <div id="main_box">
            <div class="content_box">
            <div class="content_header">
     <h2><?=$messages["{$position}"][0]?></h2>
            <p><?=$messages["{$position}"][1]?></p> 
            </div>
            <form method="post">
                <ul class="form_display"> 
                <?php if ($badLoginError != "") { ?>
                    <li><?php echo $badLoginError; ?></li>
                <?php } ?>
                    <li><label for="username">Username</label><input type="text" id="username" name="username" value="<?php echo $adminLogin; ?>" /></li>
                    <li><label for="password">Password</label><input type="password" id="password" name="password" value="<?php echo $adminPassword; ?>" /></li>
                   <?php if ($siteData['admin_require_captcha'] == 1) { ?>
                     <li style='margin-left: 40px; '><?php print recaptcha_get_html("6Ld1htoSAAAAAEayI5F-fVLCYaICJpaodJHuGb9R"); ?> <?php print $recaptchaError; ?></li>
                  
                   <?php } ?>

                   <?php if (REMOTE_SERVICE === true) { ?>
                    <li><label for="password">Domain</label><input type="text" id="numo_domain" name="numo_domain" value="<?php echo $numoDomain; ?>" /></li>
                   
                   <?php } ?>
                    <li><a href="http://<?php echo NUMO_SERVER_ADDRESS; ?>/manage.numo?module=accounts&component=forgot password">Forgot your password?</a><input type="hidden" name="cmd" value="login" /><input type="submit" id="submit" name="nocmd" value="Log In" /></li>
                </ul>
            </form>	
            
            </div>
            </div>
           <div id='bottom_bar'>Site powered by i3dTHEMES <a href="http://www.i3dthemes.com/website-plugins/">website plugins</a></div>
	</body>
	</html>	
<?php
	exit();

//if logged in by not an administrator redirect to root folder
} else if($_SESSION['is_admin'] == 0 || $_SESSION['is_admin'] == "") {
	//refresh page
	header("Location: ../");
}
?>