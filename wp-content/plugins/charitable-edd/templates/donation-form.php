<?php
/**
 * Display a donation form.
 *
 * This is a pre-checkout form that allows donors/customers to donate an
 * arbitrary amount and/or choose an associated EDD download to purchase. The
 * downloads listed are any that will contribute to the campaign goal (i.e.
 * there is a beneficiary relationship between the campaign and the download).
 *
 * @author 	Studio 164a
 * @since 	1.0.0
 */

$campaign 		= $view_args['campaign'];
$form 			= $view_args['form'];

if ( $campaign->get( 'edd_show_contribution_options' ) ) :
	wp_enqueue_script( 'charitable-edd-donation-page' );
	?>
<style>
.charitable-edd-connected-download { position: relative; margin-bottom: 1em; overflow:hidden; }
.charitable-edd-download-media { overflow: hidden; float: left; margin-right: 15px; }
.charitable-edd-download-details { float: left; } 
.charitable-edd-contribution-note { float: left; margin: 1em 0; font-weight: bold; font-style: italic; font-size: smaller; }
.charitable-edd-price-options ul { list-style: none; margin: 0; padding: 0; }
</style>
	<?php
endif;
?>
<form id="charitable-donation-form" class="charitable-form charitable-donation-form charitable-edd-donation-form" method="post">
	<?php
	/**
	 * @hook    charitable_form_before_fields
	 */
	do_action( 'charitable_form_before_fields', $form ) ?>
	
	<div class="charitable-form-fields cf">
		<?php if ( method_exists( $form, 'view' ) ) :
			$form->view()->render();
		else :
			charitable_edd_template( 'deprecated/form-fields-loop.php', array( 'form' => $form ) );
		endif; ?>
	</div><!-- .charitable-form-fields -->

	<?php
	/**
	 * @hook    charitable_form_after_fields
	 */
	do_action( 'charitable_form_after_fields', $form );

	?>
	<div class="charitable-form-field charitable-submit-field">
		<button class="button button-primary" type="submit" name="donate"><?php _e( 'Donate', 'charitable-edd' ) ?></button>
		<div class="charitable-form-processing" style="display: none;">
			<img src="<?php echo esc_url( charitable()->get_path( 'assets', false ) ) ?>/images/charitable-loading.gif" width="60" height="60" alt="<?php esc_attr_e( 'Loading&hellip;', 'charitable-edd' ) ?>" />
		</div>
	</div>
</form>
