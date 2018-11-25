<?php
/**
 * Event page content.
 *
 * This template is only used if Tribe Events is active.
 *
 * @package Reach
 */
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

	<?php get_template_part( 'partials/featured-image' ) ?>

	<!-- Event content -->
	<?php do_action( 'tribe_events_single_event_before_the_content' ) ?>
	<div class="tribe-events-single-event-description tribe-events-content entry-content description entry cf">
		<?php the_content(); ?>
	</div>
	<!-- .tribe-events-single-event-description -->
	<?php

	do_action( 'tribe_events_single_event_after_the_content' );

	do_action( 'tribe_events_single_event_before_the_meta' );

	tribe_get_template_part( 'modules/meta' );

	do_action( 'tribe_events_single_event_after_the_meta' );

	?>
</article> <!-- #post-x -->
