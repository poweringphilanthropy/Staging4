<?php
/**
 * The template used to display the login form.
 *
 * @author  Studio 164a
 * @package Charitable/Templates/Donation Form
 * @since   1.0.0
 * @version 1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<p class="login-prompt">
	<a href="#" data-charitable-toggle="charitable-donation-login-form"><?php _e( 'Registered before? Log in to use your saved details.', 'charitable' ); ?></a>
</p>
<div id="charitable-donation-login-form" class="charitable-login-form charitable-form">
	<p><?php _e( 'If you registered an account, please enter your details below to login. If this is your first time, proceed to the donation form.', 'charitable' ); ?></p>
	<?php
	wp_login_form(
		array(
			/**
			 * Filter whether usernames are used for donors.
			 *
			 * @since 1.0.0
			 *
			 * @param boolean $usernames Whether usernames are used.
			 */
			'label_username' => apply_filters( 'charitable_donor_usernames', false ) ? __( 'Username', 'charitable' ) : __( 'Email', 'charitable' ),
		)
	);
	?>
</div>
