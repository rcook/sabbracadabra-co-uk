<style>
.error {color: #900; font-weight: bold;}

.ui-helper-hidden { display: none; }
.ui-helper-hidden-accessible { position: absolute; left: -99999999px; }
.ui-helper-reset { margin: 0; padding: 0; border: 0; outline: 0; line-height: 1.3; text-decoration: none; font-size: 100%; list-style: none; }
.ui-helper-clearfix:after { content: "."; display: block; height: 0; clear: both; visibility: hidden; }
.ui-helper-clearfix { display: inline-block; }
/* required comment for clearfix to work in Opera \*/
* html .ui-helper-clearfix { height:1%; }
.ui-helper-clearfix { display:block; }
/* end clearfix */

.ui-widget-content { border: 1px solid #ccc; background: #fff; color: #333333; }
.ui-widget-content a { color: #333333; }
.ui-widget-header { border: 0px solid #2A61B3; background: none; color: #ffffff; font-weight: bold; }
.ui-widget-header a { color: #ffffff; }

.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default { font-size: 8pt; border: 1px solid #cccccc; background: #eee; font-weight: bold; color: #3473D1; }
.ui-state-default a, .ui-state-default a:link, .ui-state-default a:visited { color: #3473D1; text-decoration: none; }
.ui-state-hover, .ui-widget-content .ui-state-hover, .ui-widget-header .ui-state-hover, .ui-state-focus, .ui-widget-content .ui-state-focus, .ui-widget-header .ui-state-focus { border: 1px solid #3473D1; background: #DBE6F7; font-weight: bold; color: #3473D1; }
.ui-state-hover a, .ui-state-hover a:hover { color: #3473D1; text-decoration: none; }
.ui-state-active, .ui-widget-content .ui-state-active, .ui-widget-header .ui-state-active { border: 1px solid #DBE6F7; background: #ffffff; font-weight: bold; color: #2A61B3; }
.ui-state-active a, .ui-state-active a:link, .ui-state-active a:visited { color: #2A61B3; text-decoration: none; }
.ui-widget :active { outline: none; }
.ui-tabs { position: relative; padding: .2em; zoom: 1; } /* position: relative prevents IE scroll bug (element with position: relative inside container with overflow: auto appear as "fixed") */
.ui-tabs .ui-tabs-nav { margin: 0; padding: .2em .2em 0; }
.ui-tabs .ui-tabs-nav li { list-style: none; float: left; position: relative; top: 1px; margin: 0 .2em 1px 0;  padding: 0; white-space: nowrap; }
.ui-tabs .ui-tabs-nav li a { float: left; padding: .5em 1em; text-decoration: none; }
.ui-tabs .ui-tabs-nav li.ui-tabs-selected { margin-bottom: 0; padding-bottom: 0px; border-bottom: 1px solid #ffffff;}
.ui-tabs .ui-tabs-nav li.ui-tabs-selected a, .ui-tabs .ui-tabs-nav li.ui-state-disabled a, .ui-tabs .ui-tabs-nav li.ui-state-processing a { cursor: text; }
.ui-tabs .ui-tabs-nav li a, .ui-tabs.ui-tabs-collapsible .ui-tabs-nav li.ui-tabs-selected a { cursor: pointer; } /* first selector in group seems obsolete, but required to overcome bug in Opera applying cursor: text overall if defined elsewhere... */
.ui-tabs .ui-tabs-panel { display: block; border-width: 0; padding: 1em 0em; background: none; }
.ui-tabs .ui-tabs-hide { display: none !important; }
</style>
<script type="text/javascript" src="javascript/jquery-ui-1.8.2.custom.min.js"></script>
<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li><a href="module/accounts/account-manage/">Accounts</a> <span class="divider">/</span></li>
  <li class="active">Edit Account</li>
</ul>
<h3>Manage Account</h3>


<div class="row-fluid">
  <div class="span5">
<?php
if ($_POST['nocmd'] == "Resend Authorization Email") {
	$accountObj = new Account($_POST['account_id']);
	$accountObj->sendAuthorizationEmail();

} else if($_POST['cmd'] == "update" && $_POST['nocmd'] != "Initialize Account") {
	$proceed    = true;
	$accountObj = new Account($_POST['account_id']);
	
	//check to ensure email address unique
	if($accountObj->email_in_use($_POST['slot_3'])) {
		$proceed = false;
		//print message alerting of un-unique email
		print "<p class='error'>Email address already in use, please enter a different Email address.</p>";
	} else if(!isValidEmail($_POST['slot_3'])) {
		$proceed = false;
		//print message alerting of invalid email
		print "<p class='error'>Email address enter is not valid, please enter a valid email address.</p>";
	}
	if ($_POST['syscmd'] != "") {
	
	} else {
		//check to ensure username unique
		if($accountObj->username_in_use($_POST['slot_1']) || $_POST['slot_1'] == "") {
			$proceed = false;
			//print message alerting of un-unique username
			print "<p class='error'>Username already in use, please enter a different username.</p>";
		}
	
		//check to ensure username unique
		if($_POST['slot_2'] == "" && $_POST['syscmd'] == "initialize") {
			$proceed = false;
			//print message alerting of un-unique username
			print "<p class='error'>Password not provided, please enter a password.</p>";
		}
	}

	if($proceed) {
		$accountObj->update($_POST);

		header('Location: '.NUMO_FOLDER_PATH.'module/accounts/account-manage/');
	}
}

//load account information
$sql = "SELECT a.*,DATE_FORMAT(a.last_accessed,'%e-%b-%Y') as 'last_online' FROM accounts a, `types` t WHERE a.id='".$_GET['id']."' AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
//print $sql."<br>";
$result = $dbObj->query($sql);

if($row = mysql_fetch_array($result)) {
?>
<form method="post">
<?php
	if($_POST['syscmd'] == "initialize") {
		$row['pending'] = 1;
?>
<input type="hidden" name="syscmd" value="initialize" />
<?php
	}
?>

	<fieldset>
	<legend>Account Details</legend>
	<ul class="form_display">
		<li><label>Last Login:</label><p style='line-height: 30px;'><? if($row['last_online'] == "") { print "Never"; } else { print strtoupper($row['last_online']); } ?></p></li>
		<?php
		if($row['pending'] != "3") {
			print '<li>
							<label for="is_admin">Administrator:</label>
							<select id="is_admin" name="is_admin">
							<option value="0">No</option>
							<option value="2" '.($row['is_admin'] == "2" ? 'selected="selected"' : '').'>Administrator</option>
							<option value="1" '.($row['is_admin'] == "1" ? 'selected="selected"' : '').'>Super Administrator</option>
							</select>
						</li>';

			print '<li>
							<label for="pending">Status:</label>
							<select id="pending" name="pending">
							<option value="1">Pending Approval</option>
							<option value="0" '.($row['pending'] == 0 ? 'selected="selected"' : '').'>Approved</option>
							</select>
						</li>';


		}
			print '<li>
							<label for="activated">Activated:</label>
							<select id="activated" name="activated">
							<option value="0">Pending Activation</option>
							<option value="1" '.($row['activated'] == 1 ? 'selected="selected"' : '').'>Activated</option>
							</select>
						</li>';
		//load field information for accounts group
		$sql = "SELECT `name`,`slot`,`input_type`,`input_options` FROM `fields` WHERE type_id='".$row['type_id']."' ORDER BY `position`,`name`";

		if($row['pending'] == "3") {
			$sql = "SELECT `name`,`slot`,`input_type`,`input_options` FROM `fields` WHERE type_id='".$row['type_id']."' AND (`slot`=3 OR `slot`=4) ORDER BY `position`,`name`";
		}
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($field = mysql_fetch_array($results)) {
			$fieldValue = str_replace('"','&#34;',$row['slot_'.$field['slot']]);
			if($field['input_type'] == "password") {
				if($_POST['syscmd'] == "initialize") {
					print '<li><label for="slot_'.$field['slot'].'">Enter '.$field['name'].':</label><input type="password" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="" autocomplete="off" /></li>';
				} else {
					print '<li><label for="slot_'.$field['slot'].'">Change '.$field['name'].':</label><input type="password" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="" autocomplete="off" /></li>';
				}
			} else if($field['input_type'] == "dropdown list") {
				print '<li>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<select id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">'.generate_list_options($field['input_options'],$fieldValue).'</select>
							</li>';

			} else if($field['input_type'] == "state") {
				$countryList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_COUNTRY_LIST));
 
				$countries = array();
				foreach ($countryList as $data) {
					$countryData = explode("=", $data);
					$key = $countryData[0];
					$value = $countryData[1];
					$countries["$key"] = $value;
				}
				
				
				
				print '<li>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<select id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">';
					print generate_state_province_options($field['name'], $fieldValue);
				
				print '</select>
							</li>';

		} else if($field['input_type'] == "country") {

				print '<li>
								<label for="slot_'.$field['slot'].'">'.$field['name'].':</label>
								<select id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">'.generate_country_options($field['name'],$fieldValue).'</select>
							</li>';
			} else if($field['input_type'] == "street address") {
				print '<li><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><textarea  id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'">'.$fieldValue.'</textarea></li>';

			} else {
				print '<li><label for="slot_'.$field['slot'].'">'.$field['name'].':</label><input type="text" id="slot_'.$field['slot'].'" name="slot_'.$field['slot'].'" value="'.$fieldValue.'" autocomplete="off" /></li>';
			}
		}
		?>
		<li>
        
        <div class='btn-group'><input type="submit" id="submit_cmd" name="nocmd"  class='btn btn-primary btn-large' value="Update" /></div>
        <div class='btn-group'>
		<?php if ($row['pending'] == "3") { ?><input type="submit" class='btn btn-success btn-large' id="submit_cmd" name="nocmd" value="Initialize Account" /><input type="hidden" name="syscmd" value="initialize" /><?php } ?>
        <?php if ($row['activated'] == 0) { ?><input type="submit" name="nocmd" class='btn btn-info btn-large' value="Resend Authorization Email" /><?php } ?>
         <input type="hidden" name="cmd" value="update" />
         </div></li>
	</ul>
	</fieldset>
	<input type="hidden" name="account_id" value="<?=$row['id']?>" />
</form>
</div>
<div class="span7">
  
<?php
foreach($modules as $key => $module) {
	if ($module != "accounts") {
		print "<div class='row'><div class='span12'>";
		@include(MODULES_FOLDER_NAME."/".$module."/configuration/account.php");
		print "</div></div>";
	}
}

@mysql_free_result($result);
@mysql_free_result($results);
?>
  </div>
</div>
</div>
<?php
} else {
	print '<p>Could not locate account.</p>';
}
?>