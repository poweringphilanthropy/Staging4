<?php
/**
 * Displays the donate button to be displayed on campaign pages.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.3.2
 */

$campaign = $view_args['campaign'];

?>
<div class="campaign-donation">
	<a data-trigger-modal="charitable-donation-form-modal"
		class="donate-button button"
		href="<?php echo charitable_get_permalink( 'campaign_donation_page', array( 'campaign_id' => $campaign->ID ) ) ?>"
		aria-label="<?php printf( esc_attr_x( 'Make a donation to %s', 'make a donation to campaign', 'charitable' ), get_the_title( $campaign->ID ) ) ?>">
	<?php _e( 'Donate', 'charitable' ) ?>
	</a>
</div>
