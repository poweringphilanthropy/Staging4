<?php
/**
 * The template used to display number form fields.
 *
 * @author 	Studio 164a
 * @package Charitable/Templates/Form Fields
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
	return;
}

$form 		 = $view_args['form'];
$field 		 = $view_args['field'];
$classes 	 = $view_args['classes'];
$is_required = isset( $field['required'] ) ? $field['required'] : false;
$value		 = isset( $field['value'] ) ? $field['value'] : '';

?>
<div id="charitable_field_<?php echo $field['key'] ?>" class="<?php echo $classes ?>">	
	<?php if ( isset( $field['label'] ) ) : ?>
		<label for="charitable_field_<?php echo $field['key'] ?>_element">
			<?php echo $field['label'] ?>			
			<?php if ( $is_required ) : ?>
				<abbr class="required" title="required">*</abbr>
			<?php endif ?>
		</label>
	<?php endif ?>
	<input type="number" name="<?php echo esc_attr( $field['key'] ) ?>" id="charitable_field_<?php echo $field['key'] ?>_element" value="<?php echo esc_attr( $value ) ?>" <?php echo charitable_get_arbitrary_attributes( $field ) ?>/>
</div>
