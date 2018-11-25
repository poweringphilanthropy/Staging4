<?php
/**
 * Class responsible for loading any Charitable Ambassadors functionality that is only required on the public side of the site.
 *
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Public
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @version     1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Ambassadors_Public' ) ) :

	/**
	 * Charitable_Ambassadors_Public
	 *
	 * @since       1.0.0
	 */
	class Charitable_Ambassadors_Public {

		/**
		 * Static class instance.
		 *
		 * @since 1.1.17
		 *
		 * @var   Charitable_Ambassadors_Public
		 */
		private static $instance;

		/**
		 * Return the class instance.
		 *
		 * @since  1.1.17
		 *
		 * @return Charitable_Ambassadors_Public
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new Charitable_Ambassadors_Public();
			}

			return self::$instance;
		}

		/**
		 * Set up the class.
		 *
		 * Note that the only way to instantiate an object is with the charitable_start method,
		 * which can only be called during the start phase. In other words, don't try
		 * to instantiate this object.
		 *
		 * @access  private
		 * @since   1.0.0
		 */
		private function __construct() {
			$this->load_dependencies();
			$this->attach_hooks_and_filters();
		}

		/**
		 * Load required files.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function load_dependencies() {
			$path = charitable_ambassadors()->get_path( 'includes' ) . 'public/';

			require_once( $path . '/class-charitable-ambassadors-template.php' );
			require_once( $path . '/charitable-ambassadors-page-functions.php' );
			require_once( $path . '/charitable-ambassadors-template-functions.php' );
			require_once( $path . '/charitable-ambassadors-template-helpers.php' );
			require_once( $path . '/charitable-ambassadors-template-hooks.php' );
		}

		/**
		 * Set up hooks and filters.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function attach_hooks_and_filters() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		}

		/**
		 * Load stylesheets & scripts on the frontend.
		 *
		 * @return  void
		 * @since   1.0.0
		 */
		public function enqueue_scripts() {
			if ( ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ) {
				$suffix  = '';
				$version = time();
			} else {
				$suffix  = '.min';
				$version = charitable_ambassadors()->get_version();
			}

			ob_start();

			charitable_ambassadors_template( 'form-fields/suggested-donation-row.php', array(
				'index'       => '{index}',
				'key'         => 'suggested_donations',
				'amount'      => '',
				'description' => '',
			) );

			$row = ob_get_clean();

			$vars = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'suggested_amount_row' => $row,
			);

			wp_register_script(
				'charitable-ambassadors-campaign-submission',
				charitable_ambassadors()->get_path( 'assets', false ) . 'js/charitable-ambassadors-campaign-submission' . $suffix . '.js',
				array( 'jquery-core' ),
				$version,
				true
			);

			wp_localize_script(
				'charitable-ambassadors-campaign-submission',
				'CHARITABLE_AMBASSADORS_VARS',
				$vars
			);

			if ( ! wp_script_is( 'select2', 'registered' ) ) {
				wp_register_script(
					'select2',
					charitable()->get_path( 'assets', false ) . 'js/libraries/select2' . $suffix . '.js',
					array( 'jquery' ),
					'4.0.0',
					true
				);
			}

			if ( ! wp_style_is( 'select2', 'registered' ) ) {
				wp_register_style(
					'select2',
					charitable()->get_path( 'assets', false ) . 'css/libraries/select2' . $suffix . '.css'
				);
			}

			wp_register_script(
				'charitable-ambassadors-recipient-search',
				charitable_ambassadors()->get_path( 'assets', false ) . 'js/charitable-ambassadors-recipient-search' . $suffix . '.js',
				array( 'select2' ),
				$version,
				true
			);

			wp_register_style(
				'charitable-ambassadors-styles',
				charitable_ambassadors()->get_path( 'assets', false ) . 'css/charitable-ambassadors-styles' . $suffix . '.css'
			);
		}

		/**
		 * Instantiate the class, but only during the start phase.
		 *
		 * @param   Charitable_Ambassadors      $charitable_ambassadors
		 * @return  Charitable_Ambassadors_Public
		 * @static
		 * @access  public
		 * @since   1.0.0
		 */
		public static function start( $charitable_ambassadors ) {
			if ( $charitable_ambassadors->started() ) {
				return;
			}

			$charitable_ambassadors->register_object( new Charitable_Ambassadors_Public( $charitable_ambassadors ) );
		}
	}

endif;
