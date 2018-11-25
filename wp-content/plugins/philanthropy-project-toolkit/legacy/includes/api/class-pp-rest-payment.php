<?php
/**
 * REST API: PP_Rest_Payment class
 *
 * @package PP_Toolkit
 * @subpackage REST_API
 * @since 1.0
 */

/**
 * Core class used to purchase / checkout via the REST API.
 *
 * @since 1.0
 *
 * @see WP_REST_Controller
 */
class PP_Rest_Payment extends WP_REST_Controller {

	/**
	 * Constructor.
	 * @access public
	 */
	public function __construct() {

        $this->namespace = 'philanthropy';
        $this->rest_base = 'payment';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 * @access public
	 *
	 * @see register_rest_route()
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_item' ),
				'permission_callback' => array( $this, 'create_item_permissions_check' ),
				'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Checks if a given request has access create payment.
	 * @access public
	 * 
	 * @return true|WP_Error True if the request has access to create payment, WP_Error object otherwise.
	 */
	public function create_item_permissions_check( $request ) {

		// if ( ! is_user_logged_in() ) {
		// 	return new WP_Error( 'rest_cannot_create_payment', __( 'Sorry, you are not allowed to create payment.' ), array( 'status' => rest_authorization_required_code() ) );
		// }

		// if ( ! current_user_can( 'create_users' ) ) {
		// 	return new WP_Error( 'rest_cannot_create_user', __( 'Sorry, you are not allowed to create new users.' ), array( 'status' => rest_authorization_required_code() ) );
		// }

		return true;
	}

	/**
	 * Create payment by for a campaign.
	 * @access public
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function create_item( $request ) {

		try {
			$purchase_data = $this->build_purchase_data($request);

			$payment = $this->process_stripe_payment( $purchase_data, $request );

			if ( is_wp_error( $payment ) ) {
				throw new Exception( $payment->get_error_message() );
			}

			$response = array(
				'status' => 'success',
				'payment_id' => $payment->ID,
				'total' => $payment->total
			);


			$request->set_param( 'context', 'edit' );

			// $response = $this->prepare_item_for_response( $user, $request );
			$response = rest_ensure_response( $response );

			$response->set_status( 201 );
			// $response->header( 'Location', rest_url( sprintf( '%s/%s/%d', $this->namespace, $this->rest_base, $payment_id ) ) );

			return $response;

		} catch (Exception $e) {
			return new WP_Error( 'failed_to_process_payment', $e->getMessage() , array( 'status' => 400 ) );
		}
	}

	private function build_purchase_data($request){

		if(!isset($request['campaign_id'])){
			throw new Exception("Empty campaign ID");
		}
		
		$campaign_id = absint( $request['campaign_id'] );
		$email = (isset($request['email'])) ? $request['email'] : null;
		$purchase_key     = strtolower( md5( $email . date( 'Y-m-d H:i:s' ) . uniqid( 'edd', true ) ) );

		$purchase_data = array(
			'price' => (float) $request['donation_amount'],
			'date' => date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ),
			'user_email' => $email,
			'purchase_key' => $purchase_key,
			'currency' => edd_get_currency(),
			'downloads' => array(),
			'cart_details' => array(),
			'user_info' => array(
				'id' => 0,
				'email' => $email,
				'first_name' => (isset($request['first_name'])) ? $request['first_name'] : '',
				'last_name' => (isset($request['last_name'])) ? $request['last_name'] : '',
				'discount' => 'none',
				'address' => (isset($request['address'])) ? $request['address'] : '',
			),
			// 'status' => 'publish',
		);


		// TODO | Loop
		if(isset($request['merchandise']) && !empty($request['merchandise'])){
			$i = 0;
			foreach ($request['merchandise'] as $m) {

				$download_id = (isset($m['id'])) ? $m['id'] : 0;
				if(empty($download_id)){
					throw new Exception("Invalid download id");
				}

				$benefactors = charitable_get_table( 'edd_benefactors' )->get_benefactors_for_download( $download_id );
				if(empty($benefactors))
					continue; // skip non related download id

				$benefactor_campaign_ids = wp_list_pluck( $benefactors, 'campaign_id' );
				if(!in_array($campaign_id, $benefactor_campaign_ids)){
					// throw new Exception("Merchandise not related to campaign");
					continue;
				}

				$quantity = (isset($m['quantity'])) ? $m['quantity'] : 1;
				$price_id = (isset($m['price_id'])) ? $m['price_id'] : 0;

				$price_options = array();

				if( ! edd_has_variable_prices( $download_id ) ) {
					$price = edd_get_download_price( $download_id );
				} else {

					$prices = edd_get_variable_prices( $download_id );

					// Make sure a valid price ID was supplied
					if( ! isset( $prices[ $price_id ] ) ) {
						wp_die( __( 'The requested price ID does not exist.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 404 ) );
					}

					$price_options = array(
						'price_id' => $price_id,
						'amount'   => $prices[ $price_id ]['amount']
					);
					$price  = $prices[ $price_id ]['amount'];
				}


				$purchase_data['downloads'][$i] = array(
					'id' => $download_id,
					'options' => $price_options,
					'quantity' => $quantity
				);

				$purchase_data['cart_details'][$i] = array(
					'name'        => get_the_title( $download_id ),
					'id'          => $download_id,
					'item_number' => array(
						'id'      => $download_id,
						'options' => $price_options
					),
					'tax'         => 0,
					'discount'    => 0,
					'item_price'  => $price,
					'subtotal'    => ( $price * $quantity ),
					'price'       => ( $price * $quantity ),
					'quantity'    => $quantity,
				);

				$purchase_data['price'] += (float) $price; // update to total

			$i++;
			}
		}

		/**
		 * Generate charge details
		 */
		$stripe_id = pp_get_campaign_stripe_id( $campaign_id );
		$platform_percent_fee = pp_get_platform_fee( $campaign_id );
		$stripe_percent_fee = pp_get_stripe_percent_fee( $campaign_id );

		$_charge_details = array(
			'donor-covers-fee' => 'no', // hardcoded for now
			'stripe-id' => $stripe_id,
			'campaign-benefited' => $campaign_id,
			'donation-amount' => $purchase_data['price'],
			'stripe-percent' => $stripe_percent_fee,
			'platform-percent' => $platform_percent_fee,
		);

		$purchase_data['charge_details'] = pp_edds_get_charge_details($_charge_details);

		return $purchase_data;
	}

