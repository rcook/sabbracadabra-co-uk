<?php
function initialize_section($matches) {
	global $sectionValues;

	return '<!'.'-- #BeginSection "'.$matches[1].'" -->'.html_entity_decode(stripslashes($sectionValues[$matches[1]])).'<!'.'-- #EndSection -->';
}

function setupDisplayStyles($matches) {
	//global $displayStyles;
	global $layoutDisplay;

	$pattern = '/class=[\'"]'.$matches[1].'[\'"]/si';
	$replacement = 'style="'.$matches[2].'"';
	$layoutDisplay = preg_replace($pattern, $replacement, $layoutDisplay);

	//$displayStyles .= ".".$matches[1]." p, .".$matches[1]." h1, .".$matches[1]." h2, .".$matches[1]." h3, .".$matches[1]." div {".$matches[2].":".$matches[3]."}\n";
}

?>
<style>
form.send_form ul {margin: 5px 0px 5px 20px; padding:0; list-style:none;}
form.send_form ul li {margin: 5px 0px; padding:0;}
form.send_form ul li label {margin: 0px 5px; padding:0;}
</style>
<h2>Send Newsletter Message</h2>
<?php
$newsletterInfo = array();

if(isset($_GET['id'])) {
	$newsletterId = $_GET['id'];
	$sql = "SELECT * FROM newsletter_messages WHERE id='".$newsletterId."' AND site_id='".NUMO_SITE_ID."'";
	$result = $dbObj->query($sql);

	if($newsletterInfo = mysql_fetch_array($result)) {
		//do nothing. we just wanted to detect if a valid ID was passed and get the newsletter information for later
	} else {
		//not able to find a newsletter with the ID passed for the website
		print "Invalid newsletter id provided.  Please try again.";
		exit();
	}
} else {
	print "Invalid newsletter id provided.  Please try again.";
	exit();
}

