<?php
/**
 * The template for displaying the campaign's featured image.
 *
 * Override this template by copying it to your-child-theme/charitable/campaign/featured-image.php
 *
 * @author  Studio 164a
 * @package Reach
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

$campaign = $view_args['campaign'];

if ( ! has_post_thumbnail( $campaign->ID ) ) :
	return;
endif;

?>
<div class="campaign-image">
	<?php
	echo charitable_template_campaign_status_tag( $campaign );

	echo get_the_post_thumbnail( $campaign->ID );
	?>
</div>
