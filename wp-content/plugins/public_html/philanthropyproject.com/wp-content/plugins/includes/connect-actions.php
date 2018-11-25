<?php  

/**
 * Listen for Stripe events, primarily recurring payments
 *
 * @access      public
 * @since       1.5
 * @return      void
 */

function edds_stripe_connect_listener() {

	if(!isset( $_GET['connect-listener'] )){
		return;
	}

    if ( edd_is_test_mode() ) {
        $secret_key = trim( edd_get_option( 'test_secret_key' ) );
        $client_id = trim( edd_get_option( 'test_client_id' ) );
    } else {
        $secret_key = trim( edd_get_option( 'live_secret_key' ) );
        $client_id = trim( edd_get_option( 'live_client_id' ) );
    }

    \Stripe\Stripe::setApiKey($secret_key);
	\Stripe\Stripe::setClientId($client_id);

	if ( $_GET['connect-listener'] == 'payout-account' ) {

		if (isset($_GET['code'])) {
            // The user was redirected back from the OAuth form with an authorization code.
            $code = $_GET['code'];
            try {
                $resp = \Stripe\OAuth::token(array(
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                ));
            } catch (\Stripe\Error\OAuth\OAuthBase $e) {
                wp_die( $e->getMessage() );
            }


            $stripe_id = $resp->stripe_user_id;
        	edd_update_option('payout_stripe_account', $stripe_id );

            $state = isset($_GET['state']) ? $_GET['state'] : '';
           	if( $state == 'edds-page' ){
            	wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=edd-stripe' ) );
            	exit();
            }

        } elseif (isset($_GET['deauth'])) {

            // Deauthorization request
         //    $accountId = $_GET['deauth'];

         //    try {
	        //     \Stripe\OAuth::deauthorize(array(
	        //         'stripe_user_id' => $accountId,
	        //         'redirect_uri' => 'http://dev.funkmo/g4g/?debug',
	        //     ));
	        // } catch (\Stripe\Error\OAuth\OAuthBase $e) {
	        //     exit("Error: " . $e->getMessage());
	        // }

       		edd_update_option('payout_stripe_account', '' );
	        if( $de && ($state == 'edds-page') ){
            	wp_redirect( admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=edd-stripe' ) );
            	exit();
            }

            wp_die( '<p>Success! Account <code>$accountId</code> is disonnected.</p>' );
        } elseif (isset($_GET['error'])) {
            // The user was redirect back from the OAuth form with an error.
            $error = $_GET['error'];
            $error_description = $_GET['error_description'];

            wp_die( $error_description, 'Error: ' . $error );

        } 

	} elseif($_GET['connect-listener'] == 'webhook'){

		if ( method_exists( '\Stripe\Stripe', 'setAppInfo' ) ) {
			\Stripe\Stripe::setAppInfo( 'Easy Digital Downloads - Stripe', EDD_STRIPE_VERSION, esc_url( site_url() ) );
		}

		// retrieve the request's body and parse it as JSON
		$body = @file_get_contents( 'php://input' );
		$event = json_decode( $body );


		status_header( 200 );
		
		do_action( 'connect_listener_webhook', $event );

		die( '1' ); // Completed successfully
	}
}
add_action( 'init', 'edds_stripe_connect_listener' );

function add_charge_data(){

	$payment_mode = edd_get_chosen_gateway();

	if(!class_exists('Charitable_EDD_Cart')){
		return;
	}

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
	
	$payout_type =  get_post_meta($campaign_id , '_campaign_payout_options',true);
	$stripe_id =  get_post_meta($campaign_id , '_campaign_connected_stripe_id',true);
	if($payout_type == "direct"){
		$orgs = pp_get_connected_organizations();
		$campaign_title = "<b>".$orgs[$stripe_id]->name."</b>";
	}else{
		$campaign_title = get_the_title( $campaign_id );
	}
					

	$stripe_id = pp_get_campaign_stripe_id( $campaign_id );
	$platform_percent_fee = pp_get_platform_fee( $campaign_id );
	$stripe_percent_fee = pp_get_stripe_percent_fee( $campaign_id );

	?>

	<p class="hidden" style="display:none;">
		<input type="text" id="stripe-id" name="charge_data[stripe-id]" value="<?php echo $stripe_id; ?>">
		<input type="text" id="campaign-benefited" name="charge_data[campaign-benefited]" value="<?php echo $campaign_id; ?>">
		<input type="text" id="donation-amount" name="charge_data[donation-amount]" value="<?php echo edd_get_cart_total(); ?>">
		<input type="text" id="stripe-percent" name="charge_data[stripe-percent]" value="<?php echo $stripe_percent_fee; ?>">
		<input type="text" id="platform-percent" name="charge_data[platform-percent]" value="<?php echo $platform_percent_fee; ?>">
		<input type="text" id="update_donor_value" name="charge_data[donor-covers-fee]" value="yes">

		<!-- <input type="text" id="covered-platform-fee" name="covered-platform-fee">
		<input type="text" id="covered-stripe-fee" name="covered-stripe-fee">
		<input type="text" id="covered-fee" name="covered-fee"> -->
	</p>
	<?php
}
add_action( 'edd_purchase_form_top', 'add_charge_data' );

function edds_add_connected_stripe_amount($purchase_data, $valid_data){

	if(!isset($purchase_data['post_data']['charge_data'])){
		return $purchase_data;
	}	

	$purchase_data['charge_details'] = pp_edds_get_charge_details( $purchase_data['post_data']['charge_data'] );

	

	// DEBUG
	// echo implode(', ', $campaign_benefited);
	return $purchase_data; 
}
add_filter( 'edd_purchase_data_before_gateway', 'edds_add_connected_stripe_amount', 10, 2 );


function clear_other_cart_contents($campaign_id){

	/**
	 * Remove other downloads
	 */
	$cart = EDD()->cart->get_contents();
	if(!empty($cart) && is_array($cart)):
	$changed = false;
	foreach ($cart as $key => $data) {
		$cart_campaign_id = isset($data['options']) && isset($data['options']['campaign_id']) ? $data['options']['campaign_id'] : 0;

		// remove other campaign id downloads
		$should_remove = $cart_campaign_id != $campaign_id;
		if($should_remove){
			unset($cart[$key]);
			if(!$changed){
				$changed = true;
			}
		}
	}

	if($changed){
		EDD()->session->set( 'edd_cart', $cart );
	}
	endif;

	/**
	 * Remove other donations
	 * @var [type]
	 */
	$fees = EDD()->fees->get_fees( 'all' );

	if(!empty($fees) && is_array($fees)):
	$changed = false;
	foreach ($fees as $key => $data) {
		$fee_campaign_id = isset($data['campaign_id']) ? $data['campaign_id'] : 0;

		// remove other campaign id donations
		$should_remove = $fee_campaign_id != $campaign_id;
		if($should_remove){
			unset($fees[$key]);
			if(!$changed){
				$changed = true;
			}
		}
	}

	if($changed){
		EDD()->session->set( 'edd_cart_fees', $fees );
	}
	endif;
}

function force_single_benefited_campaign_on_edd_post_add_fee($fees, $key, $args){
	if(!isset($args['campaign_id']))
		return;

	clear_other_cart_contents( $args['campaign_id'] );
}
add_action( 'edd_post_add_fee', 'force_single_benefited_campaign_on_edd_post_add_fee', 10, 3 );

function force_single_benefited_campaign_on_post_add_to_cart($download_id, $options, $items){
	if(!isset($options['campaign_id']))
		return;

	clear_other_cart_contents( $options['campaign_id'] );
}
add_action( 'edd_post_add_to_cart', 'force_single_benefited_campaign_on_post_add_to_cart', 10, 3 );