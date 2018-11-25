<?php
/**
 * The template for displaying campaign content within loops.
 *
 * This overrides the default Charitable template defined at charitable/templates/campaign-loop/campaign.php
 *
 * @author  Studio 164a
 * @package Reach
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

$campaign = charitable_get_current_campaign();

$color = (isset($view_args['color']) && !empty($view_args['color'])) ? $view_args['color'] : false;
$style_bg_color = '';
if(!empty($color)){
	$style_bg_color = ' style="background:'.$color.'"';
}
$style_color = '';
if(!empty($color)){
	$style_color = ' style="color:'.$color.'"';
}

?>
<div id="campaign-<?php echo get_the_ID() ?>" class="campaign-widget campaign block cf">    
	<?php

	/**
	 * @hook charitable_campaign_content_loop_before
	 */
	do_action( 'charitable_campaign_content_loop_before', $campaign, $view_args );

	/**
	 * @hook charitable_campaign_content_loop_before_title
	 */
	do_action( 'charitable_campaign_content_loop_before_title', $campaign, $view_args );

	?>
	<div class="title-wrapper">
		<h3 class="block-title">
			<a href="<?php the_permalink() ?>" title="<?php the_title_attribute() ?>" target="_parent" <?php echo $style_color; ?>><?php the_title() ?></a>
		</h3>
	</div>
	<?php

	/**
	 * @hook charitable_campaign_content_loop_after_title
	 */
	do_action( 'charitable_campaign_content_loop_after_title', $campaign );

	/**
	 * @hook charitable_campaign_content_loop_after
	 */
	if(!empty($color)){
		// for dashboard, we are pretty hardcode :(
		pp_toolkit_template( 'campaign-loop/loop-stats.php', array( 'campaign' => $campaign, 'color' => $color ) );
	} else {
		do_action( 'charitable_campaign_content_loop_after', $campaign );
	}
	

	?>
</div>
