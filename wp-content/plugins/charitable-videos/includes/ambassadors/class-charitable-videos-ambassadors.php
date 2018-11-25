<?php
/**
 * The class that is responsible for adding the location field to the campaign submission form.
 *
 * @package     Charitable Videos/Classes/Charitable_Videos_Ambassadors
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Videos_Ambassadors' ) ) :

	/**
	 * Charitable_Videos_Ambassadors
	 *
	 * @since       1.0.0
	 */
	class Charitable_Videos_Ambassadors {

		/**
		 * @var     Charitable_Videos_Ambassadors
		 * @access  private
		 * @static
		 * @since   1.0.0
		 */
		private static $instance = null;

		/**
		 * Create class object. Private constructor.
		 *
		 * @access  private
		 * @since   1.0.0
		 */
		private function __construct() {
		}

		/**
		 * Create and return the class object.
		 *
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Videos_Ambassadors();
			}

			return self::$instance;
		}

		/**
		 * Add a video field to the campaign submission form.
		 *
		 * @param   array[] $fields
		 * @param   Charitable_Ambassadors_Campaign_Form $form
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_video_field( $fields, Charitable_Ambassadors_Campaign_Form $form ) {
			$fields['video'] = array(
				'label'         => __( 'Video', 'charitable-videos' ),
				'type'          => 'textarea',
				'priority'      => 18,
				'required'      => false,
				'placeholder'   => __( 'Link or embed code for a video of your campaign', 'charitable-videos' ),
				'fullwidth'     => true,
				'value'         => $form->get_campaign_value( 'video' ),
				'data_type'     => 'meta',
			);

			return $fields;
		}
	}

endif; // End class_exists check
