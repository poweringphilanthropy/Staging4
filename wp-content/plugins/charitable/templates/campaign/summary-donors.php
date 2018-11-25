<?php
/**
 * Displays the campaign's donor summary.
 *
 * Override this template by copying it to yourtheme/charitable/campaign/summary-donors.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

$campaign = $view_args['campaign'];

?>
<div class="campaign-donors campaign-summary-item">
	<?php printf(
		_x( '%s Donors', 'number of donors', 'charitable' ),
		'<span class="donors-count">' . $campaign->get_donor_count() . '</span>'
	) ?>
</div>
