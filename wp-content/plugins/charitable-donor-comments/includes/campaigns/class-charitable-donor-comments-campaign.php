<?php
/**
 * This class is responsible for customizing the comments section on campaigns.
 *
 * @package     Charitable Donor Comments/Classes/Charitable_Donor_Comments_Campaign
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Donor_Comments_Campaign' ) ) :

	/**
	 * Charitable_Donor_Comments_Campaign
	 *
	 * @since       1.0.0
	 */
	class Charitable_Donor_Comments_Campaign {

		/**
		 * The one class instance.
		 *
		 * @since 	1.0.0
		 *
		 * @var     Charitable_Donor_Comments_Campaign
		 */
		private static $instance = null;

		/**
		 * Create class object. Private constructor.
		 *
		 * @since   1.0.0
		 */
		private function __construct() {
		}

		/**
		 * Create and return the class object.
		 *
		 * @since   1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Donor_Comments_Campaign();
			}

			return self::$instance;
		}

		/**
		 * Create and return the class object.
		 *
		 * @since   1.0.0
		 *
		 * @see 	comments_template
		 *
		 * @param 	array $args The default comment args.
		 * @return 	array
		 */
		public function maybe_exclude_donor_comments_from_comments_template( $args ) {
			$include = charitable_get_option( 'show_donor_comments_in_comments_template', true );

			if ( ! $include ) {
				$args['type__not_in'] = array( 'charitable_comment' );
			}

			return $args;
		}
	}

endif;
