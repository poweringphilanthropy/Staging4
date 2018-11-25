<?php
/**
 * The template used to display the suggested amounts field.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/form-fields/suggested-donations.php
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

if ( 2 !== count( array_intersect( array( 'index', 'key' ), array_keys( $view_args ) ) ) ) {
	return;
}

$index       = esc_attr( $view_args['index'] );
$key         = esc_attr( $view_args['key'] );
$value      = isset( $view_args['value'] ) ? $view_args['value'] : array();

// echo "<pre>";
// print_r($value);
// echo "</pre>";

$height         = 250;
$width          = 250;
$placeholder    = __('Add sponsor image', 'p-toolkit');
$preview_width_css = 'max-width:250px;';
$nonce          = wp_create_nonce( 'save_imagedata' );
$img_id          = ( isset( $value['img_id'] ) ) ? $value['img_id'] : '';
$attachment     = ( !empty($img_id) ) ? wp_get_attachment_image_src($img_id, 'full') : array();
$img_url        = ( isset($attachment[0])) ? $attachment[0] : '';
$classes	= 'charitable-form-field charitable-form-field-image-crop required-field odd';

?>
<tr class="suggested-donation repeatable-fieldx" data-index="<?php echo $index ?>">
	<td colspan="3">
		<div class="repeatable-field-wrapper">
			<!-- Image Crop -->
			<div id="<?php printf( '%s_%s_img_id', $key, $index ) ?>" class="<?php echo $classes ?>">    
			    <label for="<?php printf( '%s_%s_img_id', $key, $index ) ?>"><?php _e( 'Sponsor Image', 'charitable-ambassadors' ) ?></label>
			    
			    <div class="image-crop <?php printf( '%s_%s_img_id', $key, $index ) ?>_image-editor" data-width="<?php echo $width; ?>" data-height="<?php echo $height; ?>" data-nonce="<?php echo $nonce; ?>">
        
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
			                            <input type="hidden" name="<?php printf( '%s[%s][img_id]', $key, $index ) ?>" class="image-id" value="<?php echo $img_id; ?>">
			                            <input type="hidden" class="image-url" value="<?php echo $img_url; ?>">
			                            <textarea class="image-uri" id="" cols="30" rows="10" style="display: none;"></textarea>
			                            <a href="#" class="select-image editor-button"><?php _e('Select image', 'pp-toolkit'); ?></a>
			                            <a href="#" class="save-image editor-button disabled"><?php _e('Save image', 'pp-toolkit'); ?></a>
			                        </div>
			                    </div>
			                </div>
			            </div>
			        </div>
			        
			    </div>
			</div>
			<div class="charitable-form-field even">
				<label for="<?php printf( '%s_%s_link', $key, $index ) ?>"><?php _e( 'Sponsor URL', 'charitable-ambassadors' ) ?></label>
				<input 
					type="text" 
					id="<?php printf( '%s_%s_link', $key, $index ) ?>" 
					name="<?php printf( '%s[%s][link]', $key, $index ) ?>"
					value="<?php echo (isset($value['link'])) ? esc_attr( $value['link'] ) : ''; ?>"
					placeholder="http://sponsor-url.com/" />                       
			</div>
			<button class="remove" data-pp-charitable-remove-row="<?php echo $index ?>">x</button>
		</div>
	</td>
</tr>
