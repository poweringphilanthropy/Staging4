<?php
/**
 * Add settings for the Donor Comments section to the Charitable Customizer.
 *
 * @package     Charitable Donor Comments/Classes/Charitable_Donor_Comments_Customizer
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Donor_Comments_Customizer' ) ) :

	/**
	 * Charitable_Donor_Comments_Customizer
	 *
	 * @since       1.0.0
	 */
	class Charitable_Donor_Comments_Customizer {

		/**
		 * Class instance.
		 *
		 * @since   1.0.0
		 *
		 * @var     Charitable_Donor_Comments_Customizer
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
		 *
		 * @return 	Charitable_Donor_Comments_Customizer
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Donor_Comments_Customizer();
			}

			return self::$instance;
		}

		/**
		 * Add the extra setting fields.
		 *
		 * @since   1.0.0
		 *
		 * @param 	array $fields The fields to be added.
		 * @return  array
		 */
		public function add_fields( $fields ) {

			$fields['sections']['charitable_donation_form']['settings']['donor_comment_label'] = array(
				'setting' => array(
					'transport'         => 'refresh',
					'default'           => __( 'Leave a Comment', 'charitable-donor-comments' ),
					'sanitize_callback' => 'sanitize_text_field',
				),
				'control' => array(
					'type'              => 'textarea',
					'label'             => __( 'Comment field label', 'charitable-donor-comments' ),
					'priority'          => 1028,
				),
			);

			$fields['sections']['charitable_donation_form']['settings']['donor_comment_placeholder'] = array(
				'setting' => array(
					'transport'         => 'refresh',
					'default'           => __( 'Share why you are donating, your story, or just a word of encouragement. Your comment will be displayed publicly.', 'charitable-donor-comments' ),
					'sanitize_callback' => 'esc_textarea',
				),
				'control' => array(
					'type'              => 'textarea',
					'label'             => __( 'Comment field placeholder', 'charitable-donor-comments' ),
					'priority'          => 1032,
				),
			);

			return $fields;
		}
	}

endif;
