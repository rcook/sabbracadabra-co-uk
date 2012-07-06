<?php
header('Content-type: text/xml');

$link = "http://".NUMO_SERVER_ADDRESS;

print '<?xml version="1.0" ?>';
	
?>
<rss version="2.0">
<channel>
	<?php
	$sql = "SELECT rss_title,rss_description FROM newsletter_settings WHERE site_id=".NUMO_SITE_ID;
	//print $sql;
	$results = $dbObj->query($sql);

	if($row = mysql_fetch_array($results)) {
	?>
	<title><?=$row['rss_title']?></title>     
	<link><?=$link?></link>
	<description><?=$row['rss_description']?></description> 
	<?php
	}
	
	$sql = "SELECT * FROM newsletter_messages WHERE status=1 AND site_id=".NUMO_SITE_ID." ORDER BY id desc";
	//print $sql;
	$results = $dbObj->query($sql);

	while($row = mysql_fetch_array($results)) {
	?>
	<item>
		<title><?=$row['title']?></title>       
		<link><?=$link.substr(NUMO_FOLDER_PATH,0, -5)."manage.numo?module=newsletter&amp;component=view&amp;id=".$row['id']?></link>
		<description><?=html_entity_decode(strip_tags($row['summary']))?></description>
	</item>
	<?php
	}
?>
</channel>
</rss>
<?php
exit();
?>