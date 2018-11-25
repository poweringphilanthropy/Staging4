<?php
/**
 * The sidebar containing the campaigns widget area.
 *
 * This template is only used if Charitable is active.
 *
 * @package Reach
 */

if ( ! is_active_sidebar( 'sidebar_campaign' ) ) :
	return;
endif;

?>
<div id="secondary" class="widget-area sidebar sidebar-campaign" role="complementary">
	<?php dynamic_sidebar( 'sidebar_campaign' ) ?>
</div>
