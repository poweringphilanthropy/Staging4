<?php
/**
 * The template used to display a preview of an uploaded photo.
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Form Fields
 * @since   1.4.0
 * @version 1.4.0
 */

if ( ! isset( $view_args['image'] ) || ! isset( $view_args['field'] ) ) {
	return;
}

$image = $view_args['image'];
$field = $view_args['field'];
$size = isset( $field['size'] ) ? $field['size'] : 'thumbnail';
$multiple = isset( $field['max_uploads'] ) && $field['max_uploads'] > 1 ? '[]' : '';
$is_src = strpos( $image, 'img' ) !== false;

if ( is_numeric( $size ) ) {
	$size = array( $size, $size );
}

?>
<li <?php if ( ! $is_src ) : ?>data-attachment-id="<?php echo $image ?>"<?php endif ?>>
	<a href="#" class="remove-image button"><?php _e( 'Remove', 'charitable' ) ?></a>
	<?php if ( $is_src ) :
		echo $image;
	else : ?>
		<input type="hidden" name="<?php echo $field['key'] . $multiple ?>" id="charitable_field_<?php echo $field['key'] ?>_element" value="<?php echo $image ?>" />
		<?php echo wp_get_attachment_image( $image, $size ) ?>
	<?php endif ?>
</li>
