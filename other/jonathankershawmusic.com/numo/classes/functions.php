<?php
$enqueuedJS = array();
$enqueuedCSS = array();

function moduleOffline($moduleName) {
  global $dbObj;
  global $numo;

  $result = $dbObj->query("SELECT `status` FROM modules WHERE `status`=1 AND name='{$moduleName}' AND site_id='".NUMO_SITE_ID."'");
  //print "SELECT `status` FROM modules WHERE `status`=1 AND name='{$moduleName}' AND site_id='".NUMO_SITE_ID."'";
 // print "SELECT `status` FROM modules WHERE `status`=1 AND name='{$moduleName}' AND site_id='".NUMO_SITE_ID."'";
	//				  print "<br>".!(mysql_num_rows($result) == 0)."<br>";
//					  print mysql_error();
  return mysql_num_rows($result) == 0;
}

function moduleInstalled($moduleName) {
  global $dbObj;
  $result = $dbObj->query("SELECT `status` FROM modules WHERE name='{$moduleName}' AND site_id='".NUMO_SITE_ID."'");
  $record = mysql_fetch_array($result);
  return $record['status'] >= '0';
}

function numo_secure($region = "") {
  global $_SERVER;
  $httpsOn = $_SERVER['HTTPS'] == "on";

  if ($_SERVER['SERVER_PORT'] == "443") {
    $httpsOn = true;


  }
  if ($region == "backend" && !$httpsOn && NUMO_SECURE_BACKEND === true) {
	 // print "stop";
	 // print NUMO_SECURE_BACKEND;
	  header("Location: https://".NUMO_SECURE_ADDRESS.NUMO_FOLDER_PATH);
	  exit;
  }
}



function get_avatar($email, $size = 30, $rating = "G") {

	$default = "mystery";


	if ( !empty($email) )
		$email_hash = md5( strtolower( $email ) );

	if ( $_SERVER['HTTPS'] == "on" ) {
		$host = 'https://secure.gravatar.com';
	} else {
		if ( !empty($email) )
			$host = sprintf( "http://%d.gravatar.com", ( hexdec( $email_hash{0} ) % 2 ) );
		else
			$host = 'http://0.gravatar.com';
	}

	if ( !empty($email) ) {
		$out = "$host/avatar/";
		$out .= $email_hash;
		$out .= '?s='.$size;
		$out .= '&amp;d=' . urlencode( $default );


		if ( !empty( $rating ) )
			$out .= "&amp;r={$rating}";

		$avatar = "<img alt='{$safe_alt}' src='{$out}' class='numo_avatar numo_avatar_{$size}' height='{$size}' width='{$size}' />";
	} else {
		$avatar = "<img alt='{$safe_alt}' src='{$default}' class='numo_avatar numo_avatar_{$size} avatar-default' height='{$size}' width='{$size}' />";
	}

	return $avatar;

}
function numo_enqueue_js($jsPath, $jsName = "", $jsVersion = "1", $deferred = false) {
	global $enqueuedJS;
	if ($jsName == "") {
		$jsName = time().rand(0, 1000);
	}
	$enqueuedJS["$jsName"]["$jsVersion"]["source"] = $jsPath;
	$enqueuedJS["$jsName"]["$jsVersion"]["library"] = $jsName;
	$enqueuedJS["$jsName"]["$jsVersion"]["version"] = $jsVersion;
	$enqueuedJS["$jsName"]["$jsVersion"]["deferred"] = $deferred;

}

function numo_enqueue_css($cssPath) {
	global $enqueuedCSS;
	$enqueuedCSS[] = $cssPath;
}

function update_admin_header() {
	update_check_header();
}

