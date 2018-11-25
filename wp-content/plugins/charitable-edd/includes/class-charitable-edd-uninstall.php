<?php
/**
 * Charitable Uninstall class.
 * 
 * The responsibility of this class is to manage the events that need to happen 
 * when the plugin is deactivated.
 *
 * @package		Charitable EDD/Charitable_EDD_Uninstall
 * @version 	1.0.0
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */
if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_EDD_Uninstall' ) ) : 

	/**
	 * Charitable_EDD_Uninstall
	 * 
	 * @since 		1.0.0
	 */
	class Charitable_EDD_Uninstall {

		/**
		 * Uninstall the plugin.
		 *
		 * @access 	public
		 * @since 	1.0.0
		 */
		public function __construct(){
			$this->remove_tables();
		}

		/**
		 * Remove the custom tables added by Charitable EDD.  
		 *
		 * @return 	void
		 * @access  private
		 * @since 	1.0.0
		 */
		private function remove_tables() {
			global $wpdb;

			$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "charitable_edd_benefactors" );

			delete_option( $wpdb->prefix . 'charitable_edd_benefactors_db_version' );
		}
	}

endif;
