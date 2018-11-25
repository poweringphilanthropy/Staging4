<?php
/**
 * Displays the campaign donation stats.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * @var 	Charitable_Campaign
 */
$campaign = $view_args['campaign'];

?>
<div class="campaign-donation-stats">  
	<?php echo $campaign->get_donation_summary() ?>
</div>
