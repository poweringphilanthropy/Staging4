<?php
/**
 * Displays the campaign video.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-videos/campaign-video.php
 *
 * @author  Studio 164a
 * @package Charitable Videos/Templates/Video
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

$campaign = $view_args['campaign'];
$video    = $campaign->video;
$video_id = $campaign->video_id;

if ( empty( $video ) ) {
	return;
}

if ( $video_id ) {
	$video_src        = wp_video_shortcode( array( 'src' => $video ) );
} else {
	$video_embed_args = apply_filters( 'charitable_campaign_video_embed_args', array(), $video, $campaign );
	$video_src 		  = wp_oembed_get( $video, $video_embed_args );
}

?>
<div class="campaign-video"><?php echo $video_src ?></div>
