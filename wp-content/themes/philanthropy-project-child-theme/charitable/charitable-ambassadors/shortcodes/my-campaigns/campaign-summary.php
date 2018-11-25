<?php
/**
 * The template used to display the user's campaigns.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/shortcodes/my-campaigns/campaign-summary.php
 *
 * @package Charitable Ambassadors/Templates/My Campaigns Shortcode
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

$campaign        = $view_args['campaign'];
$user            = $view_args['user'];
$currency_helper = charitable_get_currency_helper();

/**
 * Change donors count to be same as widget
 * (withoit distinc /  group donations by the same person)
 * @var array
 */
$campaign_ids = pp_get_merged_team_campaign_ids($campaign->ID);
$query_args = array(
    'number' => -1,
    'output' => 'donors',
    'campaign' => $campaign_ids,
    'distinct_donors' => false,
    'distinct' => false,
);
$donors = new Charitable_Donor_Query( $query_args );

?>
<ul class="campaign-stats user-post-stats">
	<?php if ( $campaign->has_goal() ) : ?>
		<li class="campaign-raised summary-item">
			<?php printf(
				_x( '%s Raised', 'percentage raised', 'charitable-ambassadors' ),
				'<span class="amount">' . $campaign->get_percent_donated() . '</span>'
			) ?>
		</li>
		<li class="campaign-figures summary-item">
			<?php printf(
				_x( '%s donated of %s goal', 'amount donated of goal', 'charitable-ambassadors' ),
				'<span class="amount">' . $currency_helper->get_monetary_amount( $campaign->get_donated_amount() ) . '</span>',
				'<span class="goal-amount">' . $currency_helper->get_monetary_amount( $campaign->get_goal() ) . '</span>'
			) ?>
		</li>
	<?php else : ?>
		<li class="campaign-figures summary-item">
			<?php printf(
				_x( '%s Donated', 'amount donated', 'charitable-ambassadors' ),
				'<span class="amount">' . $currency_helper->get_monetary_amount( $campaign->get_donated_amount() ) . '</span>'
			) ?>
		</li>
	<?php endif ?>
	<li class="campaign-donors summary-item">
		<?php printf(
			_x( '%s Donors', 'number of donors', 'charitable-ambassadors' ),
			'<span class="donors-count">' . $donors->count() . '</span>'
		) ?>
	</li>
	<?php if ( ! $campaign->is_endless() ) : ?>
		<li class="campaign-time-left summary-item">
			<?php echo $campaign->get_time_left() ?>
		</li>
	<?php
	endif;
	?>
</ul><!-- .campaign-stats -->
