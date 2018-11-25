<?php
/**
 * Displays the campaign thumbnail.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

$campaign = $view_args[ 'campaign' ];
$thumbnail_size = apply_filters( 'charitable_campaign_loop_thumbnail_size', 'medium' );

if ( has_post_thumbnail( $campaign->ID ) ) :

    echo get_the_post_thumbnail( $campaign->ID, $thumbnail_size );  

endif;