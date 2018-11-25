<?php
/**
 * The template used to display the suggested amounts field.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/form-fields/suggested-donations.php
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 * @version 1.1.17
 */

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
	return;
}

charitable_ambassadors_enqueue_styles();

$form  = $view_args['form'];
$field = $view_args['field'];
$key   = $field['key'];
$value = isset( $field['value'] ) && is_array( $field['value'] ) ? $field['value'] : array();
?>
<table id="charitable-campaign-suggested-donations" class="charitable-campaign-form-table charitable-repeatable-form-field-table">	
	<tbody>
		<?php
		foreach ( $value as $i => $donation ) :

			charitable_ambassadors_template( 'form-fields/suggested-donation-row.php', array(
				'index'       => $i,
				'key'         => $key,
				'amount'      => isset( $donation['amount'] ) ? $donation['amount'] : '',
				'description' => isset( $donation['description'] ) ? $donation['description'] : '',
			) );

		endforeach;
		?>
	</tbody>
	<tfoot>
		<tr>
			<td><a class="add-row" href="#" data-charitable-add-row="suggested-amount"><?php _e( 'Add a suggested amount', 'charitable-ambassadors' ) ?></a></td>
		</tr>
	</tfoot>
</table>