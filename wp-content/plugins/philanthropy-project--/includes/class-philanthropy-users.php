<?php
/**
 * Philanthropy_Users Class.
 *
 * @class       Philanthropy_Users
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Philanthropy_Users class.
 */
class Philanthropy_Users {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_action( 'charitable_after_insert_user', array($this, 'send_email_notification'), 10, 2 );
	}
	public function send_email_notification($user_id, $values){
		
		wp_send_new_user_notifications($user_id, 'admin');
		
		// $password = (isset($values['user_pass'])) ? $values['user_pass'] : '-';
		// $login_url = home_url( 'profile' );
		// $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		// $user = get_userdata( $user_id );

		// $message = sprintf(__('Hi %s, thank you for registering on %s'), $user->display_name, $blogname) . "\r\n\r\n";
		// $message .= __('Here your login info:') . "\r\n\r\n";
		// $message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n";
		// $message .= sprintf(__('Password: %s'), $password) . "\r\n\r\n";
		// $message .= __('To edit your profile, please visit the following address:') . "\r\n\r\n";

		// $message .= sprintf( __('<a href="%s">%s</a>'), $login_url, $login_url ) . "\r\n";

		// wp_mail($user->user_email, sprintf(__('[%s] Your username and password info'), $blogname), $message);
	}

	public function includes(){
		
	}

}

$GLOBALS['philanthropy_users'] = new Philanthropy_Users();