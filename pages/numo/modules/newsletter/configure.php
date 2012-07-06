<?php
$toolbarOption = array();
$toolbarOption['bold'] = "Bold";
$toolbarOption['italic'] = "Italic";
$toolbarOption['underline'] = "Underline";
$toolbarOption['separator0'] = "Separator";
$toolbarOption['justifyleft'] = "Justify Left";
$toolbarOption['justifycenter'] = "Justify Center";
$toolbarOption['justifyright'] = "Justify Right";
$toolbarOption['separator1'] = "Separator";
$toolbarOption['insert_image'] = "Insert Image";
$toolbarOption['create_link'] = "Create Link";

function list_account_group_move_options($currentId = 0) {
	global $dbObj;

	$returnStr = "";

	$sql = "SELECT id, name FROM `types` WHERE site_id='".NUMO_SITE_ID."'";
	//print $sql."<br>";
	$results = $dbObj->query($sql);

	while($row = mysql_fetch_array($results)) {
		if($currentId == $row['id']) {
			$returnStr .= "<option value=\"".$row['id']."\" selected=\"selected\">".$row['name']."</option>";
		} else {
			$returnStr .= "<option value=\"".$row['id']."\">".$row['name']."</option>";
		}
	}

	return $returnStr;
}
?>
<style>
div.layout_options {position: relative; clear: both; text-align: center;}
div.layout_options div {border: 1px solid #999; padding: 0px; margin: 0px 5px 5px 0px;text-align: center;display: inline-block; float: left;}
div.layout_options div:hover {border: 1px solid #000;}
div.layout_options a img {border: 0px; display: block;}
div.layout_options a {color: #999; text-decoration: none; display: block;}
div.layout_options a:hover {color: #000;}
ul.form_display li input.text_input {width: 300px;}
td#compose_toolbar {background: #ccc url('modules/newsletter/images/background_silver.jpg') repeat-x;padding: 0px;}
td#compose_toolbar img:hover {cursor: pointer;}
#compose_display_window {border-collapse:collapse;border: 1px solid #999; background: #f9f9f9 url('modules/newsletter/images/compose_background.jpg') repeat-y top right;}
#compose_display_window tr td.compose_display {border: 1px solid #999; border-style: inset;padding: 3px 5px;}
</style>
<h2>Available Layouts</h2>
<p style='font-style: italic; color: #444;'>Click on a layout below to configure the background and text colors used.</p>
<div class="layout_options">
<?php
//default layout option if nothing gets selected
$defaultLayout = "";

//try to open the MODULES folder
if($newsletterLayoutFolder = @opendir(MODULES_FOLDER_NAME."/newsletter/layouts/")){
	//cycle thru each file in the MODULES folder
	while($newsletterLayoutName = readdir($newsletterLayoutFolder)) {
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
<div><a href="module/newsletter/configure-layout/?layout=<?=$layoutName?>"><img src="<?=$layoutImageFilePath?>" alt="<?=$layoutNameCleaned?>" title="<?=$layoutNameCleaned?>" /><?=$layoutNameCleaned?></a></div>
<?php
		}
	}
}
?>
</div>
<div style="clear: both;"></div>
<?php
if($_POST['cmd'] == "save") {
	$_POST['header'] = str_replace('&gt;','>', str_replace('&lt;','<', $_POST['header']));
	$_POST['footer'] = str_replace('&gt;','>', str_replace('&lt;','<', $_POST['footer']));
	$sql = "UPDATE newsletter_settings SET default_account_group='".$_POST['default_account_group']."',rss_title='".$_POST['rss_title']."',rss_description='".$_POST['rss_description']."',header='".$_POST['header']."', footer='".$_POST['footer']."', smtp_host='".$_POST['smtp_host']."',smtp_user='".$_POST['smtp_username']."', smtp_password='".$_POST['smtp_password']."',smtp_port='".$_POST['smtp_port']."' WHERE site_id='".NUMO_SITE_ID."'";
	//print $sql;
	$dbObj->query($sql);
}

$sql = "SELECT * FROM newsletter_settings WHERE site_id='".NUMO_SITE_ID."'";
$results = $dbObj->query($sql);

while($row = mysql_fetch_array($results)) {
?>
<form method="post">
<h2>Default Account Group</h2>
<p style='font-style: italic; color: #444;'>Please select the account group you would like new subscribers to be added to if they do not already have an account.</p>
<ul class="form_display">
	<li><label for="default_account_group">Account Group:</label><select id="default_account_group" name="default_account_group"><option value="0">- SELECT -</option><?=list_account_group_move_options($row['default_account_group'])?></select></li>
</ul>

<h2>RSS Feed Information</h2>
<ul class="form_display">
	<li><label for="rss_title">Title:</label><input type="text" class="text_input" id="rss_title" name="rss_title" value="<?=$row['rss_title']?>" /></li>
	<li><label for="rss_description">Description:</label><textarea id="rss_description" style="width: 300px; height: 70px;" name="rss_description"><?=$row['rss_description']?></textarea></li>
</ul>

<h2>Default Newsletter Header</h2>
<table id="compose_display_window">
	</tr>
	<tr>
	<td id="compose_toolbar" class="compose_display">
	<?php
	foreach($toolbarOption as $cmd => $name) {
		if($name == "Separator") {
		?>
		<img src="modules/newsletter/images/separator.gif" alt=":" border="0" unselectable="on" width="2" height="18" hspace="2" />
		<?php
		} else {
		?>
		<img src="modules/newsletter/images/<?=$cmd?>.gif" border="0" unselectable="on" title="<?=$name?>" class="buttonEditor" onmouseover="this.className='buttonEditorOver'; this.src='modules/newsletter/images/<?=$cmd?>_on.gif';" onmouseout="this.className='buttonEditor'; this.src='modules/newsletter/images/<?=$cmd?>.gif';" onclick="process_toolbar_command('<?=$cmd?>');" width="20" height="20" />
		<?php
		}
	}
	?>
	</td>
	</tr>
	<tr>
	<td style="background: #fff" class="compose_display">
	<div contentEditable="true" id="newsletter_header_default" style="width: 610px; height: 150px; overflow: auto;"><?=html_entity_decode(stripslashes($row['header']))?></div>
	</td>
	</tr>
</table>

<h2>Default Newsletter Footer</h2>
<table id="compose_display_window">
	</tr>
	<tr>
	<td id="compose_toolbar" class="compose_display">
	<?php
	foreach($toolbarOption as $cmd => $name) {
		if($name == "Separator") {
		?>
		<img src="modules/newsletter/images/separator.gif" alt=":" border="0" unselectable="on" width="2" height="18" hspace="2" />
		<?php
		} else {
		?>
		<img src="modules/newsletter/images/<?=$cmd?>.gif" border="0" unselectable="on" title="<?=$name?>" class="buttonEditor" onmouseover="this.className='buttonEditorOver'; this.src='modules/newsletter/images/<?=$cmd?>_on.gif';" onmouseout="this.className='buttonEditor'; this.src='modules/newsletter/images/<?=$cmd?>.gif';" onclick="process_toolbar_command('<?=$cmd?>');" width="20" height="20" />
		<?php
		}
	}
	?>
	</td>
	</tr>
	<tr>
	<td style="background: #fff" class="compose_display">
	<div contentEditable="true" id="newsletter_footer_default" style="width: 610px; height: 150px; overflow: auto;"><?=html_entity_decode(stripslashes($row['footer']))?></div>
	</td>
	</tr>
</table>

<h2>SMTP Connection Information (Optional)</h2>
<?php
$mailTestObj = new MarbleMail();

if($mailTestObj->connection) {
?>
<p style="color: #060; font-weight: bold; margin: 2px 0px 10px 0px;">Enabled.  Newsletter messages will be sent using your SMTP information below.</p>
<?php
} else {
?>
<p style="color: #900; font-weight: bold; margin: 2px 0px 10px 0px;">This feature is currently inactive.  To use your SMTP provider to send your newsletter messages please enter your SMTP connection information below.</p>
<?php
}
$mailTestObj->close();
?>
<ul class="form_display">
	<li><label for="smtp_host">SMTP Host Location:</label><input type="text" class="text_input" id="smtp_host" name="smtp_host" value="<?=$row['smtp_host']?>" autocomplete="off" /></li>
	<li><label for="smtp_username">SMTP Username:</label><input type="text" class="text_input" id="smtp_username" name="smtp_username" value="<?=$row['smtp_user']?>" autocomplete="off" /></li>
	<li><label for="smtp_password">SMTP Password:</label><input type="password" class="text_input" id="smtp_password" name="smtp_password" value="<?=$row['smtp_password']?>" autocomplete="off" /></li>
	<li><label for="smtp_port">SMTP Port Number:</label><input type="text" class="text_input" id="smtp_port" name="smtp_port" value="<?=$row['smtp_port']?>" autocomplete="off" /></li>
</ul>
<input type="hidden" name="cmd" value="update" />
<div style="clear: both;">&nbsp;</div>
<input type="hidden" name="cmd" value="save" />
<input type="hidden" name="header" id="header" value="<?=str_replace('"','&#34;',$row['header'])?>" />
<input type="hidden" name="footer" id="footer" value="<?=str_replace('"','&#34;',$row['footer'])?>" />
<input type="button" name="nocmd" value="Save Changes" onclick="save_configuration(this.form)" />
</form>
<?php
}
?>
<script>
function process_toolbar_command(cmd) {
	if(cmd == "insert_image") {
		window.open('<?=NUMO_FOLDER_PATH?>modules/newsletter/popup/insert_image.php', 'popup', 'location=0,status=0,scrollbars=0,resizable=1,width=600,height=380').focus();
	} else if(cmd == "create_link") {
		window.open('<?=NUMO_FOLDER_PATH?>modules/newsletter/popup/insert_hyperlink.html', 'popup', 'location=0,status=0,scrollbars=0,resizable=1,width=350,height=160').focus();
	} else {
		document.execCommand(cmd, false, null);
	}
}

function insert_image(src, width, height, alt) {
	img = document.createElement("img");

	// set the attributes
	img.setAttribute("src", src);
	//img.setAttribute("style", "width:" + width + ";height:" + height);
	img.style['width'] = width;
	img.style['height'] = height;
	img.setAttribute("alt", alt);

	img.removeAttribute("width");
	img.removeAttribute("height");

	// Check if IE or Mozilla (other)
	if (document.selection) {
		var sel = document.selection.createRange();
		//alert(img.outerHTML);
		sel.pasteHTML(img.outerHTML);

	} else {
		// get current selection
		var sel = window.getSelection();

		// get the first range of the selection
		// (there's almost always only one range)
		var range = sel.getRangeAt(0);

		var rangeCopy = range.cloneRange();

		range.deleteContents();
		rangeCopy.insertNode(img);

		rangeCopy.collapse(true);
	}
}

function insert_link(href, target, styleClass, name) {
	aLink = document.createElement("a");

	// set the attributes
	aLink.setAttribute("href", href);
	aLink.setAttribute("class", styleClass);
	aLink.setAttribute("className", styleClass);
	aLink.setAttribute("target", target);
	aLink.setAttribute("name", name);

	// Check if IE or Mozilla (other)
	if (document.selection) {
		var range = document.selection.createRange();
		//alert(img.outerHTML);
		//range.pasteHTML(aLink.outerHTML);

		range.select();
		aLink.innerHTML = range.htmlText;
		range.pasteHTML(aLink.outerHTML);

	} else {
		// get current selection
		var sel = window.getSelection();

		// get the first range of the selection
		// (there's almost always only one range)
		var range = sel.getRangeAt(0);

		/*var rangeCopy = range.cloneRange();

		range.deleteContents();
		rangeCopy.insertNode(aLink);

		rangeCopy.collapse(true);*/

		var node = range.startContainer;
		var pos = range.startOffset;

		if(node.nodeType != 3) {
			node = node.childNodes[pos];
		}

		if(node.tagName) {
			aLink.appendChild(node);
		} else {
			aLink.innerHTML = sel;
		}

		// remove content of current selection from document
		range.deleteContents();

		//add link contents in place of selection
		range.insertNode(aLink);
	}
}

function save_configuration(frm) {
	document.getElementById("header").value = document.getElementById("newsletter_header_default").innerHTML;
	document.getElementById("footer").value = document.getElementById("newsletter_footer_default").innerHTML;

	frm.submit();
}
</script>