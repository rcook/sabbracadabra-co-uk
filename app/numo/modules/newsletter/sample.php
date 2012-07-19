<?php
$to             = "cabull@shaw.ca (Christa)";
$from           = "testing@luckymarble.com (Lucky Marble Testing)";
$messageSubject = "Testing LM Mail Class2";
$messageBody    = "test message body";

$mailObj = new MarbleMail();
$mailObj->send($to, $from, $messageSubject, $messageBody);
$mailObj->send($to, $from, "Second test message2", $messageBody);
$mailObj->close();
?>