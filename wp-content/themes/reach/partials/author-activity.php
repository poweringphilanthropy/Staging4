<?php
/**
 * Partial template displaying the activity feed shown on user profiles.
 *
 * @package 	Reach
 */

$author   = reach_get_current_author();
$activity = new WP_Query( array(
	'author'        => $author->ID,
	'post_status'   => array( 'charitable-completed', 'charitable-preapproved', 'publish' ),
	'post_type'     => array( 'donation', 'campaign', 'post' ),
	'order'         => 'DESC',
	'orderby'       => 'date',
) );

?>

<h2><?php _e( 'Activity Feed', 'reach' ) ?></h2>

<?php if ( $activity->have_posts() ) :  ?>
	
	<ul class="author-activity-feed">
	
	<?php while ( $activity->have_posts() ) :

		$activity->the_post();

		get_template_part( 'partials/author', 'activity-' . get_post_field( 'post_type', get_the_ID() ) );

	endwhile ?>
	
	</ul>

<?php else : ?>				

	<p><?php printf( _x( '%s has no activity to show yet.', 'user has no activity', 'reach' ), $author->display_name ) ?></p>

<?php

endif;
