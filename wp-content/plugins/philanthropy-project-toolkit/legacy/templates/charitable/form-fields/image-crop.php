<?php
/**
 * The template used to display image with editor.
 *
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
   return;
}

$form           = $view_args['form'];
$field          = $view_args['field'];
$classes        = $view_args['classes'];
$placeholder    = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
$size           = isset( $field['size'] ) ? $field['size'] : 'thumbnail';
$use_uploader   = isset( $field['uploader'] ) && $field['uploader'];
$max_uploads    = isset( $field['max_uploads'] ) ? $field['max_uploads'] : 1;
$max_file_size  = isset( $field['max_file_size'] ) ? $field['max_file_size'] : wp_max_upload_size();


$is_required    = isset( $field['required'] ) ? $field['required'] : false;
$width          = isset( $field['width'] ) ? $field['width'] : 200;
$height         = isset( $field['height'] ) ? $field['height'] : 200;
$export_zoom    = isset( $field['export-zoom'] ) ? $field['export-zoom'] : 1;
$value          = isset( $field['value'] ) ? $field['value'] : array();

// echo "<pre>";
// print_r($field);
// echo "</pre>";
?>
<div class="image-editor" data-width="<?php echo $width; ?>" data-height="<?php echo $height; ?>" data-export-zoom="<?php echo $export_zoom; ?>">
    <div class="cropit-preview"></div>
    <div class="controls-wrapper">
        <div class="editor-wrapper">
            <div class="rotation-btns">
                <span class="dashicons dashicons-image-rotate icon" editor-action="rotateCCW"></span>
                <span class="dashicons dashicons-image-rotate icon flip" editor-action="rotateCW"></span>
            </div>
            
            <span class="fa fa-undo fa-flip-horizontal" editor-action="rotateCW"></span>
            <div class="slider-wrapper">
                <span class="dashicons dashicons-format-image"></span>
                <input type="range" class="cropit-image-zoom-input custom">
                <span class="dashicons dashicons-format-image"></span>
            </div>
            <span class="fa fa-undo" editor-action="rotateCCW"></span>
        </div>
        <div class="input-wrapper">
            <input type="file" class="cropit-image-input">
            <input type="hidden" name="<?php echo $field['key']; ?>" class="image-id" value="">
            <input type="hidden" name="<?php echo $field['key']; ?>_image_url" class="image-url" value="">
            <!-- <textarea name="<?php // echo $field['key']; ?>_datauri" class="image-uri" id="" cols="30" rows="10"></textarea> -->
            <a href="#" class="select-image button"><?php _e('Select image', 'pp-toolkit'); ?></a>
            <a href="#" class="save-image button"><?php _e('Save image', 'pp-toolkit'); ?></a>
        </div>
    </div>
</div>
