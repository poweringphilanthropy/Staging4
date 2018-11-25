<?php
/**
 * The template displayed to logged in users who try to access the product form but do not have a campaign.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */
?>
<div class="pp-toolkit-no-campaigns">
    <p><?php _e( 'Before creating a product, you need to have an active campaign.', 'pp-toolkit' ) ?></p>
    <p><a href="<?php echo esc_url( charitable_get_permalink( 'campaign_submission_page' ) ) ?>" class="button button-primary"><?php _e( 'Create a new campaign', 'pp-toolkit' ) ?></a></p>
</div><!-- .pp-toolkit-no-campaigns -->