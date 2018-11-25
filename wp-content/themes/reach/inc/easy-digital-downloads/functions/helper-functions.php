<?php
/**
 * Helper functions for the EDD functionality.
 *
 * @package     Reach/Functions/EDD
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


/**
 * Return the product price. 
 *
 * @param   int     $download_id
 * @param   array   $args
 * @return  string
 * @since   1.0.0
 */
function reach_get_edd_product_price( $download_id, $args = array() ) {
    $download = new EDD_Download( $download_id );

    if ( ! $download->has_variable_prices() || false === $args[ 'price_id' ] ) {
        return $download->price;
    }

    $price_id = $args[ 'price_id' ];
    $prices = $download->prices;
    $price = isset( $prices[ $price_id ] ) ? $prices[ $price_id ] : false;

    return $price;
}