<?php
/**
 * Class responsible for displaying the amount to be donated on the EDD checkout form.
 *
 * @package		Charitable EDD/Classes/Charitable_EDD_Checkout
 * @version 	1.0.0
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_EDD_Checkout' ) ) :

	/**
	 * Charitable_EDD_Checkout
	 *
	 * @since 		1.0.0
	 */
	class Charitable_EDD_Checkout {

		/**
		 * @var     Charitable_EDD_Checkout
		 * @access  private
		 * @static
		 * @since   1.0.0
		 */
		private static $instance = null;

		/**
		 * Create class object.
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
				self::$instance = new Charitable_EDD_Checkout();
			}

			return self::$instance;
		}

		/**
		 * When the donation form is submitted, redirect the donor to the checkout.
		 *
		 * @param 	Charitable_Donation_Processor $processor
		 * @param   Charitable_Donation_Form_Interface $form
		 * @return  boolean
		 * @access  public
		 * @since   1.0.0
		 */
		public function donation_redirect_to_checkout( Charitable_Donation_Processor $processor, Charitable_Donation_Form_Interface $form ) {

			if ( ! $form->validate_submission() ) {
				return false;
			}

			/**
			 * Compare the cart total against the donation amount field. Any
			 * excess in the donation amount field is added to the checkout
			 * as a fee.
			 */
			$cart_total = $this->add_downloads_to_cart();
			$amount = Charitable_Donation_Form::get_donation_amount();
			$campaign_id = $processor->get_campaign()->ID;

			if ( $amount ) {
				Charitable_EDD_Cart::add_donation_fee_to_cart( $campaign_id, $amount );
			}

			/* Redirect to the checkout. */
			wp_redirect( edd_get_checkout_uri() );
			edd_die();
		}

		/**
		 * Add downloads to cart and return cart subtotal.
		 *
		 * @return  float
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_downloads_to_cart() {
			$cart_total = 0;

			if ( isset( $_POST['downloads'] ) && ! empty( $_POST['downloads'] ) ) {

				foreach ( $_POST['downloads'] as $download_id => $download_options ) {

					$quantity = isset( $download_options['quantity'] ) ? $download_options['quantity'] : 1;
					$options  = array(
						'quantity' => $quantity,
					);
					
					$line_item_amount = 0;

					if ( isset( $download_options['price_id'] ) ) {

						$options['price_id'] = $download_options['price_id'];

						if ( is_array( $options['price_id'] ) ) {
							
							$options['quantity'] = is_array( $quantity ) ? $quantity : array();

							foreach ( $options['price_id'] as $key => $price_id ) {

								$line_item_amount += $quantity * edd_get_price_option_amount( $download_id, $price_id );
								
								if ( ! is_array( $quantity ) ) {
									
									if ( ! is_array( $options['quantity'] ) ) {
										$options['quantity'] = array();
									}

									$options['quantity'][ $key ] = $quantity;	
								}

							}
						} else {

							$line_item_amount += $quantity * edd_get_price_option_amount( $download_id, $options['price_id'] );
						}
					} else {

						$line_item_amount += $quantity * edd_get_download_price( $download_id );

					}

					edd_add_to_cart( $download_id, $options );

					$cart_total += $line_item_amount;

				}
			}

			return $cart_total;
		}

		/**
		 * Display a donation notice.
		 *
		 * @return 	void
		 * @access  public
		 * @since 	1.0.0
		 */
		public function donation_amount_notice_checkout() {
			charitable_edd_template( 'checkout-notice.php' );
		}

		/**
		 * Send updated benefit amount when item quantities are updated in EDD.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function ajax_send_benefit_amount() {
			$cart = new Charitable_EDD_Cart( edd_get_cart_contents(), edd_get_cart_fees( 'item' ) );

			$return = array(
				'benefit' => charitable_format_money( strval( $cart->get_total_benefit_amount() ) ),
			);

			echo json_encode( $return );

			wp_die();
		}

		/**
		 * Add donation "fee" to purchase summary that is sent to gateways.
		 *
		 * @param 	string 	$summary
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_donations_to_purchase_summary( $summary ) {
			$fees = edd_get_cart_fees( 'item' );

			foreach ( $fees as $fee ) {
				if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) ) {
					continue;
				}

				$prepend = strlen( $summary ) ? ', ' : '';

				$summary .= sprintf( '%s%s %s', $prepend, charitable_get_currency_helper()->get_monetary_amount( $fee['amount'] ), $fee['label'] );
			}

			substr( $summary, 0, -2 );

			return $summary;
		}

		/**
		 * Add donation "fees" to cart quantity count.
		 *
		 * @param 	int 	$quantity
		 * @return  string
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_donations_to_cart_quantity( $quantity ) {
			if ( ! apply_filters( 'charitable_edd_add_donations_to_cart_quantity', true ) ) {
				return $quantity;
			}

			$fees = edd_get_cart_fees( 'item' );

			foreach ( $fees as $fee ) {
				if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) ) {
					continue;
				}

				$quantity += 1;
			}

			return $quantity;
		}

		/**
		 * Add donations to subtotal.
		 *
		 * @param 	float 	$subtotal
		 * @param 	int 	$payment_id
		 * @return  float
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_donations_to_cart_subtotal( $subtotal ) {
			return Charitable_EDD_Cart::add_donation_fees_to_subtotal( $subtotal, edd_get_cart_fees( 'item' ) );
		}

		/**
		 * Remove donations from total. These are already factored into the subtotal.
		 *
		 * @param 	float 	$total
		 * @return  float
		 * @access  public
		 * @since   1.0.0
		 */
		public function remove_donations_from_cart_total( $total ) {
			return Charitable_EDD_Cart::remove_donation_fees_from_total( $total, edd_get_cart_fees( 'item' ) );
		}
	}

endif; // End class_exists check
