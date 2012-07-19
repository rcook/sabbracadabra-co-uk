<style>
#numo_newsletter_subscription_component {border: 0px solid #666;}
#numo_newsletter_subscription_component form { margin: 0px; padding: 0px}
#numo_newsletter_subscription_component form ul {list-style-type: none; margin: 0px; padding: 0px}
#numo_newsletter_subscription_component form ul li {margin: 0px; padding: 0px; color: #f00; font-size: 13px;}
#numo_newsletter_subscription_component form ul li h3 {margin-top: 5px;}
#numo_newsletter_subscription_component form ul li label {width: 100px; display: inline-block; color: #444; font-weight: bold;}
#numo_newsletter_subscription_component form ul li label.checkbox_label {width: auto;}
#numo_newsletter_subscription_component p {color: #060; font-size: 12px; text-align: center; font-weight: bold; padding: 0px 0px 4px 0px; margin: 0px;}
#numo_newsletter_subscription_component p.error {color: #f00;font-weight: bold;}
</style>
<?php
if(isset($_SESSION['account_id'])) {
?>
<div class="whatsnew">
<div class="box1"><div class="bt"><div></div></div><div class="i1"><div class="i2"><div class="i3"><div class="box1-content">
<h3>Subscribe To Our Newsletter</h3>
<table id="numo_newsletter_subscription_component"><tr><td>
<?php
	if($_POST['cmd'] == "update") {
		$sql = "DELETE FROM newsletter_subscribers WHERE account_id=".$_SESSION['account_id'];
		$dbObj->query($sql);

		if(isset($_POST['lists'])) {
			foreach($_POST['lists'] as $key => $value) {
				$sql = "INSERT INTO newsletter_subscribers (account_id,subscription_list_id) VALUES (".$_SESSION['account_id'].",".$value.")";
				//print $sql;
				$dbObj->query($sql);
				//return;
			}
		}

		print NUMO_SYNTAX_NEWSLETTER_PROCESSED_SUBSCRIPTION_REQUEST;
	}

		$sql = "SELECT id, name FROM newsletter_subscription_lists WHERE site_id=".NUMO_SITE_ID." AND (availability=0 OR availability=1) ORDER BY name";
		$results = $dbObj->query($sql);

		if(mysql_num_rows($results) == 0) {
		?>
			<p>No subscription lists available</p></td></tr></table>

</div></div></div></div><div class="bb"><div></div></div></div>
</div>



<?php
                  return;
		}
		?>
		<h4><?=NUMO_SYNTAX_NEWSLETTER_SUBSCRIPTION_LISTS_LABEL?></h4>
		<form method="post">
		<ul>
		<?php
		while($row = mysql_fetch_array($results)) {
			$sql = "SELECT id FROM newsletter_subscribers WHERE subscription_list_id=".$row['id']." AND account_id=".$_SESSION['account_id'];
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
</div></div></div></div><div class="bb"><div></div></div></div>
</div>




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
<div class="whatsnew">
<div class="box1"><div class="bt"><div></div></div><div class="i1"><div class="i2"><div class="i3"><div class="box1-content">
<h3>Subscribe To Our Newsletter</h3>
<table id="numo_newsletter_subscription_component"><tr><td>
<?php
	$subscriberAccountGroup = 0;

	$sql = "SELECT default_account_group FROM newsletter_settings WHERE site_id=".NUMO_SITE_ID;
	$result = $dbObj->query($sql);

	if($row = mysql_fetch_array($result)) {
		$subscriberAccountGroup = $row['default_account_group'];
	}

	if($_POST['cmd'] == "unsubscribe") {
		$sql = "DELETE FROM newsletter_subscribers WHERE account_id=(SELECT a.id FROM accounts a, `types` t WHERE a.type_id=t.id AND t.site_id=".NUMO_SITE_ID." AND slot_3='".$_POST['email']."')";
		//print $sql;
		$dbObj->query($sql);

		print NUMO_SYNTAX_NEWSLETTER_PROCESSED_UNSUBSCRIBE_REQUEST."</td></tr></table>";
		return;
	} else if($_POST['cmd'] == "subscribe") {
		if($_POST['name'] == "") {
			print "<p class='error'>".NUMO_SYNTAX_NEWSLETTER_SUBCRIBER_NAME_REQUIRED."</p>";
		} else if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $_POST['email'])) {
			print "<p class='error'>".NUMO_SYNTAX_NEWSLETTER_VALID_SUBCRIBER_EMAIL_REQUIRED."</p>";
		} else if(is_array($_POST['lists'])) {
			$sql = "SELECT a.id FROM accounts a, types t WHERE a.type_id=t.id AND t.site_id=".NUMO_SITE_ID." AND a.slot_3='".$_POST['email']."'";
			//print $sql."<br>";
			$result = $dbObj->query($sql);

			//check if email address already in use
			if($row = mysql_fetch_array($result)) {
				foreach($_POST['lists'] as $key => $value) {
					$sql = "SELECT id FROM newsletter_subscribers WHERE account_id=".$row['id']." AND subscription_list_id=".$value;
					$subscriptionCheck = $dbObj->query($sql);

					if($subscription = mysql_fetch_array($subscriptionCheck)) {
						//already subscribed... do nothing.
					} else {
						$sql = "INSERT INTO newsletter_subscribers (account_id,subscription_list_id) VALUES (".$row['id'].",".$value.")";
						//print $sql."<bR>";
						$dbObj->query($sql);
					}
				}
			} else {
				if($_POST['group_id'] == "" || $_POST['group_id'] == "0") {
					print "<p class='error'>Subscription request could not be processed.  A default account group must be selected before a new subscribers can be created.</p></td></tr></table>";
					return;
				}

				//create account
				$sql = "INSERT INTO accounts (type_id,pending,slot_1,slot_2,slot_3,slot_4) VALUES (".$_POST['group_id'].",3,'','".crypt(time())."','".$_POST['email']."','".$_POST['name']."')";
				$dbObj->query($sql);

				//lookup account id
				$sql = "SELECT LAST_INSERT_ID() as 'account_id'";
				$result = $dbObj->query($sql);

				if($row = mysql_fetch_array($result)) {
					if(isset($_POST['lists'])) {
						//add account to subscription lists requested
						foreach($_POST['lists'] as $key => $value) {
							$sql = "INSERT INTO newsletter_subscribers (account_id,subscription_list_id) VALUES (".$row['account_id'].",".$value.")";
							//print $sql."<bR>";
							$dbObj->query($sql);
						}
					}
				}
			}
			print NUMO_SYNTAX_NEWSLETTER_PROCESSED_SUBSCRIPTION_REQUEST."</td></tr></table>";
?>
</div></div></div></div><div class="bb"><div></div></div></div>
</div>



<?
	return;
		}
	}

	$sql = "SELECT id, name FROM newsletter_subscription_lists WHERE site_id=".NUMO_SITE_ID." AND availability=0 ORDER BY name";
	$results = $dbObj->query($sql);

	if(mysql_num_rows($results) == 0) {
	?>
		<p>No subscription lists available</p></td></tr></table>
</div></div></div></div><div class="bb"><div></div></div></div>
</div>



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
</div></div></div></div><div class="bb"><div></div></div></div>
</div>




<?php
}
?>
