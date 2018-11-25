<?php
/**
 * Renders the checkbox to permit custom donation amounts inside the donation options metabox for the Campaign post type.
 *
 * @author 	Studio 164a
 * @since   1.0.0
 */

global $post;

$title 			= isset( $view_args['label'] ) 			? $view_args['label'] 	: '';
$tooltip 		= isset( $view_args['tooltip'] )		? '<span class="tooltip"> '. $view_args['tooltip'] . '</span>'	: '';
$description	= isset( $view_args['description'] )	? '<span class="charitable-helper">' . $view_args['description'] . '</span>' 	: '';
$is_allowed 	= get_post_meta( $post->ID, '_campaign_allow_custom_donations', true );

if ( ! strlen( $is_allowed ) ) {
	$is_allowed = true;
}
?>
<div id="charitable-campaign-allow-custom-donations-metabox-wrap" class="charitable-metabox-wrap charitable-checkbox-wrap">
	<input type="checkbox" id="campaign_allow_custom_donations" name="_campaign_allow_custom_donations" <?php checked( $is_allowed ); ?> />
	<label for="campaign_allow_custom_donations"><?php echo $title ?></label>	
</div>
