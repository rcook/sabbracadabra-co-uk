<?php
header('Content-type: text/xml');

print '<?xml version="1.0" encoding="UTF-8"?>'."\n";

include('../../../../../../wp-blog-header.php');

?>
<gallery>
<transitionDelay>15000</transitionDelay>
<numberOfButtonsOnStage>3</numberOfButtonsOnStage>
<numberOfButtonsToShift>3</numberOfButtonsToShift>
<buttonAlign>left</buttonAlign> <!-- center, left, right -->
<buttonDisplayDirection>vertical</buttonDisplayDirection>	<!-- horizontal, vertical -->

<backButtonLabel>back</backButtonLabel> 
<nextButtonLabel>next</nextButtonLabel>

<buttonWidth>236</buttonWidth> <!-- do not change -->
<buttonHeight>95</buttonHeight> <!-- do not change -->
<stageWidth>990</stageWidth> <!-- do not change -->
<buttonBarStartX>736</buttonBarStartX> <!-- do not change -->
<buttonBarStartY>20</buttonBarStartY><!-- do not change -->


	<images>
		<?php
		$portfolioImages = get_option('luckymarble_image_portfolio');

		foreach($portfolioImages as $image) {
			//only show image if it still exists
			if(wp_attachment_is_image($image['id']) || $image['id'] == "default") {
		?><pic>
		<?php
		if($image['id'] == "default") {
		?>
			<image><?=htmlentities($image['image'])?></image>
		<?php
		} else {
		?>			
			<image>../../<?=htmlentities($image['image'])?></image>
		<?php
		}
		?>		
			<link>../../../<?=($image['link'] == "" ? '' : str_replace(get_bloginfo('url').'/', '',get_page_link($image['link'])))?></link>
			<linkLabel><?=htmlentities($image['link_label'])?></linkLabel>
			<target><?=htmlentities($image['link_target'])?></target>
			<title><?=htmlentities($image['title'])?></title>
			<shortDescription><?=htmlentities($image['short_description'])?></shortDescription>
			<description><?=htmlentities($image['description'])?></description>
		</pic>
		<?php
			}
		}
		?>	
	</images>
</gallery>