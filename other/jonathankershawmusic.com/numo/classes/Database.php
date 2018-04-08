<?php

if(!function_exists('logshutdown')) {
	function logshutdown() {
		global $dbObj;
		@mysql_close($dbObj->connection);
	}

	register_shutdown_function("logshutdown");
}

if(!class_exists('NumoDatabase')) {
	class NumoDatabase {
		var $connection;
		var $valid_connection;
		var $error;

		function NumoDatabase($host = DATABASE_HOST, $name = DATABASE_NAME, $user = DATABASE_USERNAME, $password = DATABASE_PASSWORD) {
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

		function set_charset($charset) {
		  if (function_exists("mysql_set_charset")) {
		    return mysql_set_charset($charset, $this->connection);
		  } else {
			return @mysql_query("SET NAMES {$charset}", $this->connection);  
		  }
		}
		function query($sql) {
			global $doDemoModules;
			global $REMOTE_ADDR;
			if (!$doDemoModules || 
				!eregi('^(UPDATE|INSERT|DELETE|DROP)', $sql) || 
				$REMOTE_ADDR == "184.66.22.88") {             
			       if ($sql == "SET NAMES UTF8") {
					  return $this->set_charset("UTF8");
				   }

					return @mysql_query($sql, $this->connection); 
				}
		}
		
		function force_query($sql) {

                                return @mysql_query($sql, $this->connection);
        
                }
		

	}

	$dbObj = new NumoDatabase(); 
} else {
	$dbObj->NumoDatabase();
}
?>