<?php
/**
 * The main Charitable Videos class.
 *
 * The responsibility of this class is to load all the plugin's functionality.
 *
 * @package     Charitable Videos
 * @copyright   Copyright (c) 2016, Eric Daams
 * @license     http://opensource.org/licenses/gpl-1.0.0.php GNU Public License
 * @since       1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Videos' ) ) :

	/**
	 * Charitable_Videos
	 *
	 * @since   1.0.0
	 */
	class Charitable_Videos {

		/**
		 * @var string
		 */
		const VERSION = '1.0.0';

		/**
		 * @var string  A date in the format: YYYYMMDD
		 */
		const DB_VERSION = '20150805';

		/**
		 * @var string The product name.
		 */
		const NAME = 'Charitable Videos';

		/**
		 * @var string The product author.
		 */
		const AUTHOR = 'Studio 164a';

		/**
	 * @var Charitable_Videos
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
		 * Create class instance.
		 *
		 * @return  void
		 * @since   1.0.0
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
		 * @return  Charitable
		 * @since   1.0.0
		 */
		public static function get_instance() {
			return self::$instance;
		}

		/**
		 * Run the startup sequence on the charitable_start hook.
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

			$this->maybe_start_admin();

			$this->maybe_start_public();

			$this->maybe_start_ambassadors();

			$this->setup_licensing();

			$this->setup_i18n();

			// Hook in here to do something when the plugin is first loaded.
			do_action( 'charitable_videos_start', $this );
		}

		/**
		 * Include necessary files.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function load_dependencies() {
			require_once( $this->get_path( 'includes' ) . 'charitable-videos-core-functions.php' );
		}

		/**
		 * Load the admin-only functionality.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function maybe_start_admin() {
			if ( ! is_admin() ) {
				return;
			}

			require_once( $this->get_path( 'includes' ) . 'admin/class-charitable-videos-admin.php' );
			require_once( $this->get_path( 'includes' ) . 'admin/charitable-videos-admin-hooks.php' );
		}

		/**
		 * Load the public-only functionality.
		 *
		 * @return 	void
		 * @access 	private
		 * @since 	1.0.0
		 */
		private function maybe_start_public() {
			require_once( $this->get_path( 'includes' ) . 'public/class-charitable-videos-template.php' );
			require_once( $this->get_path( 'includes' ) . 'public/charitable-videos-template-functions.php' );
			require_once( $this->get_path( 'includes' ) . 'public/charitable-videos-template-hooks.php' );
		}

		/**
		 * Load up the Ambassadors integration if Ambassadors is installed.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function maybe_start_ambassadors() {
			if ( ! class_exists( 'Charitable_Ambassadors' ) ) {
				return;
			}

			require_once( $this->get_path( 'includes' ) . 'ambassadors/class-charitable-videos-ambassadors.php' );
			require_once( $this->get_path( 'includes' ) . 'ambassadors/charitable-videos-ambassadors-hooks.php' );
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
				Charitable_Videos::NAME,
				Charitable_Videos::AUTHOR,
				Charitable_Videos::VERSION,
				$this->plugin_file
			);
		}

		/**
		 * Set up the internationalisation for the plugin.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function setup_i18n() {
			if ( class_exists( 'Charitable_i18n' ) ) {

				require_once( $this->get_path( 'includes' ) . 'i18n/class-charitable-videos-i18n.php' );

				Charitable_Videos_i18n::get_instance();
			}
		}

		/**
		 * Returns whether we are currently in the start phase of the plugin.
		 *
		 * @return  bool
		 * @access  public
		 * @since   1.0.0
		 */
		public function is_start() {
			return current_filter() == 'charitable_videos_start';
		}

		/**
		 * Returns whether the plugin has already started.
		 *
		 * @return  bool
		 * @access  public
		 * @since   1.0.0
		 */
		public function started() {
			return did_action( 'charitable_videos_start' ) || current_filter() == 'charitable_videos_start';
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
		 * @since   1.0.0
		 * @access  public
		 * @return  void
		 */
		public function __clone() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'charitable-videos' ), '1.0.0' );
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since   1.0.0
		 * @access  public
		 * @return  void
		 */
		public function __wakeup() {
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'charitable-videos' ), '1.0.0' );
		}
	}

endif; // End if class_exists check
