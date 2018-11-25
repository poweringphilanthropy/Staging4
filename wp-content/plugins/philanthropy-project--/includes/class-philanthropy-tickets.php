<?php
/**
 * Philanthropy_Tickets Class.
 *
 * @class       Philanthropy_Tickets
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Philanthropy_Tickets class.
 */
class Philanthropy_Tickets {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_action( 'init', array($this, 'change_scripts'), 90 );
	}

	public function change_scripts(){
		/**
		 * We need to change the default event ticket plus js
		 * to support multi form
		 * because we have 2 form (on page and on modal)
		 */
		wp_deregister_script('event-tickets-meta');
		wp_register_script(
			'event-tickets-meta',
			Philanthropy()->plugin_url() . '/assets/event-tickets-plus/meta.js',
			array( 'jquery-cookie', 'jquery-deparam' ),
			Tribe__Tickets__Main::instance()->js_version(),
			true
		);
	}

	public function includes(){
		
	}

}

$GLOBALS['philanthropy_tickets'] = new Philanthropy_Tickets();