<?php
/**
 * Class responsible for defining the "personal cause" recipient type.
 *
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Personal_Cause
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Ambassadors_Personal_Cause' ) ) :

	/**
	 * Charitable_Ambassadors_Personal_Cause
	 *
	 * @since       1.0.0
	 */
	class Charitable_Ambassadors_Personal_Cause {

		/**
		 * Register the 'personal' campaign recipient type.
		 *
		 * @return  void
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function register() {
			$args = apply_filters( 'charitable_ambassadors_personal_recipient_type', array(
				'label'             => __( 'Your Cause', 'charitable-ambassadors' ),
				'description' 	    => __( 'You are raising money for a personal cause. You will receive the donations.', 'charitable-ambassadors' ),
				'admin_label' 		=> __( 'Personal Causes', 'charitable-ambassadors' ),
				'admin_description' => __( 'Campaign creators raise money for their own cause. You will send them the funds raised by their campaign.', 'charitable-ambassadors' ),
			) );

			charitable_register_recipient_type( 'personal', $args );
		}

		/**
		 * Add payment details fields to the campaign form.
		 *
		 * @param   array[] 							 $fields Fields in the payment section of the form.
		 * @param   Charitable_Ambassadors_Campaign_Form $form   Form object.
		 * @return  array[]
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function add_payment_details_fields( $fields, $form ) {
			if ( $form->get_recipient_type() != 'personal' ) {
				return $fields;
			}

			$payment_fields = self::get_payment_fields( $form );

			if ( count( $payment_fields ) ) {

				$fields['payment_fields'] = array(
					'legend'        => __( 'Payment Details', 'charitable-ambassadors' ),
					'type'          => 'fieldset',
					'fields'        => $payment_fields,
					'priority'      => 80,
					'page'          => 'campaign_details',
				);

			}

			return $fields;
		}

		/**
		 * Add the funding data to the campaign admin.
		 *
		 * @param   array[]             $data     		Default data to show.
		 * @param   Charitable_Campaign $campaign       Campaign object.
		 * @param   string 			    $recipient_type The type of recipient.
		 * @return  array[]
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function add_campaign_funding_data( $data, Charitable_Campaign $campaign, $recipient_type ) {
			if ( 'personal' != $recipient_type || 'paypal' != charitable_get_option( 'campaign_payout', 'paypal' ) ) {
				return $data;
			}

			$paypal = get_user_meta( $campaign->get_campaign_creator(), 'paypal', true );

			if ( empty( $paypal ) ) {
				$paypal = __( 'Not set', 'charitable-ambassadors' );
			}

			$extra_data = array(
				'paypal'    => array(
					'label' => __( 'PayPal Account', 'charitable-ambassadors' ),
					'value' => $paypal,
				),
			);

			$data = array_merge( $data, $extra_data );

			return $data;
		}

		/**
		 * Payment fields.
		 *
		 * @param   Charitable_Ambassadors_Campaign_Form $form
		 * @return  array[]
		 * @access  private
		 * @static
		 * @since   1.0.0
		 */
		private static function get_payment_fields( Charitable_Ambassadors_Campaign_Form $form ) {
			$payment_fields = array();
			$payout_option = charitable_get_option( 'campaign_payout', 'paypal' );

			if ( 'paypal' == $payout_option ) {

				$payment_fields['paypal'] = array(
					'label'         => __( 'Your PayPal Account', 'charitable-ambassadors' ),
					'description'   => __( 'When the campaign is finished, you will be paid with your PayPal account.', 'charitable-ambassadors' ),
					'type'          => 'email',
					'priority'      => 42,
					'value'         => $form->get_user_value( 'paypal' ),
					'data_type'     => 'user',
				);

			}

			$payment_fields = apply_filters( 'charitable_campaign_submission_payment_fields', $payment_fields, $form );

			uasort( $payment_fields, 'charitable_priority_sort' );

			return $payment_fields;
		}
	}

endif; // End class_exists check
