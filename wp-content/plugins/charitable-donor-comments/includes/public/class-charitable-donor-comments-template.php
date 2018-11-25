<?php
/**
 * Charitable Donor Comments template
 *
 * @version     1.0.0
 * @package     Charitable Donor Comments/Classes/Charitable_Donor_Comments_Template
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Donor_Comments_Template' ) ) :

	/**
	 * Charitable_Donor_Comments_Template
	 *
	 * @since       1.0.0
	 */
	class Charitable_Donor_Comments_Template extends Charitable_Template {

		/**
		 * Set theme template path.
		 *
		 * @since   1.0.0
		 *
		 * @return  string
		 */
		public function get_theme_template_path() {
			return trailingslashit( apply_filters( 'charitable_donor_comments_theme_template_path', 'charitable/charitable-donor-comments' ) );
		}

		/**
		 * Return the base template path.
		 *
		 * @since   1.0.0
		 *
		 * @return  string
		 */
		public function get_base_template_path() {
			return charitable_donor_comments()->get_path( 'templates' );
		}
	}

endif;
