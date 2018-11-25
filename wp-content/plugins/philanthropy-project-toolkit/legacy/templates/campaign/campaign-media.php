<?php
/**
 * Displays the campaign media embed.
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
$media    = $campaign->media_embed;
// $media    = 'https://soundcloud.com/postmalone/rockstar-feat-21-savage';

if ( empty( $media ) ) {
	return;
}

$medias = preg_split('/\r\n|[\r\n]/', $media);
foreach ($medias as $url):
?>
<div class="campaign-media"><?php echo wp_oembed_get( $url ); ?></div>
<?php
endforeach;
?>