function update_check_header() {
  global $enqueuedJS;
  global $enqueuedCSS;
  $existingJS = array();
  $knownJSLibraries = array('jquery',
							'jquery-ui',
							'jquery.jqDock',
							'jquery.nivo',
							'jquery.mini.slider',
							'jquery.video',
							'pxgradient',
							'jquery.lightbox',
							'jquery.watch',
							'jquery.musemenu',
							'jquery.museoverlay',
							'jquery.musepolyfill',
							'jquery.cookie',
							'jquery.cook',
							'jquery.cslider',
							'jquery.mmenu',
							'jquery.transit',
							'jquery.prettyPhoto',
							'jquery.quotator',
							'jquery.ba-cond',
							'jquery.fancybox',
							'jquery.fs-slider',
							'jquery.waitforimages',
							'jquery.isotope',
							'jquery.ui.totop',
							'jquery.quovolver',
							'jquery.testimonials.slider',
							'jquery-migrate'

							);

  $page = ob_get_clean();
  // print "page=[{$page}]";
  $pattern = '/<script (.*)'.'><\/script>/i';
  preg_match_all($pattern, $page, $matches, PREG_SET_ORDER);
//print "yes";

  //print sizeof ($matches);

   foreach ($matches as $jsMatch) {
	   $jsLibrary = "basic".time().rand(0, 1000);
	   $jsVersion = ""; // updated june 7, 2013
	   $jsNV = explode(" ", $jsMatch[1]);
	  // print sizeof($jsNV);
	   foreach ($jsNV as $nv) {
		   if (strstr($nv, "src")) {
			   $kv = explode("=", $nv);
			   $jsSource = $kv[1];

		   }
	   }
	 //  print $jsSource."<br>";
	   foreach ($knownJSLibraries as $library) {
		   if (strstr($jsMatch[0], $library)) {
			   $jsLibrary = $library;
			   //preg_match('/([0-9]{1,2}?)(\.?[0-9]{1,3}?)?(\.?[0-9]{1,4}?)?/i', $jsSource, $sourceMatches);
			   preg_match('/[\-\/]([0-9]{1,2}?)(\.?[0-9]{1,3}?)?(\.?[0-9]{1,4}?)?/i', $jsSource, $sourceMatches); // updated june 7, 2013
			   //print $sourceMatches[0]."<br>";
			   $jsVersion = trim($sourceMatches[0], "-/");
		      // print $jsVersion." is it now <br>";
		   }
	   }
	   $existingJS["$jsLibrary"]["$jsVersion"]['source'] = $jsSource;
	   $existingJS["$jsLibrary"]["$jsVersion"]['find'] = $jsMatch[0];
	   $existingJS["$jsLibrary"]["$jsVersion"]['version'] = $jsVersion;

	  // print htmlentities($x[0]);
   }

   foreach ($existingJS as $jsLibrary => $jsVersions) {
	   $existingJS["$jsLibrary"] = $jsVersions;
	  	uksort($jsVersions, jsVersionCompare);
	  	//var_dump($jsVersions);
	  	//print "<br>";
		for ($i = 1; $i < sizeof($jsVersions); $i++) {
			$current = array_shift($jsVersions);

			$removeJS = $current['find'];
			$page = str_replace($removeJS, "", $page);
		}
	   $existingJS["$jsLibrary"] = $jsVersions;

		$current = array_shift($jsVersions);
		//print "current = ".$current['version']."<br>";
   }
   
   //print "test";
   /*
    foreach ($existingJS as $jsLibrary => $jsVersions) {
	  	ksort($jsVersions);
		for ($i = 1; $i < sizeof($jsVersions); $i++) {
			$current = array_shift($jsVersions);
			//print $current['version']."<br>";
			//$removeJS = $current['find'];
			//$page = str_replace($removeJS, "", $page);
		}

		$current = array_shift($jsVersions);
		//print $current['version']."<br>";

  }
  */
  foreach ($enqueuedJS as $jsName => $data) {

	ksort($data);
	$current = array_pop($data);
	$javascriptSource = $current['source'];
	$javascriptLibrary = $current['library'];
	$javascriptVersion = $current['version'];
	if ($existingJS["$javascriptLibrary"]) {

		$existing = array_pop($existingJS["$javascriptLibrary"]);
		if ($existing['version'] < $current['version']) {
			$page = str_replace($existing['find'], "<script type='text/javascript' src='{$javascriptSource}'></script>", $page);
		}
	} else {
		//print "should be adding";
	  if ($data['deferred']) {
	    $page = str_replace("</body>", "<script type='text/javascript' src='{$javascriptSource}'></script></body>", $page);
		  
	  } else {
	    $page = str_replace("</head>", "<script type='text/javascript' src='{$javascriptSource}'></script></head>", $page);
	  }
	}
  }

  foreach ($enqueuedCSS as $cssSource) {
		$page = str_replace("</head>", "<link rel='stylesheet' href='{$cssSource}' type='text/css' /></head>", $page);

  }
  print $page;
}

