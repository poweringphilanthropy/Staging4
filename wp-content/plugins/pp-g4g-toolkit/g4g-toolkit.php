<?php
/**
 * Plugin Name:     Philanthropy - Greeks4good Toolkit
 * Plugin URI:      http://www.poweringphilanthropy.com/
 * Description:     Custom features for The g4g
 * Author:          Powering Philanthropy
 * Author URI:      http://www.poweringphilanthropy.com/
 * Author Email:    info@poweringphilanthropy.com
 * Version:         1.0
 * Text Domain:     g4g
 * Domain Path:     /languages/ 
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'g4g' ) ) :

/**
 * Main g4g Class
 *
 * @class g4g
 * @version	1.0
 */
final class g4g {

	/**
	 * @var string
	 */
	public $version = '1.0';

	public $capability = 'manage_options';

	/**
	 * @var g4g The single instance of the class
	 * @since 1.0
	 */
	protected static $_instance = null;

	/**
	 * Main g4g Instance
	 *
	 * Ensures only one instance of g4g is loaded or can be loaded.
	 *
	 * @since 1.0
	 * @static 
	 * @return g4g - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		self::$_instance->user_service_hours_db = new G4G_User_Service_Hours_DB();

		return self::$_instance;
	}

	/**
	 * g4g Constructor.
	 */
	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		do_action( 'g4g_loaded' );
	}

	/**
	 * Hook into actions and filters
	 * @since  1.0
	 */
	private function init_hooks() {

		register_activation_hook( __FILE__, array( $this, 'install' ) );

		add_action( 'init', array( $this, 'init' ), 0 );

		register_uninstall_hook( __FILE__, 'uninstall' );
	}

	/**
	 * All install stuff
	 * @return [type] [description]
	 */
	public function install() {
		do_action( 'g4g_install' );
	}

	/**
	 * All uninstall stuff
	 * @return [type] [description]
	 */
	public function uninstall() {
		do_action( 'g4g_uninstall' );
	}

	/**
	 * Init g4g when WordPress Initialises.
	 */
	public function init() {
		// Before init action
		do_action( 'before_g4g_init' );

		// register all scripts
		$this->register_scripts();

		// Init action
		do_action( 'after_g4g_init' );
	}

	/**
	 * Register all scripts to used on our pages
	 * @return [type] [description]
	 */
	public function register_scripts(){

		if ( $this->is_request( 'admin' ) ){

		}

		if ( $this->is_request( 'frontend' ) ){
			wp_register_script( 'g4g', $this->plugin_url() . '/assets/js/g4g.js', array('jquery', 'pp'), $this->version, true );
		}
 	}

	/**
	 * Define g4g Constants
	 */
	private function define_constants() {
		global $wpdb;

		$this->define( 'G4G_TOOLKIT_PLUGIN_FILE', __FILE__ );
		$this->define( 'G4G_TOOLKIT_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		$this->define( 'G4G_TOOLKIT_VERSION', $this->version );
		$this->define( 'G4G_USER_SERVICE_HOURS_TABLE_NAME', $wpdb->prefix . 'user_service_hours' );
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
        
        /**
         * DEPENDENCIES
         */
        if(file_exists( __DIR__ . '/vendor/autoload.php') ){
            require __DIR__ . '/vendor/autoload.php';
        }

		// load helpers
		include_once( 'includes/helpers/class-g4g-template.php' );

		// dbs
		include_once( 'includes/db/class-g4g-user-service-hours-db.php' );

		// all public includes
		include_once( 'includes/functions.php' );
		include_once( 'includes/class-g4g-users.php' );
		include_once( 'includes/class-g4g-campaign.php' );
		include_once( 'includes/class-g4g-college.php' );
		include_once( 'includes/class-g4g-covers-fee.php' );
		include_once( 'includes/class-g4g-campaign-submissions.php' );
		include_once( 'includes/class-g4g-service-hours.php' );

        include_once( 'includes/class-g4g-salesforce.php' );

		if ( $this->is_request( 'admin' ) ) {

		}

		if ( $this->is_request( 'ajax' ) ) {
			// include_once( 'includes/ajax/..*.php' );
		}

		if ( $this->is_request( 'frontend' ) ) {

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
 * Notice if woocommerce not activated
 * @return [type] [description]
 */
function g4g_activation_notices(){
	?>
    <div class="updated">
        <p>can not run</p>
    </div>
    <?php
}

/**
 * Returns the main instance of g4g to prevent the need to use globals.
 *
 * @since  1.0
 * @return g4g
 */
function g4g() {
	// if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		return g4g::instance();
	// } else {
	// 	add_action( 'admin_notices', 'g4g_activation_notices' );
	// }
}

g4g();