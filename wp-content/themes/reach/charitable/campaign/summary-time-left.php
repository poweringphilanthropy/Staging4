<?php
/**
 * The template for displaying the amount of time left in the campaign.
 *
 * Override this template by copying it to your-child-theme/charitable/campaign/summary-time-left.php
 *
 * @author  Studio 164a
 * @package Reach
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

$campaign = $view_args['campaign'];

if ( $campaign->is_endless() ) :
	return;
endif;

?>
<div class="campaign-countdown">
	<span class="countdown" data-enddate='<?php echo esc_attr( date( 'j F Y H:i:s', $campaign->get_end_time() ) ) ?>'></span>
	<span><?php _e( 'Time left to donate', 'reach' ) ?></span>
</div>
