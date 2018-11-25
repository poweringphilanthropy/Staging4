<?php

function pp_get_stripe_percent_fee($campaign_id){
	$stripe_base_fee = 2.9;
	
	$parent_id = wp_get_post_parent_id( $campaign_id );
	$id = !empty($parent_id) ? $parent_id : $campaign_id;

	$dest = get_post_meta( $id, '_campaign_payout_options', true );
	if( ($dest == 'direct') && !empty( get_post_meta( $id, '_campaign_connected_stripe_id', true ) ) ){
		$stripe_base_fee = 2.2;
	}
	
	return apply_filters( 'pp_get_stripe_percent_fee', $stripe_base_fee, $campaign_id );
}

function pp_is_donor_cover_fee_enabled_for_campaign($campaign_id){
	$enabled = !empty( edd_get_option('donor_covers_fee') );
	if(!$enabled){
		// try from single campaign
		$enabled_on_campaign = get_post_meta( $campaign_id, '_campaign_donor_covers_fee', true );
		$enabled = !empty($enabled_on_campaign);
	}

	return apply_filters( 'pp_is_donor_cover_fee_enabled_for_campaign', $enabled, $campaign_id );
}

function pp_get_campaign_stripe_id($campaign_id){
	// default
	$stripe_id = edd_get_option('payout_stripe_account');

	// maybe has parent 
	$parent_id = wp_get_post_parent_id( $campaign_id );
	$id = !empty($parent_id) ? $parent_id : $campaign_id;

	$dest = get_post_meta( $id, '_campaign_payout_options', true );
	if( ($dest == 'direct') && !empty( get_post_meta( $id, '_campaign_connected_stripe_id', true ) ) ){
		$stripe_id = get_post_meta( $id, '_campaign_connected_stripe_id', true );
	}

	return $stripe_id;
}

function pp_get_platform_fee($campaign_id){
	$platform_fee = get_post_meta( $campaign_id, '_campaign_platform_fee', true );
	if(empty($platform_fee)){
		$platform_fee = charitable_get_option('platform_fee');
	}

	return apply_filters( 'pp_get_platform_fee', $platform_fee, $campaign_id );
}

function pp_edds_calculate_fee($percentage_fee, $amount){
	return ($percentage_fee / 100) * $amount;
}

function pp_edds_get_charge_details($charge_data){

	// echo "<pre>";
	// print_r($charge_data);
	// echo "</pre>";
	// exit();

	$donation = floatval($charge_data['donation-amount']);
	$_zero_decimal_donation = round( $donation * 100, 0 );

	$_stripe_percent_decimal = round( floatval($charge_data['stripe-percent'] / 100), 3 );
	$_platform_percent_decimal = round( floatval($charge_data['platform-percent'] / 100), 3 );

	$donor_covers_fee = isset($charge_data['donor-covers-fee']) && ($charge_data['donor-covers-fee'] == 'yes');

	$charge_info = array(
		'stripe-id' => $charge_data['stripe-id'],
		'campaign-benefited' => $charge_data['campaign-benefited'],
		'donation-amount' => $donation,
		'donor-covers-fee' => $donor_covers_fee ? 'yes' : 'no',
		'stripe-percent' => floatval($charge_data['stripe-percent']), 
		'platform-percent' => floatval($charge_data['platform-percent']), 
		'platform-fee-amount' => 0.00,
		'stripe-fee-amount' => 0.00,
		'total-fee-amount' => 0.00,
		'charge-to-donor' => 0.00,
		'gross-amount' => 0.00,
		'net-amount' => 0.00,
	);

	if($donation > 0){
		$_zero_decimal_charge_to_donor = $_zero_decimal_donation;
		if($donor_covers_fee){
    		$_zero_decimal_charge_to_donor = ($_zero_decimal_donation + 30) / ( 1 - $_stripe_percent_decimal - $_platform_percent_decimal);
    	}

    	$_raw_stripe_fee = ($_zero_decimal_charge_to_donor * $_stripe_percent_decimal) + 30;

    	$charge_to_donor = bcdiv($_zero_decimal_charge_to_donor	, 1, 0) / 100;
    	$stripe_fee = $_raw_stripe_fee  / 100;
    	$platform_fee =  $_zero_decimal_charge_to_donor * $_platform_percent_decimal 	/ 100;
    	$total_fee = bcdiv($stripe_fee + $platform_fee , 1 , 2);
    	$gross_amount = $donation + $total_fee;
 
    	$net_amount = $charge_to_donor - $total_fee;

		$charge_info['platform-fee-amount'] = $platform_fee;
    	$charge_info['charge-to-donor'] = $charge_to_donor;
    	$charge_info['total-fee-amount'] = $total_fee;
    	$charge_info['stripe-fee-amount'] = $stripe_fee;
    	$charge_info['gross-amount'] = $gross_amount;
    	$charge_info['net-amount'] = $net_amount;
	}

	return $charge_info;
}



