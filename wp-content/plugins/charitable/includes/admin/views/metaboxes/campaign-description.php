<?php
/**
 * Renders the campaign description field for the Campaign post type.
 *
 * @author 	Studio 164a
 * @since   1.0.0
 * @package Charitable/Admin Views/Metaboxes
 */

global $post;

$title 					= isset( $view_args['title'] ) 		? $view_args['title'] 	: '';
$tooltip 				= isset( $view_args['tooltip'] )	? '<span class="tooltip"> '. $view_args['tooltip'] . '</span>'	: '';
$campaign_description	= esc_textarea( get_post_meta( $post->ID, '_campaign_description', true ) );

?>
<div id="charitable-campaign-description-metabox-wrap" class="charitable-metabox-wrap">
	<label class="screen-reader-text" for="campaign_description"><?php echo $campaign_description ?></label>
	<textarea name="_campaign_description" id="campaign_description" tabindex="" rows="10" placeholder="<?php _e( 'Enter a short description of your campaign', 'charitable' ); ?>"><?php echo $campaign_description ?></textarea>
</div>
