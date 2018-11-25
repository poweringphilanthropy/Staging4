<?php 
/**
 * Displays the donate button to be displayed on campaign pages. 
 *
 * @author Lafif <[<email address>]>
 * @since   1.0
 */

$campaign = $view_args[ 'campaign' ];

?>
<div class="campaign-donation">
    <a data-p_popup-open="philanthropy-donation-form-modal"
        class="donate-button button" 
        href="<?php echo charitable_get_permalink( 'campaign_donation_page', array( 'campaign_id' => $campaign->ID ) ) ?>" 
        title="<?php printf( esc_attr_x( 'Make a donation to %s', 'make a donation to campaign', 'charitable' ), get_the_title( $campaign->ID ) ) ?>">
    	<?php _e( 'Donate', 'charitable' ) ?>
    </a>
</div>
