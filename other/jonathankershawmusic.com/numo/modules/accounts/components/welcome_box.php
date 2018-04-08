<?php

//***************************************************************************
// check to see if visitor is logged in already
//***************************************************************************
if(!isset($_SESSION['account_id'])) {
	//don't show component when the visitor is already logged into an account
	//print "logged in";
	return;
} 
//***************************************************************************
?>
<?php if ($PARAMS['title'] != "") { ?>
<div class='welcome-box'>
<h3><?php echo $PARAMS['title']; ?></h3>
<?php } ?> 
<p> You are logged in as [NUMO.ACCOUNTS: INFO(i=slot_4)]</p>
<a href="?cmd=exit">Logout</a>
</div>