<?php
define('CAPTCHA_SESSION_KEY', 'G0TCHA_CAPTCHA');

function checkCaptchaCode($code=null){
	//mail ("bdevnich@gmail.com", "CAPTCHA id", $code." match against ".$_SESSION[CAPTCHA_SESSION_KEY]." - ".$_SERVER['HTTP_HOST'], "From: admin@luckymarble.com");

	return (isset($_SESSION[CAPTCHA_SESSION_KEY]) && (strcasecmp($code, $_SESSION[CAPTCHA_SESSION_KEY])==0));
}


?>