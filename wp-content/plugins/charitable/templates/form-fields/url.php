<?php
/**
 * The template used to display text form fields.
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Form Fields
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
	return;
}

$form        = $view_args['form'];
$field       = $view_args['field'];
$classes     = esc_attr( $view_args['classes'] );
$is_required = isset( $field['required'] ) ? $field['required'] : false;
$value       = isset( $field['value'] ) ? $field['value'] : '';

/* Set the default pattern */
if ( ! isset( $field['attrs']['pattern'] ) ) {
	$field['attrs']['pattern'] = 'https?://.+';
}

/* Set the default onblur attribute */
if ( ! isset( $field['attrs']['onblur'] ) ) {
	$field['attrs']['onblur'] = 'CHARITABLE.SanitizeURL(this)';
}

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
	<input type="url" name="<?php echo $field['key'] ?>" id="charitable_field_<?php echo $field['key'] ?>_element" value="<?php echo esc_attr( stripslashes( $value ) ) ?>" <?php echo charitable_get_arbitrary_attributes( $field ) ?>/>
</div>
