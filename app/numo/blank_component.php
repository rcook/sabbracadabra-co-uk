<?php
print "[NUMO.".$_GET['module'].": ".$_GET['component'];

if ($_GET['id'] != "") {
  print "(id=".$_GET['id'].")";
}
print "]";
?>