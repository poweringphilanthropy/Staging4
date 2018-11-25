<?php 
/**
 * Displays the donate button to be displayed on campaign pages. 
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 */

$campaign = charitable_get_current_campaign();

?>
<form class="campaign-donation" method="post">
	<?php wp_nonce_field( 'charitable-donate-' . charitable_get_session()->get_session_id(), 'charitable-donate-now' ) ?>
	<input type="hidden" name="charitable_action" value="start_donation" />
	<input type="hidden" name="campaign_id" value="<?php echo $campaign->ID ?>" />
	<button name="charitable_submit" class="donate-button button button-alt"><?php _e( 'Donate', 'reach' ) ?></button>
</form>