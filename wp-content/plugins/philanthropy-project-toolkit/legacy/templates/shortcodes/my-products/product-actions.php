<?php
/**
 * The template used to display the user's campaigns.
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
        <li class="edit-campaign">
           <a href="<?php echo esc_url( charitable_get_permalink( 'campaign_editing_page', array( 'campaign_id' => get_the_ID() ) ) ) ?>"><?php _e( 'Edit Campaign', 'charitable-fes' ) ?></a>
        </li>
    </ul><!-- .actions -->
</div><!-- .campaign-actions -->