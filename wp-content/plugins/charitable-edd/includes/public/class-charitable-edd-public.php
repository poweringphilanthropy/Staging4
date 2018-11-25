<?php
/**
 * Class responsible for loading any Charitable EDD functionality that is only required on the public side of the site.
 *
 * @package		Charitable EDD/Classes/Charitable_EDD_Public
 * @version 	1.0.0
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_EDD_Public' ) ) : 

	/**
	 * Charitable_EDD_Public
	 *
	 * @since 		1.0.0
	 */
	class Charitable_EDD_Public {

		/**
		 * Instantiate the class, but only during the start phase.
		 * 
		 * @param 	Charitable_EDD 		$charitable_edd
		 * @return 	void
		 * @static 
		 * @access 	public
		 * @since 	1.0.0
		 */
		public static function start( Charitable_EDD $charitable_edd ) {
			if ( $charitable_edd->started() ) {
				return;
			}

			$charitable_edd->register_object( new Charitable_EDD_Public( $charitable_edd ) );
		}

		/**
		 * Set up the class. 
		 * 
		 * Note that the only way to instantiate an object is with the charitable_start method, 
		 * which can only be called during the start phase. In other words, don't try 
		 * to instantiate this object. 
		 *
		 * @access 	private
		 * @since 	1.0.0
		 */
		private function __construct() {
			$this->load_dependencies();
			$this->attach_hooks_and_filters();
		}

		/**
		 * Load required files. 
		 *
		 * @return 	void
		 * @access  private
		 * @since 	1.0.0
		 */
		private function load_dependencies() {
			require_once( charitable_edd()->get_path( 'includes' ) . 'public/charitable-edd-template-functions.php' );
			require_once( charitable_edd()->get_path( 'includes' ) . 'public/charitable-edd-template-hooks.php' );
			require_once( charitable_edd()->get_path( 'includes' ) . 'public/class-charitable-edd-template.php' );
		}

		/**
		 * Set up hooks and filters. 
		 *
		 * @return 	void
		 * @access  private
		 * @since 	1.0.0
		 */
		private function attach_hooks_and_filters() {
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );		
		}

		/**
		 * Load stylesheets & scripts on the frontend. 
		 * 
		 * @return 	void
		 * @since 	1.0.0
		 */
		public function enqueue_scripts() {
			if ( charitable_is_page( 'campaign_donation_page' ) ) {
				wp_register_script( 'charitable-edd-donation-page', charitable_edd()->get_path( 'assets', false ) . 'js/charitable-edd-donation-page.js', array( 'jquery' ), charitable_edd()->get_version() );						
			}

			if ( edd_is_checkout() ) {
				wp_register_style( 'charitable-edd', charitable_edd()->get_path( 'assets', false ) . 'css/charitable-edd.css', array(), charitable_edd()->get_version() );
				wp_enqueue_style( 'charitable-edd' );

				wp_register_script( 'charitable-edd-checkout', charitable_edd()->get_path( 'assets', false ) . 'js/charitable-edd-checkout.js', array( 'jquery' ), charitable_edd()->get_version() );
				wp_enqueue_script( 'charitable-edd-checkout' );
			}
		}
	}

endif; // End class_exists check
