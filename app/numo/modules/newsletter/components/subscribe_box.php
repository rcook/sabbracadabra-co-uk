<link rel="stylesheet" type="text/css" href="<?php print NUMO_FOLDER_PATH; ?>modules/newsletter/components/styles/subscribe_box.css" />
<?php if ($PARAMS['title'] != "") {?>
<h3><?php echo $PARAMS['title']; ?></h3>
<?php
}

if(isset($_SESSION['account_id'])) {
?>
<table id="numo_newsletter_subscription_component"><tr><td>
<?php
	if($_POST['cmd'] == "update") {
		$sql = "DELETE FROM newsletter_subscribers WHERE account_id='".$_SESSION['account_id']."'";
		$dbObj->query($sql);

		if(isset($_POST['lists'])) {
			foreach($_POST['lists'] as $key => $value) {
				$sql = "INSERT INTO newsletter_subscribers (account_id,subscription_list_id) VALUES ('".$_SESSION['account_id']."','".$value."')";
				//print $sql;
				$dbObj->query($sql);
				//return;
			}
		}

		print NUMO_SYNTAX_NEWSLETTER_PROCESSED_SUBSCRIPTION_REQUEST;
	}

		$sql = "SELECT id, name FROM newsletter_subscription_lists WHERE site_id='".NUMO_SITE_ID."' AND (availability=0 OR availability=1) ORDER BY name";
		$results = $dbObj->query($sql);

		if(mysql_num_rows($results) == 0) {
		?>
			<p>No subscription lists available</p></td></tr></table>
		<?php
			return;
		}
		?>
		<h4><?=NUMO_SYNTAX_NEWSLETTER_SUBSCRIPTION_LISTS_LABEL?></h4>
		<form method="post">
		<ul>
		<?php
		while($row = mysql_fetch_array($results)) {
			$sql = "SELECT id FROM newsletter_subscribers WHERE subscription_list_id='".$row['id']."' AND account_id='".$_SESSION['account_id']."'";
			//print $sql;
			$result = $dbObj->query($sql);

			if($subscriber = mysql_fetch_array($result)) {
		?>
			<li><input type="checkbox" name="lists[]" checked="checked" id="numo_newsletter_list_<?=$row['id']?>" value="<?=$row['id']?>" /><label class="checkbox_label" for="numo_newsletter_list_<?=$row['id']?>"><?=$row['name']?></label></li>
		<?php
			}	else {
		?>
			<li><input type="checkbox" name="lists[]" id="numo_newsletter_list_<?=$row['id']?>" value="<?=$row['id']?>" /><label class="checkbox_label" for="numo_newsletter_list_<?=$row['id']?>"><?=$row['name']?></label></li>
		<?php
			}

			mysql_free_result($result);
		}
		?>
		</ul>
		<div style="clear:both;"><br /></div>
		<input type="submit" name="nocmd" value="<?=NUMO_SYNTAX_NEWSLETTER_ACCOUNT_SUBSCRIBE_BUTTON?>" />
		<input type="hidden" name="cmd" value="update" />
	</form>
	</td></tr></table>
<?php
} else {
?>
<script>
function numoNewsletter_unsubscribe(frm) {
	var emailValue = document.getElementById("numo_newsletter_subscriber_email").value;
	var filter=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i

	if(filter.test(emailValue)) {
		frm.cmd.value = "unsubscribe";
		frm.submit();
	} else {
		alert("<?=NUMO_SYNTAX_NEWSLETTER_INVALID_EMAIL_PROVIDED_FOR_UNSUBSCRIBE?>"); 
	}
}
</script>
<table id="numo_newsletter_subscription_component"><tr><td>
<?php
	$subscriberAccountGroup = 0;

	$sql = "SELECT default_account_group FROM newsletter_settings WHERE site_id='".NUMO_SITE_ID."'";
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		$subscriberAccountGroup = $row['default_account_group'];
	}

	if($_POST['cmd'] == "unsubscribe") {
		$sql = "DELETE FROM newsletter_subscribers WHERE account_id=(SELECT a.id FROM accounts a, `types` t WHERE a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."' AND slot_3='".$_POST['email']."')";
		//print $sql;
		$dbObj->query($sql);

		print NUMO_SYNTAX_NEWSLETTER_PROCESSED_UNSUBSCRIBE_REQUEST."</td></tr></table>";
		return;
	} else if($_POST['cmd'] == "subscribe") {
		if($_POST['name'] == "") {
			print "<p class='error'>".NUMO_SYNTAX_NEWSLETTER_SUBCRIBER_NAME_REQUIRED."</p>";
		} else if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $_POST['email'])) {
			print "<p class='error'>".NUMO_SYNTAX_NEWSLETTER_VALID_SUBCRIBER_EMAIL_REQUIRED."</p>";
		} else if(is_array($_POST['lists'])) {
			$sql = "SELECT a.id FROM accounts a, `types` t WHERE a.type_id=t.id AND t.site_id=".NUMO_SITE_ID." AND a.slot_3='".$_POST['email']."'";
			//print $sql."<br>";
			$result = $dbObj->query($sql);

			//check if email address already in use
			if($row = mysql_fetch_array($result)) {
				foreach($_POST['lists'] as $key => $value) {
					$sql = "SELECT id FROM newsletter_subscribers WHERE account_id='".$row['id']."' AND subscription_list_id='".$value."'";
					$subscriptionCheck = $dbObj->query($sql);

					if($subscription = mysql_fetch_array($subscriptionCheck)) {
						//already subscribed... do nothing.
					} else {
						$sql = "INSERT INTO newsletter_subscribers (account_id,subscription_list_id) VALUES ('".$row['id']."','".$value."')";
						//print $sql."<bR>";
						$dbObj->query($sql);
					}
				}
			} else {
				if($_POST['group_id'] == "" || $_POST['group_id'] == "0") {
					print "<p class='error'>Subscription request could not be processed.  A default account group must be selected before a new subscribers can be created.</p></td></tr></table>";
					return;
				}
			
				// find out if the account group is pending activation
				$query = "SELECT * FROM types WHERE id='{$_POST[group_id]}'";
				$result = $dbObj->query($query);
				$typeInfo = mysql_fetch_array($result);
				$activationStatus = $typeInfo['require_activation'] == 0 ? 1 : 0;
				
				//create account
				$sql = "INSERT INTO accounts (type_id,pending,activated,slot_1,slot_2,slot_3,slot_4) VALUES ('".$_POST['group_id']."',3,'{$activationStatus}','','".crypt(time())."','".$_POST['email']."','".$_POST['name']."')";
				$dbObj->query($sql);

				//lookup account id
				$sql = "SELECT LAST_INSERT_ID() as 'account_id'";
				$result = $dbObj->query($sql);

				$row = mysql_fetch_array($result);
				
				if ($activationStatus == 0 && $row['account_id'] != "") {
					include_once("numo/modules/accounts/classes/Account.php");
					$accountObj = new Account($row['account_id']);
					$accountObj->sendAuthorizationEmail();
/*
					$requestId = $row['account_id'].md5(time());

					$sql = "INSERT INTO pending_requests (id, site_id, account_id, module, component) VALUES ('".$requestId."','".NUMO_SITE_ID."','".$row['account_id']."','accounts','activate')";
					//print $sql."<br>";
					$dbObj->query($sql);

					$activationUrl = "http://".NUMO_SERVER_ADDRESS.str_replace('/numo/','/',NUMO_FOLDER_PATH)."process.numo?id=".$requestId;
				
			
		
					//send activation email message
					$headers  = 'From: '.NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS."\r\n";
					$headers .= 'Content-type: text/html; charset=iso-8859-1';
		
		
					$to = $_POST['email'];
					$subject = NUMO_SYNTAX_ACCOUNT_WELCOME_EMAIL_SUBJECT;
					$message = NUMO_SYNTAX_ACCOUNT_WELCOME_EMAIL;
		
					// clense the email
					$message = str_replace("Below are your login details.\n", "", $message);
					$message = str_replace("Username: [Username]", "", $message);
					$message = str_replace("Password: [Password]", "", $message);
					$message = nl2br($message);
					$message = str_replace("<br><br><br>", "", $message);
					$message = str_replace("<br/><br/><br/>", "", $message);
					
		
					//replace the activation link tag with an actual link
					$message = str_replace("[activation link]", "<a href='".$activationUrl."'>".$activationUrl."</a>", $message);
			
					mail($to, $subject, $message, $headers);
					//print "sent email to ".$to;
*/
				}
		


				if($row['account_id'] != "") {
					if(isset($_POST['lists'])) {
						//add account to subscription lists requested
						foreach($_POST['lists'] as $key => $value) {
							$sql = "INSERT INTO newsletter_subscribers (account_id,subscription_list_id) VALUES ('".$row['account_id']."','".$value."')";
							//print $sql."<bR>";
							$dbObj->query($sql);
						}
					}
				}
			}
			print NUMO_SYNTAX_NEWSLETTER_PROCESSED_SUBSCRIPTION_REQUEST."</td></tr></table>";
			return;
		}
	}

	$sql = "SELECT id, name FROM newsletter_subscription_lists WHERE site_id='".NUMO_SITE_ID."' AND availability=0 ORDER BY name";
	$results = $dbObj->query($sql);

	if(mysql_num_rows($results) == 0) {
	?>
		<p>No subscription lists available</p></td></tr></table>
	<?php
		return;
	}
