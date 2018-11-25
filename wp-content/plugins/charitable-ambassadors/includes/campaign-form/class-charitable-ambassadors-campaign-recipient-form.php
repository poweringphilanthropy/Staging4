<?php
/**
 * Class responsible for defining the "campaign recipient" part of the campaign form.
 *
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Campaign_Recipient_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Ambassadors_Campaign_Recipient_Form' ) ) :

	/**
	 * Charitable_Ambassadors_Campaign_Recipient_Form
	 *
	 * @since       1.0.0
	 */
	class Charitable_Ambassadors_Campaign_Recipient_Form {

		/**
		 * Static class instance.
		 *
		 * @var     Charitable_Ambassadors_Campaign_Recipient_Form
		 * @access  private
		 * @static
		 */
		private static $instance = null;

		/**
		 * Whether the form has a recipients page.
		 *
		 * @var     boolean
		 * @access  private
		 * @static
		 */
		private $has_recipients_page;

		/**
		 * Create class object. A private constructor, so this is used in a singleton context.
		 *
		 * @access  private
		 * @since   1.0.0
		 */
		private function __construct() {}

		/**
		 * Returns and/or create the single instance of this class.
		 *
		 * @return  Charitable_Donation_Processor
		 * @access  public
		 * @since   1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Ambassadors_Campaign_Recipient_Form();
			}

			return self::$instance;
		}

		/**
		 * Add the recipient details page to the form.
		 *
		 * @return  array
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_recipient_details_page( $pages ) {
			if ( ! $this->has_recipients_page() ) {
				return $pages;
			}

			$pages[] = array(
				'page' => 'recipient_details',
				'priority' => 1,
			);

			return $pages;
		}

		/**
		 * Returns the recipient details fields.
		 *
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_recipient_type_fields( $fields ) {
			if ( ! $this->has_recipients_page() ) {
				return $fields;
			}

			$fields['recipient_fields'] = array(
				'legend'        => __( 'Who Are You Raising Money For?', 'charitable-ambassadors' ),
				'type'          => 'fieldset',
				'fields'        => $this->get_recipient_fields(),
				'priority'      => 0,
				'page'          => 'recipient_details',
			);

			return $fields;
		}

		/**
		 * Returns the fields specific to a particular recipient type.
		 *
		 * @param   string  $recipient_type
		 * @param   array   $recipient_types
		 * @return  array
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_individual_recipient_type_fields( $recipient_type, $recipient_types = array() ) {
			if ( empty( $recipient_types ) ) {
				$recipient_types = charitable_get_recipient_types();
			}

			$fields = apply_filters( 'charitable_recipient_type_fields', array(), $recipient_type, $recipient_types[ $recipient_type ] );

			uasort( $fields, 'charitable_priority_sort' );

			return $fields;
		}

		/**
		 * Add search field to a recipient type.
		 *
		 * @param   array   $fields
		 * @param   string  $recipient_type
		 * @param   array   $recipient_type_args
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_recipient_type_search( $fields, $recipient_type, $recipient_type_args ) {
			if ( ! $this->has_recipients_page() ) {
				return $fields;
			}

			if ( ! $recipient_type_args['searchable'] ) {
				return $fields;
			}

			$recipient_type_args['recipient_type_key'] = $recipient_type;

			$fields[ 'recipient_type_' . $recipient_type . '_select' ] = array(
				'type' => 'recipient-type-search',
				'recipient_type' => $recipient_type_args,
				'priority' => 1,
				'data_type' => 'meta',
				'required' => true,
			);

			return $fields;
		}

		/**
		 * Set the campaign submission page to 1.
		 *
		 * @param   int $page_number
		 * @param   Charitable_Ambassadors_Campaign_Form $form
		 * @return  int
		 * @access  public
		 * @since   1.0.0
		 */
		public function set_campaign_submission_page( $page_number, Charitable_Ambassadors_Campaign_Form $form ) {
			if ( ! $this->has_recipients_page() ) {
				return $page_number;
			}

			if ( charitable_is_page( 'campaign_editing_page' ) ) {
				return 1;
			}

			if ( empty( $_POST ) ) {
				return $page_number;
			}

			$fields = $form->get_merged_fields( 'recipient_details' );

			if ( isset( $_POST['recipient'] ) ) {
				$fields = array_merge( $fields, $this->get_individual_recipient_type_fields( $_POST['recipient'] ) );
			}

			if ( $form->check_required_fields( $fields ) ) {
				$page_number = 1;
			}

			return $page_number;
		}

		/**
		 * Adds the recipient fields as the recipient details fields.
		 *
		 * @param   array[] $fields
		 * @param   Charitable_Ambassadors_Campaign_Form $form
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_hidden_recipient_type_fields( $fields, Charitable_Ambassadors_Campaign_Form $form ) {
			$extra_fields = $this->get_recipient_fields();

			$recipient_types = charitable_get_recipient_types();

			$selected_type = $form->get_recipient_type();

			foreach ( $recipient_types as $recipient_type => $recipient_type_args ) {

				if ( $recipient_type != $selected_type ) {
					continue;
				}

				$recipient_type_fields = $this->get_individual_recipient_type_fields( $recipient_type, $recipient_types );

				if ( empty( $recipient_type_fields ) ) {
					continue;
				}

				$extra_fields = array_merge( $recipient_type_fields, $extra_fields );
			}

			/**
			 * Convert all of the fields to hidden fields,
			 * and give them a priority of 0.
			 */
			foreach ( $extra_fields as $key => $field ) {

				$field['type']     = 'hidden';
				$field['priority'] = 0;
				$field['value']    = 'recipient' == $key ? $selected_type : $form->get_campaign_value( $key );

				$fields[ $key ]    = $field;

			}

			return $fields;
		}

		/**
		 * Return the recipient fields.
		 *
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_recipient_fields() {
			$recipient_fields = apply_filters( 'charitable_campaign_submission_recipient_fields', array(
				'recipient' => array(
					'type'          => 'recipient-types',
					'priority'      => 42,
					'data_type'     => 'meta',
					'required'      => true,
				),
			), $this );

			uasort( $recipient_fields, 'charitable_priority_sort' );

			return $recipient_fields;
		}

		/**
		 * Returns whether a recipients page is necessary.
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.0.0
		 */
		public function has_recipients_page() {
			if ( ! isset( $this->has_recipients_page ) ) {

				$recipient_types = charitable_get_option( 'campaign_recipients', array() );

				if ( empty( $recipient_types ) ) {
					$this->has_recipients_page = false;
					return $this->has_recipients_page;
				}

				if ( count( $recipient_types ) > 1 ) {
					$this->has_recipients_page = true;
					return $this->has_recipients_page;
				}

				$recipient_type = charitable_get_recipient_type( $recipient_types[0] );

				$this->has_recipients_page = isset( $recipient_type['searchable'] ) && $recipient_type['searchable'];
			}

			return $this->has_recipients_page;
		}

		/**
		 * Search for recipients.
		 *
		 * This receives a POST request via AJAX, which contains the search phrase.
		 *
		 * @return  json
		 * @access  public
		 * @since   1.0.0
		 */
		public function search_recipients() {
			if ( ! isset( $_POST['q'] ) ) {
				wp_send_json_error( $_POST );
			}

			if ( ! isset( $_POST['recipient_type'] ) ) {
				wp_send_json_error( $_POST );
			}

			$results = apply_filters( 'charitable_recipient_search_' . $_POST['recipient_type'], array(), $_POST['q'] );

			wp_send_json( $results );
		}
	}

endif; // End class_exists check
