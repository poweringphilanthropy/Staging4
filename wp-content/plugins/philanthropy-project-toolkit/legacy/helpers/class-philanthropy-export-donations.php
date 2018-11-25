<?php
/**
 * Class that is responsible for generating a CSV export of donations.
 *
 * @package     Charitable/Classes/Philanthropy_Export_Donations
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2015, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

/* Exit if accessed directly */
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Philanthropy_Export_Donations' ) ) :

	/* Include Charitable_Export base class. */
	if ( ! class_exists( 'Charitable_Export' ) ) {
		require_once( charitable()->get_path( 'admin' ) . 'reports/abstract-class-charitable-export.php' );
	}

	/**
	 * Philanthropy_Export_Donations
	 *
	 * @since       1.0.0
	 */
	class Philanthropy_Export_Donations extends Charitable_Export {

		/**
		 * @var     string  The type of export.
		 */
		const EXPORT_TYPE = 'donations';

		/**
		 * @var     mixed[] Array of default arguments.
		 * @access  protected
		 */
		protected $defaults = array(
			'start_date'    => '',
			'end_date'      => '',
			'campaign_id'   => 'all',
			'status'        => 'all',
		);

		/**
		 * @var     string[] List of donation statuses.
		 * @access  protected
		 */
		protected $statuses;

		/**
		 * Create class object.
		 *
		 * @param   mixed[] $args
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( $args ) {
			$this->statuses = charitable_get_valid_donation_statuses();

			add_filter( 'charitable_export_data_key_value', array( $this, 'set_custom_field_data' ), 10, 3 );

			parent::__construct( $args );
		}

		/**
		 * Filter the date and time fields.
		 *
		 * @param   mixed   $value
		 * @param   string  $key
		 * @param   array   $data
		 * @return  mixed
		 * @access  public
		 * @since   1.0.0
		 */
		public function set_custom_field_data( $value, $key, $data ) {
			switch ( $key ) {
				case 'date' :
					if ( isset( $data['post_date'] ) ) {
						$value = mysql2date( 'l, F j, Y', $data['post_date'] );
					}
					break;

				case 'time' :
					if ( isset( $data['post_date'] ) ) {
						$value = mysql2date( 'H:i A', $data['post_date'] );
					}
					break;

				case 'status' :
					if ( isset( $data['post_status'] ) ) {
						$value = $this->statuses[ $data['post_status'] ];
					}
					break;

				case 'address' :
					$value = charitable_get_donation( $data['donation_id'] )->get_donor()->get_donor_meta( 'address' );
					break;

				case 'address_2' :
					$value = charitable_get_donation( $data['donation_id'] )->get_donor()->get_donor_meta( 'address_2' );
					break;

				case 'city' :
					$value = charitable_get_donation( $data['donation_id'] )->get_donor()->get_donor_meta( 'city' );
					break;

				case 'state' :
					$value = charitable_get_donation( $data['donation_id'] )->get_donor()->get_donor_meta( 'state' );
					break;

				case 'postcode' :
					$value = charitable_get_donation( $data['donation_id'] )->get_donor()->get_donor_meta( 'postcode' );
					break;

				case 'country':
					$value = charitable_get_donation( $data['donation_id'] )->get_donor()->get_donor_meta( 'country' );
					break;

				case 'phone' :
					$value = charitable_get_donation( $data['donation_id'] )->get_donor()->get_donor_meta( 'phone' );
					break;

				case 'address_formatted':
					$value = str_replace( '<br/>', PHP_EOL, charitable_get_donation( $data['donation_id'] )->get_donor_address() );
					break;

				case 'donation_gateway' :
					$gateway = charitable_get_donation( $data['donation_id'] )->get_gateway_object();
					$value   = is_a( $gateway, 'Charitable_Gateway' ) ? $gateway->get_name() : '';
					break;

				// case 'payment_type' :
				// 	$value   = (isset($data['edd_gateway']) && ($data['edd_gateway'] == 'rest-api')) ? 'Mobile Payment' : 'Web Payment';
				// 	break;
			}

			return $value;
		}

		/**
		 * Return the CSV column headers.
		 *
		 * The columns are set as a key=>label array, where the key is used to retrieve the data for that column.
		 *
		 * @return  string[]
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function get_csv_columns() {
			$columns = array(
				'donation_id'       => __( 'Donation ID', 'charitable' ),
				'campaign_id'       => __( 'Campaign ID', 'charitable' ),
				'campaign_name'     => __( 'Campaign Title', 'charitable' ),
				'first_name'        => __( 'Donor First Name', 'charitable' ),
				'last_name'         => __( 'Donor Last Name', 'charitable' ),
				'email'             => __( 'Email', 'charitable' ),
				'address'           => __( 'Address', 'charitable' ),
				'address_2'         => __( 'Address 2', 'charitable' ),
				'city'			    => __( 'City', 'charitable' ),
				'state'			    => __( 'State', 'charitable' ),
				'postcode'		    => __( 'Postcode', 'charitable' ),
				'country' 		    => __( 'Country', 'charitable' ),
				'phone'             => __( 'Phone Number', 'charitable' ),
				// 'address_formatted' => __( 'Address Formatted', 'charitable' ),
				'address_formatted' => __( 'Full Name', 'charitable' ), // fix https://trello.com/c/awngrzSc/24-when-you-click-on-export-list-of-donations-merchandise-and-tickets-purchased-right-next-to-edit-campaign-on-the-your-campaigns-t
				'amount'            => __( 'Donation Amount', 'charitable' ),
				'date'              => __( 'Date of Donation', 'charitable' ),
				'time'              => __( 'Time of Donation', 'charitable' ),
				'status'            => __( 'Donation Status', 'charitable' ),
				// 'payment_type'      => __( 'Payment Type', 'charitable' ),
				// 'donation_gateway'  => __( 'Donation Gateway', 'charitable' ),
			);

			$columns = apply_filters( 'charitable_export_donations_columns', $columns, $this->args );
			
			// if(isset($columns['donor_address'])){
			// 	unset($columns['donor_address']);
			// }
			
			if(isset($columns['address']))
				unset($columns['address']);
			
			if(isset($columns['address_2']))
				unset($columns['address_2']);
			
			if(isset($columns['city']))
				unset($columns['city']);
			
			if(isset($columns['state']))
				unset($columns['state']);
			
			if(isset($columns['postcode']))
				unset($columns['postcode']);
			
			if(isset($columns['country']))
				unset($columns['country']);


			return $columns;
		}

		/**
		 * Get the data to be exported.
		 *
		 * @return  array
		 * @access  protected
		 * @since   1.0.0
		 */
		// protected function get_data() {
		// 	$query_args = array();

		// 	if ( strlen( $this->args['start_date'] ) ) {
		// 		$query_args['start_date'] = date( 'Y-m-d 00:00:00', strtotime( $this->args['start_date'] ) );
		// 	}

		// 	if ( strlen( $this->args['end_date'] ) ) {
		// 		$query_args['end_date'] = date( 'Y-m-d 00:00:00', strtotime( $this->args['end_date'] ) );
		// 	}

		// 	if ( 'all' != $this->args['campaign_id'] ) {
		// 		$query_args['campaign_id'] = $this->args['campaign_id'];
		// 	}

		// 	if ( 'all' != $this->args['status'] ) {
		// 		$query_args['status'] = $this->args['status'];
		// 	}

		// 	/** @deprecated filter name with misspelling */
		// 	$query_args = apply_filters( 'chairtable_export_donations_query_args', $query_args, $this->args );
		// 	$query_args = apply_filters( 'charitable_export_donations_query_args', $query_args, $this->args );

		// 	return charitable_get_table( 'campaign_donations' )->get_donations_report( $query_args );
		// }

		/**
		 * Custom get data from edd payment
		 * @return [type] [description]
		 */
		protected function get_data() {

			// debug
			$dw_id = 19741;
			$cek_dws = array();

			$campaign_id = false;
			$data = array();

			$query_args = array('number'   => -1); // all data

			if ( strlen( $this->args['start_date'] ) ) {
				$query_args['start_date'] = date( 'Y-m-d 00:00:00', strtotime( $this->args['start_date'] ) );
			}

			if ( strlen( $this->args['end_date'] ) ) {
				$query_args['end_date'] = date( 'Y-m-d 00:00:00', strtotime( $this->args['end_date'] ) );
			}

			if( ('all' != $this->args['campaign_id']) && is_array($this->args['campaign_id'])){
				foreach ($this->args['campaign_id'] as $campaign_id) {
					$donation_ids = charitable_get_table( 'campaign_donations' )->get_donation_ids_for_campaign( $campaign_id );
					$query_args['meta_query'] = array(
						array(
							'key' => 'charitable_donation_from_edd_payment',
							'value' => $donation_ids,
							'compare' => 'IN',
						),
					);

					$extension = 'charitable-edd';
					$benefactors = charitable_get_table( 'benefactors' )->get_campaign_benefactors_by_extension($campaign_id, $extension );
					$campaign_benefactor_downloads = (!empty($benefactors)) ? wp_list_pluck( $benefactors, 'edd_download_id' ) : array();

					$payments = edd_get_payments( $query_args );

					if ( ! $payments ) {
						continue;
					}

					$payment_data = $this->get_payment_data($campaign_id, $payments, $campaign_benefactor_downloads);
					$data = array_merge($data, $payment_data);
				}
			} else {
				$campaign_id = absint( $this->args['campaign_id'] );
				$donation_ids = charitable_get_table( 'campaign_donations' )->get_donation_ids_for_campaign( $campaign_id );
				$query_args['meta_query'] = array(
					array(
						'key' => 'charitable_donation_from_edd_payment',
						'value' => $donation_ids,
						'compare' => 'IN',
					),
				);

				$extension = 'charitable-edd';
				$benefactors = charitable_get_table( 'benefactors' )->get_campaign_benefactors_by_extension($campaign_id, $extension );
				$campaign_benefactor_downloads = (!empty($benefactors)) ? wp_list_pluck( $benefactors, 'edd_download_id' ) : array();

				$payments = edd_get_payments( $query_args );
				if ( ! $payments ) {
					return false;
				}

				$payment_data = $this->get_payment_data($campaign_id, $payments, $campaign_benefactor_downloads);
				$data = array_merge($data, $payment_data);
			}

			

			// echo array_sum($cek_dws[$dw_id]);
			// echo "<pre>";
			// print_r($cek_dws);
			// echo "</pre>";

		 //    exit();

			return $data;
		}

		public function get_payment_data($campaign_id, $payments, $campaign_benefactor_downloads){

			$data = array();

			foreach ($payments as $payment) {

				$edd_payment    = new EDD_Payment( $payment->ID );
				
				$downloads      = edd_get_payment_meta_cart_details( $payment->ID );
				$fees           = edd_get_payment_fees( $payment->ID, 'item' );
				$user_info      = edd_get_payment_meta_user_info( $payment->ID );
				$payment_meta   = edd_get_payment_meta( $payment->ID );

				$donation_id    = get_post_meta( $payment->ID, 'charitable_donation_from_edd_payment', true );
				if ( ! $donation_id ) {
					continue;
				}
				$donation       = charitable_get_donation( $donation_id );
				$donor = $donation->get_donor();

				/**
				 * only diplay shipping info
				 * https://trello.com/c/awngrzSc/24-when-you-click-on-export-list-of-donations-merchandise-and-tickets-purchased-right-next-to-edit-campaign-on-the-your-campaigns-t
				 */
				if ( ! isset( $user_info[ 'shipping_info' ] ) ) {
	                // $address = $donor->get_address();
	                $address = '';
	            } else {
	                $shipping_info = $user_info[ 'shipping_info' ];

	                $address_fields = array(
	                    'first_name'    => $donor->get( 'first_name' ),
	                    'last_name'     => $donor->get( 'last_name' ),
	                    'company'       => $donor->get( 'donor_company' ),
	                    'address'       => $shipping_info[ 'address' ],
	                    'address_2'     => $shipping_info[ 'address2' ],
	                    'city'          => $shipping_info[ 'city' ],
	                    'state'         => $shipping_info[ 'state' ],
	                    'postcode'      => $shipping_info[ 'zip' ],
	                    'country'       => $shipping_info[ 'country' ]
	                );

	                $address = charitable_get_location_helper()->get_formatted_address( $address_fields );
	            }    

	            $title = str_replace(array('&#8217;', '&#8217;' ), "'", get_the_title( $campaign_id )); 
	            // $title = esc_sql( get_the_title( $campaign_id ) );

				$export_data = array(
					'donation_id' => $donation_id,
					'campaign_id' => $campaign_id,
					'campaign_name' => $title,
					'email' => $payment_meta['email'],
					'first_name' => isset( $user_info['first_name'] )            ? $user_info['first_name']          : '',
					'last_name' => isset( $user_info['last_name'] )            ? $user_info['last_name']          : '',
					'post_date' => $donation->post_date,
					'post_content' => $donation->post_content,
					'post_status' => $donation->post_status,
					'amount' => '',
					'donor_address' => str_replace( '<br/>', PHP_EOL, $address ),
					'purchase_details' => '',
					'purchase_qty' => '-', // default
					'shipping' => 0, // default
					'chapter' => $payment_meta['chapter'],
					'referral' => pp_get_referrer_name($payment_meta['referral'], false),
					'edd_gateway' => $edd_payment->gateway,
				);

				if ( $fees ) {
					foreach ( $fees as $key => $fee ) {
						if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) ) {
							continue;
						}

						if(!empty($campaign_id) && ($campaign_id != $fee['campaign_id']))
							continue;

						$donation_data = array(
							'amount' => edd_format_amount($fee['amount']),
							'purchase_details' => sprintf(__('Donation for %s', 'pp-toolkit'), $title ),
						);

						/**
						 * Display as Mobile donation if donation coming from rest api
						 * @var [type]
						 */
						if($edd_payment->gateway == 'rest-api'){
							$donation_data['purchase_details'] = sprintf(__('Mobile Donation for %s', 'pp-toolkit'), $title );
						}

						$data[] = array_merge($export_data, $donation_data);
					}
				}

				if ( $downloads ) {
					foreach ( $downloads as $key => $download ) {
						// Download ID
						$id = isset( $payment_meta['cart_details'] ) ? $download['id'] : $download;

						if(!empty($campaign_id) && !in_array($id, $campaign_benefactor_downloads))
							continue;

						$download_description = get_the_title( $id );

						if ( isset( $download['item_number'] ) && isset( $download['item_number']['options'] ) ) {
							$price_options = $download['item_number']['options'];
							$price_id   = isset( $download['item_number']['options']['price_id'] )     ? $download['item_number']['options']['price_id'] : null;
							if ( edd_has_variable_prices( $id ) && isset( $price_id ) ) {
								$download_description .= ' - ' . edd_get_price_option_name( $id, $price_id, $payment->ID );
							}
						}

                        $shiping_cost = 0;
                        if($download['fees']):
                        foreach ( $download[ 'fees' ] as $fee_id => $fee ) {
                            if ( false === strpos( $fee_id, 'simple_shipping' ) ) {
                                continue;
                            }

                            $shiping_cost = (isset($fee['amount'])) ? $fee['amount'] : 0;
                            break;
                        }
                        endif;

						$download_data = array(
							'amount' => edd_format_amount($download['subtotal']),
							'purchase_details' => $download_description,
							'purchase_qty' => $download['quantity'],
							'shipping' => $shiping_cost,
							'options' => isset($download['item_number']['options']) ? $download['item_number']['options'] : array(), // maybe used later
						);

						$data[] = array_merge($export_data, $download_data);

						// debug
						$cek_dws[$id][] = $download[ 'quantity' ];
					}
				}

				// $data[] = $edd_download->get_connected_downloads();

			}

			return $data;
		}

		/**
		 * Print the CSV document headers.
		 *
		 * @return  void
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function print_headers() {
			ignore_user_abort( true );

			if ( ! charitable_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
				set_time_limit( 0 );
			}

			/* Check for PHP 5.3+ */
			if ( function_exists( 'get_called_class' ) ) {
				$class  = get_called_class();
				$export = $class::EXPORT_TYPE;
			} else {
				$export = '';
			}

			nocache_headers();
			header( "Content-Type: text/csv; charset=utf-8" );
			// header( "Content-Disposition: attachment; filename=Campaign Export - " . date( 'm-d-Y' ) . ".csv" );
			
			// fix filename and extension issue on firefox
			$filename = "Campaign Export - " . date( 'm-d-Y' );
			header( "Content-Disposition: attachment; filename=\"$filename.csv\";" );
			header( "Expires: 0" );
		}

		/**
		 * Export the CSV file.
		 *
		 * @return  void
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function export() {

			$data = array_map( array( $this, 'map_data' ), $this->get_data() );

			// echo "<pre>";
			// print_r($this->get_data());
			// echo "</pre>";
			// exit();

			$this->print_headers();

			/* Create a file pointer connected to the output stream */
			$output = fopen( 'php://output', 'w' );

			/* Print first row headers. */
			fputcsv( $output, array_values( $this->columns ) );

			/* Print the data */
			foreach ( $data as $row ) {
				fputcsv( $output, $row );
			}

			fclose( $output );

			exit();
		}
	}

endif; // End class_exists check
