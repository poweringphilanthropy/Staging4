<?php
/**
 * The template used to display the login fields.
 *
 * @author 	Studio 164a
 * @since   1.0.0
 * @version 1.0.0
 */

$form 			= charitable_get_current_donation_form();
$account_fields = $form->get_user_account_fields();
$user 			= wp_get_current_user();

if ( 0 !== $user->ID ) {
	return;
}

if ( empty( $account_fields ) ) {
	return;
}
?>
<div class="charitable-login-details">
	<h4 class="charitable-form-header"><?php _e( 'Login Details', 'charitable' ) ?></h4>
	<p class="charitable-description"><?php _e( 'When you make your donation, a new donor account will be created for you.', 'charitable' ) ?></p>
	<?php
	/**
	 * @hook 	charitable_donation_form_before_login_fields
	 */
	do_action( 'charitable_donation_form_before_login_fields' );

	foreach ( $account_fields as $key => $field ) :

		do_action( 'charitable_donation_form_user_field', $field, $key, $form );

	endforeach;

	/**
	 * @hook 	charitable_donation_form_after_login_fields
	 */
	do_action( 'charitable_donation_form_after_login_fields' );
	?>
</div>
