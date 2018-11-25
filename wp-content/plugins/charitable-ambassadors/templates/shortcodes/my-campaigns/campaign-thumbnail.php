<?php
/**
 * The template used to display the campaign thumbnail.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/shortcodes/my-campaigns/campaign-thumbnail.php
 *
 * @package Charitable Ambassadors/Templates/My Campaigns Shortcode
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

$thumbnail_size = apply_filters( 'charitable_ambassadors_my_campaign_thumbnail_size', 'thumbnail' );

if ( has_post_thumbnail() ) :

	the_post_thumbnail( $thumbnail_size );

endif;
