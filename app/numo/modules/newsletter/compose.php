<?php
if(isset($_POST['newsletter_id'])) {
	$newsletterId = $_POST['newsletter_id'];

} else if(isset($_GET['id'])) {
	$newsletterId = $_GET['id'];

} else {
	print "invalid newsletter id provided.  please try again.";
	exit();
}

if($_POST['cmd'] == "save") {
	$message = $_POST['numo_newsletter_message_content'];
	$message = str_replace("'","&#39;",$message);
	//$message = str_replace("&copy;", "X", $message);
	$message = str_replace("©", "&copy;", $message);
	//print $message;
	$sql = "UPDATE newsletter_messages SET status='".$_POST['numo_newsletter_status']."',message='{$message}',title='".str_replace("'","&#39;",$_POST['numo_newsletter_title'])."', summary='".str_replace("'","&#39;",$_POST['numo_newsletter_summary'])."' WHERE id='".$newsletterId."'";
	//print $sql;
	$dbObj->query($sql);
//exit;
	//redirect to manage newsletters page
	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage/');
}
?>
<style>
#newsletter_code_view {background: #FEF9B4; color: #666;}
#newsletter_code_view div {background: #fff; color: #000;}
td#compose_toolbar {background: #ccc url('modules/newsletter/images/background_silver.jpg') repeat-x;padding: 0px;}
td#compose_toolbar img:hover {cursor: pointer;}
#compose_display_window {border-collapse:collapse;border: 1px solid #999; background: #f9f9f9 url('modules/newsletter/images/compose_background.jpg') repeat-y top right;}
#compose_display_window tr td.compose_display {border: 1px solid #999; border-style: inset;padding: 3px 5px;}
#compose_display_window tr td label {width: 9em; color: #333; vertical-align: top; font-weight: bold; display: inline-block;}
#compose_display_window tr td input.text_input {width:400px;}
#compose_display_window tr td textarea {width:400px; height: 70px; margin-bottom: 10px;}
</style>
<h2>Compose Message</h2>
<?php
$sql = "SELECT * FROM newsletter_messages WHERE id='".$newsletterId."'";
$result = $dbObj->query($sql);

