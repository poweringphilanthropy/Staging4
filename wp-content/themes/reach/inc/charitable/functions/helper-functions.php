<?php
/**
 * Helper functions for the crowdfunding functionality.
 *
 * @package 	Reach/Crowdfunding
 * @category 	Functions
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Gets the timezone offset.
 *
 * @return  string
 * @since   1.0.0
 */
function reach_get_timezone_offset() {
	$timezone  = new DateTimeZone( charitable_get_timezone_id() );
	$date_time = new DateTime( 'now', $timezone );
	$offset    = str_replace( ':', '.', $date_time->format( 'P' ) );

	/* Offset is equal to or greater than 0 seconds */
	if ( $date_time->format( 'Z' ) >= 0 ) {
		return $offset;
	}

	return str_replace( '+', '-', $offset );
}

/**
 * Return whether the campaign has a featured image or video to display.
 *
 * @param   int $campaign_id
 * @return  boolean
 * @since   1.0.0
 */
function reach_campaign_has_media( $campaign_id ) {
	if ( 'video_in_summary' == reach_get_theme()->get_theme_setting( 'campaign_media_placement', 'featured_image_in_summary' ) ) {

		$video = trim( get_post_meta( $campaign_id, '_campaign_video', true ) );

		return strlen( $video ) > 0;
	}

	return has_post_thumbnail( $campaign_id );
}
