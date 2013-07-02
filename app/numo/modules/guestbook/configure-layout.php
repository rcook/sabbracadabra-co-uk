<?php
$layoutFilePath = "modules/".$_GET['m']."/layouts/listing.htm";

function getStyle($matches) {
	global $styleArr;

	//add style found to array
	$styleArr[strtolower(str_replace(".","stylerclass_",trim($matches[1])))][] = array("style" => strtolower(trim($matches[2])), "value" => strtolower(trim($matches[3])));

	return $matches[0];
}

function updateStyle($matches) {
	global $_POST;
	
	$matches[1] = trim($matches[1]);
	$matches[2] = trim($matches[2]);
	$matches[3] = trim($matches[3]);
	
	$newValue = $_POST["input_".str_replace(".","stylerclass_",$matches[1])."_".$matches[2]];
		
	if($newValue != "") {
		//update style
		$pattern = '/'.$matches[2].': (.*?)([\s;])/i';
		return preg_replace($pattern, $matches[2].': #'.$newValue.'$2', $matches[0]);
	}

		
	
	return $matches[0];
}

function renameLabel($styleName) {
	if($styleName == "color") {
		return "Text Color";
	} else if($styleName == "background" || $styleName == "background-color") {
		return "Background Color";
	}
}

if($_POST['cmd'] == "Cancel") {
	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage/');
} else if($_POST['cmd'] == "Save") {
	//load file contents into variable
	$styles = file($layoutFilePath);
	$styles = implode($styles);

	$pattern = '/(.*?)\s{.*?(color): (.*?)[\s;]/i';
	$styles = preg_replace_callback($pattern, "updateStyle", $styles);

	$pattern = '/(.*?)\s{.*?(background|background-color): (.*?)[\s;]/i';
	$styles = preg_replace_callback($pattern, "updateStyle", $styles);
	
	$handle = fopen($layoutFilePath, 'w');
	fwrite($handle, $styles);
	fclose($handle);
	
	header('Location: '.NUMO_FOLDER_PATH.'module/'.$_GET['m'].'/manage/');
}
?>
<style>
ul.styleProperties { display: none; border: 1px solid #4078D5; margin: 0px; padding: 0px;}
ul.styleProperties li {clear:both; list-style:none; padding:5px; border-bottom: 1px solid #EFF4FC; }
ul.styleProperties li label {padding-left: 5px; font-size: 12px;}
ul.styleProperties li h3 {background: #2A61BD;color: #fff; font-size: 14px; text-align: center; font-weight: bold; text-decoration: none; margin: 0px;}
.layout_preview p {font-style: italic; font-size: 11px; padding: 2px 0px 15px 0px; margin: 0px; width: 600px;}
.layout_preview table {border-collapse:collapse;border: 1px solid #aaa;}
.layout_preview table tr td p {font-style: italic; font-size: 11px; padding: 2px 0px 10px 0px; margin: 0px 0px 10px 0px; border-bottom: 1px dashed #ccc; width: auto;}
.layout_preview table tr td {border: 1px solid #aaa; border-style: inset; padding: 3px 5px;}
</style>
<script type="text/javascript" src="modules/<?=$_GET['m']?>/javascript/jscolor.js"></script>
<script type="text/javascript" src="modules/<?=$_GET['m']?>/javascript/style_changer.js"></script>

<form method="post" class="layout_preview">
<h2>Configure: Response Layout</h2>
<p>Below is a preview of the layout.  You will be able to re-configure the background and/or text colors for each the sections of the layout by clicking in the layout section.  When you click on a layout section its current color settings will appear under the 'Manage Colors' heading.</p>
<table height="100%">
<tr>
	<td valign="top" width="210px" height="200px">
		<h2>Manage Colors</h2>
		<p>Click on a section in the layout to the right to change its color options.</p>
		<?php
			$styleArr = array();

			//load file contents into variable
			$styles = file($layoutFilePath);
			$styles = implode($styles);



			$pattern = '/(.*?)\s{.*?(color): (.*?)[\s;]/i';
			$styles = preg_replace_callback($pattern, "getStyle", $styles);

			$pattern = '/(.*?)\s{.*?(background|background-color): (.*?)[\s;]/i';
			$styles = preg_replace_callback($pattern, "getStyle", $styles);

			//print_r($styleArr);

			foreach($styleArr as $name => $styles) {
				print "<ul class='styleProperties' id='style__".trim($name)."'>\n";
				print "<li><h3>".ucwords(str_replace("_"," ",str_replace("stylerclass_numo_","",trim($name))))."</h3></li>\n";
				
				foreach($styles as $key => $value) {
					//style name -- $name
					//style property -- $value['style']
					//style property value -- $value['value']
					
					print "<li><input id='button_input_".$name."_".$value['style']."' style='height:20px;width:20px;' class='color {valueElement:\"input_".$name."_".$value['style']."\"}' type='button' value=''><label for='button_input_".$name."_".$value['style']."'>".renameLabel($value['style'])."</label><input type='hidden' id='input_".$name."_".$value['style']."' name='input_".$name."_".$value['style']."' value='".$value['value']."' onchange=\"updateStylePreview('".str_replace("stylerclass_",".",$name)."','".$value['style']."',this.value)\" /></li>\n";
				}
				print "</ul>\n";
			}
		?>
	</td>
	<td valign="top">
	<iframe id="layout" src="<?=$layoutFilePath."?t=".time()?>" width="700px" height="500px" frameborder="0"></iframe>
	</td>	
</tr>
</table>
<input type="submit" name="cmd" value="Save" />
<input type="submit" name="cmd" value="Cancel" />
</form>