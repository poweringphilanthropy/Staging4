<?php
/**
 * Partial template displaying the author's activity summary in the banner.
 *
 * @package     Reach
 */

if ( ! reach_has_charitable() ) :
	return;
endif;

$user 	   = charitable_get_user( reach_get_current_author()->ID );
$campaigns = Charitable_Campaigns::query( array( 'author' => $user->ID ) );

?>
<div class="author-activity-summary">
	<span class="number"><?php echo $user->count_campaigns_supported() ?></span>&nbsp;<?php _e( 'Campaigns Backed', 'reach' ) ?>&nbsp;<span class='separator'>/</span>&nbsp;<span class="number"><?php echo $campaigns->post_count ?></span>&nbsp;<?php _e( 'Campaigns Created', 'reach' ) ?>
</div><!-- .author-activity-summary -->
