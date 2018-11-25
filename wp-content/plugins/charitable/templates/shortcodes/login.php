<?php
/**
 * The template used to display the login form. Provided here primarily as a way to make
 * it easier to override using theme templates.
 *
 * Override this template by copying it to yourtheme/charitable/shortcodes/login.php
 *
 * @author  Eric Daams
 * @package Charitable/Templates/Account
 * @since   1.0.0
 * @version 1.5.7
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

$login_form_args = array_key_exists( 'login_form_args', $view_args ) ? $view_args['login_form_args'] : array();

?>
<div class="charitable-login-form">
	<?php

	/**
	 * Do something before the login form.
	 *
	 * @param array $view_args All args passed to template.
	 */
	do_action( 'charitable_login_form_before', $view_args );

	wp_login_form( $login_form_args );

	?>
	<p>
		<?php if ( array_key_exists( 'registration_link', $view_args ) && $view_args['registration_link'] ) : ?>
			<a href="<?php echo esc_url( $view_args['registration_link'] ) ?>"><?php echo $view_args['registration_link_text'] ?></a>&nbsp;|&nbsp;
		<?php endif ?>
		<a href="<?php echo esc_url( charitable_get_permalink( 'forgot_password_page' ) ) ?>"><?php _e( 'Forgot Password', 'charitable' ) ?></a>
	</p>
	<?php

	/**
	 * Do something after showing the login form.
	 *
	 * @param array $view_args All args passed to template.
	 */
	do_action( 'charitable_login_form_after', $view_args )

	?>
</div>
