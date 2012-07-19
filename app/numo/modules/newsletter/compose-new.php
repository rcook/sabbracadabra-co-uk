<?php
if($_POST['cmd'] == "create") {
	$sql = "SELECT header, footer FROM newsletter_settings WHERE site_id='".NUMO_SITE_ID."'";
	$settingResult = $dbObj->query($sql);
    $dbObj->query("SET NAMES UTF8");

	if($setting = mysql_fetch_array($settingResult)) {
		$headerDefault = $setting['header'];
		$footerDefault = $setting['footer'];

		$sql = "INSERT INTO newsletter_messages (site_id,layout,title,summary,status,message) VALUES ('".NUMO_SITE_ID."','".$_POST['layout'].".htm','".str_replace("'","&#39;",$_POST['title'])."','".str_replace("'","&#39;",$_POST['summary'])."',0,'header=".str_replace("'","&#39;",$headerDefault)."[NUMO|END]footer=".str_replace("'","&#39;",$footerDefault)."[NUMO|END]')";
		//print $sql;
		//exit;
		$dbObj->query($sql);

		$sql = "SELECT LAST_INSERT_ID() as 'newsletter_id'";
		$result = $dbObj->query($sql);

		if($row = mysql_fetch_array($result)) {
			//redirect to edit page for new newsletter message
			header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/compose/?id='.$row['newsletter_id']);
		}	else {
			//redirect to manage newsletters page
			header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage/');
		}
	}
}
?>
<style>
ul.form_display li div {border: 1px solid #999; padding: 0px; margin: 0px 5px 5px 0px;text-align: center; float: left;}
ul.form_display li div:hover {border: 1px solid #DDD;}
ul.form_display li div.selected {border: 1px solid #000;}
ul.form_display li div a img {border: 0px; display: block;}
ul.form_display li a {color: #999; text-decoration: none; display: block;}
ul.form_display li a:hover {color: #DDD;}
ul.form_display li div.selected a {color: #000;}
ul.form_display li input.text_input {width: 300px;}
ul.form_display li textarea {width: 300px; height: 50px;}
</style>
<h2>Compose Newsletter</h2>
<form method="post">
<ul class="form_display">
	<li><label for="title">Title:</label><input type="text" class="text_input" id="title" name="title" value="" autocomplete="off" /></li>
	<li><label for="summary">Summary:</label><textarea id="summary" name="summary"></textarea></li>
	<li><label for="layout">Layout:</label>
	<?php
	//default layout option if nothing gets selected
	$defaultLayout = "";

	//try to open the MODULES folder
	if ($newsletterLayoutFolder = @opendir(MODULES_FOLDER_NAME."/newsletter/layouts/")){
		//cycle thru each file in the MODULES folder
		while ($newsletterLayoutName = readdir($newsletterLayoutFolder)) {
			//ingore if item named with periods or starts with an underscore
			if($newsletterLayoutName == "." || $newsletterLayoutName == ".." || substr($newsletterLayoutName, 0, 1) == "_"){
				continue; //exit WHILE loop
			}

			//check to see if item is a folder
			if(is_file(MODULES_FOLDER_NAME."/newsletter/layouts/".$newsletterLayoutName)){
				$layoutName = substr($newsletterLayoutName,0,strrpos($newsletterLayoutName, "."));

				if($defaultLayout == "") {
					$defaultLayout = $layoutName;
				}

				$layoutImageFilePath = "modules/newsletter/images/layout_option_".$layoutName.".jpg";

				if(!file_exists($layoutImageFilePath)) {
					$layoutImageFilePath = "modules/newsletter/images/layout_option_custom.jpg";
				}

				$layoutNameCleaned = ucwords(str_replace("_"," ", $layoutName))." Layout";

	?>
	<div id="newsletter_layout_option_<?=$layoutName?>__div"><a href="javascript:changeSelectedLayout('<?=$layoutName?>')"><img id="newsletter_layout_option_<?=$layoutName?>" src="<?=$layoutImageFilePath?>" alt="<?=$layoutNameCleaned?>" title="<?=$layoutNameCleaned?>" /><?=$layoutNameCleaned?></a></div>
	<?php
			}
		}
	}
	?>
	</li>
	<li><label for="form_input_submit">&nbsp;</label><input type="submit" name="nocmd" id="form_input_submit" value="Create" /></li>
</ul>
<input type="hidden" name="layout" id="selected_layout" value="<?=$defaultLayout?>" />
<input type="hidden" name="cmd" value="create" />
</form>
<script>
function changeSelectedLayout(layout) {
	//check if previously selected option exists
	if(document.getElementById("newsletter_layout_option_"+document.getElementById("selected_layout").value)) {
		//remove class style
		document.getElementById("newsletter_layout_option_"+document.getElementById("selected_layout").value+"__div").className = "";
	}

	//set selected layout option for input
	document.getElementById("selected_layout").value = layout;

	//change class style for layout
	document.getElementById("newsletter_layout_option_"+layout+"__div").className = "selected";
}
</script>

