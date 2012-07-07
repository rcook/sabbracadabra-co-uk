<?php

if(!function_exists('logshutdown')) {
	function logshutdown() {
		global $dbObj;
		@mysql_close($dbObj->connection);
	}

	register_shutdown_function("logshutdown");
}

if(!class_exists('Database')) {
	class Database {
		var $connection;
		var $valid_connection;
		var $error;

		function Database($host = DATABASE_HOST, $name = DATABASE_NAME, $user = DATABASE_USERNAME, $password = DATABASE_PASSWORD) {
			if ($name != 'DATABASE_NAME')  {
			  $this->connection  = @mysql_connect($host, $user, $password);
			  if (mysql_error()) {
				$this->error = mysql_error();  
			  }			  
			  $this->valid_connection = @mysql_select_db($name);
			  if (mysql_error()) {
				  if ($this->error != "") {
					  $this->error .= "<br>";
				  }
				$this->error .= mysql_error();  
			  }			  
			} else {
				$this->connection = false;
				$this->valid_connection = false;
			}
		}

		function query($sql) {
				//print $sql."<br>";
				return @mysql_query($sql, $this->connection);
		}

	}

	$dbObj = new Database();
} else {
	$dbObj->Database();
}
?>