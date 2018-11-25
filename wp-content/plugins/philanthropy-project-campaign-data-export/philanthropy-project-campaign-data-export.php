<?php
/**
 * Plugin Name:         Philanthropy Project - Campaign Data Export
 * Plugin URI:          https://philanthropyproject.com
 * Description:         Adds additional data to the campaign data export. 
 * Version:             1.1.0
 * Author:              WP Charitable
 * Author URI:          https://www.wpcharitable.com
 * Requires at least:   4.1
 * Tested up to:        4.3.1
 *
 * Text Domain:         ppcde
 * Domain Path:         /languages/
 *
 * @package             Philanthropy Project Campaign Data Export
 * @category            Core
 * @author              Studio164a
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Load plugin functionality, but only if Charitable and Charitable_WePay_Payout are found and activated.
 *
 * @return  void
 * @since   1.0.0
 */
function ppcde_load() {    
    /* Check for Charitable */
    if ( ! class_exists( 'Charitable' ) ) {

        if ( ! class_exists( 'Charitable_Extension_Activation' ) ) {

            require_once 'includes/class-charitable-extension-activation.php';

        }

        $activation = new Charitable_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
        $activation = $activation->run();

        return;
    } 

    add_action( 'charitable_start', 'ppcde_start' );
}

add_action( 'plugins_loaded', 'ppcde_load', 1 );

/**
 * Bootstrap the plugin functionality.
 *
 * @return  void
 * @since   0.1.0
 */
function ppcde_start() {
    add_filter( 'charitable_export_donations_columns', 'ppcde_add_export_columns' );
    add_filter( 'charitable_export_data_key_value', 'ppcde_add_export_data', 10, 3 );
    add_filter( 'edd_export_get_data_charitable_campaign_payments', 'ppcde_filter_campaign_payments_data' );
    add_filter( 'edd_export_csv_cols_charitable_campaign_payments', 'ppcde_filter_campaign_payments_cols' );
}

/** 
 * Add additional columns to the data export. 
 *
 * @param   array $columns
 * @return  array
 * @since   0.1.0
 */
function ppcde_add_export_columns( $columns ) {
    $columns[ 'donor_address' ] = __( 'Donor Address', 'ppcde' );
    $columns[ 'purchase_details' ] = __( 'Donation Details', 'ppcde' );
    $columns[ 'purchase_qty' ] = __( 'Quantity', 'ppcde' ); // add qty
    $columns[ 'shipping' ] = __( 'Shipping Fee', 'ppcde' );
    $columns['chapter'] = 'Chapter Affiliation';
    $columns['referral'] = 'Referral Source';
    return $columns;
}

/** 
 * Add data for the new fields to the data export. 
 *
 * @param   mixed   $value
 * @param   string  $key
 * @param   array   $data
 * @return  mixed
 * @since   0.1.0
 */
