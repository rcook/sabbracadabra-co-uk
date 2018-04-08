<?php
//***************************************************************************
// check to see if visitor is logged in already
//***************************************************************************
if(isset($_SESSION['account_id'])) {
	//don't show component when the visitor is already logged into an account
	return;
}
//***************************************************************************
global $HTTP_HOST;
?>
<style>
#numo_account_login_component {text-align: center; margin-left: 0px;}
#numo_account_login_component form {text-align: left; margin: 0px; padding: 10px 0px;}
#numo_account_login_component form ul li { margin: 0px; padding: 0px}
#numo_account_login_component form ul {list-style-type: none; margin: 0px; padding: 0px;}
#numo_account_login_component form ul li label {font-size: 13px; width: 90px; display: inline-block; color: #666; font-weight: bold;}
#numo_account_login_component a {font-size: 13px; text-align: center; color: #444;}
#numo_account_login_component input { width: 125px; }
</style>
<div class="whatsnew">
<div class="box1"><div class="bt"><div></div></div><div class="i1"><div class="i2"><div class="i3"><div class="box1-content">
<h3>Login</h3>
<table id="numo_account_login_component"><tr><td>
<?php
if($_POST['cmd'] == "login") {
	if(login($_POST['username'],$_POST['password'])) {
		if(isset($PARAMS['redirect'])) {
			//redirect back to custom page
			header("Location: ".$PARAMS['redirect']);

		} else {
			//redirect back to original page requested
			header("Location: ".$_SERVER["REQUEST_URI"]);
		}

		//stop processing the file
		return;
	} else {
		print "<p>".NUMO_SYNTAX_ACCOUNT_LOGIN_INFORMATION_NOT_VALID."</p>";
	}
}
?>

<form method="post">
	<ul>
		<li><label for="username"><?=NUMO_SYNTAX_ACCOUNT_LOGIN_USERNAME_FIELD_LABEL?>:</label><input type="text" id="username" name="username" value="<?=($HTTP_HOST == 'webs.my-demos.com' ? 'admin' : '')?>" /></li>
		<li><label for="password"><?=NUMO_SYNTAX_ACCOUNT_LOGIN_PASSWORD_FIELD_LABEL?>:</label><input type="password" id="password" name="password" value="<?=($HTTP_HOST == 'webs.my-demos.com' ? 'password' : '')?>" /></li>
		<li><label for="submit_account_login_cmd"></label><input type="submit" id="submit_account_login_cmd" name="nocmd" value="<?=NUMO_SYNTAX_ACCOUNT_LOGIN_BUTTON_LABEL?>" /></li>
	</ul>
	<input type="hidden" name="cmd" value="login" />
</form>
<a href="forgot_password.htm">Forgot your password?</a>
</td></tr></table>
</div></div></div></div><div class="bb"><div></div></div></div>
</div>


