<?php

/**
 * Project:     GOTCHA!: the PHP implementation of captcha.
 * File:        gotcha_image.php
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * For questions, help, comments, discussion, etc., please write to: sol2ray at gmail dot com
 *
 * @link http://phpbtree.com/captcha/
 * @copyright 2003-2005 Smart Friend Network, Inc.
 * @author Sol Toure <sol2ray at gmail dot com>
 * @version alpha 0.01;
 */

require("../../../configuration/database_connection_information.php");

error_reporting(0);

require("../../../classes/functions.php");

// start session
numo_session_start();
//session_start();
//Please modify this file to match your environment.
//mail ("brandon@luckymarble.com", "CAPTCHA session id", session_id()." - PHPSESSID = ".$_REQUEST[PHPSESSID], "From: admin@luckymarble.com");

include_once('util.php');
include_once('gotcha.php');


// Generate a random text.
// Feel free to replace this with a custom solution.
$t =  md5(uniqid(rand(), 1));


//You can eliminate the above variable ($CAPTCHA_SESSION_KEY) and use 
// the key string literal directly below.


//$_SESSION[CAPTCHA_SESSION_KEY] =  $text = substr($t, rand(0, (strlen($t)-6)), rand(3,6));
$_SESSION[CAPTCHA_SESSION_KEY] =  $text =  substr($t, rand(0, (strlen($t)-6)), rand(5,7));
//mail ("brandon@luckymarble.com", "CAPTCHA id", $_SESSION[CAPTCHA_SESSION_KEY], "From: admin@luckymarble.com");

$image_width = 150;
$image_height = 22;
$font_size = 15;
$font_depth = 2; //this is the size of shadow behind the character creating the 3d effect.


$img = new GotchaPng($image_width, $image_height);


if($img->create()){
	
	//fill the background color.
	$img->apply(new GradientEffect());
	//Apply the Grid.
	$img->apply(new GridEffect(5));
	//Add dots to the background
	$img->apply(new DotEffect());
	//Add the text.
	$t  = new TextEffect($text, $font_size, $font_depth);
	//print "My text is $text";
	//$t->addFont('SFTransRoboticsExtended.ttf');
	$t->addFont('arialbd.ttf');
	$t->addFont('GECENTUR.TTF');
	$t->addFont('TIFANYDI.TTF');
	// repeat the process for as much fonts as you want. Actually, the more the better.
	// A font type will be randomly selected for each character in the text code.
	$img->apply($t);
	//Add more dots
	//$img->apply(new DotEffect());
	$img->apply(new GridEffect(20));

	//Output the image.
	$img->render();
}

function getRandomCaptchaKey() {
  global $toolbox;
  //$randomIndex = rand(0, 15222);
  
  //$query = "SELECT word FROM form_handler_captcha_words WHERE word_id='$randomIndex'";
  //$result = $toolbox->database->query($query, "firewidget");
  //$record = $toolbox->database->fetch($result);
  //return $record['word'];
	return md5(uniqid(rand(), 1));

  //return md5(uniqid(rand(0, 15222), 1));
}
?>