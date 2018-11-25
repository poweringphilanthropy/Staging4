<?php
/**
 * The template used to display the suggested amounts field.
 *
 * Override this template by copying it to yourtheme/charitable/charitable-ambassadors/form-fields/referrer.php
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
$value = isset( $field['value'] ) ? $field['value'] : '';
$campaign = $form->get_campaign();

?>
<table id="charitable-campaign-referrer" class="pp-fundraising-table charitable-campaign-form-table charitable-repeatable-form-field-table">	
	<thead>
		<tr>
			<td class="icon"><?php echo (isset($view_args['field']['icon_url'])) ? '<img src="'.$view_args['field']['icon_url'].'">' : ''; ?></td>
			<td class="desc">
				<h3><?php _e('Track Member Participation & Fundraising Totals', 'pp-toolkit'); ?></h3>
				<p><?php _e( 'Add a list of your members in order to keep track of individual fundraising totals and participation (broken down by donations, ticket sales and merchandise sales). Guests choose from the list of member names at checkout.', 'pp-toolkit' ); ?></p>
			</td>
			<td class="button-add"><a class="add-row" href="#" data-charitable-add-row="referrer"><?php _e( '+ ADD TO CAMPAIGN', 'pp-toolkit' ); ?></a></td>
		</tr>
	</thead>
	<tbody>
		<tr class="referrer <?php echo (empty($value)) ? 'hidden' : '' ; ?>">
			<td colspan="3">
				<div class="charitable-form-field" style="width: 100%;">
					<label for="<?php echo $key; ?>"><?php _e('Member Names', 'pp-toolkit'); ?></label>
					<textarea name="<?php echo $key; ?>" id="<?php echo $key; ?>" cols="30" rows="10" placeholder="<?php _e('Add member names separated by new line (enter)', 'pp-toolkit'); ?>"><?php echo $value ?></textarea>
				</div>
				<?php $campaign->ID;?>
				<input type="checkbox" name="display_top_fundraiser_widget" value="on" <?php checked( get_post_meta( $campaign->ID, 'display_top_fundraiser_widget', true), 'yes'); ?>> <?php _e('Display top fundraisers widget on campaign?', 'pp-toolkit'); ?>
			</td>
		</tr>
	</tbody>
</table>