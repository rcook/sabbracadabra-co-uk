<?php
header('Content-type: text/xml');

print '<?xml version="1.0" encoding="UTF-8"?>'."\n";

include('../../../../../../wp-blog-header.php');

?>
<mp3player>
	<displayPlayer>on</displayPlayer>
	<mp3s>
		<?php
		$musicFiles = get_option('luckymarble_flash_music');
		$counter = 1;

		foreach($musicFiles as $music) {
		?>
		<mp3 id="<?=$counter?>">
			<title><?=htmlentities($music['title'])?></title>
			<artist><?=htmlentities($music['artist'])?></artist>
			<?php
			if($music['id'] == "default") {
			?>
				<url><?=htmlentities($music['file'])?></url>
			<?php
			} else {
			?>
				<url>../../../../<?=htmlentities($music['file'])?></url>
			<?php
			}
			?>
		</mp3>
		<?php
			$counter++;
		}
		?>
	</mp3s>
</mp3player>