if($newsletterInfo = mysql_fetch_array($result)) {
	$layout = "modules/newsletter/layouts/".$newsletterInfo['layout'];
	$layoutSections = array();

	//$newsletterMessage = str_replace("&", "%26", $newsletterInfo['message']);
	$newsletterMessage = str_replace(array('&nbsp;',"&"), array(' ', "%26"), $newsletterInfo['message']);
	$newsletterMessage = str_replace("[NUMO|END]", "&", $newsletterMessage);


	parse_str($newsletterMessage, $sectionValues);

	$layoutDisplay = file_get_contents($layout);

	//get css class declarations for color and set for P, DIV, H1, H2, H3 to overwrite page default styles
	$pattern = '/(\..*?)\s{.*?(color): (.*?)[\s;]/i';
	$displayStyles = "";
	preg_replace_callback($pattern, "setupDisplayStyles", $layoutDisplay);
?>
	<style>
<?php
	print $displayStyles;
?>
	</style>
	<form method="post" onsubmit="save_section_values()">
	<table id="compose_display_window">
	<tr>
	<td class="compose_display">
	<label for="numo_newsletter_status">Status:</label>
	<select name="numo_newsletter_status" id="numo_newsletter_status">
		<option value="0"<?=$defaultActive?>>Offline</option>
		<?php
		if($newsletterInfo['status'] == 1) {
			$defaultActive = ' selected="selected"';
		}
		?>
		<option value="1"<?=$defaultActive?>>Online</option>
	</select>
	</td>
	</tr>
	<tr>
	<td class="compose_display">
	<label for="numo_newsletter_title">Title:</label>
	<input type="text" name="numo_newsletter_title" class="text_input" id="numo_newsletter_title" value="<?=$newsletterInfo['title']?>" />
	</td>
	</tr>
	<tr>
	<td class="compose_display">
	<label for="numo_newsletter_summary">Summary:</label>
	<textarea name="numo_newsletter_summary" id="numo_newsletter_summary"><?=$newsletterInfo['summary']?></textarea>
	</td>
	</tr>
	<tr>
	<td id="compose_toolbar" class="compose_display">
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
	$toolbarOption['insertorderedlist'] = "Insert Ordered List";
	$toolbarOption['insertunorderedlist'] = "Insert Unordered List";
	$toolbarOption['separator2'] = "Separator";
	$toolbarOption['outdent'] = "Outdent";
	$toolbarOption['indent'] = "Indent";
	$toolbarOption['separator3'] = "Separator";
	$toolbarOption['insert_image'] = "Insert Image";
	$toolbarOption['create_link'] = "Create Link";
	/*$toolbarOption[''] = "";
	$toolbarOption[''] = "";
	$toolbarOption[''] = "";
	$toolbarOption[''] = "";
	$toolbarOption[''] = "";
	$toolbarOption[''] = "";
	$toolbarOption[''] = "";
	$toolbarOption[''] = "";
	$toolbarOption[''] = "";*/

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
<?php
	/***********COMPOSE EXISTING********/
	/**************DESIGN***************/
	/***********************************/
	//remove any alignment on the table for the layout when composing
	$pattern = '/<table(.*?)align=[\'"]center[\'"](.*?)>/si';
	$replacement = '<table${1}${2}>';
	$layoutDisplay = preg_replace($pattern, $replacement, $layoutDisplay);

	//make defined sections editable
	$pattern = '/<!'.'-- #BeginSection "(.*?)" -->(.*?)<!'.'-- #EndSection -->/si';
	$designDisplay = preg_replace_callback($pattern, 'initialize_section', $layoutDisplay);

	print $designDisplay;

	//print "<hr />";

	/***********************************/
	/**************CODE*****************/
	/***********************************/
	/*$layoutDisplay = str_replace('&','&#38;', $layoutDisplay);
	$layoutDisplay = str_replace('<','&#60;', $layoutDisplay);
	$layoutDisplay = str_replace('>','&#62;', $layoutDisplay);
	$layoutDisplay = nl2br($layoutDisplay);

	$pattern = '/&#60;!-- #BeginSection "(.*?)" --&#62;(.*?)&#60;!-- #EndSection --&#62;/si';
	$replacement = '<&#60;!-- #BeginSection "${1}" --&#62;<div contentEditable="true" id="${1}_editable_code_section">${2}</div>&#60;!-- #EndSection --&#62;';
	$codeDisplay = preg_replace($pattern, $replacement, $layoutDisplay);

	print "<div id='newsletter_code_view>".$codeDisplay."</div>";*/
	?>
	</td></tr>
	<tr><td align="center" class="compose_display">

	<input type="hidden" name="numo_newsletter_message_content" id="numo_newsletter_message_content" value="" />
	<input type="hidden" name="newsletter_id" value="<?=$newsletterId?>" />
	<input type="hidden" name="cmd" value="save" />
	<input type="image" name="save_button" src="modules/newsletter/images/compose_save_button.jpg" value="Save" />
	<a href="module/<?=$_GET['m']?>/manage/"><img src="modules/newsletter/images/compose_cancel_button.jpg" border="0" alt="Cancel" title="Cancel" /></a>
	</td></tr></table>
	</form>

<?php
}
function initialize_section($matches) {
	global $layoutSections;
	global $sectionValues;

	$layoutSections[] = $matches[1];

	if($sectionValues[$matches[1]] == "") {
		return '<!'.'-- #BeginSection "'.$matches[1].'" --><div contentEditable="true" id="'.$matches[1].'_editable_section"><p></p><p><br></p></div><!'.'-- #EndSection -->';
	} else {
		return '<!'.'-- #BeginSection "'.$matches[1].'" --><div contentEditable="true" id="'.$matches[1].'_editable_section">'.html_entity_decode(stripslashes(str_replace("&", "&amp;", $sectionValues[$matches[1]]))).'</div><!'.'-- #EndSection -->';
	}
}

function setupDisplayStyles($matches) {
	global $displayStyles;

	$displayStyles .= $matches[1]." p, ".$matches[1]." h1, ".$matches[1]." h2, ".$matches[1]." h3, ".$matches[1]." div {".$matches[2].":".$matches[3]."}\n";
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

function save_section_values() {
	sectionValues = "";

	<?php
	foreach($layoutSections as $key => $elementId) {
	?>
	sectionValues = sectionValues+"<?=$elementId?>="+document.getElementById("<?=$elementId?>_editable_section").innerHTML+"[NUMO|END]";
	<?php
	}
	?>
	//alert(sectionValues);
	document.getElementById("numo_newsletter_message_content").value = sectionValues.replace(/\\/g, "&#92;");
}
</script>