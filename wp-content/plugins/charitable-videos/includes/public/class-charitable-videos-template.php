<?php
/**
 * Charitable Videos template
 *
 * @version     1.0.0
 * @package     Charitable Videos/Classes/Charitable_Videos_Template
 * @author      Eric Daams
 * @copyright   Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_Videos_Template' ) ) :

	/**
	 * Charitable_Videos_Template
	 *
	 * @since       1.0.0
	 */
	class Charitable_Videos_Template extends Charitable_Template {

		/**
		 * Set theme template path.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_theme_template_path() {
			return trailingslashit( apply_filters( 'charitable_videos_theme_template_path', 'charitable/charitable-videos' ) );
		}

		/**
		 * Return the base template path.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_base_template_path() {
			return charitable_videos()->get_path( 'templates' );
		}
	}

endif;
