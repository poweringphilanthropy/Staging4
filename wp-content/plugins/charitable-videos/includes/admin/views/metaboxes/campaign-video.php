<?php
/**
 * Renders the video field for the Campaign post type.
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 */

global $post;

$video    = esc_textarea( get_post_meta( $post->ID, '_campaign_video', true ) );
$video_id = get_post_meta( $post->ID, '_campaign_video_id', true );

if ( ! wp_script_is( 'charitable-videos', 'enqueued' ) ) {
	wp_enqueue_script( 'charitable-videos' );
}

?>
<textarea name="_campaign_video" id="campaign_video" tabindex="" rows="4" class="charitable-upload-field"><?php echo esc_html( htmlspecialchars( $video ) ) ?></textarea>
<input type="hidden" name="_campaign_video_id" value="" class="charitable-upload-field-id" />
<a href="#" 
	class="charitable-upload-button button"
	data-uploader-title="<?php _e( 'Upload Video', 'charitable-videos' ) ?>" data-uploader-button-text="<?php _e( 'Insert', 'charitable-videos' ) ?>" 
	onclick="return false;"
><?php _e( 'Upload Video', 'charitable-videos' ) ?></a>
