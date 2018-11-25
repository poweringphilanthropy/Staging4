<?php
/**
 * The template used to display the reset password form. Provided here
 * primarily as a way to make it easier to override using theme templates.
 *
 * @author  Rafe Colton
 * @package Charitable/Templates/Account
 * @since   1.4.0
 * @version 1.5.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

$form = $view_args['form'];

?>
<div class="charitable-reset-password-form">
	<?php
	/**
	 * Do something before rendering the password reset form.
	 *
	 * @param array $view_args All args passed to template.
	 */
	do_action( 'charitable_reset_password_before', $view_args );

	?>
	<form id="resetpassform" class="charitable-form" action="<?php echo site_url( 'wp-login.php?action=resetpass' ); ?>" method="post" autocomplete="off">
		<?php
		/**
		 * Do something before rendering the form fields.
		 *
		 * @since 1.4.0
		 *
		 * @param Charitable_Form $form      The form object.
		 * @param array           $view_args All args passed to template.
		 */
		do_action( 'charitable_form_before_fields', $form, $view_args );

		?>
		<div class="charitable-form-fields cf">
			<input type="hidden" id="user_login" name="rp_login" value="<?php echo esc_attr( $view_args['login'] ); ?>" autocomplete="off" >
			<input type="hidden" name="rp_key" value="<?php echo esc_attr( $view_args['key'] ); ?>" >
			<?php $form->view()->render() ?>
			<p class="description"><?php echo wp_get_password_hint(); ?></p>
		</div><!-- .charitable-form-fields -->
		<?php
		/**
		 * Do something after rendering the form fields.
		 *
		 * @since 1.4.0
		 *
		 * @param Charitable_Form $form      The form object.
		 * @param array           $view_args All args passed to template.
		 */
		do_action( 'charitable_form_after_fields', $form, $view_args );

		?>
		<div class="charitable-form-field charitable-submit-field resetpass-submit">
			<input type="submit" name="submit" class="lostpassword-button" id="resetpass-button"
			 value="<?php _e( 'Reset Password', 'charitable' ); ?>">
		</div>
	</form><!-- #resetpassform -->
	<?php
	/**
	 * Do something before rendering the password reset form.
	 *
	 * @param array $view_args All args passed to template.
	 */
	do_action( 'charitable_reset_password_after', $view_args );

	?>
</div>
