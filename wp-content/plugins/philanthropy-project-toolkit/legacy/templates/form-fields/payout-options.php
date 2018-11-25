<?php 
/**
 * The template used to display radio form fields.
 *
 * Override this template by copying it to yourtheme/charitable/form-fields/radio.php
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Form Fields
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! isset( $view_args[ 'form' ] ) || ! isset( $view_args[ 'field' ] ) ) {
    return;
}

$form           = $view_args[ 'form' ];
$field          = $view_args[ 'field' ];
$classes        = $view_args[ 'classes' ];
$is_required    = isset( $field[ 'required' ] ) ? $field[ 'required' ] : false;
$options        = isset( $field[ 'options' ] ) ? $field[ 'options' ] : array();
$value          = isset( $field[ 'value' ] ) ? $field[ 'value' ] : '';
$nonce      	= wp_create_nonce( 'pp-payout-options-form' );

if ( empty( $options ) ) {
    return;
}

// echo "val:" . $value;

$campaign = $form->get_campaign();
$disable_edit = method_exists($campaign, 'get_status') && ($campaign->get_status() == 'active' ) && !empty($value);

$classes = str_replace('required-field', '', $classes);
if($disable_edit){
	$classes .= ' disabled';
}

?>
<div id="charitable_field_<?php echo $field['key'] ?>" class="<?php echo $classes ?> payout-options-wrapper" data-nonce="<?php echo $nonce; ?>">
    <?php if ( isset( $field['label'] ) ) : ?>
        <label for="charitable_field_<?php echo $field['key'] ?>">
            <?php echo $field['label'] ?>
            <?php if ( $is_required ) : ?>
                <abbr class="required" title="required">*</abbr>
            <?php endif ?>
        </label>
    <?php endif ?>

    <div class="uk-grid pp-image-radio <?php echo ($is_required) ? 'required-field' : ''; ?>" data-name="<?php echo $field[ 'key' ] ?>" data-label="<?php echo $field[ 'label' ] ?>">
    	<?php foreach ( $options as $option => $data ) : ?>
		<div class="payout-option-container uk-width-1-1 uk-width-medium-1-<?php echo count($options); ?>">
			<div class="radio-options">
				<!-- Radio image -->
	        	<input type="radio" 
	                id="<?php echo $field[ 'key' ] . '-' . $option ?>" 
	                name="<?php echo $field[ 'key' ] ?>"
	                value="<?php echo esc_attr( $option ) ?>"
	                <?php checked( $value, $option ) ?>
	                <?php echo charitable_get_arbitrary_attributes( $field ) ?>
	                <?php disabled($disable_edit); ?>/>

	            <label class="pp-image-radio-icon <?php echo 'label-' . $option; ?>" for="<?php echo $field[ 'key' ] . '-' . $option ?>" style="background-image:url();"><img src="<?php echo $data['icon']; ?>" alt=""></label>
			</div>
			<div class="description">
				<div class="heading-title"><?php _e('PAYMENT TO', 'pp-toolkit'); ?></div>
				<div class="payment-to"><?php echo $data['payment-to']; ?></div>
				<div class="via">
					<?php if($option == 'direct'){
						echo __('Directly', 'pp-toolkit');
					} else {
						echo sprintf(__('via %s', 'pp-toolkit'), $data['label']);
					} ?>
				</div>
			</div>
		</div>
		<?php endforeach ?>
    </div>


	<?php if($disable_edit) : ?>
		<input type="hidden" name="<?php echo $field[ 'key' ] ?>" value="<?php echo $value; ?>">
		<input type="hidden" name="payment_option_locked" value="yes">
		<p>Your campaign is live and payout selection is locked. To change payout details, please email <a href="mailto:<?php echo get_option('admin_email'); ?>"><?php echo get_option('admin_email'); ?></a></p>
	<?php endif; ?>
    
    <div class="payout-options-fields-wrapper <?php echo $disable_edit ? 'hidden' : ''; ?>">
		<?php  
		if(!empty($value)):
			$payout_form = PP_Payout_Options_Form::init();
			$template = new PP_Toolkit_Template("form-fields/payout-form.php", false);
	        $template->set_view_args([
	             'form'  => $payout_form,
	             'fields' => $payout_form->get_fields_by_type($value)
	         ]);
	        $template->render();

		endif;
		?>
    </div>
    <div class="loading" style="display: none;">
		<img src="<?php echo pp_toolkit()->directory_url . 'assets/img/spinner_22x22.gif'; ?>" alt="">
    </div>
</div>