?>
	<form method="post">
	<ul class="numo_newsletter_subscription_component">
		<li><label for="numo_newsletter_subscriber_name"><?=NUMO_SYNTAX_NEWSLETTER_NAME_LABEL?>:</label><input type="text" name="name" id="numo_newsletter_subscriber_name" value="<?=$_POST['name']?>" /></li>
		<li><label for="numo_newsletter_subscriber_email"><?=NUMO_SYNTAX_NEWSLETTER_EMAIL_LABEL?>:</label><input type="text" name="email" id="numo_newsletter_subscriber_email" value="<?=$_POST['email']?>" /></li>
		<li><h3><?=NUMO_SYNTAX_NEWSLETTER_SUBSCRIPTION_LISTS_LABEL?></h3></li>
		<ul>
		<?php
		while($row = mysql_fetch_array($results)) {
		?>
			<li><input type="checkbox" name="lists[]" id="numo_newsletter_list_<?=$row['id']?>" value="<?=$row['id']?>" /><label class="checkbox_label" for="numo_newsletter_list_<?=$row['id']?>"><?=$row['name']?></label></li>
		<?php
		}
		?>
		</ul>
		<li>&nbsp;</li>
		<li><input type="submit" name="nocmd" value="<?=NUMO_SYNTAX_NEWSLETTER_VISITOR_SUBSCRIBE_BUTTON?>" /><input type="button" name="nocmd" value="<?=NUMO_SYNTAX_NEWSLETTER_VISITOR_UNSUBSCRIBE_BUTTON?>" onclick="numoNewsletter_unsubscribe(this.form)" /></li>
	</ul>
	<input type="hidden" name="cmd" value="subscribe" />
	<input type="hidden" name="group_id" value="<?=$subscriberAccountGroup?>" />
</form>
</td></tr></table>
<?php
}
?>