<?php
if(!isset($PARAMS['id']) || $PARAMS['combo'] == '0') {
	return;
}

//check to see if the function has already been declared by another instance of the component
if(!function_exists('generate_numo_checkbox_options')) {
function generate_numo_checkbox_options($options, $fieldId, $currentValue = "", $type = "radio", $sep = "\r\n") {
	$nameArrayOption = "";
	$returnStr   = "";

	if($type == "checkbox") {
		$nameArrayOption = "[]";
	}

	if(is_array($options)) {
		foreach ($options as $key => $value) {
			if((is_array($currentValue) && in_array(html_entity_decode($key), $currentValue)) || (is_string($currentValue) && $currentValue == $key)) {
				$returnStr .= '<li><input type="'.$type.'" name="slot_'.$fieldId.$nameArrayOption.'" id="slot_'.$fieldId.'__'.$key.'" value="'.$key.'" checked="checked" /><label for="slot_'.$fieldId.'__'.$key.'">'.$value.'</label></li>';
			} else {
				$returnStr .= '<li><input type="'.$type.'" name="slot_'.$fieldId.$nameArrayOption.'" id="slot_'.$fieldId.'__'.$key.'" value="'.$key.'" /><label for="slot_'.$fieldId.'__'.$key.'">'.$value.'</label></li>';
			}
		}
	} else if(is_array($currentValue)) {
		$listOptions = explode($sep, trim($options));

		foreach ($listOptions as $key => $value) {
			if(in_array(html_entity_decode($value), $currentValue)) {
				$returnStr .= '<li><input type="'.$type.'" name="slot_'.$fieldId.$nameArrayOption.'" id="slot_'.$fieldId.'__'.$key.'" value="'.$value.'" checked="checked" /><label for="slot_'.$fieldId.'__'.$key.'">'.$value.'</label></li>';
			} else {
				$returnStr .= '<li><input type="'.$type.'" name="slot_'.$fieldId.$nameArrayOption.'" id="slot_'.$fieldId.'__'.$key.'" value="'.$value.'" /><label for="slot_'.$fieldId.'__'.$key.'">'.$value.'</label></li>';
			}
		}
	} else {
		$listOptions = explode($sep, trim($options));

		foreach ($listOptions as $key => $value) {
			if($currentValue == $value) {
				$returnStr .= '<li><input type="'.$type.'" name="slot_'.$fieldId.$nameArrayOption.'" id="slot_'.$fieldId.'__'.$key.'" value="'.$value.'" checked="checked" /><label for="slot_'.$fieldId.'__'.$key.'">'.$value.'</label></li>';
			} else {
				$returnStr .= '<li><input type="'.$type.'" name="slot_'.$fieldId.$nameArrayOption.'" id="slot_'.$fieldId.'__'.$key.'" value="'.$value.'" /><label for="slot_'.$fieldId.'__'.$key.'">'.$value.'</label></li>';
			}
		}
	}

	return $returnStr;
}
}

$numoFormErrors = array();