function jsVersionCompare($aIn, $bIn) {
  $a = explode(".", $aIn);
  $b = explode(".", $bIn);

  if ($a[0] != $b[0]) {
    if ($a[0] < $b[0]) {
//    print "{$a[0]} is < {$b[0]}<br>";

      return -1;
    } else {
      return 1;
    }
  } else {
//    print "primary versio is the same<br>";
    if ($a[1] != $b[1]) {
		if ($a[1] < $b[1]) {
//    print "{$a[1]} is < {$b[1]}<br>";

		  return -1;
		} else {
//		    print "{$a[1]} is > {$b[1]}<br>";

		  return 1;
		}
    } else {
//    print "secondary version {$a[1]} and {$a[1]} is the same<br>";

		if ($a[2] != $b[2]) {
			if ($a[2] < $b[2]) {
			  return -1;
			} else {
			  return 1;
			}
		} else {
		  return 0;
		}

  	}

  }
}

function numo_session_start() {
  global $secondarySavePath;

  $secondarySavePath = $_SERVER['DOCUMENT_ROOT'].NUMO_FOLDER_PATH."sessions";

  session_set_cookie_params(3600, '/', str_replace("www.", "", $_SERVER['HTTP_HOST']));
  // functionality not working when open base cannot detect writble folders Dec 12, 2011
//  @session_start();
  //$sessionInfo = session_get_cookie_params();

 // print $sessionInfo['domain'];
 // return;

  //print $secondarySavePath;

  if (USE_INTERNAL_SESSIONS === true) {
	 if (is_writable($secondarySavePath)) {
	   session_save_path ($secondarySavePath);
	 } else {
	   print "The folder 'numo/sessions/' is not writable.  Please enable write priviledges for all users on this folder.";
	 }
	 @session_start();
  } else {
	 @session_start();
  }
  setcookie(session_name(), session_id(), time()+3600);
}


