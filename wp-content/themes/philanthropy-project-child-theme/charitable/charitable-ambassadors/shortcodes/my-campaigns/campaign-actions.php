<?php
/**
 * The template used to display the user's campaigns.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/shortcodes/my-campaigns/campaign-actions.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

$campaign = $view_args[ 'campaign' ];
$user = $view_args[ 'user' ];
?>
<div class="campaign-actions user-post-actions">
    <ul class="actions">
        <li class="campaign-id">
            <div class="label"><?php _e('Campaign ID'); ?></div>
            <div class="val"><?php echo $campaign->ID; ?></div>
        </li>
        <li class="edit-campaign">
            <a href="<?php echo esc_url( charitable_get_permalink( 'campaign_editing_page', array( 'campaign_id' => get_the_ID() ) ) ) ?>">Edit Campaign</a>
        </li>
        <?php if ( charitable_get_option( 'allow_creators_donation_export', false ) ) : 
            $download_args = array(
                'campaign_id' => $campaign->ID, 
                'charitable_action' => 'creator_download_donations', 
                'download_donations_nonce' => wp_create_nonce( 'download_donations_' . $campaign->ID )
            );
            ?>
            <li class="export-donations">
                <a href="<?php echo esc_url( add_query_arg( $download_args, site_url() ) ) ?>" target="_blank" title="Export CSV file with the donations made to this campaign.">Export List of Donations, Merchandise and Tickets Purchased</a>
            </li>
        <?php endif ?>
    </ul><!-- .actions -->
</div><!-- .campaign-actions -->