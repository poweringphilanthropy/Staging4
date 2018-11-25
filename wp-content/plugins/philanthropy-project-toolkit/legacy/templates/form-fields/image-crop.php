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

wp_enqueue_script( 'cropit' );

$form           = $view_args['form'];
$field          = $view_args['field'];
$classes        = $view_args['classes'];
// $placeholder    = isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '';
// $size           = isset( $field['size'] ) ? $field['size'] : 'thumbnail';
// $use_uploader   = isset( $field['uploader'] ) && $field['uploader'];
// $max_uploads    = isset( $field['max_uploads'] ) ? $field['max_uploads'] : 1;
// $max_file_size  = isset( $field['max_file_size'] ) ? $field['max_file_size'] : wp_max_upload_size();


$is_required    = isset( $field['required'] ) ? $field['required'] : false;

/**
 * editor setting
 * @var [type]
 */
$height         = isset( $field['editor-setting']['expected-height'] ) ? $field['editor-setting']['expected-height'] : 200;
$width          = isset( $field['editor-setting']['expected-width'] ) ? $field['editor-setting']['expected-width'] : 200;
$placeholder    = isset( $field['editor-setting']['placeholder'] ) ? $field['editor-setting']['placeholder'] : false;
$preview_width_css  = isset( $field['editor-setting']['max-preview-width'] ) ? 'max-width: ' .$field['editor-setting']['max-preview-width'] .';' : '';
$nonce          = wp_create_nonce( 'save_imagedata' );
$value          = isset( $field['value'] ) ? $field['value'] : '';
$attachment     = (!empty($value)) ? wp_get_attachment_image_src($value, 'full') : array();
$img_url        = (isset($attachment[0])) ? $attachment[0] : '';
// echo "<pre>";
// print_r($field);
// echo "</pre>";
?>

<div id="charitable_field_<?php echo $field['key'] ?>" class="<?php echo $classes ?>">    
    <?php if ( isset( $field['label'] ) ) : ?>
        <label for="charitable_field_<?php echo $field['key'] ?>">
            <?php echo $field['label'] ?>         
            <?php if ( $is_required ) : ?>
                <abbr class="required" title="required">*</abbr>
            <?php endif ?>
        </label>
    <?php endif ?>
    <?php if ( isset( $field['help'] ) ) : ?>
        <p class="charitable-field-help"><?php echo $field['help'] ?></p>
    <?php endif ?>


    <div class="image-crop <?php echo $field['key'] ?>_image-editor" data-width="<?php echo $width; ?>" data-height="<?php echo $height; ?>" data-nonce="<?php echo $nonce; ?>">
        
        <div class="cropit-placeholder <?php echo (empty($img_url)) ? '' : 'loaded'; ?>" style="<?php echo $preview_width_css; ?>">
            <?php if(empty($img_url)){ ?>
            <div class="placeholder-content">
                <div class="input-file"><i class="fa fa-plus"></i> Add Photo</div>
                <div class="placeholder-text"><?php echo $placeholder; ?></div>
            </div>
            <?php } else { ?>
            <img src="<?php echo $img_url; ?>" class="cropit-result">
            <?php } ?>
        </div>

        <div class="imgcrop-popup-wrapper">
            <div class="imgcrop-content">
                <div class="cancel-wrapper">
                    <div class="imgcrop-title">Select Image</div>
                    <a href="#" class="imgcrop-cancel">x</a>
                </div>
                <div class="image-editor">
                    <div class="cropit-preview" style="<?php echo $preview_width_css; ?>"></div>
                    <div class="controls-wrapper <?php echo (empty($img_url)) ? 'hidden' : ''; ?>">
                        <div class="editor-wrapper">
                            <span class="fa fa-undo fa-flip-horizontal" editor-action="rotateCW"></span>
                            <div class="slider-wrapper">
                                <!-- <span class="dashicons dashicons-format-image small-icon"></span> -->
                                <input type="range" class="cropit-image-zoom-input custom">
                                <!-- <span class="dashicons dashicons-format-image"></span> -->
                            </div>
                            <span class="fa fa-undo" editor-action="rotateCCW"></span>
                        </div>
                        <div class="input-wrapper">
                            <input type="file" class="cropit-image-input" accept="image/*">
                            <input type="hidden" name="<?php echo $field['key']; ?>" class="image-id" value="<?php echo $value; ?>">
                            <input type="hidden" name="" class="image-url" value="<?php echo $img_url; ?>">
                            <textarea name="" class="image-uri" id="" cols="30" rows="10" style="display: none;"></textarea>
                            <a href="#" class="select-image editor-button"><?php _e('Select image', 'pp-toolkit'); ?></a>
                            <a href="#" class="save-image editor-button disabled"><?php _e('Save image', 'pp-toolkit'); ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>