//***************************************************************************
// PROCESS form information even if a session has expired
//***************************************************************************
if($_POST['cmd'] == "submit_numo_form" && $_POST['guestbook_id'] == $PARAMS['id']) {
	$formDetails = "";

	if ($numo->extensions['captcha']) {
		if (REMOTE_SERVICE === true) { 
		  require_once(ABSOLUTE_ROOT_PATH."dashboard/extensions/captcha/components/util.php");
		
		} else {
		  require_once(ABSOLUTE_ROOT_PATH."numo/extensions/captcha/components/util.php");
		}

	}

	/****************************/
	/* HANDLE TEXT FIELD INPUTS */
	/****************************/
	foreach($_POST['guestbook_fields'] as $key => $slotId) {
		$value = $_POST['slot_'.$slotId];

		$sql = "SELECT * FROM `guestbook_fields` WHERE `type_id`='".$PARAMS['id']."' AND `slot`='".$slotId."'";
		$result = $dbObj->query($sql);
    $row = mysql_fetch_array($result);

		//if no value set, check to see if one is required
		if((is_string($value) && $value == "") || !isset($_POST['slot_'.$slotId])) {
			//$sql = "SELECT `id` FROM `guestbook_fields` WHERE `type_id`='".$PARAMS['id']."' AND `required`=1 AND `slot`='".$slotId."'";
			//print $sql."<br>";
			//$result = $dbObj->query($sql);

			//value required
			if($row['required'] == "1") {
				$numoFormErrors[$slotId] = "<div>".NUMO_SYNTAX_GUESTBOOK_INPUT_REQUIRED_LABEL."</div>";
			}
		} else if ($row['input_type'] == "captcha" && function_exists("checkCaptchaCode") && !checkCaptchaCode(trim($_POST['slot_'.$row['slot']]))) {
			//print "xxx";
			$numoFormErrors[$slotId] = "<div>Incorrect Security Code.</div><div style='clear: both;'></div>";
		} else if ($row['input_type'] != "captcha") {
			if(is_array($value)) {
				$newValue = "";
				foreach($value as $key => $label) {
					$newValue .= ", ".$label;
				}

				$value = $newValue;

				$formDetails .= "<tr><td valign='top' style='border: 1px solid #666;'><b>".$_POST['name__slot_'.$slotId].":</b></td><td valign='top' style='border: 1px solid #666;'>".substr($value,2)."</td></tr>";
			} else {
				$formDetails .= "<tr><td valign='top' style='border: 1px solid #666;'><b>".$_POST['name__slot_'.$slotId].":</b></td><td valign='top' style='border: 1px solid #666;'>".$value."</td></tr>";
			}
		}
	}

	if(count($numoFormErrors) == 0) {
		if(!class_exists('NumoGuestbook')) {
		//if (REMOTE_SERVICE === true) { 
		//  require_once(ABSOLUTE_ROOT_PATH."dashboard/extensions/captcha/components/util.php");
		
		//	} else {
				require(ABSOLUTE_ROOT_PATH."numo/modules/".$matches[1]."/classes/Guestbook.php");
		//	}
		}

		$sql = "SELECT * FROM `guestbook_types` WHERE `id`='".$PARAMS['id']."'";
		//print $sql."<br>";
		//exit;
		$result = $dbObj->query($sql);
		


		if($row = mysql_fetch_array($result)) {
			$guestbookObj = new NumoGuestbook();
		    if ($row['require_review'] != "") {
				$_POST['pending'] = $row['require_review'];
				//print " yup ". $row['pending'];
			}
		    $guestbookObj->create($_POST);
			
			if($row['send_notification'] == "1") {
				$to = $row['notification_email'];

				//create a boundary string. It must be unique so we use the MD5 algorithm to generate a random hash
				//print "a";
				$random_hash = md5(date('r', time()));
                //print "b";
                //exit;
				$headers = "From: ".NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS;
				$headers .= "\r\n".'Content-Type: multipart/alternative; boundary="--'.$random_hash.'"';

				/***************************************/
				/*   DEFINE THE BODY OF THE MESSAGE    */
				/***************************************/
				$message .= "\n".'----'.$random_hash."\n";
				$message .= 'Content-Type: text/html; charset="iso-8859-1"'."\n";
				$message .= 'Content-Disposition: inline'."\n";
				$message .= 'Content-Transfer-Encoding: 7bit'."\n\n";

				if($row['include_form_info'] == "1") {
					$message .= nl2br(str_replace('[Response Details]','<table border="1" cellpadding="3" style="border: 1px solid #666; border-collapse:collapse;">'.$formDetails.'</table>',NUMO_SYNTAX_GUESTBOOK_NOTIFICATION_MESSAGE_LABEL));
				} else {
					$message .= nl2br(str_replace('[Response Details]','',NUMO_SYNTAX_GUESTBOOK_NOTIFICATION_MESSAGE_LABEL));
				}

				$message .= "\n".'----'.$random_hash.'--';
                //print $message;
                //print $header;
                //print $to;
                //print NUMO_SYNTAX_GUESTBOOK_NOTIFICATION_SUBJECT_LABEL;
                //exit;
               // exit;
				//send the email
				mail($to,NUMO_SYNTAX_GUESTBOOK_NOTIFICATION_SUBJECT_LABEL,$message,$headers);
			   // print "d";
			    //exit;
			}

			if($row['confirmation_type'] == "2") {
				//redirect to location set
				header('Location: '.$row['confirmation_value']);
				//exit;
			} else {
				echo "<p>".nl2br($row['confirmation_value'])."</p>";
               // return;
				if($PARAMS['combo'] == 1) {
					echo '[NUMO.GUESTBOOK: DISPLAY(id='.$PARAMS['id'].'&entry_saved=1)]';
				}
			}
		}

		return;
	}
}