// check login request details (admin or normal)
function login($username, $password, $registration = false, $maxAttempts = 0, $lockoutPeriod = 30) {
	global $_SESSION;
	global $dbObj;
	global $PARAMS;
	global $badLoginError;


	$sql = "SELECT a.id, a.type_id, a.is_admin, a.pending, a.activated, a.slot_1, a.slot_2, a.slot_4 FROM accounts a, `types` t WHERE a.slot_1='".$username."' AND a.type_id=t.id AND a.pending<>'3' AND t.site_id='".NUMO_SITE_ID."'";

	if ($registration) {
	  $sql = "SELECT a.* FROM accounts a, `types` t WHERE a.id='".$username."' AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
	} else {
	  $sql = "SELECT a.* FROM accounts a, `types` t WHERE a.slot_1='".$username."' AND a.type_id=t.id AND a.pending<>'3' AND t.site_id='".NUMO_SITE_ID."'";
	}

	$result = $dbObj->query($sql);
    // if we have a match to the username or account id then proceed
	if($row = mysql_fetch_array($result)) {

		// if the password matches, or if we are going through the registration process
		if((crypt($password,$row['slot_2']) == $row['slot_2'] || $registration) &&
		   !($maxAttempts > 0 && $row['current_bad_access_attempts'] >= $maxAttempts && (strtotime($row['last_bad_access_attempt_time']) + $lockoutPeriod * 60 > time()))) {

			$_SESSION['account_id'] = $row['id']; 
			$_SESSION['login_id']   = $row['slot_1'];
			$_SESSION['type_id']    = $row['type_id'];
			$_SESSION['pending']    = $row['pending'];
			$_SESSION['activated']  = $row['activated'];
			$_SESSION['is_admin']   = $row['is_admin'];
			$_SESSION['full_name']  = $row['slot_4'];
			$_SESSION['numo_site_id'] = NUMO_SITE_ID;

			// added march 26, 2014, so that items from the users cart get merged
			if ($_SESSION['shopper_id'] != "" && $_SESSION['shopper_id'] != $row['id']) {
				$sql = "SELECT `id` FROM `shopping_cart_orders` WHERE `processed`=0 AND `account_id`='".$_SESSION['shopper_id']."'";
				$results = $dbObj->query($sql);

				// if existing pending order for previous SHOPPER ID merge temp account pending order items
				if($orderRow = mysql_fetch_array($results)) {
					$sql = "SELECT `id` FROM `shopping_cart_orders` WHERE `processed`=0 AND `account_id`='".$_SESSION['account_id']."'";
					$orders = $dbObj->query($sql);

					// account has a order pending purchase, add to order
					if($order = mysql_fetch_array($orders)) {
						$sql = "UPDATE `shopping_cart_order_items` SET `order_id`='".$order['id']."' WHERE `order_id`='".$orderRow['id']."'";
						$dbObj->query($sql);

						$sql = "DELETE FROM `shopping_cart_orders` WHERE `processed`=0 AND `account_id`='".$_SESSION['shopper_id']."'";
						$dbObj->query($sql);

					
					} else {
						  $sql = "UPDATE shopping_cart_orders SET account_id='{$_SESSION['account_id']}' WHERE id='{$orderRow['id']}'";
						  $dbObj->query($sql);
					}

				} else {
				}
				$sql = "DELETE FROM `accounts` WHERE `pending`=3 AND `id`='".$_SESSION['shopper_id']."'";
				$dbObj->query($sql);
				
			}

			// added February 11, 2014 so that the user's cart can populate prior to going to the view cart page
  		    $_SESSION['shopper_id'] = $row['id'];

			$sql = "SELECT t.* FROM `types` t WHERE t.id='{$row['type_id']}' AND t.site_id='".NUMO_SITE_ID."'";
			$res = $dbObj->query($sql);
			$typeRow = mysql_fetch_array($res);
			mysql_free_result($res);

			$_SESSION['type_name'] = $typeRow['name'];

			if ($PARAMS['redirect'] == "" && $PARAMS['force_reload'] == "") {
				if ($registration) {
					$PARAMS['redirect'] = $typeRow['registration_completion_page'];
				} else {
					$PARAMS['redirect'] = $typeRow['login_completion_page'];
				}
			}

			//free SQL result
			mysql_free_result($result);

			$sql = "UPDATE accounts SET last_accessed='".date("y/m/d H:i:s")."' WHERE id='".$row['id']."'";
			$dbObj->query($sql);
			if ($row['current_bad_access_attempts'] > 0) {
			  $sql = "UPDATE accounts SET current_bad_access_attempts='0' WHERE id='".$row['id']."'";
			  $dbObj->query($sql);
			}

			return true;

		// otherwise we need to check to see if we need to log this bad login attempt
		} else {
			if ($maxAttempts > 0) {
			  $previousAttempts = $row['current_bad_access_attempts'];

			  if (++$previousAttempts == $maxAttempts) {
				  	  // send warning email to account holder
					  	require_once("modules/accounts/classes/Account.php");

					  $account = new Account($row['id']);
					  $unlockURL = $account->generateUnlockCode();
				  mail($row['slot_3'], "{$row['slot_4']}, Your account at ".$_SERVER['HTTP_HOST']." has been frozen", "{$row['slot_4']},\n\nOur system has recorded {$previousAttempts} bad login attempts on your account (username: {$row['slot_1']}) and has subsquently suspended it.\n\nAccess to the account will be automatically re-activated {$lockoutPeriod} minutes from the time of the last bad login attempt.
\nYou will not be notified of any further incursion attempts.\n\nIf you wish to gain immediate access to your account, please contact the website administrator and have them unlock your account or click on the following link to unlock the account yourself:

{$unlockURL}

Please note, this is an automated message.", "From: ".NUMO_SYNTAX_NUMO_ADMINISTRATIVE_EMAIL_ADDRESS);

				 // print "sent email to {$row['slot_3']}";
			  }
			  if ($previousAttempts >= $maxAttempts) {
				  $badLoginError = "The number of bad login attempts on this account has exceeded the allowed limit, and subsequently this account has been locked down.  Please contact
				  the system administrator.";
				//  print date("Y-m-d H:i:s", time());
				//  print "<br>";
				////  print $row['last_bad_access_attempt_time']."<br>";
				//  print $lockoutPeriod;

			  } else {
				  $badLoginError = "The information provided does not match our records.  After {$maxAttempts} failed attempts, your account is protected from further access for a period of time.";
			  }
			  // if valid login, don't change the bad access attempt information
			  if (crypt($password,$row['slot_2']) == $row['slot_2']) {

			  } else {
			  	$update = "UPDATE `accounts` SET last_bad_access_attempt_time='".date("Y-m-d H:i:s", time())."', current_bad_access_attempts='{$previousAttempts}' WHERE id='{$row['id']}'";
				$dbObj->query($update);
				//print mysql_error();

			  }

			}
		}
	}

	//free SQL result
	mysql_free_result($result);

	return false;
}

function valid_key_code() {
	if(crypt(PRODUCT_LICENSE_KEY,PRODUCT_LICENSE_CODE) == PRODUCT_LICENSE_CODE) {
		return true;
	}

	return false;
}


function isValidDomain($domain) {
      return true;
	  $urlPattern = "/^([a-z0-9]([-a-z0-9]*[a-z0-9])?\\.)+((a[cdefgilmnoqrstuwxz]|aero|arpa)|(b[abdefghijmnorstvwyz]|biz)|(c[acdfghiklmnorsuvxyz]|cat|com|coop)|d[ejkmoz]|(e[ceghrstu]|edu)|f[ijkmor]|(g[abdefghilmnpqrstuwy]|gov)|h[kmnrtu]|(i[delmnoqrst]|info|int)|(j[emop]|jobs)|k[eghimnprwyz]|l[abcikrstuvy]|(m[acdghklmnopqrstuvwxyz]|mil|mobi|museum)|(n[acefgilopruz]|name|net)|(om|org)|(p[aefghklmnrstwy]|pro)|qa|r[eouw]|s[abcdeghijklmnortvyz]|(t[cdfghjklmnoprtvwz]|travel)|u[agkmsyz]|v[aceginu]|w[fs]|y[etu]|z[amw])$/i";
	  return eregi($urlPattern, $domain) && strlen($domain) != 0;

}

function isValidEmail($email) {
  $validEmailPattern = "/^[_a-zA-Z0-9-]+(\.[_a-zA-Z0-9-]+)*@[a-zA-Z0-9-]+(\.[a-zA-Z0-9-]+)*(\.[a-zA-Z]{2,4})$/";
  if (preg_match($validEmailPattern, $email) > 0) {
    return true;
  } else {
    return false;
  }
}

function isValidDate($date, $format) {
  if ($date == date($format, strtotime($date))) {
	  return true;
  } else {
	  return false;
  }
}

function generate_state_province_options($label, $fieldValue = "") {
	$countryList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_COUNTRY_LIST));

	$countries = array();
	foreach ($countryList as $data) {
		$countryData = explode("=", $data);
		$key = $countryData[0];
		$value = $countryData[1];
		$countries["$key"] = $value;
	}



    ob_start();
	print "<option value=''>-- Select {$label} --</option>";
	if (in_array("United States", $countries)) {
				$states = getAmericanStatesArray();


		print "<option class='us-state' value='' disabled>-------- United States --------</option>";
		print generate_list_options($states,$fieldValue, '\r\n', 'us-state');
	}

	if (in_array("Canada", $countries)) {
		$states = getCanadianProvincesArray();
		print "<option class='cad-prov' value='' disabled>-------- Canada --------</option>";
		print generate_list_options($states,$fieldValue, '\r\n', 'cad-prov');
	}

	if (in_array("Australia", $countries)) {
		print "<option class='aus-prov' value='' disabled>-------- Australia --------</option>";
		$states = getAustralianProvincesArray();
		print generate_list_options($states,$fieldValue, '\r\n', 'aus-prov');
	}
	return ob_get_clean();
}

