<?php
/**
 * Partial template displaying donations in the activity feed shown on user profiles.
 *
 * @package     Reach
 */

if ( ! reach_has_charitable() ) :
	return;
endif;

$donation 	 = charitable_get_donation( get_the_ID() );
$campaign_id = current( $donation->get_campaign_donations() )->campaign_id;

?>
<li class="activity-type-donation cf">
	<p class="activity-summary">
		<?php printf( _x( '%1$s backed %2$s', 'user backed campaign(s)', 'reach' ),
			'<span class="display-name">' . reach_get_current_author()->display_name . '</span>',
			$donation->get_campaigns_donated_to( true )
		) ?><br />
		<span class="time-ago"><?php printf( _x( '%s ago', 'time ago', 'reach' ),
			human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) )
		) ?></span>
	</p>
	<?php if ( has_post_thumbnail( $campaign_id ) ) :

		echo get_the_post_thumbnail( $campaign_id, 'thumbnail' );

	endif ?>
</li><!-- .activity-type-donation -->