<?php
/**
 * Sets up translations for Charitable Donor Comments.
 *
 * @package     Charitable/Classes/Charitable_i18n
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/* Ensure that Charitable_i18n exists */
if ( ! class_exists( 'Charitable_i18n' ) ) :
	return;
endif;

if ( ! class_exists( 'Charitable_Donor_Comments_i18n' ) ) :

	/**
	 * Charitable_Donor_Comments_i18n
	 *
	 * @since       1.0.0
	 */
	class Charitable_Donor_Comments_i18n extends Charitable_i18n {

		/**
		 * Plugin textdomain.
		 *
		 * @since   1.0.0
		 *
		 * @var     string
		 */
		protected $textdomain = 'charitable-donor-comments';

		/**
		 * Set up the class.
		 *
		 * @since   1.0.0
		 */
		protected function __construct() {
			$this->languages_directory = apply_filters( 'charitable_donor_comments_languages_directory', 'charitable-donor-comments/languages' );
			$this->locale = apply_filters( 'plugin_locale', get_locale(), $this->textdomain );
			$this->mofile = sprintf( '%1$s-%2$s.mo', $this->textdomain, $this->locale );

			$this->load_textdomain();
		}
	}

endif;
