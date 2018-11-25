<?php 
/**
 * Renders the simple updates field for the Campaign post type.
 *
 * @author  Studio 164a
 * @since   1.0.0
 */
global $post;

$updates = get_post_meta( $post->ID, '_campaign_updates', true );
$textarea_name = 'campaign_updates';
$textarea_rows = apply_filters( 'charitable_simple_updates_rows', 8 );
$textarea_placeholder = __( 'Write updates about your campaign...', 'charitable-simple-updates' );
$textarea_tab_index = isset( $view_args['tab_index'] ) ? $view_args['tab_index'] : 0;

if ( $GLOBALS['wp_version'] >= 3.3 && function_exists( 'wp_editor' ) ) : 

    wp_editor( $updates, 'charitable-campaign-updates', array( 
        'textarea_name' => '_campaign_updates', 
        'textarea_rows' => $textarea_rows, 
        'tabindex'      => $textarea_tab_index, 
        'media_buttons' => false,
        'tinymce'       => false, 
        'wpautop'       => true
    ) );

else : ?>

<textarea name="<?php esc_attr_e( $textarea_name ); ?>" id="<?php esc_attr_e( $textarea_id ); ?>" tabindex="<?php esc_attr_e( $textarea_tab_index ); ?>" rows="<?php esc_attr_e( $textarea_rows ); ?>" placeholder="<?php esc_attr_e( $textarea_placeholder ); ?>"><?php echo esc_html( htmlspecialchars( $textarea_content ) ); ?></textarea>

<?php endif ?>
<p class="charitable-help"><?php _e( 'You can use this area to write updates about your campaign. HTML is supported.', 'charitable-simple-updates' ) ?></p>
<!-- <textarea name="_campaign_updates" id="campaign_updates" tabindex="" rows="8"><?php echo esc_html( htmlspecialchars( $updates ) ) ?></textarea>
 -->
