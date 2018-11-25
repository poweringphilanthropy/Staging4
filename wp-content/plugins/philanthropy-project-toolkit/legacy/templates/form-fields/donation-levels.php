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
<table id="charitable-campaign-suggested-donations" class="pp-fundraising-table charitable-campaign-form-table charitable-repeatable-form-field-table">	
	<thead>
		<tr>
			<td class="icon"><?php echo (isset($view_args['field']['icon_url'])) ? '<img src="'.$view_args['field']['icon_url'].'">' : ''; ?></td>
			<td class="desc">
				<h3><?php _e('Donation Levels', 'pp-toolkit'); ?></h3>
				<p><?php _e( 'When people make a donation to your campaign, they will be able to donate any amount they choose. You can also provide suggested donation amounts in the table below.', 'pp-toolkit' ); ?></p>
			</td>
			<td class="button-add"><a class="add-row" href="#" data-charitable-add-row="donation-levels"><?php _e( '+ ADD TO CAMPAIGN', 'pp-toolkit' ); ?></a></td>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( $value as $i => $donation ) :

			pp_toolkit_template( 'form-fields/donation-levels-row.php', array(
				'index'       => $i,
				'key'         => $key,
				'amount'      => isset( $donation['amount'] ) ? $donation['amount'] : '',
				'description' => isset( $donation['description'] ) ? $donation['description'] : '',
			) );

		endforeach;
		?>
	</tbody>
	<tfoot class="add-more <?php echo (empty($value)) ? 'hide' : ''; ?>">
		<tr>
			<td colspan="3"><a class="add-row" href="#" data-charitable-add-row="donation-levels"><?php _e( 'Add another donation level', 'pp-toolkit' ) ?></a></td>
		</tr>
	</tfoot>
</table>