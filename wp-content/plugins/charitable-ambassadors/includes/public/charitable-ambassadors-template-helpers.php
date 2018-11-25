<?php 

/**
 * Charitable Ambassadors Template Helpers. 
 *
 * @author      Studio164a
 * @category    Functions
 * @package     Charitable Ambassadors/Functions/Template
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Enqueue Ambassadors styles if they haven't been enqueued yet.
 *
 * @return  void
 * @since   1.1.0
 */
function charitable_ambassadors_enqueue_styles() {
    if ( ! wp_style_is( 'charitable-ambassadors-styles', 'enqueued' ) && wp_style_is( 'charitable-ambassadors-styles', 'registered' ) ) {
        wp_enqueue_style( 'charitable-ambassadors-styles' );
    }    
}