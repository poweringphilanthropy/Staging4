<?php
/**
 * The template used to display the merchandise fields.
 *
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! isset( $view_args[ 'form' ] ) || ! isset( $view_args[ 'field' ] ) ) {
    return;
}

$form 		 = $view_args['form'];
$field 		 = $view_args['field'];
$classes 	 = $view_args['classes'];
$is_required = isset( $field['required'] ) ? $field['required'] : false;
$value		 = isset( $field['value'] ) 	 ? $field['value'] 	: '';

$campaign = $form->get_campaign();
$connected_orgs = pp_get_connected_organizations();
$non_profit = isset($connected_orgs[$value]) ? $connected_orgs[$value] : false;

// echo $campaign->get_status();
// echo "<pre>";
// print_r($non_profit);
// echo "</pre>";

?>
<div id="charitable_field_select_non_profit">
	
	<?php if(!method_exists($campaign, 'get_status') || ($campaign->get_status() != 'active') ): ?>
	<div class="charitable-form-field charitable-form-field-text fullwidth">
		<select name="connected_stripe_id" id="select-non-profit" class="select-non-profit" placeholder="<?php _e('Search organization', 'pp-toolkit'); ?>" required="required" data-orgs="<?php echo htmlspecialchars(json_encode($connected_orgs), ENT_QUOTES, 'UTF-8');; ?>"> 
		<?php if($non_profit && !empty($non_profit) ){
			echo '<option selected value="'.$non_profit->stripe_id.'" selected="selected">'.$non_profit->name.'</option>';
		} ?>
			
		</select>
	</div>
	<?php endif; ?>

	<!-- <div class="charitable-form-field charitable-form-field-text fullwidth"><p class="p-bolder"><?php printf('*** If you\'d like to invite this nonprofit to accept direct payments for your campaign so that your donations can be considered tax deductible, please ask the non profit to register here: <a href="%s" target="_blank">%s</a>', 'https://poweringphilanthropy.com/non-profit-registration/', 'https://poweringphilanthropy.com/non-profit-registration/'); ?></p></div>-->

	<div id="non-profit-data" class="charitable-form-field charitable-form-field-text fullwidth <?php echo (!$non_profit || empty($non_profit) ) ? 'hidden' : ''; ?>">
		<?php 
		// echo "<pre>";
		// print_r($non_profit);
		// echo "</pre>";
		?>

		<div class="current_non_profit_data">
			<div class="org_logo">
				<?php if(isset($non_profit->logo) && !empty($non_profit->logo) ) : ?>
				<img src="<?php echo $non_profit->logo; ?>" alt="<?php echo $non_profit->name; ?>">
				<?php endif; ?>
			</div>
			<div>
				Name: <span class="org_name"><?php echo isset($non_profit->name) ? $non_profit->name : ''; ?></span>
			</div>
			<div>
				Url: <span class="org_link"><?php echo isset($non_profit->url) ? '<a href="'.$non_profit->url.'" target="_blank">'.$non_profit->url.'</a>' : ''; ?></span>
			</div>
			<div>
				Tax ID: <span class="org_tax_id"><?php echo isset($non_profit->tax_id) ? $non_profit->tax_id : ''; ?></span>
			</div>
		</div>
	</div>
</div>