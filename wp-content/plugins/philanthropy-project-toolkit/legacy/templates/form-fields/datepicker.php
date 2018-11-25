<?php
/**
 * The template used to display datepicker form fields.
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
	return;
}

$form 		 = $view_args['form'];
$field 		 = $view_args['field'];
$classes 	 = esc_attr( $view_args['classes'] );
$is_required = isset( $field['required'] ) ? $field['required'] : false;
$value		 = isset( $field['value'] ) ? esc_attr( $field['value'] ) : '';
$min_date	 = isset( $field['min_date'] ) ? esc_attr( $field['min_date'] ) : '';
$max_date	 = isset( $field['max_date'] ) ? esc_attr( $field['max_date'] ) : '';

$json_args   = array(
	'changeMonth' => false,
	'changeYear'  => false,
	'dateFormat'  => 'MM d, yy',
);

if ( array_key_exists( 'min_date', $field ) ) {
	$json_args['minDate'] = '+' . $field['min_date'];
}

if ( array_key_exists( 'max_date', $field ) ) {
	$json_args['maxDate'] = '+' . $field['max_date'];
}

/* Enqueue the datepicker */
if ( ! wp_script_is( 'jquery-ui-datepicker' ) ) {
	wp_enqueue_script( 'jquery-ui-datepicker' );
}

wp_add_inline_script( 'jquery-ui-datepicker', "jQuery(document).ready( function(){
		jQuery( '.datepicker' ).datepicker( " . json_encode( $json_args ) . ");
	});"
);

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
	<input type="text" class="datepicker" name="<?php echo $field['key'] ?>" value="<?php echo $value ?>" <?php echo charitable_get_arbitrary_attributes( $field ) ?>/>
</div>