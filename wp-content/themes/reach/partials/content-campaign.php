<?php
/**
 * Campaign content block.
 *
 * This template is only used if Charitable is active.
 *
 * @package Reach
 */

if ( ! reach_has_charitable() ) :
	return;
endif;

?>
<article id="campaign-<?php echo get_the_ID() ?>" <?php post_class( 'campaign-content' ) ?>>
	<div class="block entry-block">
		<div class="entry">
			<h2><?php _e( 'About the Campaign', 'reach' ) ?></h2>
			<?php the_content() ?>
		</div>
	</div>
</article>