	private function process_stripe_payment($purchase_data, $request){
		global $edd_options;

		if ( edd_is_test_mode() ) {
			$secret_key = trim( edd_get_option( 'test_secret_key' ) );
		} else {
			$secret_key = trim( edd_get_option( 'live_secret_key' ) );
		}

		if ( empty( $request['stripe_token'] ) ) {
			throw new Exception( __( 'Missing Stripe Token.' ) );
		}

		$card_data = $request['stripe_token'];

		try {

			if(!isset($purchase_data['charge_details'])){
				throw new Exception("Empty charge details data.");
			}

			$charge_details = $purchase_data['charge_details'];

			/**
			 * FOR G4G we use site name - campaign benefited
			 */
			$purchase_summary = edd_get_option('purchase_summary_prefix');
			if(isset($charge_details['campaign-benefited']) && !empty($charge_details['campaign-benefited']) ){
				$purchase_summary .= ' - ' . get_the_title( $charge_details['campaign-benefited'] );
			}

			if(!isset($charge_details['stripe-id']) || empty($charge_details['stripe-id']) ){
				throw new Exception("Empty stripe ID");
			}

			if( !isset($charge_details['charge-to-donor']) ){
				throw new Exception("Empty charge to donor");
			}

			if( !isset($charge_details['platform-fee-amount']) ){
				throw new Exception("Empty platform fee");
			}

			$purchase_amount = $charge_details['charge-to-donor'];
			$platform_fee = $charge_details['platform-fee-amount'];

			$opts = array('stripe_account' => $charge_details['stripe-id'] );

			\Stripe\Stripe::setApiKey( $secret_key );

			if ( method_exists( '\Stripe\Stripe', 'setAppInfo' ) ) {
				\Stripe\Stripe::setAppInfo( 'Easy Digital Downloads - Stripe', EDD_STRIPE_VERSION, esc_url( site_url() ) );
			}

			// setup the payment details
			$payment_data = array(
				'price'        => $purchase_data['price'], // or use purchase_amount?
				'date'         => $purchase_data['date'],
				'user_email'   => $purchase_data['user_email'],
				'purchase_key' => $purchase_data['purchase_key'],
				'currency'     => edd_get_currency(),
				'downloads'    => $purchase_data['downloads'],
				'cart_details' => $purchase_data['cart_details'],
				'user_info'    => $purchase_data['user_info'],
				'status'       => (isset( $purchase_data['status'] )) ? $purchase_data['status'] : 'pending',
				'gateway'      => (isset( $purchase_data['gateway'] )) ? $purchase_data['gateway'] : 'rest-api'
			);

			$customer_id = edds_get_stripe_customer_id( get_current_user_id() );

			if ( empty( $customer_id ) ) {

				// No customer ID found, let's look one up based on the email
				$customer_id = edds_get_stripe_customer_id( $purchase_data['user_email'], false );

			}

			if ( ! empty( $customer_id ) ) {

				$customer_exists = true;

				try {

					// Retrieve the customer to ensure the customer has not been deleted
					$cu = \Stripe\Customer::retrieve( $customer_id, $opts );

					if( isset( $cu->deleted ) && $cu->deleted ) {

						// This customer was deleted
						$customer_exists = false;

					}

				// No customer found
				} catch ( Exception $e ) {

					$customer_exists = false;

				}

			}

			if ( ! $customer_exists ) {

				// Create a customer first so we can retrieve them later for future payments
				$cu = \Stripe\Customer::create( apply_filters( 'edds_create_customer_args', array(
					'description' => $purchase_data['user_email'],
					'email'       => $purchase_data['user_email'],
				), $payment_data ), $opts );

				$customer_id = is_array( $cu ) ? $cu['id'] : $cu->id;

				$customer_exists = true;

			}

			$existing_card = false;
			$preapprove_only = edd_get_option( 'stripe_preapprove_only' );

			if ( $customer_exists ) {

				if ( is_array( $card_data ) ) {
					$card_data['object'] = 'card';
				}

				$card    = $cu->sources->create( array( 'source' => $card_data ) );
				$card_id = $card->id;

				// Process a normal one-time charge purchase
				if( ! $preapprove_only ) {

					if( edds_is_zero_decimal_currency() ) {

						$amount = $purchase_amount;

					} else {

						// Round to the nearest integer, see GitHub issue #270
						$amount = round( $purchase_amount * 100, 0 );
						$platform_fee = round( $platform_fee * 100, 0 );

					}

					$statement_descriptor = edd_get_option( 'stripe_statement_descriptor', '' );
					if ( empty( $statement_descriptor ) ) {
						$statement_descriptor = substr( $purchase_summary, 0, 22 );
					}
					$statement_descriptor = apply_filters( 'edds_statement_descriptor', $statement_descriptor, $purchase_data );

					$unsupported_characters = array( '<', '>', '"', '\'' );
					$statement_descriptor   = str_replace( $unsupported_characters, '', $statement_descriptor );

					$args = array(
						'amount'      => $amount,
						'currency'    => edd_get_currency(),
						'customer'    => $customer_id,
						'source'      => $card_id,
						'description' => html_entity_decode( $purchase_summary, ENT_COMPAT, 'UTF-8' ),
						'metadata'    => array(
							'email'   => $purchase_data['user_info']['email']
						)
					);


					// statement description appear on the customer’s account statement
					if( ! empty( $statement_descriptor ) ) {
						$args[ 'statement_descriptor' ] = $statement_descriptor;
					}

					if( ! empty( $platform_fee ) ) {
						$args[ 'application_fee' ] = $platform_fee;
					}

					if( $multiple_transfer ) {
						$args[ 'transfer_group' ] = $purchase_data['purchase_key'];
					}

					// echo "<pre>";
					// print_r(apply_filters( 'pp_edds_create_charge_args', $args, $purchase_data ));
					// echo "</pre>";
					// exit();

					$charge = \Stripe\Charge::create( apply_filters( 'pp_edds_create_charge_args', $args, $purchase_data ), $opts );
				}

				// attach donation to process on edd_insert_payment
				if(isset($request['campaign_id']) && isset($request['donation_amount'])){
					Charitable_EDD_Cart::add_donation_fee_to_cart( $request['campaign_id'], $request['donation_amount'] );
				}

				// record the pending payment
				$payment = edd_insert_payment( $payment_data );

				$edd_customer = new EDD_Customer( $purchase_data['user_email'] );
				if ( $edd_customer->id > 0 ) {
					$edd_customer->update_meta( edd_stripe_get_customer_key(), $customer_id );
				}

			} else {

				throw new Exception( __( 'Customer Creation Failed.' ) );
				
			}

			if ( $payment && ( ! empty( $customer_id ) || ! empty( $charge ) ) ) {

				$payment = new EDD_Payment( $payment );

				if(isset($purchase_data['charge_details']) && !empty($purchase_data['charge_details'])){
					$payment->update_meta( 'charge_details', $purchase_data['charge_details'] );
				}

				if ( $preapprove_only ) {
					$payment->status = 'preapproval';
					$payment->update_meta( '_edds_stripe_customer_id', $customer_id );
				} else {
					$payment->status = 'publish';
				}

				if ( $existing_card ) {
					$payment->update_meta( '_edds_used_existing_card', true );
				}

				// You should be using Stripe's API here to retrieve the invoice then confirming it's been paid
				if ( ! empty( $charge ) ) {

					$payment->add_note( 'Stripe Charge ID: ' . $charge->id );
					$payment->transaction_id = $charge->id;

				} elseif ( ! empty( $customer_id ) ) {

					$payment->add_note( 'Stripe Customer ID: ' . $customer_id );

				}

				$payment->save();

				edd_empty_cart();

			} else {

				throw new Exception( __( 'Your payment could not be recorded.' ) );
				
			}

	 	} catch ( \Stripe\Error\Card $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			edd_record_gateway_error( __( 'Stripe Error', 'edds' ), sprintf( __( 'There was an error while processing a Stripe payment. Payment data: %s', ' edds' ), json_encode( $err ) ), 0 );

			if( isset( $err['message'] ) ) {
				return new WP_Error( 'payment_error', $err['message'] );
			} else {
				return new WP_Error( 'payment_error', __( 'There was an error processing your payment, please ensure you have entered your card number correctly.', 'edds' ) );
			}


		} catch ( \Stripe\Error\ApiConnection $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			
			edd_record_gateway_error( __( 'Stripe Error', 'edds' ), sprintf( __( 'There was an error processing your payment (Stripe\'s API was down). Error: %s', 'edds' ), json_encode( $err['message'] ) ), 0 );

			return new WP_Error( 'payment_error', __( 'There was an error processing your payment (Stripe\'s API is down), please try again', 'edds' ) );

		} catch ( \Stripe\Error\InvalidRequest $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			// Bad Request of some sort. Maybe Christoff was here ;)
			if( isset( $err['message'] ) ) {
				return new WP_Error( 'request_error', $err['message'] );
			} else {
				return new WP_Error( 'request_error', __( 'The Stripe API request was invalid, please try again', 'edds' ) );
			}

		} catch ( \Stripe\Error\Api $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			if( isset( $err['message'] ) ) {
				return new WP_Error( 'request_error', $err['message'] );
			} else {
				return new WP_Error( 'request_error', __( 'The Stripe API request was invalid, please try again', 'edds' ) );
			}

		} catch ( \Stripe\Error\Authentication $e ) {

			$body = $e->getJsonBody();
			$err  = $body['error'];

			// Authentication error. Stripe keys in settings are bad.
			if( isset( $err['message'] ) ) {
				return new WP_Error( 'request_error', $err['message'] );
			} else {
				return new WP_Error( 'api_error', __( 'The API keys entered in settings are incorrect', 'edds' ) );
			}

		} catch ( Exception $e ) {
			// some sort of other error
			$body = $e->getJsonBody();
			$err  = $body['error'];
			if( isset( $err['message'] ) ) {
				return new WP_Error( 'payment_error', $err['message'] );
			} else {
				return new WP_Error( 'api_error', __( 'Something went wrong.', 'edds' ) );
			}

		}

		return $payment;
	}

