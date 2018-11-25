<?php
/**
 * The sidebar containing the main widget area.
 *
 * @package Reach
 */

if ( ! is_active_sidebar( 'default' ) ) {
	return;
}
?>
<div id="secondary" class="widget-area sidebar" role="complementary">
	<?php dynamic_sidebar( 'default' ); ?>
</div><!-- #secondary -->
