<?php
/**
 * Partial template displaying campaigns in the activity feed shown on user profiles.
 *
 * @package 	Reach
 */

if ( ! reach_has_charitable() ) :
	return;
endif;

?>
<li class="activity-type-campaign cf">
	<p class="activity-summary">
		<?php printf( _x( '%1$s created %2$s', 'user created campaign', 'reach' ),
			'<span class="display-name">' . reach_get_current_author()->display_name . '</span>',
			'<a href="' . get_permalink() . '" title="' . the_title_attribute( array( 'echo' => false ) ) . '">' . get_the_title() . '</a>'
		) ?><br />
		<span class="time-ago"><?php printf( _x( '%s ago', 'time ago', 'reach' ),
			human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) )
		) ?></span>
	</p>
	<?php if ( has_post_thumbnail() ) :

		the_post_thumbnail( 'thumbnail' );

	endif ?>
</li><!-- .activity-type-campaign -->
