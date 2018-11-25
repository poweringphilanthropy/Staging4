<?php
/**
 * The template used to display the edit campaign link.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

/**
 * @var 	Charitable_Campaign
 */
$campaign = charitable_get_current_campaign();

/**
 * This will be the value from the post_status column,
 * or, if the campaign is published, 'active' or 'finished'.
 *
 * @var 	string
 */
$status = $campaign->get_status();

?>
<div class="charitable-ambassadors-campaign-creator-toolbar">
	<div class="layout-wrapper">
		<span class="campaign-status"><?php printf( _x( 'Status: %s', 'status for campaign', 'reach' ),
		'<span class="status status-' . esc_attr( $status ) . '">' . ucwords( $status ) . '</span>' )?>
		</span>
		<a href="<?php echo esc_url( charitable_get_permalink( 'campaign_editing_page' ) ) ?>" class="edit-link">
			<?php _e( 'Edit campaign', 'reach' ) ?>
		</a>
	</div><!-- .layout-wrapper -->
</div><!-- .charitable-ambassadors-campaign-creator-toolbar -->
