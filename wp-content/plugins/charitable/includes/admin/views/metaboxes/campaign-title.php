<?php
/**
 * Renders the campaign title field for the Campaign post type.
 *
 * @author 	Studio 164a
 * @since   1.0.0
 * @package Charitable/Admin Views/Metaboxes
 */

global $post;

$title 		= isset( $view_args['title'] ) 	? $view_args['title'] 	: '';
$tooltip 	= isset( $view_args['tooltip'] )? '<span class="tooltip"> '. $view_args['tooltip'] . '</span>'	: '';

/**
 * Filter the title field placeholder text.
 *
 * @since 3.1.0
 *
 * @param string  $text Placeholder text. Default 'Enter title here'.
 * @param WP_Post $post Post object.
 */
$title_placeholder = apply_filters( 'enter_title_here', __( 'Enter campaign title here', 'charitable' ), $post );
?>
<div id="charitable-campaign-title-metabox-wrap" class="charitable-metabox-wrap">
	<label class="screen-reader-text" for="title"><?php echo $title_placeholder; ?></label>
	<input type="text" name="post_title" size="30"  value="<?php echo esc_attr( htmlspecialchars( $post->post_title ) ); ?>" id="title" spellcheck="true" autocomplete="off" placeholder="<?php echo $title_placeholder ?>" tabindex="1" />
</div>
