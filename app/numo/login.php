<?php
$errorMessage = "";

//process login request
if($_POST['cmd'] == "login") {
	if(!login($_POST['username'],$_POST['password'])) {
		$errorMessage = "<p>Could not find account matching login information. Please try again.</p>";
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
        <?php if ($_SERVER['HTTPS'] == "on") { ?>
		<base href="https://<?php echo NUMO_SECURE_ADDRESS."".NUMO_FOLDER_PATH; ?>" />	
        <?php } else { ?>
		<base href="http://<?php echo NUMO_SERVER_ADDRESS."".NUMO_FOLDER_PATH; ?>" />	
        <?php } ?>	
		<style>		
		body { padding: 0px; margin: 0px; font-family: Arial, sans-serif; color: #333; font-size: 0.9em;}
		div,h2,p { padding: 0px; margin: 0px; }
		#top_bar {background: #E4E4E4 url('images/bar_bg.jpg') repeat-x; height: 40px; text-align: left; color: #333333; font-weight: bold;}
		#top_bar img { position: absolute; top: 0px; right: 0px;}
		#top_bar p { margin: 0px; padding: 0px 5px; line-height: 40px;}		
		.content_box {
			 width: 300px; 
			 margin: 25px auto; 
			 text-align: left; 
			 border: 1px solid #dddddd;
			 border-radius: 5px;
			 box-shadow: 0 4px 18px #cccccc;
			 margin-top: 100px;
	    }
		.content_box p {color: #999; font-size: 0.75em; padding: 10px 5px;}
		.content_box form {padding: 10px; margin: 0px;}
		.content_box form ul {padding: 0px; margin: 0px; list-style-type: none; }
		.content_box form ul li { padding: 2px 0px; font-size: 11px; color: #f00; padding-bottom: 10px;}
		.content_box form ul li label { float: left; width: 12em; font-weight: normal; font-size: 13px; color: #666;}
		.content_header { 
		    background: #E4E4E4 url('images/bar_bg.jpg') repeat-x; 
			border-top-right-radius: 5px; 
			border-top-left-radius: 5px;
			-moz-border-top-right-radius: 5px; 
			-moz-border-top-left-radius: 5px;
			-webkit-border-top-right-radius: 5px; 
			-webkit-border-top-left-radius: 5px;
			border: 1px solid #ffffff;
			
		}
		.content_header img {margin: 0px; padding: 0px 7px 0px 0px; border: 0px;}
		.content_header h2 { line-height: 40px; color: #333333; font-size: 1.4em; text-decoration: none; text-shadow: 1px 1px #ffffff; }
		.content_box input[type=submit] { float: right;  		    background: #E4E4E4 url('images/bar_bg.jpg') repeat-x; 
			 border: 1px solid #dddddd;
			 border-radius: 5px;
			 padding: 3px 7px 3px 7px;
			 font-weight: bold;
			 box-shadow: 1px 1px 3px #cccccc;
text-shadow: 1px 1px #ffffff;
cursor: pointer;
}
.content_box input[type=submit]:hover { 
  color: #F60;
}
		.content_box input[type=text], .content_box input[type=password] { width: 95%; height: 25px; font-size: 24px;  border: 1px solid #dddddd; background-color: #fafafafa; padding: 3px; color: #666; }
		.content_box a { color: #336699; font-size: 9pt; line-height: 20px; }
		.content_box a:hover { color: #F60; }
        </style>
	</head>
            <div id="top_bar">
                <p>Administrative Login</p>
                <img src="images/logo.jpg" alt="NUMO" title="NUMO" />
            </div>
            <div class="content_box">
            <div class="content_header"><img src="images/admin-icon.png" align="left" /><h2>Administrator Login</h2></div>
<!--            <p>Login to your administrative account below.</p> -->
            <form method="post">
                <ul class="form_display">
                    <li><label for="username">Username</label><input type="text" id="username" name="username" value="<?php echo $adminLogin; ?>" /></li>
                    <li><label for="password">Password</label><input type="password" id="password" name="password" value="<?php echo $adminPassword; ?>" /></li>
                    <li><a href="http://<?php echo NUMO_SERVER_ADDRESS; ?>/manage.numo?module=accounts&component=forgot password">Forgot your password?</a><input type="hidden" name="cmd" value="login" /><input type="submit" id="submit" name="nocmd" value="login" /></li>
                </ul>
            </form>	
            
            </div>
	</body>
	</html>	
<?php
	exit();

//if logged in by not an administrator redirect to root folder
} else if($_SESSION['is_admin'] ==0 || $_SESSION['is_admin'] == "") {
	//refresh page
	header("Location: ../");
}
?>