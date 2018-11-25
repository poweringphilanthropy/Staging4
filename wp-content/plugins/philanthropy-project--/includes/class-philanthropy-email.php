<?php
/**
 * Philanthropy_Email Class.
 *
 * @class       Philanthropy_Email
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Philanthropy_Email class.
 */
class Philanthropy_Email {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_action( 'init', array($this, 'init'), 11 );
		add_filter('wp_mail_from', array($this, 'change_mail_from'), 11, 1);
		add_filter('wp_mail_from_name', array($this, 'change_mail_from_name'), 11, 1);
	}

	public function init(){

		// remove default ninja form message "Thank you for filling out this form."
		remove_action( 'ninja_forms_post_process', 'ninja_forms_email_user', 1000 );
	}

	public function change_mail_from($from_email){

		// only change if from wordpress@sitename
		if ( strpos( strtolower($from_email), 'wordpress') !== false ) {
		    $from_email = get_option('admin_email');
		}

		return $from_email;
	}

	public function change_mail_from_name($from_name){

		// only change if from wordpress@sitename
		if ( strpos( strtolower($from_name), 'wordpress') !== false ) {
		    $from_name = get_bloginfo( 'name' );
		}

		return $from_name;
	}

	public function includes(){
		
	}

}

$GLOBALS['philanthropy_email'] = new Philanthropy_Email();