<?php
/**
 * Displays the campaign summary.
 *
 * Override this template by copying it to yourtheme/charitable/campaign/summary.php
 *
 * @author 	Studio 164a
 * @since   1.0.0
 */

$campaign = $view_args['campaign'];

/**
 * @hook charitable_campaign_summary_before
 */
do_action( 'charitable_campaign_summary_before', $campaign );

?>
<div class="campaign-summary">  
	<?php

	/**
	 * @hook charitable_campaign_summary
	 */
	do_action( 'charitable_campaign_summary', $campaign );

	?>
</div>
<?php

/**
 * @hook charitable_campaign_summary_after
 */
do_action( 'charitable_campaign_summary_after', $campaign );
