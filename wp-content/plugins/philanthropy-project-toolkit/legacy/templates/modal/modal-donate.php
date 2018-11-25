<?php 
/**
 * Displays the modal content
 *
 * @author Lafif <[<email address>]>
 * @since   1.0
 */

$campaign = $view_args[ 'campaign' ];
$form = new Charitable_Donation_Amount_Form( $campaign );
$user_fields = $form->get_user_fields();
$user        = wp_get_current_user();
$use_ajax    = 'make_donation' == $form->get_form_action() && (int) Charitable_Gateways::get_instance()->gateways_support_ajax();
$form_id     = isset( $view_args['form_id'] ) ? $view_args['form_id'] : 'charitable-donation-form';

if ( ! $form ) {
	return;
}

?>
<div class="p_popup" data-p_popup="<?php echo $view_args[ 'id' ]; ?>">
    <div class="p_popup-inner">
        <a class="p_popup-close" data-p_popup-close="<?php echo $view_args[ 'id' ] ?>" href="#">x</a>

        <?php  
		// $form->render();

		// charitable_template( 'donation-form/form-donation.php', array(
		// 		'campaign' => $campaign,
		// 		'form' => $form,
		// 	) ); 
		// do_action( 'charitable_donation_form_donor_fields_before', $form );
        ?>
        
    	<form method="post" id="<?php echo esc_attr( $form_id ) ?>" class="philanthropy-modal form-donate" data-action="donate" data-use-ajax="<?php echo esc_attr( $use_ajax ) ?>" style="padding: 0px;">
	        <div class="p_popup-header">
				<h2 class="p_popup-title"><span><?php echo $view_args[ 'label' ] ?></span></h2>
				<div class="p_popup-notices uk-width-1-1"></div>
	        </div>
	        <div class="p_popup-content">
				<?php

				if ( $campaign->has_ended() ) :
					
					echo sprintf('<p>This campaign is no longer accepting donations</p>');

				else :
					/**
					 * @hook    charitable_form_before_fields 
					 */
					do_action( 'charitable_form_before_fields', $form ) ?>
					
					<div class="charitable-form-fields cf">        
					<?php
					$form->view()->render_notices();
					$form->view()->render_honeypot();
					$form->view()->render_hidden_fields();

					$i = 1;

					foreach ( $form->get_fields() as $key => $field ) :
						$form->view()->render_field( $field, $key, array(
							'index' => $i,
						) );

						$i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form , $i);

						// do_action( 'charitable_form_field', $field, $key, $form, $i );

						// $i += apply_filters( 'charitable_form_field_increment', 1, $field, $key, $form, $i );

					endforeach;

					?>
					
					</div>

					<?php
					/**
					 * @hook    charitable_form_after_fields
					 */
					do_action( 'charitable_form_after_fields', $form );
					
					
					do_action( 'charitable_donation_form_donor_fields_after', $form );

				endif;
				?>
	        </div>
	        <div class="p_popup-footer">

	        	<?php if ( ! $campaign->has_ended() ) : ?>
				<div class="p_popup-submit-field">
					<button class="button button-primary philanthropy-add-to-cart  uk-width-1-1 uk-width-small-5-10 uk-width-medium-4-10" type="submit" name="add_to_cart"><?php _e( 'Donate', 'philanthropy' ); ?></button>
					<a href="<?php echo edd_get_checkout_uri(); ?>" class="button philanthropy-go-to-checkout  uk-width-1-1 uk-width-small-5-10"><?php _e('Proceed to Checkout', 'philanthropy'); ?></a>
				</div>
				<?php endif; ?>
	        </div>
	    </form>
    </div>
</div>