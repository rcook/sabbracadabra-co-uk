<?
class MarbleMail {
  var $connection;
  var $newLine;
  var $smtpLocation;
  var $smtpPort;
  var $smtpUsername;
  var $smtpPassword;

  function MarbleMail() {
  	global $dbObj;

  	$sql = "SELECT smtp_host, smtp_user, smtp_password, smtp_port FROM newsletter_settings WHERE site_id='".NUMO_SITE_ID."'";
  	$results = $dbObj->query($sql);

    $this->newLine = "\r\n";

    while($row = mysql_fetch_array($results)) {
		$this->smtpLocation  = $row['smtp_host'];
		$this->smtpPort      = $row['smtp_port'];
		$this->smtpUsername  = $row['smtp_user'];
		$this->smtpPassword  = $row['smtp_password'];

		//connect to server
		$this->connection = @fsockopen($this->smtpLocation, $this->smtpPort, $errno, $errstr, 30);

		//say HELO
		fputs($this->connection, "HELO $this->smtpLocation". $this->newLine);

		//request for auth login
		fputs($this->connection,"AUTH LOGIN" . $this->newLine);

		//send the username
		fputs($this->connection, base64_encode($this->smtpUsername) . $this->newLine);

		//send the password
		fputs($this->connection, base64_encode($this->smtpPassword) . $this->newLine);
		$smtpResponse = fgets($this->connection, 4096);
		//print $smtpResponse . "<br>";
	}
  }

  function send($to, $from, $subject, $message) {
	$toName   = $to;
	$fromName = $from;

	$toArray = explode(" (", $to);
	if(Count($toArray) > 1) {
	  $to = $toArray[0];
	  $toName = str_replace(")", "", $toArray[1]);
	}

	$fromArray = explode(" (", $from);
	if(Count($fromArray) > 1) {
	  $from  = $fromArray[0];
	  $fromName = str_replace(")", "", $fromArray[1]);
	}

	//email from
	fputs($this->connection, "MAIL FROM: <$from>" . $this->newLine);
	$smtpResponse = fgets($this->connection, 4096);
	//print $smtpResponse . "<br>";

	//email to
	fputs($this->connection, "RCPT TO: <$to>" . $this->newLine);
	$smtpResponse = fgets($this->connection, 4096);
	//print $smtpResponse . "<br>";

	//the email
	fputs($this->connection, "DATA" . $this->newLine);
	$smtpResponse = fgets($this->connection, 4096);
	//print $smtpResponse . "<br>";

	//construct headers
	$headers = "MIME-Version: 1.0" . $this->newLine;

    // changed from iso-8859-1 to UTF-8 on April 22, 2014
	//$headers .= "Content-type: text/html; charset=iso-8859-1" . $this->newLine;
	$headers .= "Content-type: text/html; charset=UTF-8" . $this->newLine;
	//$headers .= "To: $toName <$to>" . $this->newLine;
	//$headers .= "From: $fromName <$from>" . $this->newLine;

	//send message
	fputs($this->connection, "To: $to ($toName)\r\nFrom: $from ($fromName)\r\nSubject: $subject\r\n$headers\r\n\r\n$message\r\n.\r\n");
	$smtpResponse = fgets($this->connection, 4096);
	//print $smtpResponse . "<br>";
  }

  function close() {
    fputs($this->connection,"QUIT" . $this->newLine);
    $smtpResponse = fgets($this->connection, 4096);
	//print $smtpResponse . "<br>";

    fclose($this->connection);
  }
}

?>