//***************************************************************************
// check to see if visitor is logged in already
//***************************************************************************
$sql = "SELECT `default_group`,`show_registration` FROM `guestbook_types` WHERE `id`='".$PARAMS['id']."' AND `restrict_access`=1";
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
	//if not logged in show login options
	if(!isset($_SESSION['account_id'])) {
		//show both registration and login components
		if($row['show_registration'] == 1 && $_GET['numo'] != "registered") {
			print '<table cellpadding="10" cellspacing="10"><tr><td valign="top" style="border: 1px dotted #ddd;">[NUMO.ACCOUNTS: LOGIN BOX]</td><td valign="top" style="border: 1px dotted #ddd;">[NUMO.GUESTBOOK: REGISTER(id='.$row['default_group'].')]</td></tr></table>';

			//don't display the component
			return;
		//just show login component
		} else {
			print "[NUMO.ACCOUNTS: LOGIN BOX]";

			//don't display the component
			return;
		}
	//check if user account is pending their activation
	} else if($_SESSION['activated'] == 0) {
		echo '<h2>'.NUMO_SYNTAX_ACCOUNT_PENDING_ACTIVATION_ALERT_TITLE.'</h2>'.NUMO_SYNTAX_ACCOUNT_PENDING_ACTIVATION_ALERT;
		return;

	//check if user account is pending administrative review
	} else if($_SESSION['pending'] == 1) {
		echo '<h2>'.NUMO_SYNTAX_ACCOUNT_PENDING_REVIEW_TITLE.'</h2>'.NUMO_SYNTAX_ACCOUNT_PENDING_REVIEW;
		return;
	}

}

