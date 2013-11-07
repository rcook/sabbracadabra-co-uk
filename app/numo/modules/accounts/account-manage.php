<?php
function list_account_group_move_options($currentId) {
	global $dbObj;
	global $groupData;


	$returnStr = "";

	$sql = "SELECT * FROM `types` WHERE site_id='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	while($row = mysql_fetch_array($results)) {
		$groupID = $row['id'];
		$groupData["$groupID"] = $row;
		if($currentId == $row['id']) {
			$returnStr .= "<option value=\"".$row['id']."\" selected=\"selected\">".$row['name']."</option>";
		} else {
			$returnStr .= "<option value=\"".$row['id']."\">Move to '".$row['name']."'</option>";
		}
	}

	return $returnStr;
}
	global $_COOKIE;

//foreach ($_POST as $x => $y) {
//print $x."=".$y."<br>";
//}
//remove account
if($_POST['cmdb'] == "remove") {
	$accountObj = new Account($_POST['account_id']);
	$accountObj->remove();
} else if($_POST['cmd'] == "move_account") {
	//print "account id: ".$_POST['account_id']."<br>";
	//print "current type id: ".$_POST['current_type_id']."<br>";
	//print "new type id: ".$_POST['type_id']."<br>";

    $accountObj = new Account($_POST['account_id']);
	$accountObj->changeGroup($_POST['type_id']);
}

$orderByOptions["1"] = "a.slot_4";
$orderByOptions["2"] = "a.slot_3";
$orderByOptions["3"] = "a.slot_1";
$orderByOptions["4"] = "t.name";


if ($_POST['order_by'] == "") {
  if ($_COOKIE['search__order_by'] != "") {
    $_POST['order_by'] = $_COOKIE['search__order_by'];
  } else {
    $_POST['order_by'] = "1";
  }

  $orderBy = $_POST['order_by'];
} else {
  $orderBy = $_POST['order_by'];
}

if ($_POST['results_per_page'] == "") {
  if ($_COOKIE['search__results_per_page'] != "") {
    $_POST['results_per_page'] = $_COOKIE['search__results_per_page'];
  } else {
    $_POST['results_per_page'] = 10;
  }

  $resultsPerPage = $_POST['results_per_page'];
} else {
  $resultsPerPage = $_POST['results_per_page'];
}


if ($_POST['page'] == "" || $_POST['page'] < 0) {
  if ($_COOKIE['search__page'] != "" && $_POST['page'] == "") {
    $_POST['page'] = $_COOKIE['search__page'];
  } else {
    $_POST['page'] = 1;
  }
  $currentPage = $_POST['page'];
} else {
  $currentPage = $_POST['page'];

}

if ($_POST['name'] != "") {
  setcookie("search__name", $_POST['name']);
} else if ($_POST['name'] == "" && $_POST['cmd'] == "search") {
  setcookie("search__name", "");
} else if ($_POST['name'] == "" && $_POST['cmd'] == "search") {
  $_POST['name'] = $_COOKIE['search__name'];
}

if ($_POST['email'] != "") {
  setcookie("search__email", $_POST['email']);
} else if ($_POST['email'] == "" && $_POST['cmd'] == "search") {
  setcookie("search__email", "");
} else if ($_POST['name'] == "" && $_POST['cmd'] == "search") {
  $_POST['email'] = $_COOKIE['search__email'];
}

if ($_POST['username'] != "") {
  setcookie("search__username", $_POST['username']);
} else if ($_POST['username'] == "" && $_POST['cmd'] == "search") {
  setcookie("search__username", "");
} else if ($_POST['username'] == "" && $_POST['cmd'] == "search") {
  $_POST['username'] = $_COOKIE['search__username'];
}


$offset = $resultsPerPage * ($currentPage - 1);

if ($offset < 0) {
  $currentPage = 1;
  $offset = 0;
}
setcookie("search__page", $currentPage);
setcookie("search__results_per_page", $resultsPerPage);
list_account_group_move_options(0);
?>
<script>
function sortOnColumn(orderBy) {
	document.forms['main_submit'].order_by.value = orderBy;
  document.forms['main_submit'].submit();

}
</script>

<ul class="breadcrumb">
  <li><a href="./">Home</a> <span class="divider">/</span></li>
  <li class="active">Accounts</li>
  <li>&nbsp; <a href="module/accounts/account-create/" style='margin-top: -2px;' class='btn btn-success btn-mini'>Create Account</a></li>
</ul>