function pp_get_donor_fees_on_checkout(){
	$charges = array();
	if(class_exists('PP_Team_Fundraising')){
	$_campaign_on_cart = PP_Team_Fundraising::pp_get_campaigns_on_cart();
	$campaign_ids = array_keys($_campaign_on_cart);
	} else {
		$cart      = new Charitable_EDD_Cart( edd_get_cart_contents(), edd_get_cart_fees( 'item' ) );
		$campaigns = $cart->get_benefits_by_campaign();
		$campaign_ids = array_keys($campaigns);
	}
	$campaign_id = current($campaign_ids);
			
			
	$enable_donor_covers_fee = pp_is_donor_cover_fee_enabled_for_campaign($campaign_id);
	$campaign_title = get_the_title( $campaign_id );
	$stripe_id = pp_get_campaign_stripe_id( $campaign_id );
	$platform_percent_fee = pp_get_platform_fee( $campaign_id );
	$stripe_percent_fee = pp_get_stripe_percent_fee( $campaign_id );
	$donation = floatval(edd_get_cart_total());
	$_zero_decimal_donation = round( $donation * 100, 0 );
	$_stripe_percent_decimal = round( floatval($stripe_percent_fee / 100), 3 );
	$_platform_percent_decimal = round( floatval($platform_percent_fee / 100), 3 );
	$donor_covers_fee = isset($enable_donor_covers_fee) && ($enable_donor_covers_fee == 'yes');
	$_zero_decimal_charge_to_donor = $_zero_decimal_donation;
	if($donor_covers_fee){
		$_zero_decimal_charge_to_donor = ($_zero_decimal_donation + 30) / ( 1 - $_stripe_percent_decimal - $_platform_percent_decimal);
	}
	$_raw_stripe_fee = ($_zero_decimal_charge_to_donor * $_stripe_percent_decimal) + 30;
	$charge_to_donor = round($_zero_decimal_charge_to_donor) / 100;
	$stripe_fee = round($_raw_stripe_fee) / 100;
	$platform_fee = round( $_zero_decimal_charge_to_donor * $_platform_percent_decimal ) / 100;
	$total_fee = $stripe_fee + $platform_fee;
	$gross_amount = $donation + $total_fee;
	$net_amount = $charge_to_donor - $total_fee;
	$charges['platform-fee-amount'] = $platform_fee;
	$charges['charge-to-donor'] = $charge_to_donor;
	$charges['total-fee-amount'] = $total_fee;
	$charges['stripe-fee-amount'] = $stripe_fee;
	$charges['gross-amount'] = $gross_amount;
	$charges['net-amount'] = $net_amount;
	$charges['donor-covers-fee'] = $donor_covers_fee;
	$charges['campaign_title'] = $campaign_title;
	$charges['platform_percent_fee'] = $platform_percent_fee;
	$charges['stripe_percent_fee'] = $stripe_percent_fee;
	return $charges;
}