if($_POST['cmd'] == "send") {
	/*************************************************/
	/*      GENERATE LIST OF EMAILS TO SEND TO	 		 */
	/*************************************************/
	$sendToList = array();

	$subscriptionWhere = "";
	$accountWhere = "";

	//loop thru post to see what lists the message should be sent to
	foreach($_POST as $key => $value) {
		//subscription list
		if(substr($key,0,17) == "subscription_list") {
			$subscriptionWhere .= "s.subscription_list_id='".$value."' OR ";
		//account group
		} elseif(substr($key,0,14) == "account_groups") {
			$accountWhere .= "type_id='".$value."' OR ";
		}
	}

	//if subscription list(S) requested in send get email addresses for subscribers
	if($subscriptionWhere != "") {
		$subscriptionWhere = substr($subscriptionWhere,0,-4);

		//get email/name for subscribers
		$sql = "SELECT DISTINCT a.slot_4, a.slot_3 FROM accounts a, newsletter_subscribers s WHERE (".$subscriptionWhere.") AND a.activated=1 AND s.account_id=a.id ORDER BY a.slot_4";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($row = mysql_fetch_array($results)) {
			$sendToList[$row['slot_3']] = $row['slot_4'];
		}
	}

	//if account group(S) requested in send get email addresses for accounts
	if($accountWhere != "") {
		$accountWhere = substr($accountWhere,0,-4);

		//get email/name for accounts
		$sql = "SELECT DISTINCT slot_4, slot_3 FROM accounts WHERE ".$accountWhere." ORDER BY slot_4";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($row = mysql_fetch_array($results)) {
			$sendToList[$row['slot_3']] = $row['slot_4'];
		}
	}

	/*************************************************/
	/*      GENERATE NEWSLETTER MESSAGE TO SEND	 		 */
	/*************************************************/
	$layout = "modules/newsletter/layouts/".$newsletterInfo['layout'];
	$layoutSections = array();

	//get newsletter message data ready to be placed
	$newsletterMessage = str_replace("&", "%26", $newsletterInfo['message']);
	$newsletterMessage = str_replace("[NUMO|END]", "&", $newsletterMessage);

	//parse the message string as if it were a query string (i.e. $_GET)
	parse_str($newsletterMessage, $sectionValues);

	//read in the layout file
	$layoutDisplay = file_get_contents($layout);

	//make a copy of the layout to be used to setup styles
	$styleCode = $layoutDisplay;

	//get css class declarations for color and set for P, DIV, H1, H2, H3 to overwrite page default styles
	$pattern = '/\.(.*?)\s{(.*?)}/i';
	$displayStyles = "";
	preg_replace_callback($pattern, "setupDisplayStyles", $styleCode);

	//remove style tag and its contents from display
	$pattern = '/<style>.*?<\/style>/si';
	$replacement = '';
	$layoutDisplay = preg_replace($pattern, $replacement, $layoutDisplay);

	//place content from the MESSAGE string into the correct sections
	$pattern = '/<!'.'-- #BeginSection "(.*?)" -->(.*?)<!'.'-- #EndSection -->/si';
	$layoutDisplay = preg_replace_callback($pattern, 'initialize_section', $layoutDisplay);

	/*************************************************/
	/*            SEND NEWSLETTER MESSAGE         	 */
	/*************************************************/
	$mailObj = new MarbleMail();

	if($mailObj->connection) {
		foreach($sendToList as $email => $name) {
			// Send the email message out
			$mailObj->send($email." (".$name.")", NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS, $newsletterInfo['title'], str_replace("[name]", $name, $layoutDisplay));
		}
	} else {
		//Set mail headers
		$headers  = 'From: '.NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS."\r\n";
		$headers .= 'Content-type: text/html; charset=iso-8859-1';

		foreach($sendToList as $email => $name) {
			// Send the email message out
			mail($name." <".$email.">", $newsletterInfo['title'], str_replace("[name]", $name, $layoutDisplay), $headers);
		}
	}

	$mailObj->close();

	print "Thank-you.  Your message has been sent out to  ".count($sendToList)." subscribers.";

	$sql = "UPDATE newsletter_messages SET send_count=send_count+".count($sendToList)." WHERE id=".$_GET['id']." AND site_id=".NUMO_SITE_ID;
	//print $sql;
	$results = $dbObj->query($sql);

	exit;
}
?>
<p style="font-style: italic;">Please select the subscription list(s) and account group(s) you would like to send the '<?=$newsletterInfo['title']?>' newsletter message out to.</p>
<form method="post" class="send_form">
<h3>Subscription Lists</h3>
<ul>
<?php
$sql = "SELECT l.*, (SELECT COUNT(*) FROM newsletter_subscribers WHERE subscription_list_id=l.id) as 'subscribers' FROM newsletter_subscription_lists l WHERE site_id='".NUMO_SITE_ID."' ORDER BY name";
//print $sql;
$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
?>
<li><input type="checkbox" value="<?=$row['id']?>" name="subscription_list_<?=$row['id']?>" id="subscription_list_<?=$row['id']?>" /><label for="subscription_list_<?=$row['id']?>"><?=$row['name']?> (<?=$row['subscribers']?> subscribers)</label></li>
<?php
}
?>
</ul>

<h3>Account Groups</h3>
<ul>
<?php
$sql = "SELECT id, name, (SELECT COUNT(*) FROM accounts WHERE type_id=t.id) as 'members' FROM `types` t WHERE site_id=".NUMO_SITE_ID." ORDER BY name";
$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
?>
<li><input type="checkbox" value="<?=$row['id']?>" name="account_groups_<?=$row['id']?>" id="account_groups_<?=$row['id']?>" /><label for="account_groups_<?=$row['id']?>"><?=$row['name']?> (<?=$row['members']?> accounts)</label></li>
<?php
}
?>
</ul>
<input type="hidden" name="cmd" value="send" />
<input type="submit" name="nocmd" value="Send Newsletter" />
</form>