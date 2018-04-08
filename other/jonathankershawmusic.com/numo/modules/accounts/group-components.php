<h2>Instructions to place a component into your web page:</h2>
<ol>
	<li>Open up the page where you wish to place your component in your HTML editor</li>
	<li>Copy the code below for the component you wish to use</li>
	<li>Place your cursor at the location you wish to have the component</li>
	<li>Paste the code for the component into your page</li>
</ol>
<p>Important Note: Components will only appear when viewed on your LIVE server.  When viewing pages with components in them on your local computer the component code text will appear.</p>
<div>
<?php
//load account information
$sql = "SELECT name, allow_registration FROM `types` WHERE id='".$_GET['id']."' AND site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
?>
<h3>'<?=$row['name']?>' Group Components</h3>
<?php
if($row['allow_registration'] == "1") {
?>
<?php if (REMOTE_SERVICE === true) { ?>
<h4>Registration Box</h4>
<textarea cols="50" rows="1" onfocus='this.select()'><numo module="accounts" component="registration_box" params="id=<?=$_GET['id']?>"></numo></textarea>
<?php } else { ?>
<h4>Registration Box</h4>
<textarea cols="50" rows="1" onfocus='this.select()'>[NUMO.ACCOUNTS: REGISTRATION BOX(id=<?=$_GET['id']?>)]</textarea>
<?php } ?>
<?php
} else {
?>
<p>Registration has not enabled for this account group.</p>
<?php
}
}
?>
<h3>General Components</h3>
<?php if (REMOTE_SERVICE === true) { ?>
<h4>Login Box</h4>
<textarea cols="50" rows="1" onfocus='this.select()'><numo module="accounts" component="login_box"></numo></textarea>

<h4>Forgot Password</h4>
<textarea cols="50" rows="1" onfocus='this.select()'><numo module="accounts" component="forgot_password"></numo></textarea>

<h4>Update Account Details</h4>
<textarea cols="50" rows="1" onfocus='this.select()'><numo module="accounts" component="update_account_details"></numo></textarea>

<h4>Update Account Password</h4>
<textarea cols="50" rows="1" onfocus='this.select()'><numo module="accounts" component="update_password"></numo></textarea>

<h4>Logout Link</h4>
<textarea cols="50" rows="1" onfocus='this.select()'><numo module="accounts" component="links"></numo></textarea>
</div>

<?php } else { ?>
<h4>Login Box</h4>
<textarea cols="50" rows="1" onfocus='this.select()'>[NUMO.ACCOUNTS: LOGIN BOX]</textarea>

<h4>Forgot Password</h4>
<textarea cols="50" rows="1" onfocus='this.select()'>[NUMO.ACCOUNTS: FORGOT PASSWORD]</textarea>

<h4>Update Account Details</h4>
<textarea cols="50" rows="1" onfocus='this.select()'>[NUMO.ACCOUNTS: UPDATE ACCOUNT DETAILS]</textarea>

<h4>Update Account Password</h4>
<textarea cols="50" rows="1" onfocus='this.select()'>[NUMO.ACCOUNTS: UPDATE PASSWORD]</textarea>

<h4>Logout Link</h4>
<textarea cols="50" rows="1" onfocus='this.select()'>[NUMO.ACCOUNTS: LINKS]</textarea>
</div>
<?php } ?>