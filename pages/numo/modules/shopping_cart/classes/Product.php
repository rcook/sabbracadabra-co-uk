<?php
class Product {
  var $id;

  function Product($id = 0) {
  	$this->id = $id;
  }

  function create($info) {
  	global $_SERVER;
  	global $dbObj;

		$activationUrl = "N/A";

		//initialize variables
		$fieldsList = "";
		$valuesList = "";

		//type id
		if(array_key_exists('type_id', $info)) {
			$fieldsList .= ",type_id";
			$valuesList .= ",'".$info['type_id']."'";
		}

		//pending
		if(array_key_exists('pending', $info)) {
			$fieldsList .= ",pending";
			$valuesList .= ",'".$info['pending']."'";
		}

		//status
		if(array_key_exists('status', $info)) {
			$fieldsList .= ",status";
			$valuesList .= ",'".$info['status']."'";
		}

		//shipping cost
		if(array_key_exists('shipping', $info)) {
			$fieldsList .= ",shipping";

			if(is_numeric($info['shipping'])) {
				$valuesList .= ",'".number_format($info['shipping'], 2, '.', '')."'";
			} else {
				$valuesList .= ",'0.00'";
			}
		}

		//shipping 2 cost
		if(array_key_exists('shipping2', $info)) {
			$fieldsList .= ",shipping2";

			if(is_numeric($info['shipping2'])) {
				$valuesList .= ",'".number_format($info['shipping2'], 2, '.', '')."'";
			} else {
				$valuesList .= ",'0.00'";
			}
		}

		//activated
		if(array_key_exists('account_id', $info)) {
			$fieldsList .= ",account_id";
			$valuesList .= ",'".$info['account_id']."'";
		}

		//slots
		foreach($info as $key => $value) {
			if(substr($key,0,5) == "slot_" && $key != "slot_6") {
				if(is_array($value)) {
					$newValue = "";

					foreach($value as $k => $v) {
						$newValue .= $v.",";
					}

					$value = substr($newValue,0,-1);
				}

				$fieldsList .= ",".$key;
				$valuesList .= ",'".$value."'";
			}
			// configure slot_6 which is the special configuration for shipping type
			if ($key == "slot_5") {
				
				if ($value == "2" && $_POST['slot_6'] != "simple") {
				  $fValue = "accounts:".$_POST['egood_config_accounts'];
				  
				  $configField = 'egood_config_'.$_POST['slot_6'];
				  if (is_array($_POST["$configField"])) {
					$fValue .= "\r\n".$_POST['slot_6'].":";

					foreach ($_POST["$configField"] as $k => $v) {
						//print $k."=".$v."<br>";
					  $fValue .= $v.",";
					}
					$fValue = trim ($fValue, ",");
				  } else if ($_POST["$configField"] != "") {
					  if ($fValue != "") {
						    						  $fValue .= "\r\n";

						  $fValue .= $_POST['slot_6'].":".$_POST["$configField"];

					  } else {
											$fValue .= $_POST['slot_6'].":".$_POST["$configField"];
  						  $fValue .= "\r\n";

					  }
				  }

				  
				  
				} else {
				  $fValue = "";
				}
				$fieldsList .= ",slot_6";
				$valuesList .= ",'".$fValue."'";
				

			}
		}

		//insert account into database
		$sql = "INSERT INTO `shopping_cart_products` (`site_id`,`when_created`".$fieldsList.") VALUES (".NUMO_SITE_ID.",'".date("y/m/d H:i:s")."'".$valuesList.")";
		//print $sql."<br>";
		//exit;

		$dbObj->query($sql);

		$sql = "SELECT LAST_INSERT_ID() as 'product_id'";
		$result = $dbObj->query($sql);

		if($row = mysql_fetch_array($result)) {
			return $row['product_id'];
		}
  }

	function update($info) {
  	global $dbObj;

  	//update account information
		foreach($info as $key => $value) {
			//custom slot fields
			if(substr($key,0,5) == "slot_" && $key != "slot_6") {
				if(is_array($value)) {
					$newValue = "";

					foreach($value as $k => $v) {
						$newValue .= htmlentities($v).",";
					}

					$value = substr($newValue,0,-1);
				}

				//check to see if PHP is set to automatically escape characters
				if (!get_magic_quotes_gpc() && false) {
					//if PHP isn't set to escape POST/GET information, manually escape value
					$setString .= ",".$key."='".addslashes($value)."'";
				} else {
					$setString .= ",".$key."='".$value."'";
				}


			//pending and activated values
			} else if($key == "pending" || $key == "status" || $key == "account_id") {
				$setString .= ",".$key."=".$value;
			} else if($key == "shipping" || $key == "shipping2") {
				if(is_numeric($value)) {
					$setString .= ",".$key."='".number_format($value, 2, '.', '')."'";
				} else {
					$setString .= ",".$key."='0.00'";
				}
			}
			
			if ($key == "slot_5") {
				
				if ($value == "2" && $_POST['slot_6'] != "simple") {
					//print "yes!";
				  $fValue = "accounts:".$_POST['egood_config_accounts'];
				  
				  $configField = 'egood_config_'.$_POST['slot_6'];
				 // print $configField;
				  if (is_array($_POST["$configField"])) {
					$fValue .= "\r\n".$_POST['slot_6'].":";

					foreach ($_POST["$configField"] as $k => $v) {
						//print $k."=".$v."<br>";
					  $fValue .= $v.",";
					}
					$fValue = trim ($fValue, ",");
				  } else if ($_POST["$configField"] != "") {
					  if ($fValue != "") {
						    						  $fValue .= "\r\n";

						  $fValue .= $_POST['slot_6'].":".$_POST["$configField"];

					  } else {
											$fValue .= $_POST['slot_6'].":".$_POST["$configField"];
  						  $fValue .= "\r\n";

					  }
				  }
				  
				  
				} else {
				  $fValue = "";
				}
				$setString .= ",slot_6='{$fValue}'";
				//$fieldsList .= ",slot_6";
				//$valuesList .= ",'".$fValue."'";
				

			}
		}

		//remove first comma from string
		$setString = substr($setString,1);

		//update account information
		$sql = "UPDATE `shopping_cart_products` SET ".$setString." WHERE id='".$this->id."'";
		//print $sql."<br>";
		//exit;
		$dbObj->query($sql);
	}

	function remove() {
		global $dbObj;

		$sql = "DELETE FROM `shopping_cart_products` WHERE id='".$this->id."'";
		//print $sql."<br>";
		$dbObj->query($sql);

		$sql = "SELECT file_name FROM `shopping_cart_product_images` WHERE listing_id='".$this->id."'";
		//print $sql."<br>";
		$results = $dbObj->query($sql);

		while($row = mysql_fetch_array($results)) {
			@unlink("modules/shopping_cart/uploads/".$row['file_name']);
		}

		$sql = "DELETE FROM `shopping_cart_product_images` WHERE listing_id='".$this->id."'";
		//print $sql."<br>";
		$dbObj->query($sql);
	}
}
?>