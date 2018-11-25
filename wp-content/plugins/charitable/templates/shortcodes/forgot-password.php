<?php
/**
 * The template used to display the forgot password form. Provided here primarily as a way to make it easier to override using theme templates.
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
<div class="charitable-forgot-password-form">
	<?php
	/**
	* Do something before the forgot password form.
	*
	* @since 1.4.0
	*
	* @param array $view_args Shortcode attributes.
	*/
	do_action( 'charitable_forgot_password_before', $view_args );

	?>
	<form id="lostpasswordform" class="charitable-form" action="<?php echo wp_lostpassword_url() ?>" method="post">
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
			<?php $form->view()->render() ?>
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
		<div class="charitable-form-field charitable-submit-field">
			<input type="submit" name="submit" class="lostpassword-button" value="<?php esc_attr_e( 'Reset Password', 'charitable' ) ?>" />
		</div>

	</form><!-- #lostpasswordform -->
	<?php
	/**
	 * Do something after the forgot password form.
	 * @since 1.4.0
	 *
	 * @param array $view_args Shortcode attributes.
	 */
	do_action( 'charitable_forgot_password_after', $view_args );

	?>
</div>