<h2>Manage Accounts</h2>
<form method="post" name='main_submit' id='main_submit' >
  <input type='hidden' id='page' name='page' value='0' />
  <input type='hidden' id='current_page' name='current_page' value='<?=$currentPage?>' />
  <input type='hidden' id='order_by' name='order_by' value='<?=$orderBy?>' />
	<fieldset>
	<legend>Search</legend>
    <table class="table table-striped">
      <tr>
        <th>Name</th>
        <th>Email Address</th>
        <th>Username</th>
        <th>Results Per Page</th>
        <th>&nbsp;</th>
      </tr>
      <tr>
        <td><input type="text" id="name" name="name" value="<?=$_POST['name']?>" /></td>
        <td><input type="text" id="email" name="email" value="<?=$_POST['email']?>" /></td>
        <td><input type="text" id="username" name="username" value="<?=$_POST['username']?>" /></td>
        <td><select id="results_per_page" name="results_per_page" >
			  <option>10</option>
			  <option <?php if ($_POST['results_per_page'] == "25") { print "selected"; } ?>>25</option>
			  <option <?php if ($_POST['results_per_page'] == "50") { print "selected"; } ?>>50</option>
			  <option <?php if ($_POST['results_per_page'] == "100") { print "selected"; } ?>>100</option>
			</select>
		</td>
		<td><input class='btn btn-primary ' type="submit" name="nocmd" id="submit_cmd" value="Search" /></td>
      </tr>
    </table>
	</fieldset>
	<input type="hidden" name="cmd" value="search" />
</form>
<?php



//if($_POST['cmd'] == "search") {
// get total results count
$sql = "SELECT count(*) total_results FROM accounts a, `types` t WHERE a.slot_1 LIKE '%".$_POST['username']."%' AND a.slot_3 LIKE '%".$_POST['email']."%' AND a.slot_4 LIKE '%".$_POST['name']."%' AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."'";
$results = $dbObj->query($sql);
$totalRec = mysql_fetch_array($results);
$totalResults = $totalRec['total_results'];
mysql_free_result($results);






// now just get the results we need
$sql = "SELECT a.is_admin, a.id, a.slot_1, a.slot_3, a.slot_4, t.name, t.id as type_id, a.pending, a.activated FROM accounts a, `types` t WHERE a.slot_1 LIKE '%".$_POST['username']."%' AND a.slot_3 LIKE '%".$_POST['email']."%' AND a.slot_4 LIKE '%".$_POST['name']."%' AND a.type_id=t.id AND t.site_id='".NUMO_SITE_ID."' ORDER BY ".$orderByOptions["{$orderBy}"]." LIMIT {$offset}, {$resultsPerPage}";
$results = $dbObj->query($sql);

//counter for odd/even styling
$oddEvenCounter = 0;
$resultsThisPage = mysql_num_rows($results);
?>
<h3>Search Results</h3>
<?php
echo '<table class="table table-striped">';
echo '<tr>';
echo '<th onclick="sortOnColumn(1)" '.($orderBy == 1 ? 'class="highlight_label"' : '').'>Name</th>';
echo '<th onclick="sortOnColumn(2)" '.($orderBy == 2 ? 'class="highlight_label"' : '').'>Email Address</th>';
echo '<th onclick="sortOnColumn(3)" '.($orderBy == 3 ? 'class="highlight_label"' : '').'>Username</th>';
echo '<th onclick="sortOnColumn(4)" '.($orderBy == 4 ? 'class="highlight_label"' : '').'>Group</th>';
echo '<th class="nosort">Flags</th><th class="nosort">&nbsp</th></tr>';

while($row = mysql_fetch_array($results)) {
	echo '<tr class="'.($oddEvenCounter % 2 == 0 ? 'even' : 'odd').'">';
	echo '<td>'.$row['slot_4'].'</td>';
	echo '<td>'.$row['slot_3'].'</td>';
	echo '<td>'.$row['slot_1'].'</td>';

	echo '<td><form style="margin:0px;" method="post"><input type="hidden" id="page" name="page" value="'.$currentPage.'" /><input type="hidden" name="results_per_page" value="'.$resultsPerPage.'" /><select ';
	if (($row['is_admin'] == '1' && $_SESSION['is_admin'] == '1') || ($row['is_admin'] != 1 && $access->hasAccess("accounts", "account-edit"))) {
	  echo 'onchange="this.form.submit()"';
	} else {
	  echo 'readonly disabled';
	}
	echo ' name="type_id">'.list_account_group_move_options($row['type_id']).'</select><input type="hidden" name="current_type_id" value="'.$row['type_id'].'" /><input type="hidden" name="account_id" value="'.$row['id'].'" /><input type="hidden" name="cmd" value="move_account" /></form></td>';
		echo '<td>';
		//print $groupData["{$row['type_id']}"]['require_approval'];
		//if ($groupData["{$row['type_id']}"]['require_approval'] == 1) {
			if ($row['pending'] == 1) {
				print "Pending Approval";
			}
		//}
		//if ($groupData["{$row['type_id']}"]['require_activation'] == 1) {
			// pending == 3 == subscriber
			if ($row['activated'] != 1) {
				if ($groupData["{$row['type_id']}"]['require_approval'] == 1 && $row['pending'] != 0 ) {
					print ",";
				}
				print "Pending Activation";
			}
		//}


	echo '</td>';
	echo '<td style="text-align: right">';
	if (($row['is_admin'] == '1' && $_SESSION['is_admin'] == '1') || ($row['is_admin'] != 1 && $access->hasAccess("accounts", "account-edit"))) {
		echo '<a class="btn btn-primary" href="module/'.$_GET['m'].'/account-edit/?id='.$row['id'].'">Edit</a> ';
	    echo '<a class="btn btn-danger" href="module/'.$_GET['m'].'/'.$_GET['i'].'/" onclick="return confirmRemove(\''.$row['id'].'\', \''.$row['is_admin'].'\');">Remove</a>';
	}
	echo '</td>';
	echo '</tr>';

	$oddEvenCounter++;
}