function ppcde_add_export_data( $value, $key, $data ) {
    static $matched_items = array();
    static $row_has_shipping = false;
    static $row_item_qty = false;

    switch ( $key ) {
        case 'donor_address' : 

            $donation = charitable_get_donation( $data[ 'donation_id' ] );


            $donor = $donation->get_donor();

            $payment_id = Charitable_EDD_Payment::get_payment_for_donation( $data[ 'donation_id' ] );

            $user_info = edd_get_payment_meta_user_info( $payment_id );

            if ( ! isset( $user_info[ 'shipping_info' ] ) ) {
                $address = $donor->get_address();
            }
            else {
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

            $value = str_replace( '<br/>', PHP_EOL, $address );
            
            break;

        case 'purchase_details' :
            $payment_id = Charitable_EDD_Payment::get_payment_for_donation( $data[ 'donation_id' ] );
            $donation_log = get_post_meta( $data[ 'donation_id' ], 'donation_from_edd_payment_log', true );
            $found = false;

            foreach ( $donation_log as $idx => $campaign_donation ) {
                /* If we have already listed this donation ID, skip */
                if ( isset( $matched_items[ $data[ 'donation_id' ] ][ $idx ] ) ) {
                    continue;
                }

                /* If the campaign_id in the donation log entry does not match the current campaign donation record, skip */
                if ( $campaign_donation[ 'campaign_id' ] != $data[ 'campaign_id' ] ) {
                    continue;
                }

                /* If the amount does not match, skip */
                if ( $campaign_donation[ 'amount' ] != $data[ 'amount' ] ) {
                    continue;
                }

                /* At this point, we know it matches. Check if it's a fee */
                if ( $campaign_donation[ 'edd_fee' ] ) {

                    $value = __( 'Donation', 'ppcde' );
                    $row_item_qty = '-';

                }
                /* If not, work through the purchased downloads and find the matching one. */
                else {
                    foreach ( edd_get_payment_meta_cart_details( $payment_id ) as $download ) {

                        /* The download ID must match */
                        if ( $download[ 'id' ] != $campaign_donation[ 'download_id' ] ) {
                            continue;
                        }

                        /* The amount for this particular download must also match */
                        /**
                         * Commented out, since some downloads doesn't use 100% downloads price as donation
                         */
                        // if ( $download[ 'subtotal' ] != $data[ 'amount' ] ) {
                        //     continue;
                        // }
                    
                        $download_description = $download[ 'name' ];

                        if ( isset( $download[ 'item_number' ][ 'options' ][ 'price_id' ] ) ) {
                            $variation = edd_get_price_option_name( $download[ 'id' ], $download[ 'item_number' ][ 'options' ][ 'price_id' ] );
                            if(!empty($variation)){
                                $download_description .= ' - ' . $variation;
                            }
                        }

                        /* If we get here, we have a match. */
                        $row_item_qty = $download[ 'quantity' ];
                        // $value = sprintf( '%s x %d', strip_tags( $download_description ), $download[ 'quantity' ] );
                        $value = $download_description;
                        $matched_items[ $data[ 'donation_id' ] ][ $idx ] = $value;
                        
                        if(!empty( $download[ 'fees' ] )):
                        foreach ( $download[ 'fees' ] as $fee_id => $fee ) {
                            if ( false === strpos( $fee_id, 'simple_shipping' ) ) {
                                continue;
                            }

                            /* This row has shipping, so store the $fee in our static variable */
                            $row_has_shipping = $fee;
                            break;
                        }
                        endif;

                        // break, since we have a match
                        $found = true;
                        break;
                    }
                }

                // break loop, if found
                if($found){
                    break;
                }
                
            }

            // $value = array(
            //     'value' => $value,
            //     'matched' => $matched_items,
            //     'cart' => edd_get_payment_meta_cart_details( $payment_id )
            // );

            break;

        case 'purchase_qty' :
            if ( ! $row_item_qty ) {
                $value = '-';
            }

            $value = $row_item_qty;

            /* Reset to false */
            $row_item_qty = false;

            // $donation_log = get_post_meta( $data[ 'donation_id' ], 'donation_from_edd_payment_log', true );
            // $value = $donation_log;
            break;

        case 'shipping' : 

            /* If this row is showing a product purchase and the product purchase had shipping fees, 
               $row_has_shipping will be an array */
            if ( ! $row_has_shipping ) {
                $value = '0';
            }

            $value = isset( $row_has_shipping[ 'amount' ] ) ? $row_has_shipping[ 'amount' ] : '0';

            /* Reset to false */
            $row_has_shipping = false;

            break;

        case 'chapter':

            $payment_id = Charitable_EDD_Payment::get_payment_for_donation($data['donation_id']);
            $meta = edd_get_payment_meta($payment_id);
            $value = $meta['chapter'];

            break;

        case 'referral':

            $payment_id = Charitable_EDD_Payment::get_payment_for_donation($data['donation_id']);
            $meta = edd_get_payment_meta($payment_id);
            $value = $meta['referral'];

            break;
    }   
    
    return $value; 
}

/**
 * Filter the data exported through the EDD Campaign Payments batch export. 
 *
 * @param   array[] $data
 * @return  array[]
 * @since   1.1.0
 */
function ppcde_filter_campaign_payments_data( $data ) {    
    foreach( $data as $idx => $row ) {

        unset( 
            $data[ $idx ][ 'address1' ],
            $data[ $idx ][ 'address2' ],
            $data[ $idx ][ 'city' ],
            $data[ $idx ][ 'state' ],
            $data[ $idx ][ 'country' ],
            $data[ $idx ][ 'zip' ]
        );

        $payment_id = $row[ 'id' ];
        $user_info = edd_get_payment_meta_user_info( $payment_id );
        $shipping_info = isset( $user_info[ 'shipping_info' ] ) ? $user_info[ 'shipping_info' ] : array();

        if ( empty( $user_info[ 'shipping_info' ] ) ) {
            $data[ $idx ][ 'shipping' ] = '';
            continue;
        }

        $address_fields = array(
            'first_name'    => $row[ 'first' ],
            'last_name'     => $row[ 'last' ],
            'address'       => isset( $shipping_info[ 'address' ] )     ? $shipping_info[ 'address' ]       : '',
            'address_2'     => isset( $shipping_info[ 'address2' ] )    ? $shipping_info[ 'address2' ]      : '',
            'city'          => isset( $shipping_info[ 'city' ] )        ? $shipping_info[ 'city' ]          : '',
            'state'         => isset( $shipping_info[ 'state' ] )       ? $shipping_info[ 'state' ]         : '',
            'postcode'      => isset( $shipping_info[ 'zip' ] )         ? $shipping_info[ 'zip' ]           : '',
            'country'       => isset( $shipping_info[ 'country' ] )     ? $shipping_info[ 'country' ]       : ''
        );

        $address = charitable_get_location_helper()->get_formatted_address( $address_fields );
        $data[ $idx ][ 'shipping' ] = str_replace( '<br/>', PHP_EOL, $address );
    }    
    
    return $data;
}

/**
 * Filter the columns exported through the EDD Campaign Payments batch export. 
 *
 * @param   string[] $cols
 * @return  string[]
 * @since   1.1.0
 */
function ppcde_filter_campaign_payments_cols( $cols ) {
    unset( 
        $cols[ 'address1' ],
        $cols[ 'address2' ],
        $cols[ 'city' ],
        $cols[ 'state' ],
        $cols[ 'country' ],
        $cols[ 'zip' ]
    );

    $cols[ 'shipping' ] = __( 'Shipping Address' );
    return $cols;
}