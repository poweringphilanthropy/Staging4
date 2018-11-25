<?php
/**
 * The template used to display text form fields.
 *
 * @author 	Studio 164a
 * @package Charitable/Templates/Form Fields
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
	return;
}

$form 			= $view_args['form'];
$field 			= $view_args['field'];
$value			= isset( $field['value'] ) ? $field['value'] : '';
?>
<input type="hidden" name="<?php echo $field['key'] ?>" value="<?php echo $value ?>" <?php echo charitable_get_arbitrary_attributes( $field ) ?>/>