function getAmericanStatesArray() {
		$statesList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_AMERICAN_STATE_LIST));
		$states = array();
		foreach ($statesList as $data) {
			$satesData = explode("=", $data);
			$key = $satesData[0];
			$value = $satesData[1];
			$states["$key"] = $value;
		}
		return $states;
}

function getCanadianProvincesArray() {
			$statesList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_CANADIAN_PROVINCE_LIST));

		$states = array();
		foreach ($statesList as $data) {
			$satesData = explode("=", $data);
			$key = $satesData[0];
			$value = $satesData[1];
			$states["$key"] = $value;
		}
		return $states;
}


function isValidCardNumber($ccNumber, $acceptedTypes = array("visa", "mastercard", "amex", "discover", "diners")) {
  /* Validate; return value is card type if valid. */
  $false = false;
  $cardType = "";
  $cardRegExes = array(
    "/^4\d{12}(\d\d\d){0,1}$/" => "visa",
    "/^5[12345]\d{14}$/" => "mastercard",
    "/^3[47]\d{13}$/" => "amex",
    "/^6011\d{12}$/" => "discover",
    "/^30[012345]\d{11}$/" => "diners",
    "/^3[68]\d{12}$/" => "diners",
  );

  foreach ($cardRegExes as $regEx => $type) {
	if (preg_match($regEx, $ccNumber)) {
	  $cardType = $type;
	  break;
	}
  }

  if (!$cardType) {
    return $false;
  }

  /* mod 10 checksum algorithm */
  $revCode = strrev($ccNumber);
  $checksum = 0;

  for ($i = 0; $i < strlen($revCode); $i++) {
    $currentNum = intval($revCode[$i]);
    if($i & 1) { /* Odd position */
      $currentNum *= 2;
    }
    /* Split digits and add. */
    $checksum += $currentNum % 10;
	if ($currentNum > 9) {
      $checksum += 1;
    }
  }

  if ($checksum % 10 == 0) {
    return $cardType;
  } else {
    return $false;
  }
}

