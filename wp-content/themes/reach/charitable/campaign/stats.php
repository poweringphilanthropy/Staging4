<?php
/**
 * The template for displaying the campaign's stats.
 *
 * Override this template by copying it to your-child-theme/charitable/campaign/stats.php
 *
 * @author  Studio 164a
 * @package Reach
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * @var 	Charitable_Campaign
 */
$campaign = $view_args['campaign'];

?>
<ul class="campaign-stats">
	<li class="campaign-raised">
		<span><?php echo charitable_format_money( $campaign->get_donated_amount() ) ?></span>
		<?php _e( 'Donated', 'reach' ) ?>
	</li>
	<?php if ( $campaign->has_goal() ) : ?>
		<li class="campaign-goal">
			<span><?php echo charitable_format_money( $campaign->get_goal() ) ?></span>
			<?php _e( 'Goal', 'reach' ) ?>
		</li>
	<?php endif ?>
	<li class="campaign-backers">
		<span><?php echo $campaign->get_donor_count() ?></span>
		<?php _e( 'Donors', 'reach' ) ?>
	</li>
</ul>
