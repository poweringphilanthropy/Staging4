<?php
/**
 * Displays the campaign's donation summary.
 *
 * Override this template by copying it to yourtheme/charitable/campaign/summary-donations.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

$campaign = $view_args['campaign'];

?>
<div class="campaign-figures campaign-summary-item">
	<?php echo $campaign->get_donation_summary() ?>
</div>
