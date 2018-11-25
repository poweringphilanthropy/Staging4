<?php
/**
 * The main Charitable EDD Connect class.
 *
 * The responsibility of this class is to load all the plugin's functionality.
 *
 * @package   Charitable EDD Connect
 * @copyright Copyright (c) 2017, Eric Daams
 * @license   http://opensource.org/licenses/gpl-1.0.0.php GNU Public License
 * @since     1.0.0
 * @version   1.1.4
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_EDD' ) ) :

	/**
	 * Charitable_EDD
	 *
	 * @since 1.0.0
	 */
	class Charitable_EDD {

		/* @var string */
		const VERSION = '1.1.4';

		/* @var string */
		const DB_VERSION = '20150213';

		/* @var string */
		const NAME = 'Charitable Easy Digital Downloads Connect';

		/* @var string */
		const AUTHOR = 'Studio 164a';

		/**
		 * @var Charitable_EDD
		 */
		private static $instance = null;

		/**
		 * The root file of the plugin.
		 *
		 * @var 	string
		 * @access  private
		 */
		private $plugin_file;

		/**
		 * The root directory of the plugin.
		 *
		 * @var 	string
		 * @access  private
		 */
		private $directory_path;

		/**
		 * The root directory of the plugin as a URL.
		 *
		 * @var 	string
		 * @access  private
		 */
		private $directory_url;

		/**
		 * @var 	array       Store of registered objects.
		 * @access  private
		 */
		private $registry;

		/**
		 * Create class instance.
		 *
		 * @return 	void
		 * @since 	1.0.0
		 */
		public function __construct( $plugin_file ) {
			$this->plugin_file 		= $plugin_file;
			$this->directory_path 	= plugin_dir_path( $plugin_file );
			$this->directory_url 	= plugin_dir_url( $plugin_file );

			add_action( 'charitable_start', array( $this, 'start' ), 5 );
		}

		/**
		 * Returns the original instance of this class.
		 *
		 * @return 	Charitable_EDD
		 * @since 	1.0.0
		 */
		public static function get_instance() {
			return self::$instance;
		}

		/**
		 * Run the startup sequence on the charitable_start hook.
		 *
		 * This is only ever executed once.
		 *
		 * @return 	void
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function start() {
			// If we've already started (i.e. run this function once before), do not pass go.
			if ( $this->started() ) {
				return;
			}

			// Set static instance
			self::$instance = $this;

			$this->load_dependencies();

			$this->attach_hooks_and_filters();

			$this->setup_licensing();

			$this->setup_i18n();

			$this->maybe_permit_guest_donors();

			$this->maybe_start_admin();

			$this->maybe_start_public();

			// Hook in here to do something when the plugin is first loaded.
			do_action( 'charitable_edd_start', $this );
		}

		/**
		 * Include necessary files.
		 *
		 * @return 	void
		 * @access 	private
		 * @since 	1.0.0
		 */
		private function load_dependencies() {
			require_once( $this->get_path( 'includes' ) . 'charitable-edd-core-functions.php' );
			require_once( $this->get_path( 'includes' ) . 'class-charitable-edd-campaign.php' );

			/* Donations */
			require_once( $this->get_path( 'includes' ) . 'donations/class-charitable-edd-donation-form.php' );
			require_once( $this->get_path( 'includes' ) . 'donations/class-charitable-edd-payment.php' );
			require_once( $this->get_path( 'includes' ) . 'donations/class-charitable-edd-checkout.php' );
			require_once( $this->get_path( 'includes' ) . 'donations/class-charitable-edd-cart.php' );
			require_once( $this->get_path( 'includes' ) . 'donations/charitable-edd-donation-hooks.php' );

			require_once( $this->get_path( 'includes' ) . 'db/class-charitable-edd-benefactors-db.php' );
			require_once( $this->get_path( 'includes' ) . 'widgets/class-charitable-edd-campaign-downloads.php' );
		}

		/**
		 * Set up hook and filter callback functions.
		 *
		 * @return 	void
		 * @access 	private
		 * @since 	1.0.0
		 */
		private function attach_hooks_and_filters() {
			add_action( 'charitable_benefactor_added', array( 'Charitable_EDD_Benefactors_DB', 'charitable_benefactor_added' ), 10, 3 );
			add_action( 'charitable_benefactor_updated', array( 'Charitable_EDD_Benefactors_DB', 'charitable_benefactor_updated' ), 10, 3 );
			add_action( 'charitable_benefactor_deleted', array( 'Charitable_EDD_Benefactors_DB', 'charitable_benefactor_deleted' ), 10, 3 );

			/* Flush caches when benefactors are added, updated or deleted. */
			add_action( 'charitable_benefactor_added', 'wp_cache_flush' );
			add_action( 'charitable_benefactor_updated', 'wp_cache_flush' );
			add_action( 'charitable_benefactor_deleted', 'wp_cache_flush' ); 

			add_action( 'widgets_init', array( $this, 'register_widgets' ) );
			add_action( 'charitable_donation_amount_form_submit', array( 'Charitable_EDD_Cart', 'add_donation_fee_to_cart' ), 10, 2 );
			add_action( 'edd_remove_fee', array( 'Charitable_EDD_Cart', 'remove_donation_fee_from_session' ), 9 );
			add_action( 'charitable_benefactors_addon_loaded', array( $this, 'load_addon_class' ) );

			add_filter( 'charitable_active_addons', array( $this, 'load_benefactors_addon' ) );
			add_filter( 'charitable_db_tables', array( $this, 'register_table' ) );
			add_filter( 'charitable_donation_form_class', array( $this, 'register_donation_form_class' ) );
			add_filter( 'charitable_streamlined_donation_form_class', array( $this, 'register_donation_form_class' ) );
			add_filter( 'charitable_benefactor_class_charitable-edd', array( $this, 'get_benefactor_class' ) );
			add_filter( 'charitable_get_campaign_benefactors', array( $this, 'get_campaign_benefactors' ), 10, 3 );
			add_filter( 'charitable_benefactor_contribution_type', array( $this, 'benefactor_contribution_type' ), 10, 3 );
			add_filter( 'charitable_require_user_account', array( $this, 'maybe_permit_guest_donors' ) );
			add_filter( 'charitable_donation_amount_form_redirect', array( $this, 'set_donation_amount_form_redirect_url' ) );
			add_filter( 'charitable_ajax_make_donation_data', array( $this, 'send_cart_data_on_ajax_donation' ) );
			add_filter( 'charitable_donation_gateway_label', array( $this, 'set_gateway_label' ), 10, 2 );
			add_filter( 'charitable_public_form_view_custom_field_templates', array( $this, 'set_custom_field_templates' ) );
			add_filter( 'charitable_in_test_mode', 'edd_is_test_mode' );
			add_filter( 'charitable_has_active_gateways', '__return_true' );
			add_filter( 'charitable_active_gateways', '__return_empty_array' );
		}

		/**
		 * Set up licensing for the extension.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function setup_licensing() {
			charitable_get_helper( 'licenses' )->register_licensed_product(
				Charitable_EDD::NAME,
				Charitable_EDD::AUTHOR,
				Charitable_EDD::VERSION,
				$this->plugin_file
			);
		}

		/**
		 * Set up the internationalisation for the plugin.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.1.2
		 */
		private function setup_i18n() {
			if ( class_exists( 'Charitable_i18n' ) ) {

				require_once( $this->get_path( 'includes' ) . 'i18n/class-charitable-edd-i18n.php' );

				Charitable_EDD_i18n::get_instance();
			}
		}

		/**
		 * Start admin functionality.
		 *
		 * @return 	void
		 * @access  private
		 * @since 	1.0.0
		 */
		private function maybe_start_admin() {
			if ( ! is_admin() ) {
				return;
			}

			require_once( $this->get_path( 'admin' ) . 'class-charitable-edd-admin.php' );
			require_once( $this->get_path( 'admin' ) . 'charitable-edd-admin-hooks.php' );
		}

		/**
		 * Start publi functionality.
		 *
		 * @return 	void
		 * @access 	private
		 * @since 	1.0.0
		 */
		private function maybe_start_public() {
			if ( is_admin() ) {
				return;
			}

			require_once( $this->get_path( 'includes' ) . 'public/class-charitable-edd-public.php' );

			Charitable_EDD_Public::start( $this );
		}

		/**
		 * Returns whether we are currently in the start phase of the plugin.
		 *
		 * @return 	bool
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function is_start() {
			return current_filter() == 'charitable_edd_start';
		}

		/**
		 * Returns whether the plugin has already started.
		 *
		 * @return 	bool
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function started() {
			return did_action( 'charitable_edd_start' ) || current_filter() == 'charitable_edd_start';
		}

		/**
		 * Returns the plugin's version number.
		 *
		 * @return 	string
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function get_version() {
			return self::VERSION;
		}

		/**
		 * Returns plugin paths.
		 *
		 * @param 	string $path 			// If empty, returns the path to the plugin.
		 * @param 	bool $absolute_path 	// If true, returns the file system path. If false, returns it as a URL.
		 * @return 	string
		 * @since 	1.0.0
		 */
		public function get_path( $type = '', $absolute_path = true ) {
			$base = $absolute_path ? $this->directory_path : $this->directory_url;

			switch ( $type ) {
				case 'includes' :
					$path = $base . 'includes/';
					break;

				case 'admin' :
					$path = $base . 'includes/admin/';
					break;

				case 'templates' :
					$path = $base . 'templates/';
					break;

				case 'assets' :
					$path = $base . 'assets/';
					break;

				case 'directory' :
					$path = $base;
					break;

				default :
					$path = $this->plugin_file;
			}

			return $path;
		}

		/**
		 * Register custom widgets.
		 *
		 * @return 	void
		 * @access  public
		 * @since 	1.0.0
		 */
		public function register_widgets() {
			register_widget( 'Charitable_EDD_Campaign_Downloads' );
		}

		/**
		 * Load the addon benefactor class.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function load_addon_class() {
			require_once( $this->get_path( 'includes' ) . 'class-charitable-edd-benefactor.php' );
		}

		/**
		 * Register table.
		 *
		 * @param 	array 		$tables
		 * @return 	array
		 * @access  public
		 * @since 	1.0.0
		 */
		public function register_table( $tables ) {
			$tables['edd_benefactors'] = 'Charitable_EDD_Benefactors_DB';
			return $tables;
		}

		/**
		 * Register the Charitable_EDD_Donation_Form object as the handler.
		 *
		 * @return 	string
		 * @access  public
		 * @since 	1.0.0
		 */
		public function register_donation_form_class() {
			return 'Charitable_EDD_Donation_Form';
		}

		/**
		 * Stores an object in the plugin's registry.
		 *
		 * @param 	mixed 		$object
		 * @return 	void
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function register_object( $object ) {
			if ( ! is_object( $object ) ) {
				return;
			}

			$class = get_class( $object );

			$this->registry[ $class ] = $object;
		}

		/**
		 * Returns a registered object.
		 *
		 * @param 	string 		$class 	The type of class you want to retrieve.
		 * @return 	mixed 				The object if its registered. Otherwise false.
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function get_object( $class ) {
			return isset( $this->registry[ $class ] ) ? $this->registry[ $class ] : false;
		}

		/**
		 * Register the Benefactors Addon.
		 *
		 * @param 	array 		$active_addons
		 * @return 	array
		 * @access  public
		 * @since 	1.0.0
		 */
		public function load_benefactors_addon( $active_addons ) {
			if ( ! in_array( 'charitable-benefactors', $active_addons ) ) {
				$active_addons[] = 'charitable-benefactors';
			}

			return $active_addons;
		}

		/**
		 * If EDD is set up to accept guest checkouts, we'll accept guest donors too.
		 *
		 * @return 	boolean
		 * @access  public
		 * @since 	1.0.0
		 */
		public function maybe_permit_guest_donors() {
			global $edd_options;

			return false === ( isset( $edd_options['logged_in_only'] ) && $edd_options['logged_in_only'] );
		}

		/**
		 * Set page to redirect to EDD checkout when the amount form has been submitted.
		 *
		 * @param 	string 	$redirect_url
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function set_donation_amount_form_redirect_url( $redirect_url ) {
			return edd_get_checkout_uri();
		}

		/**
		 * Send current cart data back as response when donating via AJAX submission.
		 *
		 * @param 	mixed[] $data
		 * @return  mixed[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function send_cart_data_on_ajax_donation( $data ) {
			$data['edd_cart'] = edd_get_cart_contents();
			return $data;
		}

		/**
		 * Return benefactors for campaign.
		 *
		 * @param 	Object|false 		$ret
		 * @param 	int 				$campaign_id
		 * @param 	string 				$extension
		 * @return 	Object|false 		False if not calling our extension. Object otherwise.
		 * @access  public
		 * @since 	1.0.0
		 */
		public function get_campaign_benefactors( $ret, $campaign_id, $extension ) {
			if ( 'charitable-edd' == $extension ) {
				$ret = charitable_get_table( 'edd_benefactors' )->get_campaign_benefactors( $campaign_id, false );
			}

			return $ret;
		}

		/**
		 * Returns the benefactor class name.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_benefactor_class() {
			return 'Charitable_EDD_Benefactor';
		}

		/**
		 * Customize the description of the type of the benefactor relationship.
		 *
		 * @param 	string 					$contribution_type
		 * @param 	boolean 				$per_item
		 * @param 	Charitable_Benefactor 	$benefactor
		 * @return 	string
		 * @access  public
		 * @since 	1.0.0
		 */
		public function benefactor_contribution_type( $contribution_type, $per_item, Charitable_Benefactor $benefactor ) {

			/** Verify that this is an EDD benefactor relationship **/
			if ( ! is_null( $benefactor->edd_is_global_contribution ) ) {

				$edd_benefactor = new Charitable_EDD_Benefactor( $benefactor );
				$contribution_type = $edd_benefactor->get_contribution_type( $per_item );
			}

			return $contribution_type;
		}

		/**
		 * Set the gateway label of a donation to EDD if it was made through EDD.
		 *
		 * @param 	string $label
		 * @param 	Charitable_Donation $donation
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function set_gateway_label( $label, Charitable_Donation $donation ) {
			if ( 'EDD' != strtoupper( $label ) ) {
				return $label; 
			}

			$payment_id = Charitable_EDD_Payment::get_payment_for_donation( $donation->ID );

			return sprintf( '%s (%s)', edd_get_gateway_checkout_label( edd_get_payment_gateway( $payment_id ) ), 'EDD' );
		}

		/**
		 * Define our custom field template.
		 *
		 * @since  1.1.3
		 *
		 * @param  array $templates Array of custom templates.
		 * @return array
		 */
		public function set_custom_field_templates( $templates ) {
			return array_merge( $templates, array(
				'edd-downloads' => array( 'class' => 'Charitable_EDD_Template', 'path' => 'donation-form/edd-downloads.php' ),
			) );
		}

		/**
		 * Throw error on object clone.
		 *
		 * This class is specifically designed to be instantiated once. You can retrieve the instance using charitable()
		 *
		 * @since 	1.0.0
		 * @access 	public
		 * @return 	void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'charitable-edd' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since 	1.0.0
		 * @access 	public
		 * @return 	void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'charitable-edd' ), '1.0.0' );
		}
	}

endif; // End if class_exists check
