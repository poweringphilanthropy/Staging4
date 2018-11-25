<?php
/**
 * The template used to display the reset password form. Provided here
 * primarily as a way to make it easier to override using theme templates.
 *
 * Override this template by copying it to yourtheme/charitable/account/reset-password.php
 *
 * @author  Rafe Colton
 * @package Charitable/Templates/Account
 * @since   1.4.0
 * @version 1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {exit; } // Exit if accessed directly

/**
 * @var 	Charitable_Reset_Password_Form
 */
$form = $view_args['form'];

?>
<div class="charitable-reset-password-form">
	<?php 
	if ( $form->has_key() ) :

		/**
		 * @hook charitable_reset_password_before
		 */
		do_action( 'charitable_reset_password_before' );
	
		?>
		<form id="resetpassform" class="charitable-form" method="post" autocomplete="off">

			<?php do_action( 'charitable_form_before_fields', $form ); ?>

			<div class="charitable-form-fields cf">
				<?php $form->view()->render() ?>
				<p class="description"><?php echo wp_get_password_hint() ?></p>
			</div><!-- .charitable-form-fields -->

			<?php do_action( 'charitable_form_after_fields', $form ); ?>

			<div class="charitable-form-field charitable-submit-field resetpass-submit">
				<button id="resetpass-button" class="button button-primary lostpassword-button" type="submit"><?php _e( 'Reset Password', 'charitable' ) ?></button>
			</div>
		</form>
		<?php

		/**
		 * @hook charitable_reset_password_after
		 */
		do_action( 'charitable_reset_password_after' );

	else :

		$errors = charitable_get_notices()->get_errors();

		if ( ! empty( $errors ) ) {
			charitable_template( 'form-fields/errors.php', array(
				'errors' => $errors,
			) );
		}

	endif;

	?>
</div><!-- .charitable-reset-password-form -->
