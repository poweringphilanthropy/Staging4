<?php 
/**
 * Charitable EDD Donation Hooks. 
 *
 * Action/filter hooks used for Charitable EDD donations. 
 * 
 * @package     Charitable EDD/Functions/Donations
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Display the download purchase field on the donation form. 
 *
 * @see     charitable_edd_template_donation_form_download_simple
 * @see     charitable_edd_template_donation_form_download_variable
 */
add_action( 'charitable_edd_donation_form_download', 'charitable_edd_template_donation_form_download_simple' );
add_action( 'charitable_edd_donation_form_download', 'charitable_edd_template_donation_form_download_variable' );

/**
 * Show how much the download purchase will contribute to fundraising campaigns.
 *
 * @see     charitable_edd_template_download_contribution_amount
 */
add_action( 'edd_purchase_link_end', 'charitable_edd_template_download_contribution_amount' );
