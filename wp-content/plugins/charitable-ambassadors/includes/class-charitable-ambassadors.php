<?php
/**
 * The main Charitable Ambassadors class.
 *
 * The responsibility of this class is to load all the plugin's functionality.
 *
 * @package     Charitable Ambassadors
 * @copyright   Copyright (c) 2017, Eric Daams
 * @license     http://opensource.org/licenses/gpl-1.0.0.php GNU Public License
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Ambassadors' ) ) :

	/**
	 * Charitable_Ambassadors
	 *
	 * @since   1.0.0
	 */
	class Charitable_Ambassadors {

		/**
		 * @var string
		 */
		const VERSION = '1.1.18';

		/**
		 * @var string  A date in the format: YYYYMMDD
		 */
		const DB_VERSION = '20150331';

		/**
		 * @var string The product name.
		 */
		const NAME = 'Charitable Ambassadors';

		/**
		 * @var string The product author.
		 */
		const AUTHOR = 'Studio 164a';

		/**
		 * @var Charitable_Ambassadors
		 */
		private static $instance = null;

		/**
		 * The root file of the plugin.
		 *
		 * @var     string
		 * @access  private
		 */
		private $plugin_file;

		/**
		 * The root directory of the plugin.
		 *
		 * @var     string
		 * @access  private
		 */
		private $directory_path;

		/**
		 * The root directory of the plugin as a URL.
		 *
		 * @var     string
		 * @access  private
		 */
		private $directory_url;

		/**
		 * @var     array       Store of registered objects.
		 * @access  private
		 */
		private $registry;

		/**
		 * Create class instance.
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function __construct( $plugin_file ) {
			$this->plugin_file      = $plugin_file;
			$this->directory_path   = plugin_dir_path( $plugin_file );
			$this->directory_url    = plugin_dir_url( $plugin_file );

			add_action( 'charitable_start', array( $this, 'start' ), 5 );
		}

		/**
		 * Returns the original instance of this class.
		 *
		 * @return  Charitable
		 * @since   1.0.0
		 */
		public static function get_instance() {
			return self::$instance;
		}

		/**
		 * Run the startup sequence.
		 *
		 * This is only ever executed once.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function start() {
			// If we've already started (i.e. run this function once before), do not pass go.
			if ( $this->started() ) {
				return;
			}

			// Set static instance
			self::$instance = $this;

			$this->load_dependencies();

			$this->maybe_upgrade();

			$this->finish_install();

			$this->attach_hooks_and_filters();

			$this->setup_licensing();

			$this->setup_i18n();

			$this->maybe_start_admin();

			$this->maybe_start_public();

			/* Hook in here to do something when the plugin is first loaded */
			do_action( 'charitable_ambassadors_start', $this );
		}

		/**
		 * Include necessary files.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function load_dependencies() {
			$includes_dir = $this->get_path( 'includes' );

			require_once( $includes_dir . 'charitable-ambassadors-core-functions.php' );
			require_once( $includes_dir . 'class-charitable-ambassadors-shortcodes.php' );
			require_once( $includes_dir . 'campaigns/class-charitable-ambassadors-campaign.php' );
			require_once( $includes_dir . 'campaigns/charitable-ambassadors-campaign-hooks.php' );
			require_once( $includes_dir . 'campaign-form/class-charitable-ambassadors-campaign-form.php' );
			require_once( $includes_dir . 'campaign-form/class-charitable-ambassadors-campaign-recipient-form.php' );
			require_once( $includes_dir . 'campaign-form/class-charitable-ambassadors-ambassador.php' );
			require_once( $includes_dir . 'campaign-form/class-charitable-ambassadors-personal-cause.php' );
			require_once( $includes_dir . 'campaign-form/charitable-ambassadors-campaign-form-hooks.php' );
			require_once( $includes_dir . 'emails/class-charitable-ambassadors-email-new-campaign.php' );
			require_once( $includes_dir . 'emails/class-charitable-ambassadors-email-creator-campaign-submitted.php' );
			require_once( $includes_dir . 'emails/class-charitable-ambassadors-email-creator-campaign-ending.php' );
			require_once( $includes_dir . 'emails/class-charitable-ambassadors-email-creator-donation-notification.php' );
			require_once( $includes_dir . 'emails/charitable-ambassadors-email-functions.php' );
			require_once( $includes_dir . 'emails/charitable-ambassadors-email-hooks.php' );
			require_once( $includes_dir . 'public/class-charitable-ambassadors-public.php' );
			require_once( $includes_dir . 'widgets/class-charitable-ambassadors-campaign-creator-widget.php' );
			require_once( $includes_dir . 'compat/charitable-ambassadors-compat-functions.php' );
			require_once( $includes_dir . 'compat/charitable-ambassadors-compat-hooks.php' );
		}

		/**
		 * Set up hook and filter callback functions.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function attach_hooks_and_filters() {
			/* Set up start objects */
			add_action( 'charitable_ambassadors_start', array( 'Charitable_Ambassadors_Shortcodes', 'start' ) );

			add_action( 'init', array( $this, 'add_rewrite_tag' ), 5 );
			add_action( 'init', array( $this, 'add_rewrite_rule' ), 10 );
			add_action( 'widgets_init', array( $this, 'setup_widget' ) );
			add_action( 'charitable_creator_download_donations', array( $this, 'download_donations_csv' ) );

			add_filter( 'charitable_active_addons', array( $this, 'load_addons' ) );
			// add_filter( 'charitable_javascript_vars', array( $this, 'add_charitable_javascript_vars' ) );
			add_filter( 'charitable_form_field_template', array( $this, 'use_charitable_ambassadors_templates' ), 10, 2 );
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
				Charitable_Ambassadors::NAME,
				Charitable_Ambassadors::AUTHOR,
				Charitable_Ambassadors::VERSION,
				$this->plugin_file
			);
		}

		/**
		 * Set up the internationalisation for the plugin.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.1.0
		 */
		private function setup_i18n() {
			if ( class_exists( 'Charitable_i18n' ) ) {

				require_once( $this->get_path( 'includes' ) . 'i18n/class-charitable-ambassadors-i18n.php' );

				Charitable_Ambassadors_i18n::get_instance();
			}
		}

		/**
		 * Start admin functionality.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function maybe_start_admin() {
			if ( ! is_admin() ) {
				return;
			}

			require_once( $this->get_path( 'admin' ) . 'class-charitable-ambassadors-admin.php' );
			require_once( $this->get_path( 'admin' ) . 'charitable-ambassadors-admin-hooks.php' );
		}

		/**
		 * Start publi functionality.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function maybe_start_public() {
			if ( is_admin() ) {
				return;
			}			

			Charitable_Ambassadors_Public::get_instance();
		}

		/**
		 * Returns whether we are currently in the start phase of the plugin.
		 *
		 * @return  bool
		 * @access  public
		 * @since   1.0.0
		 */
		public function is_start() {
			return current_filter() == 'charitable_ambassadors_start';
		}

		/**
		 * Returns whether the plugin has already started.
		 *
		 * @return  bool
		 * @access  public
		 * @since   1.0.0
		 */
		public function started() {
			return did_action( 'charitable_ambassadors_start' ) || current_filter() == 'charitable_ambassadors_start';
		}

		/**
		 * Returns the plugin's version number.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_version() {
			return self::VERSION;
		}

		/**
		 * Returns plugin paths.
		 *
		 * @param   string $path            // If empty, returns the path to the plugin.
		 * @param   bool $absolute_path     // If true, returns the file system path. If false, returns it as a URL.
		 * @return  string
		 * @since   1.0.0
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
		 * Stores an object in the plugin's registry.
		 *
		 * @param   mixed       $object
		 * @return  void
		 * @access  public
		 * @since   1.0.0
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
		 * @param   string      $class  The type of class you want to retrieve.
		 * @return  mixed               The object if its registered. Otherwise false.
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_object( $class ) {
			return isset( $this->registry[ $class ] ) ? $this->registry[ $class ] : false;
		}

		/**
		 * Perform upgrade routine if necessary.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function maybe_upgrade() {
			$db_version = get_option( 'charitable_ambassadors_version' );

			if ( self::VERSION !== $db_version ) {

				require_once( charitable()->get_path( 'includes' ) . 'class-charitable-upgrade.php' );
				require_once( $this->get_path( 'includes' ) . 'class-charitable-ambassadors-upgrade.php' );

				Charitable_Ambassadors_Upgrade::upgrade_from( $db_version, self::VERSION );
			}
		}

		/**
		 * Make sure the User Dashboard & Recipients addons are activated.
		 *
		 * @param   string[] $active_addons
		 * @return  string[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function load_addons( $active_addons ) {
			if ( ! in_array( 'charitable-recipients', $active_addons ) ) {
				$active_addons[] = 'charitable-recipients';
			}

			return $active_addons;
		}

		/**
		 * Register additional emails.
		 *
		 * @param   string[]    $emails
		 * @return  string[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function register_emails( $emails ) {
			$new_emails = array(
				'new_campaign' => 'Charitable_Ambassadors_Email_New_Campaign',
				'creator_campaign_submission' => 'Charitable_Ambassadors_Email_Creator_Campaign_Submission',
				'creator_campaign_ending' => 'Charitable_Ambassadors_Email_Creator_Campaign_Ending',
				'creator_donation_notification' => 'Charitable_Ambassadors_Email_Creator_Donation_Notification',
			);

			$emails = array_merge( $emails, $new_emails );

			return $emails;
		}

		/**
		 * Use Ambassadors templates for the certain fields field.
		 *
		 * @return  Charitable_Ambassadors_Template|false
		 * @access  public
		 * @since   1.0.0
		 */
		public function use_charitable_ambassadors_templates( $template, $field ) {
			$ambassadors_templates = array( 'suggested-donations', 'page', 'recipient-types', 'recipient-type-search' );

			if ( in_array( $field['type'], $ambassadors_templates ) ) {

				$template_name = 'form-fields/' . $field['type'] . '.php';
				$template = new Charitable_Ambassadors_Template( $template_name, false );

			}

			return $template;
		}

		/**
		 * Fired in the request right after the plugin is activated.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.1.0
		 */
		public function finish_install() {
			$install = get_transient( 'charitable_ambassadors_install' );

			if ( ! $install ) {
				return;
			}

			add_action( 'init', 'flush_rewrite_rules', 100 );

			delete_transient( 'charitable_ambassadors_install' );
		}

		/**
		 * Add custom rewrite tag.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_rewrite_tag() {
			add_rewrite_tag( '%campaign_id%', '([0-9]+)' );
		}

		/**
		 * Add endpoint for editing campaigns.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_rewrite_rule() {
			add_rewrite_rule( '(.?.+?)/([0-9]+)/edit/?$', 'index.php?pagename=$matches[1]&campaign_id=$matches[2]', 'top' );
		}

		/**
		 * Set up campaign creator widget.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function setup_widget() {
			register_widget( 'Charitable_Ambassadors_Campaign_Creator_Widget' );
		}

		/**
		 * Download a CSV file with the donation history for the given campaign.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function download_donations_csv() {
			if ( ! isset( $_GET['campaign_id'] ) ) {
				return false;
			}

			$campaign_id = $_GET['campaign_id'];

			/* Check for nonce existence. */
			if ( ! charitable_verify_nonce( 'download_donations_nonce', 'download_donations_' . $campaign_id ) ) {
				return false;
			}

			if ( ! charitable_is_current_campaign_creator( $campaign_id ) ) {
				return false;
			}

			require_once( charitable()->get_path( 'admin' ) . 'reports/class-charitable-export-donations.php' );

			add_filter( 'charitable_export_capability', '__return_true' );

			$export_args = apply_filters( 'charitable_ambassadors_campaign_creator_donations_export_args', array(
				'campaign_id'   => $campaign_id,
			) );

			new Charitable_Export_Donations( $export_args );

			exit();
		}

		/**
		 * Throw error on object clone.
		 *
		 * This class is specifically designed to be instantiated once. You can retrieve the instance using charitable()
		 *
		 * @since   1.0.0
		 * @access  public
		 * @return  void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'charitable-ambassadors' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since   1.0.0
		 * @access  public
		 * @return  void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'charitable-ambassadors' ), '1.0.0' );
		}
	}

endif; // End if class_exists check
