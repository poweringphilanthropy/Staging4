<?php
/**
 * Charitable Ambassadors Settings UI.
 *
 * @package     Charitable/Classes/Charitable_Ambassadors_Settings
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Ambassadors_Settings' ) ) :

	/**
	 * Charitable_Ambassadors_Settings
	 *
	 * @final
	 * @since      1.0.0
	 */
	final class Charitable_Ambassadors_Settings {

		/**
		 * The single instance of this class.
		 *
		 * @var     Charitable_Ambassadors_Settings|null
		 * @access  private
		 * @static
		 */
		private static $instance = null;

		/**
		 * Create object instance.
		 *
		 * @access  private
		 * @since   1.0.0
		 */
		private function __construct() {
		}

		/**
		 * Returns and/or create the single instance of this class.
		 *
		 * @return  Charitable_Ambassadors_Settings
		 * @access  public
		 * @since   1.2.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Ambassadors_Settings();
			}

			return self::$instance;
		}

		/**
		 * Add Ambassadors tab to the Charitable settings UI.
		 *
		 * @param   string[] $sections The settings sections.
		 * @return  string[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_ambassadors_section( $sections ) {
			$keys   = array_keys( $sections );
			$values = array_values( $sections );

			array_splice( $keys, 3, 0, 'ambassadors' );
			array_splice( $values, 3, 0, __( 'Ambassadors', 'charitable-ambassadors' ) );

			return array_combine( $keys, $values );
		}

		/**
		 * Include "ambassadors" as a setting group.
		 *
		 * @param   string[] $groups The setting groups.
		 * @return  string[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_ambassadors_settings_group( $groups ) {
			$groups[] = 'ambassadors';
			return $groups;
		}

		/**
		 * Add settings fields to add to the Ambassadors tab.
		 *
		 * @param 	array[] $fields List of settings fields.
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_ambassadors_settings_fields( $fields ) {
			if ( ! charitable_is_settings_view( 'ambassadors' ) ) {
				return array();
			}

			$fields = array(
				'section_ambassadors_general' => array(
					'title'             => __( 'General Settings', 'charitable-ambassadors' ),
					'type'              => 'heading',
					'priority'          => 2,
				),
				'campaign_recipients' => array(
					'title'             => __( 'What Can Ambassadors Raise Money For?', 'charitable-ambassadors' ),
					'type'              => 'multi-checkbox',
					'priority'          => 4,
					'options'           => $this->get_campaign_recipient_types(),
					'default'           => 'ambassador',
				),
				'campaign_payout' => array(
					'title'             => __( 'How Will You Send Money to Ambassadors', 'charitable-ambassadors' ),
					'type'              => 'select',
					'priority'          => 6,
					'options'           => $this->get_campaign_payout_methods(),
					'attrs'             => array(
						'data-show-only-if-key' => 'charitable_settings_campaign_recipients_personal',
						'data-show-only-if-value' => 'checked',
					),
				),
				'section_campaign_form' => array(
					'title'             => __( 'Campaign Submission Form', 'charitable-ambassadors' ),
					'type'              => 'heading',
					'priority'          => 22,
				),
				'require_user_account_for_campaign_submission' => array(
					'title'             => __( 'Require User Account', 'charitable-ambassadors' ),
					'type'              => 'checkbox',
					'priority'          => 24,
					'default'           => 1,
					'help'              => __( 'Require users to be logged in before they can submit a campaign.', 'charitable-ambassadors' ),
				),
				'auto_approve_campaigns' => array(
					'title'             => __( 'Automatically Approve Campaigns', 'charitable-ambassadors' ),
					'type'              => 'checkbox',
					'priority'          => 26,
					'default'           => 0,
					'help'              => __( 'If you enable this, campaigns will be automatically published once the user has submitted it.', 'charitable-ambassadors' ),
				),
				'campaign_length_min' => array(
					'title'             => __( 'Minimum Campaign Length', 'charitable-ambassadors' ),
					'type'              => 'number',
					'priority'          => 28,
					'default'           => 0,
					'min'               => 0,
					'required'          => false,
					'help'              => __( 'The minimum number of days that a user-submitted campaign can run for. Leave empty or set to 0 for no minimum length.', 'charitable-ambassadors' ),
					'class'             => 'short',
				),
				'campaign_length_max' => array(
					'title'             => __( 'Maximum Campaign Length', 'charitable-ambassadors' ),
					'type'              => 'number',
					'priority'          => 30,
					'required'          => false,
					'min'               => 0,
					'help'              => __( 'The maximum number of days that a user-submitted campaign can run for. Leave blank to allow users to choose any end date.', 'charitable-ambassadors' ),
					'class'             => 'short',
				),
				'allow_creators_donation_export' => array(
					'title'             => __( 'Allow Campaign Creators to Export Donation History', 'charitable-ambassadors' ),
					'type'              => 'radio',
					'priority'          => 32,
					'required'          => true,
					'options'           => array(
						'1'             => __( 'Yes', 'charitable-ambassadors' ),
						'0'             => __( 'No', 'charitable-ambassadors' ),
					),
					'default'           => '0',
					'help'              => __( 'Allow campaign creators to export a CSV file containing the donor name, email address and amount of all donations made to their campaign.', 'charitable-ambassadors' ),
				),
			);

			if ( class_exists( 'Charitable_Recurring' ) ) {
				$fields['allow_creators_to_create_recurring_campaigns'] = array(
					'title'				=> __( 'Allow Campaign Creators to Accept Recurring Donations for their Campaigns', 'charitable-ambassadors' ),
					'type'				=> 'radio',
					'priority'          => 34,
					'required'          => true,
					'options'           => array(
						'1'             => __( 'Yes', 'charitable-ambassadors' ),
						'0'             => __( 'No', 'charitable-ambassadors' ),
					),
					'default'           => '0',
					'help'              => __( 'Allow campaign creators to enable recurring donation options for their campaign.', 'charitable-ambassadors' ),
				);
			}

			/* Pre 1.4, the payout method field will always be visible, so we need a clarifying note. */
			if ( version_compare( charitable()->get_version(), '1.4.0', '<' ) ) {
				$fields['campaign_payout']['help'] = __( 'This setting is only applied when funds are raised for Personal Causes.', 'charitable-ambassadors' );
			}

			return $fields;
		}

		/**
		 * Add the campaign submission page settings to the General settings tab in Charitable.
		 *
		 * @param   array[] $fields
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_campaign_submission_page_settings( $fields ) {
			$new_fields = apply_filters( 'charitable_ambassadors_campaign_submission_page_setting', array(
				'campaign_submission_page'  => array(
					'title'     => __( 'Campaign Submission Page', 'charitable-ambassadors' ),
					'type'      => 'select',
					'priority'  => 38,
					'options'   => charitable_get_admin_settings()->get_pages(),
					'help'      => __( 'The static page should contain the <code>[charitable_submit_campaign]</code> shortcode.', 'charitable-ambassadors' ),
				),
				'campaign_submission_success_page'  => array(
					'title'     => __( 'Campaign Submission Success Page', 'charitable-ambassadors' ),
					'type'      => 'select',
					'priority'  => 39,
					'options'   => array(
						'home'  => __( 'Homepage', 'charitable-ambassadors' ),
						'pages' => array(
							'options' => charitable_get_admin_settings()->get_pages(),
							'label' => __( 'Choose a Static Page', 'charitable-ambassadors' ),
						),
					),
					'help'      => __( 'This is the page to which users are redirected after submitting a campaign.', 'charitable-ambassadors' ),
				),
			) );

			$fields = array_merge( $fields, $new_fields );

			return $fields;
		}

		/**
		 * Return an array of supported campaign causes.
		 *
		 * @return  string[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_campaign_recipient_types() {
			$types = array();

			foreach ( charitable_get_recipient_types() as $type => $details ) {
				$types[ $type ] = sprintf( '%s<span class="charitable-help">%s</span>', $details['admin_label'], $details['admin_description'] );
			}

			return $types;
		}

		/**
		 * Return the payout methods that are available to pay campaign creators.
		 *
		 * @return  string[]
		 * @access  public
		 * @since   1.1.0
		 */
		public function get_campaign_payout_methods() {
			return apply_filters( 'charitable_ambassadors_payout_methods', array(
				'manual' => array(
					'label' => __( 'Manual', 'charitable-ambassadors' ),
					'options' => array(
						'paypal' => apply_filters( 'charitable_gateway_paypal_name', __( 'PayPal', 'charitable-ambassadors' ) ),
						),
					),
				)
			);
		}
	}

endif;
