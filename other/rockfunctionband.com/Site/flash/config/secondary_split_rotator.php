<?php

function get_option($portfolioName) {
  return array();
} 

header('Content-type: text/xml');

print '<?xml version="1.0" encoding="UTF-8"?>'."\n";

include('../../../../../../wp-blog-header.php');
?>
<SplitRotator>
  <Settings>
    <imageWidth>380</imageWidth>
    <imageHeight>200</imageHeight>
    <segments>7</segments>
    <tweenTime>1.2</tweenTime>
    <tweenDelay>0.1</tweenDelay>
    <tweenType>easeInOutBack</tweenType>
    <zDistance>0</zDistance>
    <expand>20</expand>
    <innerColor>0x111111</innerColor>
    <textBackground>0x0064C8</textBackground>
    <shadowDarkness>100</shadowDarkness>
    <textDistance>25</textDistance>
    <autoplay>12</autoplay>
  </Settings>

<?php
$portfolioImages = get_option('luckymarble_image_rotator');

foreach($portfolioImages as $image) {
	//only show image if it still exists
	if(wp_attachment_is_image($image['id']) || $image['id'] == "default") {
		if($image['id'] == "default") {
		?>
	<Image Filename="<?=htmlentities($image['image'])?>">
		<?php
		} else {
		?>
	<Image Filename="../../<?=htmlentities($image['image'])?>">
		<?php
		}
		?>
		<Text>
			<headline><?=htmlentities($image['title'])?></headline>
			<break>&nbsp;</break>
			<paragraph><?=htmlentities($image['description'])?></paragraph>
		</Text>
	</Image>
<?php
	}
}
?>

</SplitRotator>
