<?php
/**
 * Plugin Name:     Easy Digital Downloads - Purchase Limit
 * Plugin URI:      https://easydigitaldownloads.com/extension/purchase-limit/
 * Description:     Allows site owners to specify max purchase limits on individual products
 * Version:         1.2.19
 * Author:          Daniel J Griffiths
 * Author URI:      http://section214.com
 * Text Domain:     edd-purchase-limit
 *
 * @package         EDD\PurchaseLimit
 * @author          Daniel J Griffiths <dgriffiths@section214.com>
 * @copyright       Copyright (c) 2013-2014, Daniel J Griffiths
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) {
	exit;
}


if( ! class_exists( 'EDD_Purchase_Limit' ) ) {

	/**
	 * Main EDD_Purchase_Limit class
	 *
	 * @since       1.0.0
	 */
	class EDD_Purchase_Limit {


		/**
		 * @var         EDD_Purchase_Limit $instance The one true EDD_Purchase_Limit
		 * @since       1.0.0
		 */
		private static $instance;


		/**
		 * Get active instance
		 *
		 * @access      public
		 * @since       1.0.1
		 * @return      object self::$instance The one true EDD_Purchase_Limit
		 */
		public static function instance() {
			if( ! self::$instance ) {
				self::$instance = new EDD_Purchase_Limit();
				self::$instance->setup_constants();
				self::$instance->includes();
				self::$instance->load_textdomain();
				self::$instance->hooks();
			}

			return self::$instance;
		}


		/**
		 * Setup plugin constants
		 *
		 * @access      private
		 * @since       1.0.9
		 * @return      void
		 */
		private function setup_constants() {
			// Plugin version
			define( 'EDD_PURCHASE_LIMIT_VERSION', '1.2.19' );

			// Plugin path
			define( 'EDD_PURCHASE_LIMIT_DIR', plugin_dir_path( __FILE__ ) );

			// Plugin URL
			define( 'EDD_PURCHASE_LIMIT_URL', plugin_dir_url( __FILE__ ) );
		}


		/**
		 * Include necessary files
		 *
		 * @access      private
		 * @since       1.0.9
		 * @return      void
		 */
		private function includes() {
			// Include scripts
			require_once EDD_PURCHASE_LIMIT_DIR . 'includes/scripts.php';
			require_once EDD_PURCHASE_LIMIT_DIR . 'includes/functions.php';
			require_once EDD_PURCHASE_LIMIT_DIR . 'includes/shortcodes.php';

			if( is_admin() ) {
				require_once EDD_PURCHASE_LIMIT_DIR . 'includes/admin/settings/register.php';
				require_once EDD_PURCHASE_LIMIT_DIR . 'includes/admin/downloads/meta-boxes.php';
			}
		}


		/**
		 * Run action and filter hooks
		 *
		 * @access      private
		 * @since       1.0.1
		 * @return      void
		 */
		private function hooks() {
			// Handle licensing
			if( class_exists( 'EDD_License' ) ) {
				$license = new EDD_License( __FILE__, 'Purchase Limit', EDD_PURCHASE_LIMIT_VERSION, 'Daniel J Griffiths' );
			}
		}


		/**
		 * Internationalization
		 *
		 * @access      public
		 * @since       1.0.0
		 * @return      void
		 */
		public function load_textdomain() {
			// Set filter for language directory
			$lang_dir = EDD_PURCHASE_LIMIT_DIR . '/languages/';
			$lang_dir = apply_filters( 'edd_purchase_limit_languages_directory', $lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'edd-purchase-limit' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'edd-purchase-limit', $locale );

			// Setup paths to current locale file
			$mofile_local  = $lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/edd-purchase-limit/' . $mofile;

			if( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/edd-purchase-limit/ folder
				load_textdomain( 'edd-purchase-limit', $mofile_global );
			} elseif( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/edd-purchase-limit/languages/ folder
				load_textdomain( 'edd-purchase-limit', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'edd-purchase-limit', false, $lang_dir );
			}
		}
	}
}


/**
 * The main function responsible for returning the one true EDD_Purchase_Limit
 * instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \EDD_Purchase_Limit The one true EDD_Purchase_Limit
 */
function edd_purchase_limit() {
	if( ! class_exists( 'Easy_Digital_Downloads' ) ) {
		if( ! class_exists( 'S214_EDD_Activation' ) ) {
			require_once 'includes/libraries/class.s214-edd-activation.php';
		}

		$activation = new S214_EDD_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

		return EDD_Purchase_Limit::instance();
	} else {
		return EDD_Purchase_Limit::instance();
	}
}
add_action( 'plugins_loaded', 'edd_purchase_limit' );