//***************************************************************************
?>
<link rel="stylesheet" type="text/css" href="//<?php print NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH; ?>modules/guestbook/components/styles/submit_form.css" />
<form method="post" class="numo_form_display" enctype="multipart/form-data">
	<ul>
		<?php
		//load field information
		$sql = "SELECT `name`,`slot`,`input_type`,`input_options` FROM `guestbook_fields` WHERE type_id='".$PARAMS['id']."' ORDER BY `position`,`name`";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($field = mysql_fetch_array($results)) {
			if($field['input_type'] == "heading") {
				print '<li><h3 style="margin: 0px 0px 2px 0px;">'.$field['input_options'].'</h3></li>';

			} else if($field['input_type'] == "label") {
				print '<li><p style="margin: 0px 0px 2px 0px;">'.nl2br($field['input_options']).'</p></li>';

			} else if($field['input_type'] == "checkbox") {
				print '<li'.(array_key_exists($field['slot'],$numoFormErrors) ? ' class="numo_form_error"' : '').'><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><ul class="numo_checkbox_inputs">'.generate_numo_checkbox_options($field['input_options'],$field['slot'],$_POST['slot_'.$field['slot']],"checkbox").'</ul> '.$numoFormErrors[$field['slot']].'
							<input type="hidden" name="guestbook_fields[]" value="'.$field['slot'].'" /><input type="hidden" name="name__slot_'.$field['slot'].'" value="'.$field['name'].'" /></li>';

			} else if($field['input_type'] == "radio") {
				print '<li'.(array_key_exists($field['slot'],$numoFormErrors) ? ' class="numo_form_error"' : '').'><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><ul class="numo_checkbox_inputs">'.generate_numo_checkbox_options($field['input_options'],$field['slot'],$_POST['slot_'.$field['slot']],"radio").'</ul> '.$numoFormErrors[$field['slot']].'
							<input type="hidden" name="guestbook_fields[]" value="'.$field['slot'].'" /><input type="hidden" name="name__slot_'.$field['slot'].'" value="'.$field['name'].'" /></li>';
			} else if($field['input_type'] == "email") {
				
				  print '<li'.(array_key_exists($field['slot'],$numoFormErrors) ? ' class="numo_form_error"' : '').'><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><input class="numo_text_input" type="email" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="'.$_POST['slot_'.$field['slot']].'" /> '.$numoFormErrors[$field['slot']].'<input type="hidden" name="guestbook_fields[]" value="'.$field['slot'].'" /><input type="hidden" name="name__slot_'.$field['slot'].'" value="'.$field['name'].'" /></li>';
				
			} else if($field['input_type'] == "textarea") {
				print '<li'.(array_key_exists($field['slot'],$numoFormErrors) ? ' class="numo_form_error"' : '').'><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><textarea id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">'.$_POST['slot_'.$field['slot']].'</textarea> '.$numoFormErrors[$field['slot']].'<input type="hidden" name="guestbook_fields[]" value="'.$field['slot'].'" /><input type="hidden" name="name__slot_'.$field['slot'].'" value="'.$field['name'].'" /></li>';

			} else if($field['input_type'] == "dropdown list") {
				print '<li'.(array_key_exists($field['slot'],$numoFormErrors) ? ' class="numo_form_error"' : '').'>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<select id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">'.generate_list_options($field['input_options'],$_POST['slot_'.$field['slot']]).'</select> '.$numoFormErrors[$field['slot']].'
							<input type="hidden" name="guestbook_fields[]" value="'.$field['slot'].'" /><input type="hidden" name="name__slot_'.$field['slot'].'" value="'.$field['name'].'" /></li>';

			} else if ($field['input_type'] == "multiple select") {
				print '<li'.(array_key_exists($field['slot'],$numoFormErrors) ? ' class="numo_form_error"' : '').'>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<select multiple="multiple" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'[]">'.generate_list_options($field['input_options'],$_POST['slot_'.$field['slot']]).'</select> '.$numoFormErrors[$field['slot']].'
							<input type="hidden" name="guestbook_fields[]" value="'.$field['slot'].'" /><input type="hidden" name="name__slot_'.$field['slot'].'" value="'.$field['name'].'" /></li>';
			} else if($field['input_type'] == "captcha") {
				if (FACEBOOK !== true) {
				print '<li'.(array_key_exists($field['slot'],$numoFormErrors) ? ' class="numo_form_error"' : '').'>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								[NUMO*CAPTCHA: RENDER IMAGE] <input class="numo_text_input_short" type="text"  id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" /> '.$numoFormErrors[$field['slot']].'
							<input type="hidden" name="guestbook_fields[]" value="'.$field['slot'].'" /><input type="hidden" name="name__slot_'.$field['slot'].'" value="'.$field['name'].'" /></li>';
				}
			} else {
				if ($field['slot'] == "1" && FACEBOOK === true) {
					global $user_profile;
				  print '<li><label for="slot_1">'.$user_profile['name'].'</label></li>';
					
				} else {
				  print '<li'.(array_key_exists($field['slot'],$numoFormErrors) ? ' class="numo_form_error"' : '').'><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><input class="numo_text_input" type="text" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="'.$_POST['slot_'.$field['slot']].'" /> '.$numoFormErrors[$field['slot']].'<input type="hidden" name="guestbook_fields[]" value="'.$field['slot'].'" /><input type="hidden" name="name__slot_'.$field['slot'].'" value="'.$field['name'].'" /></li>';
				}
			}
		}
		?>
		<li><input type="submit" name="nocmd" value="<?=NUMO_SYNTAX_GUESTBOOK_SUBMIT_LABEL?>" /></li>
	</ul>
	<input type="hidden" name="cmd" value="submit_numo_form" />
	<input type="hidden" name="guestbook_id" value="<?=$PARAMS['id']?>" />
	<input type="hidden" name="ip_address" value="<?=$_SERVER['REMOTE_ADDR']?>" />
	<input type="hidden" name="account_id" value="<?=(isset($_SESSION['account_id']) ? $_SESSION['account_id'] : '0')?>" />
</form>