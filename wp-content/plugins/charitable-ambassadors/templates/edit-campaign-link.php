<?php
/**
 * The template used to display the edit campaign link.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/edit-campaign-link.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

$campaign = charitable_get_current_campaign();

charitable_ambassadors_enqueue_styles();

?>
<div class="charitable-ambassadors-campaign-creator-toolbar">
    <?php printf( '<span class="campaign-status">%s: <span class="status status-%s">%s</span></span>', __( 'Status', 'charitable-ambassadors' ), $campaign->get_status(), ucwords( $campaign->get_status() ) ) ?>
    <?php printf( '<a href="%s" class="edit-link">%s</a>', charitable_get_permalink( 'campaign_editing_page' ), __( 'Edit campaign', 'charitable-ambassadors' ) ) ?>
</div><!-- .charitable-ambassadors-campaign-creator-toolbar -->