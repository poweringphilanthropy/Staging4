<?php
/**
 * The template used to display the suggested amounts field.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/form-fields/suggested-donations.php
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args['form'] ) || ! isset( $view_args['field'] ) ) {
	return;
}

$form  = $view_args['form'];
$field = $view_args['field'];
$key   = $field['key'];
$value = isset( $field['value'] ) && is_array( $field['value'] ) ? $field['value'] : array();


?>
<table id="charitable-campaign-suggested-donations" class="charitable-campaign-form-table charitable-repeatable-form-field-table">	
	<tbody>
		<?php
		$i = 0;
		foreach ( $value as $benefactor_id => $merchandise ) :
			
			PP_Merchandise::init()->setup_values($merchandise);

			// echo "<pre>";
			// print_r($fields);
			// echo "</pre>";

			pp_toolkit_template( 'form-fields/merchandise-row.php', array(
				'index'       => $i,
				'form'         => $form,
			) );

		$i++;
		endforeach;
		?>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="3"><a class="add-row" href="#" data-pp-toolkit-add-row="merchandise"><?php _e( 'Add a suggested amount', 'charitable-ambassadors' ) ?></a></td>
		</tr>
	</tfoot>
</table>