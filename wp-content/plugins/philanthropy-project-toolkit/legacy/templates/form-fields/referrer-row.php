<?php
/**
 * The template used to display the suggested amounts field.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/form-fields/referrer-row.php
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
$value      = isset( $view_args['value'] ) ? $view_args['value'] : '';

?>
<tr class="volunteers-need repeatable-fieldx" data-index="<?php echo $index ?>">
	<td colspan="3">
		<div class="repeatable-field-wrapper">
			<div class="charitable-form-field odd" style="width: 100%;">
				<label for="<?php printf( '%s_%d', $key, $index ) ?>"><?php _e( 'Member Name', 'pp-toolkit' ) ?></label>
				<input 
					type="text" 
					id="<?php printf( '%s_%s', $key, $index ) ?>" 
					name="<?php printf( '%s[%s]', $key, $index ) ?>" 
					value="<?php echo $value; ?>"
					/>
			</div>
			
			<button class="remove" data-pp-charitable-remove-row="<?php echo $index ?>">x</button>
		</div>
	</td>
</tr>