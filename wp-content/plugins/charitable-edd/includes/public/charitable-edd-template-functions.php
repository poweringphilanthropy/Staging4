<?php 
/**
 * Charitable EDD Template Functions. 
 *
 * Functions used with template hooks.
 * 
 * @package     Charitable EDD/Functions/Templates
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly


/**********************************************/ 
/* DONATION FORM
/**********************************************/

if ( ! function_exists( 'charitable_edd_template_donation_form_download_simple' ) ) :
    /**
     * Load the template where a single download is displayed.  
     *
     * @param   WP_Post $download
     * @return  void
     * @since   1.0.0
     */
    function charitable_edd_template_donation_form_download_simple( $download ) {
        if ( edd_has_variable_prices( $download->ID ) ) {
            return;
        }

        charitable_edd_template( 'donation-form/simple-download.php', array( 'download' => $download ) );
    }
endif;

if ( ! function_exists( 'charitable_edd_template_donation_form_download_variable' ) ) :
    /**
     * Load the template where a single download is displayed.  
     *
     * @param   WP_Post $download
     * @return  void
     * @since   1.0.0
     */
    function charitable_edd_template_donation_form_download_variable( $download ) {
        if ( ! edd_has_variable_prices( $download->ID ) ) {
            return;
        }

        charitable_edd_template( 'donation-form/variable-download.php', array( 'download' => $download ) );
    }
endif;

/**********************************************/ 
/* PRODUCT BUTTON
/**********************************************/

if ( ! function_exists( 'charitable_edd_template_download_contribution_amount' ) ) :
    /**
     * Show a note to indicate how much the download purchase will contribute to fundraising.
     *
     * @param   int $download_id
     * @return  void
     * @since   1.0.0
     */
    function charitable_edd_template_download_contribution_amount( $download_id ) {
        if ( ! charitable_get_option( 'show_product_contribution_note', false ) ) {
            return;
        }

        charitable_edd_template( 'download-contribution.php', array( 'download_id' => $download_id ) );
    }
endif;
