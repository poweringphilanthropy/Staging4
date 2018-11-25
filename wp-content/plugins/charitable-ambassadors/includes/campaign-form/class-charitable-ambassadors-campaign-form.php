<?php
/**
 * Class that manages the display and processing of the campaign form.
 *
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Campaign_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Ambassadors_Campaign_Form' ) ) :

	/**
	 * Charitable_Ambassadors_Campaign_Form
	 *
	 * @since       1.0.0
	 */
	class Charitable_Ambassadors_Campaign_Form extends Charitable_Form {

		/**
		 * Shortcode parameters.
		 *
		 * @var     array
		 * @access  protected
		 */
		protected $args;

		/**
		 * Page number.
		 *
		 * @var     int
		 * @access  protected
		 */
		protected $page;

		/**
		 * The nonce action identifier.
		 *
		 * @var     string
		 */
		protected $nonce_action = 'charitable_campaign_submission';

		/**
		 * The nonce name.
		 *
		 * @var     string
		 */
		protected $nonce_name = '_charitable_campaign_submission_nonce';

		/**
		 * Action to be executed upon form submission.
		 *
		 * @var     string
		 * @access  protected
		 */
		protected $form_action = 'save_campaign';

		/**
		 * The current user.
		 *
		 * @var     Charitable_User
		 * @access  protected
		 */
		protected $user;

		/**
		 * The context of the submission.
		 *
		 * @var     string
		 * @access  protected
		 */
		protected $submission_context;

		/**
		 * Create class object.
		 *
		 * @param   array $args User-defined shortcode attributes.
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( $args = array() ) {
			$this->id = uniqid();
			$this->args = $args;
			$this->attach_hooks_and_filters();
		}

		/**
		 * Set up callback methods for actions & filters.
		 *
		 * @return  void
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function attach_hooks_and_filters() {
			add_filter( 'charitable_campaign_submission_fields', array( $this, 'filter_non_editable_fields' ), 5 );
			add_filter( 'charitable_campaign_submission_fields', array( $this, 'filter_update_only_fields' ), 5 );
			add_filter( 'charitable_campaign_meta_key', array( $this, 'set_thumbnail_id_meta_key' ), 10, 2 );
			add_filter( 'charitable_campaign_submission_meta_data', array( $this, 'save_picture' ), 10, 3 );
			add_filter( 'charitable_campaign_submission_meta_data', array( $this, 'get_end_date' ), 10, 3 );
			add_filter( 'charitable_campaign_submission_fields_map', array( $this, 'save_end_date' ), 10, 2 );
			add_filter( 'charitable_sanitize_campaign_meta__campaign_length', array( $this, 'sanitize_campaign_length' ) );

			do_action( 'charitable_ambassadors_campaign_form_start', $this );
		}

		/**
		 * Return the current user's Charitable_User object.
		 *
		 * @return  Charitable_User
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_user() {
			if ( ! isset( $this->user ) ) {
				$this->user = new Charitable_User( get_current_user_id() );
			}

			return $this->user;
		}

		/**
		 * Return the current campaign's Charitable_Campaign object.
		 *
		 * @return  Charitable_Campaign|false
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_campaign() {
			if ( ! isset( $this->campaign ) ) {

				if ( isset( $_POST['ID'] ) ) {
					$campaign_id = $_POST['ID'];
				} else {
					$campaign_id = get_query_var( 'campaign_id', false );
				}

				$this->campaign = $campaign_id ? new Charitable_Campaign( $campaign_id ) : false;
			}

			return $this->campaign;
		}

		/**
		 * Returns the value of a particular key.
		 *
		 * @param   string $key The key of the field value we need.
		 * @return  mixed
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_campaign_value( $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				return $_POST[ $key ];
			}

			$campaign = $this->get_campaign();
			$value = '';

			if ( $campaign ) {
				switch ( $key ) {
					case 'ID' :
						$value = $campaign->ID;
						break;

					case 'post_title' :
						$value = $campaign->post_title;
						break;

					case 'description' :
						$value = $campaign->get( 'description' );
						break;

					case 'goal' :
						$value = $campaign->get( 'goal' );
						break;

					case 'campaign_category' :
						$value = wp_get_object_terms( $campaign->ID, 'campaign_category', array( 'fields' => 'ids' ) );
						break;

					case 'campaign_tag' :
						$value = wp_get_object_terms( $campaign->ID, 'campaign_tag', array( 'fields' => 'ids' ) );
						break;

					case 'post_content' :
						$value = $campaign->post_content;
						break;

					case 'image' :
						$thumbnail_id = get_post_thumbnail_id( $campaign->ID );

						if ( empty( $thumbnail_id ) ) {
							$value = '';
						} elseif ( version_compare( charitable()->get_version(), '1.4.0', '<' ) ) {
							$value = wp_get_attachment_image( $thumbnail_id );
						} else {
							$value = $thumbnail_id;
						}
						break;

					case 'suggested_amounts' :
						$value = $campaign->get( 'suggested_donations' );
						break;

					case 'recurring_donations' :
						$current = $campaign->get( 'recurring_donations' );
						$value   = in_array( $current, array( 'simple', 'advanced' ) );
						break;

					default :
						$data = $campaign->get( 'submission_data' );

						if ( ! is_array( $data ) ) {
							$value = '';
						} else {
							// Fallback.
							$value = array_key_exists( $key, $data ) ? $data[ $key ] : $campaign->get( $key );
						}
				}//end switch
			}//end if

			return apply_filters( 'charitable_campaign_value', $value, $key, $campaign );
		}

		/**
		 * Returns the value of a particular key.
		 *
		 * @param 	string $key     The key to search for.
		 * @param 	mixed  $default The default value to return if none is found.
		 * @return  mixed
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_user_value( $key, $default = '' ) {
			if ( isset( $_POST[ $key ] ) ) {
				return $_POST[ $key ];
			}

			$user = $this->get_user();
			$value = $default;

			if ( $user ) {
				switch ( $key ) {
					case 'user_description' :
						$value = $user->description;
						break;

					default :
						if ( $user->has_prop( $key ) ) {
							$value = $user->__get( $key );
						}
				}
			}

			return apply_filters( 'charitable_campaign_submission_user_value', $value, $key, $user );
		}

		/**
		 * Returns the recipient type associated with this campaign.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_recipient_type() {
			$recipient_type = apply_filters( 'charitable_ambassadors_campaign_form_recipient_type', $this->get_campaign_value( 'recipient' ), $this );

			if ( empty( $recipient_type ) ) {

				$recipient_types = charitable_get_option( 'campaign_recipients', array() );

				/* If neither recipient type is checked, we use 'ambassador' as a default (i.e. funds raised for organization). */
				if ( empty( $recipient_types ) ) {
					return 'ambassador';
				}

				if ( count( $recipient_types ) > 1 ) {
					return $recipient_type;
				}

				$recipient_type = $recipient_types[0];
			}

			return $recipient_type;
		}

		/**
		 * Adds hidden fields to the start of the donation form.
		 *
		 * @since  1.2.0
		 *
		 * @return array
		 */
		public function get_hidden_fields() {
			/**
			 * Filter the hidden fields.
			 *
			 * @since 1.0.0
			 *
			 * @param array                                $fields The array of hidden fields.
			 * @param Charitable_Ambassadors_Campaign_Form $form   This instance of `Charitable_Ambassadors_Campaign_Form`.
			 */
			return apply_filters( 'charitable_ambassadors_campaign_form_hidden_fields', array_merge( parent::get_hidden_fields(), array(
				'page' => $this->get_current_page(),
				'ID'   => $this->get_campaign_value( 'ID' ),
			) ), $this );
		}		

		/**
		 * Return the campaign fields.
		 *
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_campaign_fields() {
			$currency_symbol = is_callable( array( Charitable_Currency::get_instance(), 'get_currency_symbol' ) ) ? Charitable_Currency::get_instance()->get_currency_symbol() : '$';

			$campaign_fields = array(
				'post_title' => array(
					'label'         => __( 'Campaign Name', 'charitable-ambassadors' ),
					'type'          => 'text',
					'priority'      => 2,
					'required'      => true,
					'fullwidth'     => true,
					'value'         => $this->get_campaign_value( 'post_title' ),
					'data_type'     => 'core',
					'editable'      => false,
				),
				'description' => array(
					'label'         => __( 'Short Description', 'charitable-ambassadors' ),
					'type'          => 'textarea',
					'priority'      => 4,
					'required'      => true,
					'fullwidth'     => true,
					'placeholder'   => __( 'A short, snappy description of your campaign', 'charitable-ambassadors' ),
					'value'         => $this->get_campaign_value( 'description' ),
					'data_type'     => 'meta',
				),
				'goal' => array(
					'label'         => sprintf( '%s (%s)', __( 'Fundraising Goal', 'charitable-ambassadors' ), $currency_symbol ),
					'type'          => 'number',
					'priority'      => 6,
					'min'           => 0,
					'required'      => false,
					'placeholder'   => '&#8734;',
					'value'         => $this->get_campaign_value( 'goal' ),
					'data_type'     => 'meta',
					'editable'      => false,
				),				
				'post_content' => array(
					'label'         => __( 'Full Description', 'charitable-ambassadors' ),
					'type'          => 'editor',
					'priority'      => 14,
					'required'      => true,
					'fullwidth'     => true,
					'value'         => $this->get_campaign_value( 'post_content' ),
					'data_type'     => 'core',
				),
				'image' => array(
					'label'         => __( 'Featured Image', 'charitable-ambassadors' ),
					'type'          => 'picture',
					'priority'      => 16,
					'required'      => false,
					'fullwidth'     => true,
					'size'          => 'medium',
					'uploader'      => true,
					'max_uploads'   => 1,
					'parent_id'     => $this->get_campaign_value( 'ID' ),
					'value'         => $this->get_campaign_value( 'image' ),
					'data_type'     => 'meta',
				),
			);

			$campaign_fields['length'] = $this->setup_campaign_length_field();

			$categories = get_terms( 'campaign_category', array( 'hide_empty' => false, 'fields' => 'id=>name' ) );
			$tags = get_terms( 'campaign_tag', array( 'hide_empty' => false, 'fields' => 'id=>name' ) );

			if ( count( $categories ) ) {

				$campaign_fields['campaign_category'] = array(
					'label'         => __( 'Categories', 'charitable-ambassadors' ),
					'type'          => 'multi-checkbox',
					'priority'      => 10,
					'required'      => false,
					'options'       => $categories,
					'value'         => $this->get_campaign_value( 'campaign_category' ),
					'data_type'     => 'taxonomy',
				);

			}

			if ( count( $tags ) ) {

				$campaign_fields['campaign_tag'] = array(
					'label'         => __( 'Tags', 'charitable-ambassadors' ),
					'type'          => 'multi-checkbox',
					'priority'      => 12,
					'required'      => false,
					'options'       => $tags,
					'value'         => $this->get_campaign_value( 'campaign_tag' ),
					'data_type'     => 'taxonomy',
				);

			}

			$campaign_fields = apply_filters( 'charitable_campaign_submission_campaign_fields', $campaign_fields, $this );

			uasort( $campaign_fields, 'charitable_priority_sort' );

			return $campaign_fields;
		}

		/**
		 * Return the donation options fields.
		 *
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_donation_options_fields() {
			$donation_fields = array(
				'donation_options'  => array(
					'type'          => 'paragraph',
					'priority'      => 22,
					'fullwidth'     => true,
					'content'       => __( 'When people make a donation to your campaign, they will be able to donate any amount they choose. You can also provide suggested donation amounts in the table below.', 'charitable-ambassadors' ),
				),
				'suggested_donations' => array(
					'type'          => 'suggested-donations',
					'priority'      => 24,
					'fullwidth'     => true,
					'value'         => $this->get_campaign_value( 'suggested_donations' ),
					'data_type'     => 'meta',
				),
				'allow_custom_donations' => array(
					'type'          => 'hidden',
					'priority'      => 26,
					'fullwidth'     => true,
					'value'         => 1,
					'data_type'     => 'meta',
				),
			);

			if ( class_exists( 'Charitable_Recurring' ) && charitable_get_option( 'allow_creators_to_create_recurring_campaigns', 0 ) ) {

				$donation_fields['recurring_donations'] = array(
					'type'			=> 'checkbox',
					'label'			=> __( 'Allow your donors to give with monthly recurring donations.', 'charitable-ambassadors' ),
					'priority'		=> 28,
					'value'			=> '1',
					'checked'     	=> $this->get_campaign_value( 'recurring_donations' ),
					'data_type'     => 'meta',
				);

			}

			$donation_fields = apply_filters( 'charitable_campaign_submission_donation_options_fields', $donation_fields, $this );

			uasort( $donation_fields, 'charitable_priority_sort' );

			return $donation_fields;
		}

		/**
		 * Return the core user fields.
		 *
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_user_fields() {
			$user_fields = apply_filters( 'charitable_campaign_submission_user_fields', array(
				'first_name' => array(
					'label'         => __( 'First name', 'charitable-ambassadors' ),
					'type'          => 'text',
					'priority'      => 42,
					'required'      => true,
					'value'         => $this->get_user_value( 'first_name' ),
					'data_type'     => 'user',
				),
				'last_name' => array(
					'label'         => __( 'Last name', 'charitable-ambassadors' ),
					'type'          => 'text',
					'priority'      => 44,
					'required'      => true,
					'value'         => $this->get_user_value( 'last_name' ),
					'data_type'     => 'user',
				),
				'user_email' => array(
					'label'         => __( 'Email', 'charitable-ambassadors' ),
					'type'          => 'email',
					'required'      => true,
					'priority'      => 46,
					'value'         => $this->get_user_value( 'user_email' ),
					'data_type'     => 'user',
				),
				'city' => array(
					'label'         => __( 'City', 'charitable-ambassadors' ),
					'type'          => 'text',
					'priority'      => 48,
					'required'      => false,
					'value'         => $this->get_user_value( 'donor_city' ),
					'data_type'     => 'user',
				),
				'state' => array(
					'label'         => __( 'State', 'charitable-ambassadors' ),
					'type'          => 'text',
					'priority'      => 50,
					'required'      => false,
					'value'         => $this->get_user_value( 'donor_state' ),
					'data_type'     => 'user',
				),
				'country' => array(
					'label'         => __( 'Country', 'charitable-ambassadors' ),
					'type'          => 'select',
					'options'       => charitable_get_location_helper()->get_countries(),
					'priority'      => 52,
					'required'      => false,
					'value'         => $this->get_user_value( 'donor_country', charitable_get_option( 'country' ) ),
					'data_type'     => 'user',
				),
				'user_description' => array(
					'label'         => __( 'Bio', 'charitable-ambassadors' ),
					'type'          => 'textarea',
					'priority'      => 54,
					'required'      => false,
					'value'         => $this->get_user_value( 'description' ),
					'data_type'     => 'user',
				),
				'organisation' => array(
					'label'         => __( 'Organization', 'charitable-ambassadors' ),
					'type'          => 'text',
					'priority'      => 56,
					'required'      => false,
					'value'         => $this->get_user_value( 'organisation' ),
					'data_type'     => 'user',
				),
			), $this );

			uasort( $user_fields, 'charitable_priority_sort' );

			return $user_fields;
		}

		/**
		 * Campaign form fields to be displayed.
		 *
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_fields() {
			$fields = apply_filters( 'charitable_campaign_submission_fields', array(
				'campaign_fields' => array(
					'legend'        => __( 'Campaign Details', 'charitable-ambassadors' ),
					'type'          => 'fieldset',
					'fields'        => $this->get_campaign_fields(),
					'priority'      => 20,
					'page'          => 'campaign_details',
				),
				'donation_fields' => array(
					'legend'        => __( 'Donation Options', 'charitable-ambassadors' ),
					'type'          => 'fieldset',
					'fields'        => $this->get_donation_options_fields(),
					'priority'      => 40,
					'page'          => 'campaign_details',
				),
				'user_fields' => array(
					'legend'        => __( 'Your Details', 'charitable-ambassadors' ),
					'type'          => 'fieldset',
					'fields'        => $this->get_user_fields(),
					'priority'      => 60,
					'page'          => 'campaign_details',
				),
			), $this );

			uasort( $fields, 'charitable_priority_sort' );

			return $fields;
		}

		/**
		 * Campaign form fields to be displayed.
		 *
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_pages() {
			if ( ! isset( $this->pages ) ) {
				$this->pages = apply_filters( 'charitable_campaign_submission_pages', array(
					array(
						'page' 	   => 'campaign_details',
						'priority' => 2,
					),
				) );

				uasort( $this->pages, 'charitable_priority_sort' );

				$this->pages = array_values( $this->pages );
			}

			return $this->pages;
		}

		/**
		 * Returns the fields for the current page.
		 *
		 * @return  array
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_current_page_fields() {
			return $this->get_page_fields( $this->get_current_page() );
		}

		/**
		 * Return the current page name.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_current_page() {			
			if ( ! isset( $this->current_page ) ) {
				$pages 				= $this->get_pages();
				$this->current_page = $pages[ $this->get_current_page_number() ]['page'];
			}

			return $this->current_page;
		}

		/**
		 * Return the current page name.
		 *
		 * @return  string|false
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_previous_page() {
			return isset( $_POST['page'] ) ? $_POST['page'] : false;
		}

		/**
		 * Return the current page name.
		 *
		 * @return  string|false
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_previous_page_number() {
			$previous = $this->get_previous_page();

			if ( ! $previous ) {
				return false;
			}

			foreach ( $this->get_pages() as $number => $page ) {
				if ( $previous == $page['page'] ) {
					return $number;
				}
			}

			return false;
		}

		/**
		 * Determine the current page number based on the args and submitted values.
		 *
		 * @return  int
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_current_page_number() {
			return apply_filters( 'charitable_ambassadors_campaign_form_current_page', 0, $this );
		}

		/**
		 * Checks whether there are multiple pages to the form.
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.0.0
		 */
		public function has_pages() {
			return count( $this->get_pages() ) > 1;
		}

		/**
		 * Returns whether the current page is the final page.
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.0.0
		 */
		public function is_final_page() {
			return $this->get_current_page_number() + 1 == count( $this->get_pages() );
		}

		/**
		 * Return the HTML for the continue button on pages.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_page_submit_button() {
			$button_text = apply_filters( 'charitable_ambassadors_form_page_submission_button_text', __( 'Save & Continue', 'charitable-ambassadors' ), $this );

			$output = sprintf( '<input class="button button-primary" type="submit" name="next-page" value="%s" />', esc_attr( $button_text ) );

			return $output;
		}

		/**
		 * Return the submit buttons.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_submit_buttons() {
			if ( ! $this->is_final_page() ) {
				return $this->get_page_submit_button();
			}

			if ( false === $this->get_campaign() || 'draft' == $this->get_campaign()->post_status ) {
				$primary_text = apply_filters( 'charitable_ambassadors_form_submission_buttons_primary_new_text', __( 'Submit Campaign', 'charitable-ambassadors' ), $this );
			} else {
				$primary_text = apply_filters( 'charitable_ambassadors_form_submission_buttons_primary_update_text', __( 'Update Campaign', 'charitable-ambassadors' ), $this );
			}

			$secondary_text = apply_filters( 'charitable_ambassadors_form_submission_buttons_preview_text', __( 'Save &amp; Preview', 'charitable-ambassadors' ) );

			$output = sprintf( '<input class="button button-secondary" type="submit" name="preview-campaign" value="%s" /> <input class="button button-primary" type="submit" name="submit-campaign" value="%s" />',
				esc_attr( $secondary_text ),
				esc_attr( $primary_text )
			);

			return $output;
		}

		/**
		 * Remove non-editable fields when we are editing a published campaign.
		 *
		 * @param 	array[] $fields The fields in the form.
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function filter_non_editable_fields( $fields ) {
			if ( false == $this->get_campaign() || in_array( $this->get_campaign()->post_status, array( 'pending', 'draft' ) ) ) {
				return $fields;
			}

			return $this->get_fields_filtered_by_callback( $fields, array( $this, 'is_non_editable_field' ) );
		}

		/**
		 * Remove "update only" fields when we are creating a new campaign.
		 *
		 * @param 	array[] $fields The fields in the form.
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function filter_update_only_fields( $fields ) {
			if ( false == $this->get_campaign() || in_array( $this->get_campaign()->post_status, array( 'pending', 'draft' ) ) ) {
				return $this->get_fields_filtered_by_callback( $fields, array( $this, 'is_update_only_field' ) );
			}

			return $fields;
		}

		/**
		 * Returns all fields as a merged array.
		 *
		 * @param   string $page The page we're on currently.
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_merged_fields( $page = '' ) {
			$parent_fields = empty( $page ) ? $this->get_fields() : $this->get_page_fields( $page );

			$fields = array();

			foreach ( $parent_fields as $key => $section ) {

				if ( isset( $section['fields'] ) ) {
					$fields = array_merge( $fields, $section['fields'] );
				} else {
					$fields[ $key ] = $section;
				}
			}

			return $fields;
		}

		/**
		 * Organize fields by data type, also filtering out unused parameters (we just need the key and the type).
		 *
		 * @param   string $key   The key of the field.
		 * @param   array  $field The field settings.
		 * @param   array  $ret   Default return value.
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function sort_field_by_data_type( $key, $field, $ret ) {
			/* Filter out paragraphs and fields without a type. */
			if ( ! isset( $field['type'] ) || 'paragraph' == $field['type'] ) {
				return $ret;
			}

			/* Get the data type. Default to meta if no type is set. */
			$data_type = isset( $field['data_type'] ) ? $field['data_type'] : 'meta';

			$ret[ $data_type ][ $key ] = $field['type'];

			return $ret;
		}

		/**
		 * Verify that the current user can create or edit this campaign.
		 *
		 * @return  boolean This will return true if the user can edit the campaign, or if this is a new campaign.
		 * @access  public
		 * @since   1.0.0
		 */
		public function current_user_can_edit_campaign() {
			$campaign = $this->get_campaign();

			if ( ! $campaign ) {
				return true;
			}

			return $this->get_campaign()->post_author == get_current_user_id();
		}

		/**
		 * Save campaign after form submission.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public static function save_campaign() {
			$form = new Charitable_Ambassadors_Campaign_Form();

			if ( ! $form->validate_nonce() ) {
				return;
			}

			/* Confirm that the current user has permission to edit this campaign. */
			if ( false !== $form->get_campaign() && ! $form->current_user_can_edit_campaign() ) {
				return;
			}

			if ( ! isset( $_POST['page'] ) ) {
				return;
			}

			/**
			 * Save a particular page in the process.
			 *
			 * @since 1.0.0
			 *
			 * @param Charitable_Ambassadors_Campaign_Form $form This instance of `Charitable_Ambassadors_Campaign_Form`.
			 */
			do_action( 'charitable_campaign_submission_save_page_' . $_POST['page'], $form );
		}

		/**
		 * Save the campaign details.
		 *
		 * @param   Charitable_Ambassadors_Campaign_Form $form The form object.
		 * @return  void
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function save_campaign_details( Charitable_Ambassadors_Campaign_Form $form ) {
			/* If required fields are missing, stop. */
			if ( ! $form->check_required_fields( $form->get_merged_fields() ) ) {
				return;
			}

			/* Organize fields into multi-dimensional array of core, meta, taxonomy and user data. */
			$fields = array();

			foreach ( $form->get_merged_fields() as $key => $field ) {
				$fields = $form->sort_field_by_data_type( $key, $field, $fields );
			}

			/* Allow plugins/themes to filter the submitted values prior to saving. */
			$submitted = apply_filters( 'charitable_campaign_submission_values', $_POST, $fields, $form );

			/* Set the context of the submission. */
			$form->set_submission_context( $submitted );

			/* Allow plugins/themes to filter the fields that will be saved by the campaign. */
			$fields = apply_filters( 'charitable_campaign_submission_fields_map', $fields, $submitted, $form );

			/* Save user data */
			$user_id = $form->save_user_data( $fields, $submitted );

			/* If the user could not be created successfully, return. */
			if ( ! $user_id ) {
				return;
			}

			/* Save campaign data */
			$campaign_id = $form->save_core_campaign_data( $fields, $submitted, $user_id );

			/* Save all submission data */
			$form->save_submission_data( $submitted, $campaign_id );

			/* Save taxonomy data */
			$form->save_campaign_taxonomies( $fields, $submitted, $campaign_id );

			/* Save campaign meta */
			$form->save_campaign_meta( $fields, $submitted, $campaign_id );

			/* Allow plugins/themes to do something now. */
			do_action( 'charitable_campaign_submission_save', $submitted, $campaign_id, $user_id, $form );

			wp_safe_redirect( $form->get_redirect_url( $submitted, $campaign_id, $user_id ) );

			exit();
		}

		/**
		 * Set the context of the submission.
		 *
		 * This will either be preview, submission, update or preview-update.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.1.0
		 */
		public function set_submission_context( $submitted ) {
			$id     = isset( $submitted['ID'] ) ? $submitted['ID'] : false;
			$status = $id ? get_post_status( $id ) : false;

			$this->submission_context['is_preview']           = isset( $submitted['preview-campaign'] );
			$this->submission_context['is_new']               = ! $id || empty( $id );
			$this->submission_context['is_submission']        = ! $this->submission_context['is_preview'] && ( ! $id || 'draft' == $status );
			$this->submission_context['is_already_published'] = 'publish' == $status;
		}

		/**
		 * Returns the submission of the context.
		 *
		 * @return  string
		 * @access  public
		 * @since   1.1.0
		 */
		public function get_submission_context() {
			return $this->submission_context;
		}

		/**
		 * Save the user data for this form.
		 *
		 * @param   array[] $fields    List of all form fields (not just user ones).
		 * @param   array   $submitted The values submitted by the user.
		 * @return  int     $user_id
		 * @access  public
		 * @since   1.0.0
		 */
		public function save_user_data( $fields, $submitted ) {
			if ( ! isset( $fields['user'] ) ) {
				return get_current_user_id();
			}

			$user_fields = apply_filters( 'charitable_campaign_submission_user_data', $submitted, $fields['user'], $this );
			$user_keys   = array_keys( $fields['user'] );
			$user        = $this->get_user();
			$user_id     = $user->update_profile( $submitted, $user_keys );

			/**
			 * If the user is currently listed as a subscriber, we want to
			 * remove that role and set them as a campaign creator.
			 */
			if ( $user_id ) {
				$user->remove_role( 'subscriber' );
				$user->add_role( 'campaign_creator' );
			}

			return $user_id;
		}

		/**
		 * Create the campaign as a new object in the wp_posts table.
		 *
		 * @param   array[] $fields      List of all form fields (not just user ones).
		 * @param   array   $submitted   The values submitted by the user.
		 * @param   int     $user_id     The ID of the user who submitted the form.
		 * @return  int     $campaign_id The ID of the newly created campaign.
		 * @access  public
		 * @since   1.0.0
		 */
		public function save_core_campaign_data( $fields, $submitted, $user_id ) {
			if ( ! isset( $fields['core'] ) ) {
				return array_key_exists( 'ID', $submitted ) ? $submitted['ID'] : 0;
			}

			$values = array(
				'post_type'   => 'campaign',
				'post_author' => $user_id,
			);

			$context = $this->get_submission_context();

			/* Updating an existing campaign, so set the ID. */
			if ( ! $context['is_new'] ) {
				$values['ID'] 			= $submitted['ID'];
				$values['post_status']  = get_post_status( $submitted['ID'] );
			}

			/* If we are previewing the campaign, the status is "draft". */
			if ( $context['is_new'] && $context['is_preview'] ) {
				$values['post_status']  = 'draft';
			}

			/* New submission, or a submission that was previously draft. */
			if ( $context['is_submission'] ) {
				$status 				= charitable_get_option( 'auto_approve_campaigns', 0 ) ? 'publish' : 'pending';
				$values['post_status']  = apply_filters( 'charitable_campaign_submission_initial_status', $status, $submitted, $user_id );
			}

			/* We've already touched this. */
			unset( $fields['core']['ID'] );

			foreach ( $fields['core'] as $key => $field_type ) {

				if ( 'checkbox' == $field_type ) {

					$values[ $key ] = isset( $submitted[ $key ] );

				} elseif ( isset( $submitted[ $key ] ) ) {

					$values[ $key ] = $submitted[ $key ];

				}
			}

			$values = apply_filters( 'charitable_campaign_submission_core_data', $values, $user_id, $submitted, $this );

			/* Update post if ID is set. */
			if ( isset( $values['ID'] ) ) {
				return wp_update_post( $values );
			}

			return wp_insert_post( $values );
		}

		/**
		 * Save the raw submitted data as a JSON encoded array.
		 *
		 * @param   array $submitted   The values submitted by the user.
		 * @param   int   $campaign_id The campaign ID.
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function save_submission_data( $submitted, $campaign_id ) {
			update_post_meta( $campaign_id, '_campaign_submission_data', $submitted );
		}

		/**
		 * Save the campaign taxonomy data.
		 *
		 * @param   array[] $fields      All the form fields.
		 * @param   array   $submitted   The values submitted by the user.
		 * @param   int     $campaign_id The campaign ID.
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function save_campaign_taxonomies( $fields, $submitted, $campaign_id ) {
			if ( ! isset( $fields['taxonomy'] ) ) {
				return;
			}

			$taxonomy_fields = $fields['taxonomy'];

			$submitted = apply_filters( 'charitable_campaign_submission_taxonomy_data', $submitted, $campaign_id, $taxonomy_fields, $this );

			foreach ( $taxonomy_fields as $taxonomy => $field_type ) {

				if ( isset( $submitted[ $taxonomy ] ) ) {
					$terms = is_array( $submitted[ $taxonomy ] ) ? array_map( 'intval', $submitted[ $taxonomy ] ) : intval( $submitted[ $taxonomy ] );
					wp_set_object_terms( $campaign_id, $terms, $taxonomy, false );
				}
			}
		}

		/**
		 * Save the meta fields for the newly created campaign.
		 *
		 * @param   array[]     $fields
		 * @param   array       $submitted
		 * @param   int         $campaign_id
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function save_campaign_meta( $fields, $submitted, $campaign_id ) {
			if ( ! isset( $fields['meta'] ) ) {
				return 0;
			}

			$meta_fields = $fields['meta'];

			$submitted = apply_filters( 'charitable_campaign_submission_meta_data', $submitted, $campaign_id, $meta_fields, $this );

			$updated_pairs = array();

			/* We're formatting the end date ourselves, so no need to run this filter. */
			remove_filter( 'charitable_sanitize_campaign_meta_campaign_end_date', array( 'Charitable_Campaign', 'sanitize_campaign_end_date' ), 10, 2 );

			if ( class_exists( 'Charitable_Recurring' ) ) {
				remove_filter( 'charitable_sanitize_campaign_meta_campaign_allow_custom_donations', 'charitable_recurring_sanitize_custom_donations', 11, 2 );
				add_filter( 'charitable_sanitize_campaign_meta_campaign_recurring_donations', array( $this, 'sanitize_recurring_donations' ), 10, 3 );
			}

			foreach ( $fields['meta'] as $key => $field_type ) {

				$meta_key = apply_filters( 'charitable_campaign_meta_key', '_campaign_' . $key, $key, $campaign_id );

				if ( 'checkbox' != $field_type && ! isset( $submitted[ $key ] ) ) {
					continue;
				}

				$value = 'checkbox' == $field_type ? isset( $submitted[ $key ] ) : $submitted[ $key ];

				/**
				 * This filter is deprecated. Use charitable_sanitize_campaign_meta{$key} instead.
				 *
				 * @deprecated
				 */
				$value = apply_filters( 'charitable_sanitize_campaign_meta', $value, $meta_key, $submitted );

				/**
				 * Filter this meta value.
				 *
				 * The filter hook is charitable_sanitize_campaign_meta{$key}.
				 *
				 * For example, for _campaign_end_date the filter hook will be:
				 *
				 * charitable_sanitize_campaign_meta_campaign_end_date
				 */
				$value = apply_filters( 'charitable_sanitize_campaign_meta' . $meta_key, $value, $submitted, $campaign_id );

				update_post_meta( $campaign_id, $meta_key, $value );

				$updated_pairs[ $key ] = $value;

			}//end foreach

			/* Turn the filter back on. */
			add_filter( 'charitable_sanitize_campaign_meta_campaign_end_date', array( 'Charitable_Campaign', 'sanitize_campaign_end_date' ), 10, 2 );

			if ( class_exists( 'Charitable_Recurring' ) ) {
				add_filter( 'charitable_sanitize_campaign_meta_campaign_allow_custom_donations', 'charitable_recurring_sanitize_custom_donations', 11, 2 );
				remove_filter( 'charitable_sanitize_campaign_meta_campaign_recurring_donations', array( $this, 'sanitize_recurring_donations' ), 10, 3 );
			}

			return count( $updated_pairs );
		}

		/**
	 	* Upload campaign thumbnail and add file field to the submitted fields.
		 *
		 * @param   array       $submitted
		 * @param   int         $campaign_id
		 * @param   array[]     $meta_fields
		 * @return  array
		 * @access  public
		 * @since   1.0.0
		 */
		public function save_picture( $submitted, $campaign_id, $meta_fields ) {
			if ( isset( $_FILES ) && isset( $_FILES['image'] ) && ! empty( $_FILES['image']['name'] ) ) {

				$attachment_id = $this->upload_post_attachment( 'image', $campaign_id );

				if ( ! is_wp_error( $attachment_id ) ) {

					$submitted['image'] = $attachment_id;

				} else {
					/**
					 * @todo Handle image upload error.
					 */
					charitable_get_notices()->add_error( __( 'There was an error uploading your campaign image.', 'charitable-ambassadors' ) );
				}
			} elseif ( ! array_key_exists( 'image', $submitted ) ) {

				/* The picture has been removed. */
				$submitted['image'] = '';

			}

			return $submitted;
		}

		/**
		 * Save campaign end date based on length.
		 *
		 * @param   array   $submitted
		 * @param   int     $campaign_id
		 * @param   array[] $meta_fields
		 * @return  array
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_end_date( $submitted, $campaign_id, $meta_fields ) {

			if ( ! array_key_exists( 'end_date', $meta_fields ) ) {
				return $submitted;
			}

			/* A length is set, so parse the end date based on that. */
			if ( array_key_exists( 'length', $submitted ) ) {

				if ( '0' == $submitted['length'] || empty( $submitted['length'] ) ) {

					/* This is an endless campaign. */
					$submitted['end_date'] = 0;

				} else {

					$end_date = strtotime( sprintf( '+%d day', $submitted['length'] ), current_time( 'timestamp' ) );
					$submitted['end_date'] = date( 'Y-m-d 00:00:00', $end_date );

				}

			/* An end_date field has been submitted. */
			} elseif ( array_key_exists( 'end_date', $submitted ) ) {

				$end_date = date( 'Y-m-d 00:00:00', strtotime( $submitted['end_date'] ) );
				$end_date = apply_filters( 'charitable_ambassadors_get_formatted_end_date', $end_date, $submitted['end_date'] );

				$submitted['end_date'] = $end_date;

			/* The length or end date are one of the fields but they're not set, so just keep the current value. */
			} elseif ( array_key_exists( 'length', $this->get_merged_fields() ) || array_key_exists( 'end_date', $this->get_merged_fields() ) ) {

				$submitted['end_date'] = get_post_meta( $campaign_id, '_campaign_end_date' );

			/* The end date & length are not included in the form, so there is no end date. */
			} else {

				$submitted['end_date'] = 0;

			}//end if

			return $submitted;
		}

		/**
		 * Add end_date to the campaign meta fields to be saved.
		 *
		 * @param   array[] $fields
		 * @param 	array 	$submitted
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function save_end_date( $fields, $submitted ) {

			$context = $this->get_submission_context();

			/* If the campaign is already published and
			 * a length is not submitted, we're not changing
			 * the end date.
			 */
			if ( ! $context['is_already_published'] || array_key_exists( 'length', $submitted ) ) {
				$fields['meta']['end_date'] = 'date';
			}

			return $fields;
		}

		/**
		 * Make sure the campaign length is a valid value.
		 *
		 * @param 	mixed $value The length specified by the user.
		 * @return  int
		 * @access  public
		 * @since   1.0.0
		 */
		public function sanitize_campaign_length( $value ) {
			$min = charitable_get_option( 'campaign_length_min' );
			$max = charitable_get_option( 'campaign_length_max' );

			/* If the passed value is less than the miniminum length permitted
			 * or greater than the max permitted, set to the minimum length
			 */
			if ( ( $min && $value < $min ) || ( $max && $value > $max ) ) {
				$value = apply_filters( 'charitable_default_campaign_length', $min, $max, $min );
			}

			return $value;
		}

		/**
		 * Sanitize the recurring donations meta.
		 *
		 * @param 	mixed $value       Current value of setting.
		 * @param 	array $submitted   Values submitted by the user.
		 * @param 	int   $campaign_id The ID of the campaign.
		 * @return  string
		 * @access  public
		 * @since   1.1.14
		 */
		public function sanitize_recurring_donations( $value, $submitted, $campaign_id ) {
			$current = get_post_meta( $campaign_id, '_campaign_recurring_donations', true );

			if ( ! $value ) {
				$value = 'disabled';
			} elseif ( 'advanced' == $current ) {
				$value = 'advanced';
			} else {
				$value = 'simple';
			}

			return $value;
		}

		/**
		 * Set meta key for thumbnail ID.
		 *
		 * @param 	string $key 		 The meta key of the image.
		 * @param 	string $original_key The original key.
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function set_thumbnail_id_meta_key( $key, $original_key ) {
			if ( 'image' == $original_key ) {
				$key = '_thumbnail_id';
			}

			return $key;
		}

		/**
		 * Redirect to the campaign if it's published or we're previewing.
		 *
		 * Otherwise, redirect to the campaign submission success page set in the admin settings.
		 *
		 * @param   array $submitted
		 * @param   int $campaign_id
		 * @param   int $user_id
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_redirect_url( $submitted, $campaign_id, $user_id ) {
			$context = $this->get_submission_context();

			if ( $context['is_already_published'] ) {
				$url = get_permalink( $campaign_id );
				$url = esc_url_raw( add_query_arg( array( 'updated' => true ), $url ) );
			} elseif ( $context['is_preview'] ) {
				$url = get_permalink( $campaign_id );
				$url = esc_url_raw( add_query_arg( array( 'preview' => true ), $url ) );
			} else {
				if ( 'home' == charitable_get_option( 'campaign_submission_success_page', 'home' ) ) {
					$url = home_url();
				} else {
					$url = charitable_get_permalink( 'campaign_submission_success_page', array( 'campaign_id' => $campaign_id ) );
				}
			}

			return apply_filters( 'charitable_campaign_submission_redirect_url', $url, $submitted, $campaign_id, $user_id );
		}

		/**
		 * Returns the campaign length field.
		 *
		 * @since  1.1.17
		 *
		 * @return array
		 */
		protected function setup_campaign_length_field() {
			$campaign_min = charitable_get_option( 'campaign_length_min' );
			$campaign_max = charitable_get_option( 'campaign_length_max' );

			if ( empty( $campaign_max ) ) {
				$campaign_length_placeholder = '&#8734;';
				$length_required = false;
			} else {
				if ( 0 == $campaign_min ) {
					$campaign_min = 1;
				}
				$campaign_length_placeholder = apply_filters( 'charitable_default_campaign_length', $campaign_min, $campaign_max, $campaign_min );
				$length_required = true;
			}

			$field = array(
				'label'       => __( 'Length in Days', 'charitable-ambassadors' ),
				'type'        => 'number',
				'priority'    => 8,
				'required'    => $length_required,
				'placeholder' => $campaign_length_placeholder,
				'min'         => $campaign_min,
				'value'       => $this->get_campaign_value( 'length' ),
				'data_type'   => 'meta',
				'editable'    => false,
			);

			if ( ! empty( $campaign_max ) ) {
				$field['max'] = $campaign_max;
			}

			return $field;
		}

		/**
		 * Filter out fields by callback.
		 *
		 * @param   array[] $fields
		 * @param   callback $callback
		 * @return  array[]
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function get_fields_filtered_by_callback( $fields, $callback ) {
			foreach ( $fields as $section_key => $section ) {

				if ( ! isset( $section['fields'] ) ) {
					continue;
				}

				foreach ( $section['fields'] as $field_key => $field ) {
					if ( call_user_func( $callback, $field ) ) {
						unset( $fields[ $section_key ]['fields'][ $field_key ] );
					}
				}
			}

			return $fields;
		}

		/**
		 * Returns true if the given field is a non-editable field.
		 *
		 * @return  boolean
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function is_non_editable_field( $field ) {
			return isset( $field['editable'] ) && false === $field['editable'];
		}

		/**
		 * Returns true if the given field is an update only field.
		 *
		 * @return  boolean
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function is_update_only_field( $field ) {
			return isset( $field['update_only'] ) && $field['update_only'];
		}

		/**
		 * Return the fields for the current page.
		 *
		 * @return  array
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function get_page_fields( $page ) {
			$fields = array();

			foreach ( $this->get_fields() as $key => $field ) {

				if ( isset( $field['page'] ) && $page == $field['page'] ) {

					$fields[ $key ] = $field;

				}
			}

			return $fields;
		}

		/**
		 * Adds hidden fields to the start of the donation form.
		 *
		 * @deprecated 1.4.0
		 *
		 * @since  1.0.0
		 * @since  1.2.0 Deprecated.
		 *
		 * @param  Charitable_Form $form The form object.
		 * @return boolean
		 */
		public function add_hidden_fields( $form ) {
			$hidden_fields = apply_filters( 'charitable_ambassadors_campaign_form_hidden_fields', array(
				'charitable_action' => $this->form_action,
				'page' => $this->get_current_page(),
				'ID' => $this->get_campaign_value( 'ID' ),
			), $this );

			foreach ( $hidden_fields as $name => $value  ) {
				printf( '<input type="hidden" name="%s" value="%s" />', $name, $value );
			}

			return true;
		}
	}

endif; // End class_exists check
