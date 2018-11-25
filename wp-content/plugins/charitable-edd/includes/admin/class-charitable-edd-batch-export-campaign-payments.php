<?php
/**
 * Class that is responsible for generating a CSV export of campaign-related EDD payments.
 *
 * @package     Charitable EDD/Classes/Charitable_EDD_Batch_Export_Campaign_Payments
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_EDD_Batch_Export_Campaign_Payments' ) ) :

	/**
	 * Charitable_EDD_Batch_Export_Campaign_Payments
	 *
	 * @since       1.0.0
	 */
	class Charitable_EDD_Batch_Export_Campaign_Payments extends EDD_Batch_Export {

		/**
		 * Our export type. Used for export-type specific filters/actions
		 *
		 * @var     string
		 * @since   1.0.0
		 */
		public $export_type = 'charitable_campaign_payments';

		/**
		 * Set the CSV columns
		 *
		 * @return  string[] $cols All the columns
		 * @access  public
		 * @since   1.0.0
		 */
		public function csv_cols() {
			$cols = array(
				'id'                => __( 'Payment ID', 'charitable-edd' ), // unaltered payment ID (use for querying)
				'seq_id'            => __( 'Payment Number', 'charitable-edd' ), // sequential payment ID
				'donation_id'       => __( 'Donation ID', 'charitable-edd' ),
				'email'             => __( 'Email', 'charitable-edd' ),
				'first'             => __( 'First Name', 'charitable-edd' ),
				'last'              => __( 'Last Name', 'charitable-edd' ),
				'address1'          => __( 'Address', 'charitable-edd' ),
				'address2'          => __( 'Address (Line 2)', 'charitable-edd' ),
				'city'              => __( 'City', 'charitable-edd' ),
				'state'             => __( 'State', 'charitable-edd' ),
				'country'           => __( 'Country', 'charitable-edd' ),
				'zip'               => __( 'Zip Code', 'charitable-edd' ),
				'campaign_id'       => __( 'Campaign ID', 'charitable-edd' ),
				'campaign_name'     => __( 'Campaign Name', 'charitable-edd' ),
				'campaign_amount'   => __( 'Campaign Donation Amount', 'charitable-edd' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
				'products'          => __( 'Products', 'charitable-edd' ),
				'quantity'          => __( 'Quantity', 'charitable-edd' ),
				'skus'              => __( 'SKUs', 'charitable-edd' ),
				'amount'            => __( 'Total Payment Amount', 'charitable-edd' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
				'tax'               => __( 'Tax', 'charitable-edd' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
				'discount'          => __( 'Discount Code', 'charitable-edd' ),
				'discount_amount'   => __( 'Total Discount', 'charitable-edd' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
				'gateway'           => __( 'Payment Method', 'charitable-edd' ),
				'trans_id'          => __( 'Transaction ID', 'charitable-edd' ),
				'key'               => __( 'Purchase Key', 'charitable-edd' ),
				'date'              => __( 'Date', 'charitable-edd' ),
				'user'              => __( 'User', 'charitable-edd' ),
				'status'            => __( 'Status', 'charitable-edd' ),
			);

			if ( ! edd_use_skus() ) {
				unset( $cols['skus'] );
			}
			if ( ! edd_get_option( 'enable_sequential' ) ) {
				unset( $cols['seq_id'] );
			}

			$data = apply_filters( 'edd_export_csv_cols_' . $this->export_type, $cols );

			return $cols;
		}

		/**
		 * Get the Export Data.
		 *
		 * @global  WPDB $wpdb
		 * @return  array[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_data() {
			global $wpdb;

			$data = array();

			$args = array(
				'number'   => 30,
				'page'     => $this->step,
				'status'   => $this->status,
			);

			if ( ! empty( $this->start ) || ! empty( $this->end ) ) {

				$args['date_query'] = array(
					array(
						'after'     => date( 'Y-m-d H:i:s', strtotime( $this->start ) ),
						'before'    => date( 'Y-m-d H:i:s', strtotime( $this->end ) ),
						'inclusive' => true,
					),
				);

			}

			if ( $this->campaign ) {
				$donation_ids = charitable_get_table( 'campaign_donations' )->get_donation_ids_for_campaign( $this->campaign );

				$args['meta_query'] = array(
					array(
						'key' => 'charitable_donation_from_edd_payment',
						'value' => $donation_ids,
						'compare' => 'IN',
					),
				);
			}

			$payments = edd_get_payments( $args );

			if ( ! $payments ) {
				return false;
			}

			foreach ( $payments as $payment ) {
				$payment_meta   = edd_get_payment_meta( $payment->ID );
				$user_info      = edd_get_payment_meta_user_info( $payment->ID );
				$total          = edd_get_payment_amount( $payment->ID );
				$shipping_info  = isset( $user_info['shipping_info'] ) ? $user_info['shipping_info'] : array();
				$user_id        = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];
				$products       = array();
				$donation_id    = get_post_meta( $payment->ID, 'charitable_donation_from_edd_payment', true );
				$discounts      = wp_list_pluck( edd_get_payment_meta_cart_details( $payment->ID ), 'discount' );
				$discount_amount = count( $discounts ) ? array_sum( $discounts ) : 0;

				if ( ! $donation_id ) {
					continue;
				}
				
				$edd_campaign_donations = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
				
				if ( ! $edd_campaign_donations ) {
					continue;
				}

				if ( is_numeric( $user_id ) ) {
					$user = get_userdata( $user_id );
				} else {
					$user = false;
				}
				
				foreach ( $edd_campaign_donations as $edd_campaign_donation ) {
					
					$campaign_id   = $edd_campaign_donation['campaign_id'];
					$campaign_name = get_the_title( $campaign_id );
					$quantity      = array_key_exists( 'quantity', $edd_campaign_donation ) ? $edd_campaign_donation['quantity'] : '';

					/* This was a straight donation, not a product purchase. */
					if ( $edd_campaign_donation['edd_fee'] ) {

						$product = sprintf( _x( '%s donation to %s', '$ donation to campaign', 'charitable-edd' ),
							html_entity_decode( charitable_format_money( $edd_campaign_donation['amount'] ) ),
							get_the_title( $edd_campaign_donation['campaign_id'] )
						);
						
					} else {
						
						if ( array_key_exists( 'price_id', $edd_campaign_donation ) ) {

							$product = sprintf( '%s - %s - %s',
								get_the_title( $edd_campaign_donation['download_id'] ),
								edd_get_price_option_name( $edd_campaign_donation['download_id'], $edd_campaign_donation['price_id'] ),
								edd_get_price_option_amount( $edd_campaign_donation['download_id'], $edd_campaign_donation['price_id'] )
							);

						} else {

							$product = sprintf( '%s - %s', 
								get_the_title( $edd_campaign_donation['download_id'] ),
								edd_get_download_price( $edd_campaign_donation['download_id'] )
							);
							
						}

						if ( edd_use_skus() ) {
							$sku = edd_get_download_sku( $edd_campaign_donation['download_id'] );
						} else {
							$sku = false;
						}
						
						$product = strip_tags( $product );

					}

					$data[] = array(
						'id'                => $payment->ID,
						'seq_id'            => edd_get_payment_number( $payment->ID ),
						'donation_id'       => $donation_id,
						'email'             => $payment_meta['email'],
						'first'             => isset( $user_info['first_name'] )            ? $user_info['first_name']          : '',
						'last'              => isset( $user_info['last_name'] )             ? $user_info['last_name']           : '',
						'address1'          => isset( $user_info['address']['line1'] )      ? $user_info['address']['line1']    : '',
						'address2'          => isset( $user_info['address']['line2'] )      ? $user_info['address']['line2']    : '',
						'city'              => isset( $user_info['address']['city'] )       ? $user_info['address']['city']     : '',
						'state'             => isset( $user_info['address']['state'] )      ? $user_info['address']['state']    : '',
						'country'           => isset( $user_info['address']['country'] )    ? $user_info['address']['country']  : '',
						'zip'               => isset( $user_info['address']['zip'] )        ? $user_info['address']['zip']      : '',
						'campaign_id'       => $campaign_id,
						'campaign_name'     => $campaign_name,
						'campaign_amount'   => $edd_campaign_donation['amount'],
						'products'          => $product,
						'quantity'          => $quantity,
						'skus'              => $sku === false ? '' : $sku,
						'amount'            => html_entity_decode( edd_format_amount( $total ) ),
						'tax'               => html_entity_decode( edd_format_amount( edd_get_payment_tax( $payment->ID, $payment_meta ) ) ),
						'discount'          => isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ? $user_info['discount'] : __( 'none', 'charitable-edd' ),
						'discount_amount'   => html_entity_decode( edd_format_amount( $discount_amount ) ),
						'gateway'           => edd_get_gateway_admin_label( get_post_meta( $payment->ID, '_edd_payment_gateway', true ) ),
						'trans_id'          => edd_get_payment_transaction_id( $payment->ID ),
						'key'               => $payment_meta['key'],
						'date'              => $payment->post_date,
						'user'              => $user ? $user->display_name : __( 'guest', 'charitable-edd' ),
						'status'            => edd_get_payment_status( $payment, true ),
					);

				}

				$data = apply_filters( 'edd_export_get_data', $data );
				$data = apply_filters( 'edd_export_get_data_' . $this->export_type, $data );
			}

			return $data;
		}

		/**
		 * Return the calculated completion percentage.
		 *
		 * @return  int
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_percentage_complete() {

			$status = $this->status;
			$args   = array(
				'start-date' => date( 'n/d/Y', strtotime( $this->start ) ),
				'end-date'   => date( 'n/d/Y', strtotime( $this->end ) ),
			);

			if ( 'any' == $status ) {

				$total = array_sum( (array) edd_count_payments( $args ) );

			} else {

				$total = edd_count_payments( $args )->$status;

			}

			$percentage = 100;

			if ( $total > 0 ) {
				$percentage = ( ( 30 * $this->step ) / $total ) * 100;
			}

			if ( $percentage > 100 ) {
				$percentage = 100;
			}

			return $percentage;
		}

		/**
		 * Set the properties specific to the payments export
		 *
		 * @param   mixed[] $request  The Form Data passed into the batch processing
		 * @access  public
		 * @since   1.0.0
		 */
		public function set_properties( $request ) {
			$this->start  = isset( $request['start'] )  ? sanitize_text_field( $request['start'] )  : '';
			$this->end    = isset( $request['end'] )   ? sanitize_text_field( $request['end'] )   : '';
			$this->status = isset( $request['status'] ) ? sanitize_text_field( $request['status'] ) : 'complete';
			$this->campaign = isset( $request['campaign'] ) ? intval( $request['campaign'] ) : 0;
		}
	}

endif; // End class_exists check
