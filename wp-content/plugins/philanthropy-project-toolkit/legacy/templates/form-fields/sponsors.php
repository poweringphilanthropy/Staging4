<?php
/**
 * The template used to display the suggested amounts field.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/form-fields/sponsors.php
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

// echo $key;
// echo "<pre>";
// print_r($value);
// echo "</pre>";
?>
<table id="charitable-campaign-sponsors" class="pp-fundraising-table charitable-campaign-form-table charitable-repeatable-form-field-table">	
	<thead>
		<tr>
			<td class="icon"><?php echo (isset($view_args['field']['icon_url'])) ? '<img src="'.$view_args['field']['icon_url'].'">' : ''; ?></td>
			<td class="desc">
				<h3><?php _e('Sponsor Recognition', 'pp-toolkit'); ?></h3>
				<p><?php _e( 'You can upload sponsor logos to be displayed on your campaign page.', 'pp-toolkit' ); ?></p>
			</td>
			<td class="button-add"><a class="add-row" href="#" data-charitable-add-row="sponsors"><?php _e( '+ ADD TO CAMPAIGN', 'pp-toolkit' ); ?></a></td>
		</tr>
	</thead>
	<tbody>
		<?php
		foreach ( $value as $i => $sponsor ) :

			pp_toolkit_template( 'form-fields/sponsors-row.php', array(
				'index'       => $i,
				'key'         => $key,
				'value'      => $sponsor,
			) );

		endforeach;
		?>
	</tbody>
	<tfoot class="add-more <?php echo (empty($value)) ? 'hide' : ''; ?>">
		<tr>
			<td colspan="3"><a class="add-row" href="#" data-charitable-add-row="sponsors"><?php _e( 'Add another sponsor', 'pp-toolkit' ) ?></a></td>
		</tr>
	</tfoot>
</table>