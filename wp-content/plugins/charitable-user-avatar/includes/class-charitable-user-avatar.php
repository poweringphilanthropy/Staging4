<?php
/**
 * The main Charitable User Avatar class.
 *
 * The responsibility of this class is to load all the plugin's functionality.
 *
 * @package   Charitable User Avatar
 * @copyright Copyright (c) 2017, Eric Daams
 * @license   http://opensource.org/licenses/gpl-1.0.0.php GNU Public License
 * @since  	  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_User_Avatar' ) ) :

	/**
	 * Charitable_User_Avatar
	 *
	 * @since  1.0.0
	 */
	class Charitable_User_Avatar {

		/* @var string */
		const VERSION = '1.0.5';

		/* @var string */
		const DB_VERSION = '20150818';

		/* @var string */
		const NAME = 'Charitable User Avatar';

		/* @var string */
		const AUTHOR = 'Studio 164a';

		/**
		 * Single class instance.
		 *
		 * @var Charitable_User_Avatar
		 */
		private static $instance = null;

		/**
		 * The root file of the plugin.
		 *
		 * @var string
		 */
		private $plugin_file;

		/**
		 * The root directory of the plugin.
		 *
		 * @var string
		 */
		private $directory_path;

		/**
		 * The root directory of the plugin as a URL.
		 *
		 * @var string
		 */
		private $directory_url;

		/**
		 * Store of registered objects.
		 *
		 * @var array
		 */
		private $registry;

		/**
		 * Create class instance.
		 *
		 * @since 1.0.0
		 *
		 * @param string $plugin_file The core plugin file.
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
		 * @since  1.0.0
		 *
		 * @return Charitable_User_Avatar
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
			// If we've already started (i.e. run this function once before), do not pass go.
			if ( $this->started() ) {
				return;
			}

			// Set static instance.
			self::$instance = $this;

			$this->load_dependencies();

			$this->attach_hooks_and_filters();

			$this->maybe_start_ambassadors();

			$this->setup_licensing();

			$this->setup_i18n();

			// Hook in here to do something when the plugin is first loaded.
			do_action( 'charitable_user_avatar_start', $this );
		}

		/**
		 * Include necessary files.
		 *
		 * @return void
		 * @since  1.0.0
		 */
		private function load_dependencies() {
			require_once( $this->get_path( 'includes' ) . 'charitable-user-avatar-functions.php' );
			require_once( $this->get_path( 'includes' ) . 'class-charitable-user-avatar-profile-form.php' );
		}

		/**
		 * Set up hook and filter callback functions.
		 * 
		 * @return void
		 * @since  1.0.0
		 */
		private function attach_hooks_and_filters() {
			add_action( 'charitable_user_avatar_start',	array( 'Charitable_User_Avatar_Profile_Form', 'start' ) );
			add_filter( 'charitable_user_avatar', 		array( $this, 'get_user_avatar' ), 10, 3 );
			add_filter( 'charitable_user_avatar_src', 	array( $this, 'get_user_avatar_src' ), 10, 3 );
		}

		/**
		 * Load up the Ambassadors integration if Ambassadors is installed.
		 *
		 * @return void
		 * @since  1.0.2
		 */
		private function maybe_start_ambassadors() {
			if ( ! class_exists( 'Charitable_Ambassadors' ) ) {
				return;
			}

			require_once( $this->get_path( 'includes' ) . 'ambassadors/class-charitable-user-avatar-campaign-form.php' );

			add_action( 'charitable_user_avatar_start', array( 'Charitable_User_Avatar_Campaign_Form', 'start' ) );
		}

		/**
		 * Set up licensing for the extension. 
		 *
		 * @return void
		 * @since  1.0.0
		 */
		private function setup_licensing() {
			charitable_get_helper( 'licenses' )->register_licensed_product( 
				Charitable_User_Avatar::NAME, 
				Charitable_User_Avatar::AUTHOR, 
				Charitable_User_Avatar::VERSION,
				$this->plugin_file 
			);
		}

		/**
		 * Set up the internationalisation for the plugin. 
		 *
		 * @return void
		 * @since  1.1.0
		 */
		private function setup_i18n() {
			if ( class_exists( 'Charitable_i18n' ) ) {

				require_once( $this->get_path( 'includes' ) . 'i18n/class-charitable-user-avatar-i18n.php' );

				Charitable_User_Avatar_i18n::get_instance();
			}
		}

		/**
		 * Returns whether we are currently in the start phase of the plugin. 
		 *
		 * @return bool
		 * @since  1.0.0
		 */
		public function is_start() {
			return current_filter() == 'charitable_user_avatar_start';
		}

		/**
		 * Returns whether the plugin has already started.
		 * 
		 * @return bool
		 * @since  1.0.0
		 */
		public function started() {
			return did_action( 'charitable_user_avatar_start' ) || current_filter() == 'charitable_user_avatar_start';
		}

		/**
		 * Returns the plugin's version number. 
		 *
		 * @return string
		 * @since  1.0.0
		 */
		public function get_version() {
			return self::VERSION;
		}

		/**
		 * Returns plugin paths. 
		 *
		 * @param  string $path 			// If empty, returns the path to the plugin.
		 * @param  bool $absolute_path 	// If true, returns the file system path. If false, returns it as a URL.
		 * @return string
		 * @since  1.0.0
		 */
		public function get_path($type = '', $absolute_path = true ) {		
			$base = $absolute_path ? $this->directory_path : $this->directory_url;

			switch( $type ) {
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
		 * @since  1.0.0
		 *
		 * @param  mixed $object
		 * @return void
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
		 * @param  string $class The type of class you want to retrieve.
		 * @return mixed The object if its registered. Otherwise false.
		 * @since  1.0.0
		 */
		public function get_object( $class ) {
			return isset( $this->registry[ $class ] ) ? $this->registry[ $class ] : false;
		}

		/**
		 * Return the user's avatar, or false if the user has not uploaded their own avatar.
		 *
		 * @since  1.0.0
		 *
		 * @param  string|false    $avatar The default avatar to use if one hasn't been set.
		 * @param  Charitable_User $user   The Charitable_User object.
		 * @return string
		 */
		public function get_user_avatar( $avatar, Charitable_User $user ) {
			$custom_avatar = get_user_meta( $user->ID, 'avatar', true );

			if ( empty( $custom_avatar ) ) {
				return $avatar;
			}

			if ( is_null( get_post( $custom_avatar ) ) ) {
				return $avatar;
			}

			return $custom_avatar;
		}

		/**
		 * Return the src of the user's avatar, or false if they have not uploaded their own.  
		 *
		 * @param  string|false $avatar_src
		 * @param  Charitable_User $user
		 * @param  string $size
		 * @return string|false
		 * @since  1.0.0
		 */
		public function get_user_avatar_src( $avatar_src, Charitable_User $user, $size = '' ) {
			$avatar_attachment_id = $this->get_user_avatar( $avatar_src, $user );

			if ( $avatar_attachment_id ) {
				$attachment_src = wp_get_attachment_image_src( $avatar_attachment_id, $size );

				if ( $attachment_src ) {
					$avatar_src = $attachment_src[ 0 ];
				}
			}

			return $avatar_src;
		}

		/**
		 * Throw error on object clone.
		 *
		 * This class is specifically designed to be instantiated once. You can retrieve the instance using charitable()
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function __clone() {
			charitable_get_deprecated()->doing_it_wrong(
				__FUNCTION__,
				__( 'Cheatin&#8217; huh?', 'charitable-user-avatar' ),
				'1.0.0'
			);
		}

		/**
		 * Disable unserializing of the class.
		 *
		 * @since  1.0.0
		 * @return void
		 */
		public function __wakeup() {
			charitable_get_deprecated()->doing_it_wrong(
				__FUNCTION__,
				__( 'Cheatin&#8217; huh?', 'charitable-user-avatar' ),
				'1.0.0'
			);
		}
	}

endif;
