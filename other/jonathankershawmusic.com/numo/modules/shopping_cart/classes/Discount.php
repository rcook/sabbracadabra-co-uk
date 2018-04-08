<?php
class Discount {
	function Discount($id) {
	  $this->attributes['id'] = $id;
	  $this->attributes['discount_tax_removed'] = 0;
	  $this->qualifiedProducts = array();;
	  $this->load();
	
	}
	
	function load() {
	  global $dbObj;
	  
	  $query = "SELECT * FROM shopping_cart_discount WHERE id='{$this->attributes['id']}' AND site_id='".NUMO_SITE_ID."'";
	  $result = $dbObj->query($query);
	  $this->attributes = mysql_fetch_array($result);
	}

    function remove() {
	  global $dbObj;
	  $update = "UPDATE shopping_cart_discount SET status=-1 WHERE id='{$this->attributes['id']}' AND site_id='".NUMO_SITE_ID."'";
	  $dbObj->query($update);
	  $this->attributes['status'] = -1;
	}
    function pause() {
	  global $dbObj;
	  $update = "UPDATE shopping_cart_discount SET status=0 WHERE id='{$this->attributes['id']}' AND site_id='".NUMO_SITE_ID."'";
	  $dbObj->query($update);
	  $this->attributes['status'] = 0;
	}
    function reenable() {
	  global $dbObj;
	  $update = "UPDATE shopping_cart_discount SET status=1 WHERE id='{$this->attributes['id']}' AND site_id='".NUMO_SITE_ID."'";
	  $dbObj->query($update);
	  $this->attributes['status'] = 1;
	}
	function getName() {
	  return $this->attributes['discount_name'];	
	}
	
	function getDescription() {
	  $description = "";
	  if ($this->attributes['visibility'] == "2") {
		$discription = "Coupon ";  
	  }
	  if ($this->attributes['amount_type'] == "0") {
		 $description = "{$this->attributes['currency_symbol']}{$this->attributes['amount']} Instant Rebate";  
	  } else {
		 $description = rtrim(trim($this->attributes['amount'], '0'), '.')."% off of {$this->attributes['currency_symbol']}".number_format($this->attributes['original_amount'], 2, '.', '');
	  }
	  return $description;
	}
	function canBeCobmbined() {
	  return $this->attributes['compounding'] == "1";	
	}
	

	
	function calculateRebateAmount($amount, $store = true) {
	  $calculatedAmount = 0;
	  if ($this->attributes['amount_type'] == "0") {
		  $calculatedAmount = $this->attributes['amount'];

	  } else if ($this->attributes['amount_type'] == "1") {
		  $calculatedAmount = $amount * ($this->attributes['amount']/100);
	  }
	  
	  if ($store) {
	    $this->attributes['original_amount'] = $amount;
		$this->attributes['rebate_amount']   = $calculatedAmount;
	  }
	  return $calculatedAmount;
	}
	
	function getRebateAmount($amount = "") {
	  if ($amount == "") {
	    return $this->attributes['rebate_amount'];	
	  } else {
		return $this->calculateRebateAmount($amount, false);  
	  }
	}
	
	function update($data) {
	  global $dbObj;
	  $startDate = date("Y-m-d H:i", strtotime($_POST['start_date']));
	  $endDate = date("Y-m-d H:i", strtotime($_POST['end_date']));
	  if ($data['qualifier_scope'] == "0") {
		 $scopeExtensionID = "";  
		
	  } else if ($data['qualifier_scope'] == "1") {
		 $scopeExtensionID = implode(",", $_POST['scope_extension_id__categories']);  
	  } else if ($data['qualifier_scope'] == "2") {
		 $scopeExtensionID = implode(",", $_POST['scope_extension_id__products']);  
	  }
 	
	  $update = "UPDATE shopping_cart_discount SET start_date='{$startDate}', end_date='{$endDate}', discount_name='{$_POST['discount_name']}',
												   visibility='{$_POST['visibility']}', amount_type='{$_POST['amount_type']}', qualifier_scope='{$_POST['qualifier_scope']}',
												   amount='{$_POST['amount']}', discount_scope='{$_POST['discount_scope']}', scope_quantifier='{$_POST['scope_quantifier']}',
												   scope_extension_id='{$scopeExtensionID}', discount_type='{$_POST['discount_type']}' WHERE site_id='".NUMO_SITE_ID."' AND id='{$this->attributes['id']}'";
	  $dbObj->query($update);
	  //print $update;
	  //exit;
	  // validate the coupon code
	  if ($data['visibility'] == "2") {
		  $couponCode = str_replace(" ", "-", trim($data['access_qualifier-coupon']));
	      $update = "UPDATE shopping_cart_discount SET access_qualifier='{$couponCode}' WHERE site_id='".NUMO_SITE_ID."' AND id='{$this->attributes['id']}'";
		  $dbObj->query($update);
	  }
	  
	  
	  $this->load();
	}
}
	