/**
 * Retrieve the exsting cards setting.
 * @return bool
 */
function edd_stripe_existing_cards_enabled() {
	$use_existing_cards = edd_get_option( 'stripe_use_existing_cards', false );
	return ! empty( $use_existing_cards );
}

/**
 * Given a user ID, retrieve existing cards within stripe.
 *
 * @since 2.6
 * @param int $user_id
 *
 * @return array
 */
function edd_stripe_get_existing_cards( $user_id = 0 ) {
	if ( empty( $user_id ) ) {
		return array();
	}

	$enabled = edd_stripe_existing_cards_enabled();
	if ( ! $enabled ) {
		return array();
	}

	static $existing_cards;

	if ( ! is_null( $existing_cards ) && array_key_exists( $user_id, $existing_cards ) ) {
		return $existing_cards[ $user_id ];
	}

	// Check if the user has existing cards
	$customer_cards = array();
	$stripe_customer_id = edds_get_stripe_customer_id( $user_id );
	if ( ! empty( $stripe_customer_id ) ) {
		$secret_key      = edd_is_test_mode() ? trim( edd_get_option( 'test_secret_key' ) ) : trim( edd_get_option( 'live_secret_key' ) ) ;
		\Stripe\Stripe::setApiKey( $secret_key );
		try {
			$stripe_customer = \Stripe\Customer::retrieve( $stripe_customer_id );

			if ( isset( $stripe_customer->deleted ) && $stripe_customer->deleted ) {
				return array();
			}

			$customer_sources = $stripe_customer->sources->all( array( "object" => "card" ) );
			$default_source   = $stripe_customer->default_source;

			foreach ( $customer_sources->data as $source ) {
				$customer_cards[ $source->id ] = array(
					'source' => $source,
				);

				$customer_cards[ $source->id ][ 'default' ] = $source->id === $default_source ? true : false;
			}
		} catch ( Exception $e ) {
			return array();
		}
	}

	$existing_cards[ $user_id ] = $customer_cards;
	return $existing_cards[ $user_id ];
}

/**
 * Look up the stripe customer id in user meta, and look to recurring if not found yet
 *
 * @since  2.4.4
 * @since  2.6               Added lazy load for moving to customer meta and ability to look up by customer ID.
 * @param  int  $id_or_email The user ID, customer ID or email to look up.
 * @param  bool $by_user_id  If the lookup is by user ID or not.
 *
 * @return string       Stripe customer ID
 */
function edds_get_stripe_customer_id( $id_or_email, $by_user_id = true ) {
	$stripe_customer_id = '';
	$meta_key           = edd_stripe_get_customer_key();

	if ( is_email( $id_or_email ) ) {
		$by_user_id = false;
	}

	$customer = new EDD_Customer( $id_or_email, $by_user_id );
	if ( $customer->id > 0 ) {
		$stripe_customer_id = $customer->get_meta( $meta_key );
	}

	if ( empty( $stripe_customer_id ) ) {
		$user_id = 0;
		if ( ! empty( $customer->user_id ) ) {
			$user_id = $customer->user_id;
		} else if ( $by_user_id && is_numeric( $id_or_email ) ) {
			$user_id = $id_or_email;
		} else if ( is_email( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );
			if ( $user ) {
				$user_id = $user->ID;
			}
		}

		if ( ! isset( $user ) ) {
			$user = get_user_by( 'id', $user_id );
		}

		if ( $user ) {

			$customer = new EDD_Customer( $user->user_email );

			if ( ! empty( $user_id ) ) {
				$stripe_customer_id = get_user_meta( $user_id, $meta_key, true );

				// Lazy load migrating data over to the customer meta from Stripe issue #113
				$customer->update_meta( $meta_key, $stripe_customer_id );
			}

		}

	}

	if ( empty( $stripe_customer_id ) && class_exists( 'EDD_Recurring_Subscriber' ) ) {

		$subscriber   = new EDD_Recurring_Subscriber( $id_or_email, $by_user_id );

		if ( $subscriber->id > 0 ) {

			$verified = false;

			if ( ( $by_user_id && $id_or_email == $subscriber->user_id ) ) {
				// If the user ID given, matches that of the subscriber
				$verified = true;
			} else {
				// If the email used is the same as the primary email
				if ( $subscriber->email == $id_or_email ) {
					$verified = true;
				}

				// If the email is in the EDD 2.6 Additional Emails
				if ( property_exists( $subscriber, 'emails' ) && in_array( $id_or_email, $subscriber->emails ) ) {
					$verified = true;
				}
			}

			if ( $verified ) {
				$stripe_customer_id = $subscriber->get_recurring_customer_id( 'stripe' );
			}

		}

		if ( ! empty( $stripe_customer_id ) ) {
			$customer->update_meta( $meta_key, $stripe_customer_id );
		}

	}

	return $stripe_customer_id;
}

