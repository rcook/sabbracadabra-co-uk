<?php 
if (REMOTE_SERVICE === true && false) {
print "<numo module=\"".$_GET['module']."\" component=\"".$_GET['component']."\"";

if ($_GET['id'] != "") {
  print "params=\"id=".$_GET['id']."\"";
} else if ($_GET['args'] != "") {
	print "params=\"".urldecode($_GET['args'])."\"";
}
print "></numo>";
} else {
print "[NUMO.".$_GET['module'].": ".$_GET['component'];

if ($_GET['id'] != "") {
  print "(id=".$_GET['id'].")";
} else if ($_GET['args'] != "") {
	print "(".urldecode($_GET['args']).")";
}
print "]";
}
?>