<?php
/**
 * Roles and Capabilities for Charitable Ambassadors
 *
 * @version     1.0.0
 * @package     Charitable/Classes/Charitable_Ambassadors_Roles
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Ambassadors_Roles' ) ) :

	/**
	 * Charitable_Ambassadors_Roles class.
	 *
	 * @since       1.0.0
	 */
	class Charitable_Ambassadors_Roles {

		/**
		 * Sets up roles for Charitable. This is called by the install script.
		 *
		 * @return  void
		 * @static
		 * @since   1.0.0
		 */
		public static function add_roles() {
			add_role( 'campaign_creator', __( 'Campaign Creator', 'charitable-ambassadors' ), array(
				'read' => true,
				'edit_campaigns' => true,
			) );
		}

		/**
		 * Removes roles. This is called upon deactivation.
		 *
		 * @global  WP_Roles
		 * @return  void
		 * @static
		 * @access  public
		 * @since   1.0.0
		 */
		public static function remove_caps() {
			global $wp_roles;

			if ( class_exists( 'WP_Roles' ) ) {
				if ( ! isset( $wp_roles ) ) {
					$wp_roles = new WP_Roles();
				}
			}

			if ( is_object( $wp_roles ) ) {

				// Remove the main post type capabilities
				$caps = array( 'read', 'edit_posts' );

				foreach ( $caps as $cap ) {
					$wp_roles->remove_cap( 'campaign_creator', $cap );
				}

				remove_role( 'campaign_creator' );
			}
		}
	}

endif; // End class_exists check.