function getAustralianProvincesArray() {
			$statesList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_AUSTRALIAN_PROVINCE_LIST));
		$states = array();
		foreach ($statesList as $data) {
			$satesData = explode("=", $data);
			$key = $satesData[0];
			$value = $satesData[1];
			$states["$key"] = $value;
		}
	return $states;
}

function generate_country_options($label, $fieldValue = "") {
				$countryList = explode("\r\n", str_replace("<br>", "\r\n", NUMO_SYNTAX_NUMO_COUNTRY_LIST));
				$countries = array();
				foreach ($countryList as $data) {
					$countryData = explode("=", $data);
					$key = $countryData[0];
					$value = $countryData[1];
					$countries["$key"] = $value;
				}
	$listOptions = generate_list_options($countries, $fieldValue);
	return "<option value=''>-- Select {$label} --</option>".$listOptions;

}

function generate_expiry_month_options($fieldValue = "") {
	$months = array();
	for ($i = 1; $i<=12; $i++) {
		$month = sprintf('%02d', $i);
		$months["$month"] = date("m", mktime(0, 0, 0, $i));
	}
	return generate_list_options($months, $fieldValue);
}

function generate_expiry_year_options($fieldValue = "") {
	$months = array();
	for ($i = date("Y"); $i< date("Y") + 10; $i++) {

		$years["$i"] = $i;
	}
	return generate_list_options($years, $fieldValue);
}

function generate_list_options($options, $currentValue = "", $sep = "\r\n", $class = "") {
	$returnStr   = "";

	if(is_array($options)) {
		foreach ($options as $key => $value) {
			$value = html_entity_decode($value);
			if((is_array($currentValue) && in_array(html_entity_decode($key), $currentValue)) || (is_string($currentValue) && $currentValue == $key)) {
				$returnStr .= '<option '.($class != "" ? "class='{$class}'" : "").' value="'.$key.'" selected="selected">'.$value.'</option>';
			} else {
				$returnStr .= '<option '.($class != "" ? "class='{$class}'" : "").'value="'.$key.'">'.$value.'</option>';
			}
		}
	} else if(is_array($currentValue)) {
		$listOptions = explode($sep, trim($options));

		foreach ($listOptions as $key => $value) {
						$value = html_entity_decode($value);

			if(in_array(html_entity_decode($value), $currentValue)) {
				$returnStr .= '<option '.($class != "" ? "class='{$class}'" : "").'value="'.$value.'" selected="selected">'.$value.'</option>';
			} else {
				$returnStr .= '<option '.($class != "" ? "class='{$class}'" : "").'value="'.$value.'">'.$value.'</option>';
			}
		}
	} else {
		//print nl2br($options);
		//$options = str_replace("\r\n", '\n', $options);
		//$options = str_replace('\n', $sep, $options);

		//print $options;

		$listOptions = explode($sep, trim($options));
		//print sizeof($listOptions);

		foreach ($listOptions as $key => $value) {
						$value = html_entity_decode($value);

			if (strstr($value, "=")) {
				$valueData = explode("=", $value, 2);
				$key = $value;
				$value = $valueData[1];

			} else {
				$key = $value;
			}
			if($currentValue == $key) {
				$returnStr .= '<option '.($class != "" ? "class='{$class}'" : "").'value="'.$key.'" selected="selected">'.$value.'</option>';
			} else {
				$returnStr .= '<option '.($class != "" ? "class='{$class}'" : "").'value="'.$key.'">'.$value.'</option>';
			}
		}
	}

	return $returnStr;
}

