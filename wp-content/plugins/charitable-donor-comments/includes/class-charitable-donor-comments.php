<?php
/**
 * The main Charitable Donor Comments class.
 *
 * The responsibility of this class is to load all the plugin's functionality.
 *
 * @package   Charitable Donor Comments
 * @copyright Copyright (c) 2017, Eric Daams
 * @license   http://opensource.org/licenses/gpl-1.0.0.php GNU Public License
 * @since     1.0.0
 * @version   1.0.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Donor_Comments' ) ) :

	/**
	 * Charitable_Donor_Comments
	 *
	 * @since 1.0.0
	 */
	class Charitable_Donor_Comments {

		/* @var string Plugin version. */
		const VERSION = '1.0.0';

		/* @var string Database version. */
		const DB_VERSION = '20151021';

		/* @var string The product name. */
		const NAME = 'Charitable Donor Comments';

		/* @var string The product author. */
		const AUTHOR = 'Studio 164a';

		/**
		 * Single static instance of this class.
		 *
		 * @since 1.0.0
		 *
	     * @var   Charitable_Donor_Comments
	     */
		private static $instance = null;

		/**
		 * The root file of the plugin.
		 *
		 * @since  1.0.0
		 *
		 * @var     string
		 */
		private $plugin_file;

		/**
		 * The root directory of the plugin.
		 *
		 * @since 1.0.0
		 *
		 * @var   string
		 */
		private $directory_path;

		/**
		 * The root directory of the plugin as a URL.
		 *
		 * @since 1.0.0
		 *
		 * @var   string
		 */
		private $directory_url;

		/**
		 * Create class instance.
		 *
		 * @since 1.0.0
		 *
		 * @param string $plugin_file Absolute path to the main plugin file.
		 */
		public function __construct( $plugin_file ) {
			$this->plugin_file      = $plugin_file;
			$this->directory_path   = plugin_dir_path( $plugin_file );
			$this->directory_url    = plugin_dir_url( $plugin_file );

			add_action( 'charitable_start', array( $this, 'start' ), 6 );
		}

		/**
		 * Returns the original instance of this class.
		 *
		 * @since  1.0.0
		 *
		 * @return Charitable_Donor_Comments
		 */
		public static function get_instance() {
			return self::$instance;
		}

		/**
		 * Run the startup sequence on the charitable_start hook.
		 *
		 * This is only ever executed once.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function start() {
			/* If we've already started (i.e. run this function once before), do not pass go. */
			if ( $this->started() ) {
				return;
			}

			/* Set static instance. */
			self::$instance = $this;

			$this->load_dependencies();

			$this->maybe_start_admin();

			$this->maybe_start_public();

			$this->setup_licensing();

			$this->setup_i18n();

			$this->setup_customizer();

			/* Hook in here to do something when the plugin is first loaded. */
			do_action( 'charitable_donor_comments_start', $this );
		}

		/**
		 * Include necessary files.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function load_dependencies() {
			require_once( $this->get_path( 'includes' ) . 'charitable-donor-comments-core-functions.php' );
			require_once( $this->get_path( 'includes' ) . 'campaigns/class-charitable-donor-comments-campaign.php' );
			require_once( $this->get_path( 'includes' ) . 'campaigns/charitable-donor-comments-campaign-hooks.php' );
			require_once( $this->get_path( 'includes' ) . 'donations/class-charitable-donor-comments-donation.php' );
			require_once( $this->get_path( 'includes' ) . 'donations/charitable-donor-comments-donation-hooks.php' );
		}

		/**
		 * Load the admin-only functionality.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function maybe_start_admin() {
			if ( ! is_admin() ) {
				return;
			}

			require_once( $this->get_path( 'includes' ) . 'admin/class-charitable-donor-comments-admin.php' );
			require_once( $this->get_path( 'includes' ) . 'admin/charitable-donor-comments-admin-hooks.php' );
		}

		/**
		 * Load the public-only functionality.
		 *
		 * @since  1.0.0
		 *
		 * @return 	void
		 */
		private function maybe_start_public() {
			require_once( $this->get_path( 'includes' ) . 'public/class-charitable-donor-comments-template.php' );
		}

		/**
		 * Set up licensing for the extension.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function setup_licensing() {
			charitable_get_helper( 'licenses' )->register_licensed_product(
				Charitable_Donor_Comments::NAME,
				Charitable_Donor_Comments::AUTHOR,
				Charitable_Donor_Comments::VERSION,
				$this->plugin_file
			);
		}

		/**
		 * Set up the internationalisation for the plugin.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		private function setup_i18n() {
			if ( class_exists( 'Charitable_i18n' ) ) {

				require_once( $this->get_path( 'includes' ) . 'i18n/class-charitable-donor-comments-i18n.php' );

				Charitable_Donor_Comments_i18n::get_instance();
			}
		}

		/**
	     * Set up the customizer.
	     *
	     * @since  1.0.0
	     *
	     * @return void
	     */
	    private function setup_customizer() {
	    	global $wp_customize;

			if ( $wp_customize ) {
		    	require_once( $this->get_path( 'includes' ) . 'admin/customizer/class-charitable-donor-comments-customizer.php' );

				add_filter( 'charitable_customizer_fields', array( Charitable_Donor_Comments_Customizer::get_instance(), 'add_fields' ) );
			}
	    }

		/**
		 * Returns whether we are currently in the start phase of the plugin.
		 *
		 * @since  1.0.0
		 *
		 * @return bool
		 */
		public function is_start() {
			return current_filter() == 'charitable_donor_comments_start';
		}

		/**
		 * Returns whether the plugin has already started.
		 *
		 * @since  1.0.0
		 *
		 * @return bool
		 */
		public function started() {
			return did_action( 'charitable_donor_comments_start' ) || current_filter() == 'charitable_donor_comments_start';
		}

		/**
		 * Returns the plugin's version number.
		 *
		 * @since  1.0.0
		 *
		 * @return string
		 */
		public function get_version() {
			return self::VERSION;
		}

		/**
		 * Returns plugin paths.
		 *
		 * @since  1.0.0
		 *
		 * @param   string  $type          If empty, returns the path to the plugin.
		 * @param   boolean $absolute_path If true, returns the file system path. If false, returns it as a URL.
		 * @return string
		 */
		public function get_path( $type = '', $absolute_path = true ) {
			$base = $absolute_path ? $this->directory_path : $this->directory_url;

			switch ( $type ) {
				case 'includes' :
					$path = $base . 'includes/';
					break;

				case 'templates' :
					$path = $base . 'templates/';
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
		 * Throw error on object clone.
		 *
		 * This class is specifically designed to be instantiated once. You can retrieve the instance using charitable()
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function __clone() {
			charitable_get_deprecated()->doing_it_wrong(
				__FUNCTION__,
				__( 'Cheatin&#8217; huh?', 'charitable-donor-comments' ),
				'1.0.0'
			);
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public function __wakeup() {
			charitable_get_deprecated()->doing_it_wrong(
				__FUNCTION__,
				__( 'Cheatin&#8217; huh?', 'charitable-donor-comments' ),
				'1.0.0'
			);
		}
	}

endif;
