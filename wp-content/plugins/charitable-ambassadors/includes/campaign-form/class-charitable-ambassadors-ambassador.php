<?php
/**
 * Class responsible for defining the "ambassador" recipient type.
 *
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Ambassador
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly.

if ( ! class_exists( 'Charitable_Ambassadors_Ambassador' ) ) :

	/**
	 * Charitable_Ambassadors_Ambassador
	 *
	 * @since       1.0.0
	 */
	class Charitable_Ambassadors_Ambassador {

		/**
		 * Register the 'ambassador' campaign recipient type.
		 *
		 * @return  void
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function register() {
			$args = apply_filters( 'charitable_ambassadors_ambassador_recipient_type', array(
				'label' => __( 'Our Organization', 'charitable-ambassadors' ),
				'description' => __( 'You are raising money for us. All donations to your fundraising campaign will help our cause.', 'charitable-ambassadors' ),
				'admin_label' => __( 'Your Organization', 'charitable-ambassadors' ),
				'admin_description' => __( 'Campaign creators raise money for your organization. Every donation they receive will support your cause.', 'charitable-ambassadors' ),
			) );

			charitable_register_recipient_type( 'ambassador', $args );
		}
	}

endif; // End class_exists check