	/**
	 * Retrieves the payment's schema, conforming to JSON Schema.
	 * @access public
	 *
	 * @return array Item schema data.
	 */
	public function get_item_schema() {
		$schema = array(
			'$schema'    => 'http://json-schema.org/schema#',
			'title'      => 'payment',
			'type'       => 'object',
			'properties' => array(
				'stripe_token'     => array(
					'description' => __( 'Stripe Token' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'required'    => true,
				),
				'campaign_id'     => array(
					'description' => __( 'Campaign ID' ),
					'type'        => 'integer',
					'context'     => array( 'edit' ),
					'required'    => true,
				),
				'donation_amount'     => array(
					'description' => __( 'Donation amount' ),
					'type'        => 'integer',
					'context'     => array( 'edit' ),
					'required'    => true,
				),
				'email'       => array(
					'description' => __( 'The email address for the user.' ),
					'type'        => 'string',
					'format'      => 'email',
					'context'     => array( 'edit' ),
					'required'    => true,
				),
				'first_name'  => array(
					'description' => __( 'First name for the user.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				'last_name'   => array(
					'description' => __( 'Last name for the user.' ),
					'type'        => 'string',
					'context'     => array( 'edit' ),
					'arg_options' => array(
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
				// 'merchandise'           => array(
				// 	'description' => __( 'Purchased merchandise.' ),
				// 	'type'        => 'array',
					// 'items'       => array(
					// 	'type'    => 'string',
					// ),
					// 'context'     => array( 'edit' ),
				// ),
			),
		);

		return $this->add_additional_fields_schema( $schema );
	}
}
