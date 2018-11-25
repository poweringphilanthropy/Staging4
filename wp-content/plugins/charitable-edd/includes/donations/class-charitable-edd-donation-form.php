<?php
/**
 * A custom donation form handler for Charitable campaigns using EDD products.
 *
 * @package   Charitable EDD/Classes/Charitable_EDD_Donation_Form
 * @version   1.0.0
 * @author    Eric Daams
 * @copyright Copyright (c) 2017, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_EDD_Donation_Form' ) ) :

	/**
	 * Charitable_EDD_Donation_Form
	 *
	 * @since       1.0.0
	 */
	class Charitable_EDD_Donation_Form extends Charitable_Donation_Form implements Charitable_Donation_Form_Interface {

		/**
		 * The Charitable_EDD_Campaign object.
		 *
		 * @var     Charitable_EDD_Campaign
		 * @access  protected
		 */
		protected $edd_campaign;

		/**
		 * Arguments that are passed to the views.
		 *
		 * @var     array
		 * @access  protected
		 */
		protected $view_args;

		/**
		 * Create a donation form object.
		 *
		 * @param   Charitable_Campaign $campaign
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( Charitable_Campaign $campaign ) {
			$this->campaign     = $campaign;
			$this->id           = uniqid();
			$this->edd_campaign = new Charitable_EDD_Campaign( $this->campaign->ID );
			$this->view_args    = array(
				'campaign'      => $this->campaign,
				'edd_campaign'  => $this->edd_campaign,
				'form'          => $this,
			);

			$this->attach_hooks_and_filters();
		}

		/**
		 * Set up callbacks for actions and filters.
		 *
		 * @return  void
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function attach_hooks_and_filters() {			
			/* Backwards compatibility for Charitable pre-1.5. */
			if ( version_compare( charitable()->get_version(), '1.5', '<' ) ) {
				parent::attach_hooks_and_filters();

				add_filter( 'charitable_form_field_template', array( $this, 'define_custom_templates' ), 11, 2 );
				remove_filter( 'charitable_donation_form_gateway_fields', array( $this, 'add_credit_card_fields' ), 10, 2 );
				remove_action( 'charitable_donation_form_after_user_fields', array( $this, 'add_password_field' ) );
			}

			$this->setup_download_fields();

			do_action( 'charitable_edd_donation_form', $this );
		}

		/**
		 * Render the donation form.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function render() {
			/* If the campaign has ended, donations cannot continue. */
			if ( $this->campaign->has_ended() ) {
				wp_redirect( get_permalink( $this->campaign->ID ) );
				exit;
			}

			charitable_edd_template( 'donation-form.php', $this->view_args );
		}

		/**
		 * Return arguments to pass to the view.
		 *
		 * @return  mixed[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_view_args() {
			return $this->view_args;
		}

		/**
		 * Return the Charitable_EDD_Campaign object associated with this campaign.
		 *
		 * @return  Charitable_EDD_Campaign
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_edd_campaign() {
			return $this->edd_campaign;
		}

		/**
		 * Return the donation form fields.
		 *
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_fields() {
			$fields = apply_filters( 'charitable_edd_donation_form_fields', array(
				'donation_fields' => array(
					'legend'   => __( 'Donation Amount', 'charitable-edd' ),
					'type'     => 'fieldset',
					'fields'   => $this->get_donation_fields(),
					'priority' => 20,
				),
			), $this );

			uasort( $fields, 'charitable_priority_sort' );

			return $fields;
		}

		/**
		 * Add download fields to the donation form.
		 *
		 * @param   array[] $fields
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_download_fields( $fields ) {
			if ( ! $this->get_campaign()->get( 'edd_show_contribution_options' ) ) {
				return $fields;
			}

			$fields['download_fields'] = array(
				'legend'   => $this->get_campaign()->get( 'edd_contribution_options_title' ),
				'type'     => 'fieldset',
				'fields'   => $this->get_download_fields(),
				'priority' => 40,
			);

			return $fields;
		}

		/**
		 * Return the array of download fields.
		 *
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_download_fields() {
			$download_fields = apply_filters( 'charitable_edd_donation_form_download_fields', array(
				'intro' => array(
					'type'     => 'paragraph',
					'priority' => 2,
					'content'  => $this->get_campaign()->get( 'edd_contribution_options_explanation' ),
				),
				'downloads' => array(
					'type'     => 'edd-downloads',
					'priority' => 4,
					'required' => false,
				),
			), $this );

			uasort( $download_fields, 'charitable_priority_sort' );

			return $download_fields;
		}

		/**
		 * Use custom template for some form fields.
		 *
		 * @param   string|false $custom_template
		 * @return  string|false|Charitable_Template
		 * @access  public
		 * @since   1.0.0
		 */
		public function define_custom_templates( $custom_template, $field ) {
			if ( 'edd-downloads' == $field['type'] ) {
				$template_name   = 'donation-form/edd-downloads.php';
				$custom_template = new Charitable_EDD_Template( $template_name, false );
			}

			return $custom_template;	
		}

		/**
		 * Display option to just donate money without choosing a download as a reward.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function select_no_download( $downloads ) {
			charitable_edd_template( 'donation-form/no-download.php' );
		}

		/**
		 * Validate the form submission.
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.0.0
		 */
		public function validate_submission() {

			/* If we have already validated the submission, return the value. */
			if ( $this->validated ) {
				return $this->valid;
			}

			$this->validated = true;

			$this->valid = $this->validate_security_check()
				&& $this->check_required_fields( $this->get_merged_fields() )
				&& $this->validate_amount();

			$this->valid = apply_filters( 'charitable_validate_edd_donation_form_submission', $this->valid, $this );

			return $this->valid;

		}

		/**
		 * Checks whether the security checks (nonce and honeypot) pass.
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.0.4
		 */
		public function validate_security_check() {

			$ret = true;

			if ( ! $this->validate_nonce() || ! $this->validate_honeypot() ) {

				charitable_get_notices()->add_error( __( 'There was an error with processing your form submission. Please reload the page and try again.', 'charitable-edd' ) );

				$ret = false;

			}

			return apply_filters( 'charitable_validate_donation_form_submission_security_check', $ret, $this );

		}

		/**
		 * Checks whether the set amount is valid.
		 *
		 * @return  boolean
		 * @access  public
		 * @since   1.0.4
		 */
		public function validate_amount() {

			$ret = true;

			/* Ensure that a valid amount has been submitted. */
			if ( self::get_donation_amount() <= 0 && ! apply_filters( 'charitable_permit_0_donation', false ) ) {

				charitable_get_notices()->add_error( sprintf(
					__( 'You must donate more than %s.', 'charitable-edd' ),
					charitable_format_money( '0' )
				) );

				$ret = false;

			}

			return apply_filters( 'charitable_validate_donation_form_submission_amount_check', $ret, $this );

		}

		/**
		 * Return the donation amount for this form submission.
		 *
		 * @since  1.1.3
		 *
		 * @return float
		 */
		public static function get_donation_amount() {
			$amount = parent::get_donation_amount();

			if ( array_key_exists( 'downloads', $_POST ) ) {
				$downloads = $_POST['downloads'];

				foreach ( $downloads as $download_id => $download ) {
					if ( ! is_array( $download ) ) {
						$download = array();
					}

					$download['id']       = $download_id;
					$download['quantity'] = 1;

					$downloads[ $download_id ] = $download;
				}

				$cart    = new Charitable_EDD_Cart( $downloads );
				$amount += $cart->get_total_benefit_amount();
			}

			return $amount;
		}

		/**
		 * Override implementation in Charitable_Donation_Form
		 *
		 * @return  void
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function setup_payment_fields() {
		}

		/**
		 * Override implementation in Charitable_Donation_Form
		 *
		 * @return  string[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_hidden_gateway_field( $fields ) {
			return $fields;
		}

		/**
		 * Set up download fields if there are downloads linked to the current campaign.
		 *
		 * @return  void
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function setup_download_fields() {
			if ( false === $this->edd_campaign->get_connected_downloads() ) {
				return;
			}

			add_action( 'charitable_edd_donation_form_fields', array( $this, 'add_download_fields' ), 2 );
		}
	}

endif; // End class_exists check
