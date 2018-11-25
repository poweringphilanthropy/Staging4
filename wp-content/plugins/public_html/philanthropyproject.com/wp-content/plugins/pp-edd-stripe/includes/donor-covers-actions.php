<?php
/**
 * Add additional meta fields to be saved when updating a campaign.
 */
function add_donor_covers_fee_meta_keys( $keys ) {
	array_push( $keys,
		'_campaign_donor_covers_fee'
	);

	return $keys;
}
add_filter( 'charitable_campaign_meta_keys', 'add_donor_covers_fee_meta_keys' );

function add_metabox_donor_covers_fee($fields){

	if(!empty( edd_get_option('donor_covers_fee') )){
		return $fields;
	}

	$fields['section-edd-separator'] = array(
		'view'              => 'metaboxes/field-types/hr',
		'priority'          => 15,
	);

	$fields['pp-donor-covers-fee'] = array(
		'view'              => 'metaboxes/field-types/checkbox',
		'priority'          => 15.1,
		'label'             => __( 'Enable donor covers fee', 'charitable-edd' ),
		'meta_key'          => '_campaign_donor_covers_fee',
		'default'           => 0,
	);

	return $fields;
}

add_filter( 'charitable_campaign_donation_options_fields', 'add_metabox_donor_covers_fee' );


function load_donor_covers_fee_js(){
	wp_register_script( 'pp-donor-covers-fee-js', EDDSTRIPE_PLUGIN_URL . 'assets/js/pp-donor-covers-fee.js', array( 'jquery' ), EDD_STRIPE_VERSION, true );

	if ( edd_is_checkout() ) {
		wp_enqueue_script( 'pp-donor-covers-fee-js' );

		$vars = array();

		wp_localize_script( 'pp-donor-covers-fee-js', 'DONOR_COVERS_FEE_VARS', $vars );
	}
}
add_action( 'wp_enqueue_scripts', 'load_donor_covers_fee_js', 100 );


/**
 * Validates the supplied discount sent via AJAX.
 *
 * @since 1.0
 * @return void
 */
add_action( 'wp_ajax_edd_update_quantity_custom', 'edd_ajax_update_cart_item_quantity_custom' );
add_action( 'wp_ajax_nopriv_edd_update_quantity_custom', 'edd_ajax_update_cart_item_quantity_custom' );

add_action( 'wp_ajax_edd_refresh_cart_based_on_donor_select', 'edd_refresh_cart_based_on_donor_select' );
add_action( 'wp_ajax_nopriv_edd_refresh_cart_based_on_donor_select', 'edd_refresh_cart_based_on_donor_select' );

function edd_ajax_update_cart_item_quantity_custom() {
	if ( ! empty( $_POST['quantity'] ) && ! empty( $_POST['download_id'] ) ) {

		$download_id = absint( $_POST['download_id'] );
		$quantity    = absint( $_POST['quantity'] );
		$options     = json_decode( stripslashes( $_POST['options'] ), true );

		EDD()->cart->set_item_quantity( $download_id, $quantity, $options );

		$return = array(
			'download_id' => $download_id,
			'quantity'    => EDD()->cart->get_item_quantity( $download_id, $options ),
			'subtotal'    => html_entity_decode( edd_currency_filter( edd_format_amount( EDD()->cart->get_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'taxes'       => html_entity_decode( edd_currency_filter( edd_format_amount( EDD()->cart->get_tax() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'       => html_entity_decode( edd_currency_filter( edd_format_amount( EDD()->cart->get_total() ) ), ENT_COMPAT, 'UTF-8' ),
			'is_donor'       => false
		);

		// Allow for custom cart item quantity handling
			$charges = pp_get_donor_fees_on_checkout();
			if($charges['donor-covers-fee'] ): 
			$currency_helper = charitable_get_currency_helper();
			$return['total'] = html_entity_decode( $currency_helper->get_monetary_amount( $charges['charge-to-donor']), ENT_COMPAT, 'UTF-8' );
			$return['total_fees'] =  html_entity_decode( $currency_helper->get_monetary_amount( $charges['total-fee-amount']), ENT_COMPAT, 'UTF-8' );
			$return['is_donor'] = true;
			endif;
		}
		echo json_encode($return);
	edd_die();
}

function edd_refresh_cart_based_on_donor_select(){
		 $return = array(
			// 'download_id' => $download_id,
			// 'quantity'    => EDD()->cart->get_item_quantity( $download_id, $options ),
			'subtotal'    => html_entity_decode( edd_currency_filter( edd_format_amount( EDD()->cart->get_subtotal() ) ), ENT_COMPAT, 'UTF-8' ),
			'taxes'       => html_entity_decode( edd_currency_filter( edd_format_amount( EDD()->cart->get_tax() ) ), ENT_COMPAT, 'UTF-8' ),
			'total'       => html_entity_decode( edd_currency_filter( edd_format_amount( EDD()->cart->get_total() ) ), ENT_COMPAT, 'UTF-8' ),
			'is_donor'       => false
		);
		if($_POST['donor_selection'] == "true"){
			$charges = pp_get_donor_fees_on_checkout();
			if($charges['donor-covers-fee'] ): 
			$currency_helper = charitable_get_currency_helper();
			$return['total'] = html_entity_decode( $currency_helper->get_monetary_amount( $charges['charge-to-donor']), ENT_COMPAT, 'UTF-8' );
			$return['total_fees'] =  html_entity_decode( $currency_helper->get_monetary_amount( $charges['total-fee-amount']), ENT_COMPAT, 'UTF-8' );
			$return['is_donor'] = true;
			endif;
		}
		

		echo json_encode($return); 
	edd_die();
}