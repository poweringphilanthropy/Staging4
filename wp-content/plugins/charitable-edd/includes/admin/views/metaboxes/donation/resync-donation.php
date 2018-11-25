<?php
/**
 * Renders the re-sync donation meta box for the Donation post type.
 *
 * @author  Studio 164a
 * @since   1.1.0
 */
global $post;

$meta = charitable_get_donation( $post->ID )->get_donation_meta();

?>
<div id="charitable-resync-donation-metabox" class="charitable-metabox">
    <p class="charitable-help"><?php _e( 'Re-syncing your donation will update it based on the Easy Digital Downloads payment that it is linked to and any current contribution rules you have in place for your campaigns.', 'charitable-edd' ) ?></p>
    <p><a href="<?php echo esc_url( add_query_arg( array(
            'post'              => $post->ID,
            'charitable_action' => 'resync_donation_from_edd_payment',
        ),
        admin_url( 'post.php' ) ) ) ?>"><?php _e( 'Re-sync Donation', 'charitable-edd' ) ?></a>
    </p>
</div>
