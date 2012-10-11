<?php
	numo_enqueue_js(NUMO_FOLDER_PATH."extensions/wysiwyg/jscripts/tiny_mce/tiny_mce.js", "tiny_mce", "3.5.4.1");
//	numo_enqueue_js(NUMO_FOLDER_PATH."extensions/wysiwyg/jscripts/tiny_mce/jquery-1.5.5.js", "jquery", "1.5.5");
  if (!isset($PARAMS['wysiwyg_id'])) {
	  $PARAMS['wysiwyg_id'] = "elm1";
  }
  if (!isset($PARAMS['wysiwyg_name'])) {
	  $PARAMS['wysiwyg_name'] = "elm1";
  }  
  if (!isset($PARAMS['wysiwyg_width'])) {
	  $PARAMS['wysiwyg_width'] = "80%";
  }  
  if (!isset($PARAMS['wysiwyg_wrap_with_form_tag'])) {
	  $PARAMS['wysiwyg_wrap_with_form_tag'] = true;
  }
?>
<!-- TinyMCE -->
<script type="text/javascript">
	tinyMCE.init({
		// General options
		mode : "textareas",
		theme : "advanced",
		skin : "o2k7",
		plugins : "autolink,lists,pagebreak,style,layer,table,save,advhr,advimage,advlink,emotions,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,inlinepopups,autosave",

		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,pagebreak,restoredraft",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		// Example word content CSS (should be your site CSS) this one removes paragraph margins
		content_css : "<?php echo NUMO_FOLDER_PATH; ?>extensions/wysiwyg/components/css/word.css",

		// Drop lists for link/image/media/template dialogs
		template_external_list_url : "<?php echo NUMO_FOLDER_PATH; ?>extensions/wysiwyg/components/lists/template_list.js",
		external_link_list_url : "<?php echo NUMO_FOLDER_PATH; ?>extensions/wysiwyg/components/lists/link_list_js.php",
		external_image_list_url : "<?php echo NUMO_FOLDER_PATH; ?>extensions/wysiwyg/components/lists/image_list_js.php",
		media_external_list_url : "<?php echo NUMO_FOLDER_PATH; ?>extensions/wysiwyg/components/lists/media_list_js.php",

		// Replace values for the template plugin
		template_replace_values : {
			username : "Some User",
			staffid : "991234"
		}
	});
</script>
<!-- /TinyMCE -->

<?php if ($PARAMS['wysiwyg_wrap_with_form_tag']) { ?>
<form method="post" action="http://tinymce.moxiecode.com/dump.php?example=true">
<?php } ?>
	<!-- Gets replaced with TinyMCE, remember HTML in a textarea should be encoded -->
	<textarea id="<?php echo $PARAMS['wysiwyg_id']; ?>" name="<?php echo $PARAMS['wysiwyg_name']; ?>" rows="15" cols="80" style="width: <?php echo $PARAMS['wysiwyg_width']; ?>"><?php echo $PARAMS['wysiwyg_content']; ?></textarea>
<?php if ($PARAMS['wysiwyg_wrap_with_form_tag']) { ?>
	<br />
	<input type="submit" name="save" value="Submit" />
	<input type="reset" name="reset" value="Reset" />
</form>
<?php } ?>