function check_license_key($productLicenseKey,$productName) {
	$ch = curl_init(); //init

	curl_setopt($ch, CURLOPT_URL, 'http://numo.server-apps.com/check/'); //setup request to website to check license key
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // return the response
	curl_setopt($ch, CURLOPT_POST, 1); //transfer information as a POST request
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'lpk='.urlencode($productLicenseKey).'&domain='.urlencode($_SERVER["HTTP_HOST"]).'&pid='.urlencode($productName)); //pass product license key and domain name along to be checked

	//send request and save response to variable
	$response = @curl_exec($ch);

	/*

	------------------------
	|||  RESPONSE CODES  |||
	------------------------

	-1 = License key is not valid for the product it was entered for (i.e. license code for members module provided when installing a blog module)
	-2 = License already used for an installation on a different domain name
	-3 = Invalid domain name passed along
	 0 = Product license key does not exist (could not be found)
	 1 = License already used for an installation for THIS domain name
	 2 = License never used, record created at LM

	*/
	//print "response: ".$response;

	//check to see if the curl request completed without error
	if(curl_errno($ch)) {
		//send email to luckymarble for manual confirmation
		//@mail("numo@luckymarble.com","NUMO License Check","Product License Key: ".$productLicenseKey."\nDomain: ".$_SERVER["HTTP_HOST"]."\nModule: ".$productName);

	//error with license key provided
	} else if($response <= 0) {
		curl_close($ch); //close curl connection

		if($response == -1) {
			return " ** Product license key not valid for '".$productName."' module";

		} else if($response == -2) {
			return " ** Product license key already in use";

		} else {
			return " ** Invalid product license key";
		}
	}
	curl_close($ch); //close curl connection
	return ""; //success
}

function forceIframe($startWidth = 550, $startHeight = 500) {
  $src = "http://".$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI'];
  $src .="&iframe=1";
  print "<iframe id='numo_via_iframe_".time()."' src='{$src}'  onload='FrameManager.registerFrame(this)' scrolling='no'  width='{$startWidth}px' height='{$startHeight}px' style='border: 0px none; background: transparent'></iframe>";
  ?><script type="text/javascript" src="http://<?php print NUMO_SERVER_ADDRESS.NUMO_FOLDER_PATH; ?>javascript/iframe-start.js"></script><?php
}


if (!function_exists('date_default_timezone_set')) {
function date_default_timezone_set($timezone) {

  if ($timezone != "") {
    putenv("TZ=".$timezone);
  }
}
}

if (!function_exists('htmlspecialchars_decode')) {
 
function htmlspecialchars_decode($code) {
  $code = str_replace('&', '&amp;', $code);
  $code = str_replace('"', '&quot;', $code);
  $code = str_replace("'", '&#039;', $code);
  $code = str_replace('<', '&lt;', $code);
  $code = str_replace('>', '&gt;', $code);
  return $code;
}
}

function fetch_available_upgrades($renderForHome = false) {
	$ch = curl_init(); //init
    global $dbObj;
	$modules = array();
	global $siteData;
	//print "yup";
	$query = "SELECT * FROM modules WHERE site_id='".NUMO_SITE_ID."'";
	$result = $dbObj->query($query);
	
	$primaryLicenseKey = "";
	$secondaryLicenseKey = "";
	while ($record = mysql_fetch_array($result)) {
	//print ".";
	$modules[] = $record['name'];
		if ($record['license_key'] != "" & $secondaryLicenseKey == "") {
		  $secondaryLicenseKey = $record['license_key'];
		}
		if (($record['name'] == "accounts" || $record['name'] == "settings") && $record['license_key'] != "") {
			$primaryLicenseKey = $record['license_key'];
		}
	}
	if ($primaryLicenseKey == "") {
		$primaryLicenseKey = $secondaryLicenseKey;
	}
	 
	

	curl_setopt($ch, CURLOPT_URL, 'http://numo.server-apps.com/upgrade/view-upgrades/'); //setup request to website to check license key
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  // return the response
	curl_setopt($ch, CURLOPT_POST, 1); //transfer information as a POST request
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'lpk='.urlencode($primaryLicenseKey).'&show_home_page_offers='.$siteData['show_home_page_offers'].'&render_for_home='.($renderForHome ? "1" : "0").'&numo_folder='.NUMO_FOLDER_PATH.'&domain='.urlencode($_SERVER["HTTP_HOST"]).'&modules='.implode(",", $modules)); //pass product license key and domain name along to be checked

	//send request and save response to variable
	$response = @curl_exec($ch);

    print $response;
	return ""; //success
}
?>