/**
 * Get the meta key for storing Stripe customer IDs in
 *
 * @access      public
 * @since       1.6.7
 * @return      string
 */
function edd_stripe_get_customer_key() {

	$key = '_edd_stripe_customer_id';
	if( edd_is_test_mode() ) {
		$key .= '_test';
	}
	return $key;
}

/**
 * Determines if the shop is using a zero-decimal currency
 *
 * @access      public
 * @since       1.8.4
 * @return      bool
 */
function edds_is_zero_decimal_currency() {

	$ret      = false;
	$currency = edd_get_currency();

	switch( $currency ) {

		case 'BIF' :
		case 'CLP' :
		case 'DJF' :
		case 'GNF' :
		case 'JPY' :
		case 'KMF' :
		case 'KRW' :
		case 'MGA' :
		case 'PYG' :
		case 'RWF' :
		case 'VND' :
		case 'VUV' :
		case 'XAF' :
		case 'XOF' :
		case 'XPF' :

			$ret = true;
			break;

	}

	return $ret;
}

/**
 * Custom callback for authorize button
 *
 * @return void
 */
function edd_authorize_button_callback( $args ) {

	// create auth url
    if ( edd_is_test_mode() ) {
        $secret_key = trim( edd_get_option( 'test_secret_key' ) );
        $client_id = trim( edd_get_option( 'test_client_id' ) );
    } else {
        $secret_key = trim( edd_get_option( 'live_secret_key' ) );
        $client_id = trim( edd_get_option( 'live_client_id' ) );
    }

    \Stripe\Stripe::setApiKey($secret_key);
	\Stripe\Stripe::setClientId($client_id);


	$edd_option = edd_get_option( $args['id'] );

	if($edd_option){
		
		$url = home_url( 'index.php?connect-listener=payout-account&deauth=' . $edd_option . '&state=edds-page' );

		$button = $edd_option . '(<a href="'.$url.'">'.__('Deauthorize', 'pp-tookkit').'</a>)';
	} else {
		$url = '#';
		// https://stripe.com/docs/connect/oauth-reference
        try {
        	$url = \Stripe\OAuth::authorizeUrl(array(
	            'scope' => 'read_write',
	            'stripe_landing' => 'login',
	            'state' => 'edds-page',
	            'redirect_uri' => home_url( 'index.php?connect-listener=payout-account' ),
        	));
        } catch (Exception $e) {
			echo '<p>' .$e->getMessage().'</p>';
        }

    	$button = '<a href="'.$url.'">'.__('Authorize', 'pp-tookkit').'</a>'; 
    }

	$class = edd_sanitize_html_class( $args['field_class'] );

	$html = '';
	$html .= '<input type="hidden" name="edd_settings[' . edd_sanitize_key( $args['id'] ) . ']" value="' . esc_attr( stripslashes( $edd_option ) ) . '" />';
	$html .= $button;
	$html .= '<p class="description">' . $args['desc'] . '</p>';

	echo apply_filters( 'edd_after_setting_output', $html, $args );
}