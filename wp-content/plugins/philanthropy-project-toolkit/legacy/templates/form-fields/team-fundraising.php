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

?>
<table id="charitable-campaign-team-fundraising" class="pp-fundraising-table charitable-campaign-form-table charitable-repeatable-form-field-table">	
	<thead>
		<tr>
			<td class="icon"><?php echo (isset($view_args['field']['icon_url'])) ? '<img src="'.$view_args['field']['icon_url'].'">' : ''; ?></td>
			<td class="desc">
				<h3><?php _e('Team Member Fundraising Pages', 'pp-toolkit'); ?></h3>
				<p><?php _e( 'Allow members to create their own unique fundraising page on behalf of your campaign (donations raised from team member fundraising pages count towards your overall campaign fundraising goal.)', 'pp-toolkit' ); ?></p>
			</td>
			<td class="button-add">
				<!-- <a class="add-row" href="#" data-charitable-add-row="team-fundraising"><?php // _e( 'ENABLE / DISABLE', 'pp-toolkit' ); ?></a> -->
				<div class="switch-button">
					<input type="checkbox" id="team_fundraising" name="team_fundraising" value="on" <?php checked($value, 'on', true); ?>><label for="team_fundraising">Toggle</label>
				</div>
			</td>
		</tr>
	</thead>
</table>