echo '</table>';
//}
?>

<style>
/*
form#remove_account { padding-top: 75px; }
.bttm_submit_button {position: fixed; bottom: 0px; right: 0px; background: #779FE1; border-top: 1px solid #2A61BD; width: 100%; height: 100px; padding: 0px 20px; margin: 0px; }

.bttm_submit_button input {background: #EEEEEE; color: #333; border: 1px solid #333; height: 35px; margin: 8px 0px 10px 0px}
.bttm_submit_button input.page-buttons { height: 20px; margin: 5px 0px 5px 10px;}
.bttm_submit_button input.page-button-selected { background: #444444; color: #ffffff; height: 20px; margin: 5px 0px 5px 10px;}
.bttm_submit_button input:hover {background: #bbb; color: #333; border: 1px solid #333; cursor: pointer;}
*/
div.pagination a { cursor: pointer; }
</style>
<?php
$totalPages = ceil($totalResults / $resultsPerPage);
?>
<div class='bttm_submit_button'>
<?php if ($currentPage > 1) { ?><input style='margin-left: 25px; float: left' type='button' value='< Back' onclick='jumpToPreviousResultsPage(this)' /><?php } ?>
<?php if ($currentPage < $totalPages) { ?><input style='float: right' type='button' value='Next >' onclick='jumpToNextResultsPage(this)' /><?php } ?>
<div style='text-align: center; padding-top: 3px;'>Displaying results <?=($offset + 1)?> to <?=($offset+$resultsThisPage)?> of <?=$totalResults?>
<div class='pagination'>
  <ul>
<?php
if ($currentPage < 5) {
  $pageNavStart = 1;
} else if ($currentPage > $totalPages - 12) {
  $pageNavStart = $totalPages - 14;
} else {
  $pageNavStart = $currentPage - 3;
}
$totalPagesToDisplay = 15;
for ($page = $pageNavStart; $page <= $totalPages && $page < ($pageNavStart+$totalPagesToDisplay); $page++) {
  print "<li ";
  if ($page == $currentPage) {
    print "class='active' ";
  } else {
    print "class='' ";
  }
  print ">";
  print "<a onclick='jumpToResultsPage(this)'>{$page}</a>";

  print "</li>";
}
?>
</ul>
</div>
</div></div>

<script>
function jumpToResultsPage(button) {
  document.forms['main_submit'].page.value = button.innerHTML;
  document.forms['main_submit'].submit();
}

function jumpToNextResultsPage(button) {
  document.forms['main_submit'].page.value = parseInt(document.forms['main_submit'].current_page.value) + 1;
  document.forms['main_submit'].submit();
}
function jumpToPreviousResultsPage(button) {
  document.forms['main_submit'].page.value = parseInt(document.forms['main_submit'].current_page.value) - 1;
  document.forms['main_submit'].submit();
}

</script>

<form method="post" name="remove_account" id="remove_account">
<input type="hidden" name="account_id" value="" />
<input type="hidden" name="name" value="<?=$_POST['name']?>" />
<input type="hidden" name="email" value="<?=$_POST['email']?>" />
<input type="hidden" name="username" value="<?=$_POST['username']?>" />
<input type="hidden" name="cmdb" value="remove" />
<input type="hidden" name="cmd" value="search" />
</form>
<script>
function confirmRemove(accountId, isAdmin) {
	if (isAdmin == 1) {
	  alert("You cannot delete an account that is an administrator.  You must first remove administrative priviledges before deleting this account.");
	} else if(confirm("Are you absolutely sure you wish to remove this account?")) {
		document.forms['remove_account'].account_id.value = accountId;
		document.forms['remove_account'].submit();
	}

	return false;
}
</script>
