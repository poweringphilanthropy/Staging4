<?php
/**
 * Plugin Name: Philanthropy Project
 * Description: Custom plugin for philanthropyproject.com
 * Author: Lafif Astahdziq
 * Version: 1.0
 * Text Domain: philanthropy
 * Domain Path: /languages/ 
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'Philanthropy' ) ) :

/**
 * Main Philanthropy Class
 *
 * @class Philanthropy
 * @version	1.0
 */
final class Philanthropy {

	/**
	 * @var string
	 */
	public $version = '1.0';

	public $capability = 'manage_options'; // admin

	/**
	 * @var Philanthropy The single instance of the class
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Main Philanthropy Instance
	 *
	 * Ensures only one instance of Philanthropy is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static
	 * @return Philanthropy - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Philanthropy Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		do_action( 'philanthropy_loaded' );
	}

	/**
	 * Hook into actions and filters
	 * @since  1.0
	 */
	private function init_hooks() {
		add_action( 'init', array($this, 'register_scripts') );
	}

	public function register_scripts(){
		wp_register_style( 'philanthropy', plugins_url( '/assets/css/philanthropy.css', __FILE__ ) );
		wp_register_script( 'philanthropy', plugins_url( '/assets/js/philanthropy.js', __FILE__ ), array('jquery'), $this->version, true );
	}


	/**
	 * Define Philanthropy Constants
	 */
	private function define_constants() {

		$this->define( 'PHILANTHROPY_PLUGIN_FILE', __FILE__ );
		$this->define( 'PHILANTHROPY_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'PHILANTHROPY_VERSION', $this->version );
	}

	/**
	 * Define constant if not already set
	 * @param  string $name
	 * @param  string|bool $value
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * What type of request is this?
	 * string $type ajax, frontend or admin
	 * @return bool
	 */
	public function is_request( $type ) {
		switch ( $type ) {
			case 'admin' :
				return is_admin();
			case 'ajax' :
				return defined( 'DOING_AJAX' );
			case 'cron' :
				return defined( 'DOING_CRON' );
			case 'frontend' :
				return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
		}
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		include_once( 'includes/class-aq_resizer.php' );
		include_once( 'includes/functions-philanthropy.php' );
		include_once( 'includes/class-philanthropy-template.php' );
		include_once( 'includes/class-philanthropy-email.php' );

		// users
		include_once( 'includes/class-philanthropy-users.php' );

		// modal
		include_once( 'includes/class-philanthropy-modal.php' );
		
		include_once( 'includes/class-philanthropy-charitable.php' );
		
		include_once( 'includes/class-philanthropy-edd.php' );
		// include_once( 'includes/class-philanthropy-tickets.php' );
		
		// leaderboard
		include_once( 'includes/class-philanthropy-leaderboard.php' );
		

		include_once( 'includes/class-rename-wp-login.php' );

		if ( $this->is_request( 'admin' ) ) {

		}

		if ( $this->is_request( 'ajax' ) ) {
			// include_once( 'includes/ajax/..*.php' );
		}

		if ( $this->is_request( 'frontend' ) ) {
			include_once( 'includes/class-philanthropy-frontend.php' );
		}
	}

	/**
	 * Get the plugin url.
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}

	/**
	 * Get the plugin path.
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( __FILE__ ) );
	}

	/**
	 * Get Ajax URL.
	 * @return string
	 */
	public function ajax_url() {
		return admin_url( 'admin-ajax.php', 'relative' );
	}

}

endif;

/**
 * Returns the main instance of Philanthropy to prevent the need to use globals.
 *
 * @since  1.0
 * @return Philanthropy
 */
function Philanthropy() {
	return Philanthropy::instance();
}

// Global for backwards compatibility.
$GLOBALS['philanthropy'] = Philanthropy();