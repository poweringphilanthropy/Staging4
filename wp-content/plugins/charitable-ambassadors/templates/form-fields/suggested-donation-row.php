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
$amount      = isset( $view_args['amount'] ) ? $view_args['amount'] : '';
$description = isset( $view_args['description'] ) ? $view_args['description'] : '';

?>
<tr class="suggested-donation repeatable-field" data-index="<?php echo $index ?>">
	<td>
		<div class="repeatable-field-wrapper">
			<div class="charitable-form-field odd">
				<label for="<?php printf( '%s_%s_amount', $key, $index ) ?>"><?php _e( 'Amount', 'charitable-ambassadors' ) ?></label>
				<input
					type="number"
					id="<?php printf( '%s_%s_amount', $key, $index ) ?>"
					name="<?php printf( '%s[%s][amount]', $key, $index ) ?>"
					value="<?php echo esc_attr( $amount ) ?>"
					min="0"
					step="0.01" />
			</div>
			<div class="charitable-form-field even">
				<label for="<?php printf( '%s_%s_description', $key, $index ) ?>"><?php _e( 'Description (optional)', 'charitable-ambassadors' ) ?></label>
				<input 
					type="text" 
					id="<?php printf( '%s_%s_description', $key, $index ) ?>" 
					name="<?php printf( '%s[%s][description]', $key, $index ) ?>"
					value="<?php echo esc_attr( $description ) ?>" />                       
			</div>
			<button class="remove" data-charitable-remove-row="<?php echo $index ?>">x</button>
		</div>
	</td>
</tr>
