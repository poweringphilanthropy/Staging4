<?php
/**
 * Charitable EDD Donation Hooks
 *
 * Action/filter hooks used to handle donations.
 *
 * @package     Charitable EDD/Functions/Donation
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Process a donation form submission with EDD.
 *
 * @see     Charitable_EDD_Donation_Form::save_donation()
 */
// add_action( 'charitable_make_edd_donation', array( 'Charitable_EDD_Donation_Form', 'save_donation' ) );

/**
 * Before processing the donation form, redirect to checkout.
 *
 * @see     Charitable_EDD_Checkout::donation_redirect_to_checkout()
 */
add_action( 'charitable_before_process_donation_form', array( Charitable_EDD_Checkout::get_instance(), 'donation_redirect_to_checkout' ), 10, 2 );
add_action( 'charitable_before_process_donation_amount_form', array( Charitable_EDD_Checkout::get_instance(), 'donation_redirect_to_checkout' ), 10, 2 );

/**
 * Display a notice in the EDD checkout table to say how much will be contributed to the campaign.
 *
 * @see     Charitable_EDD_Checkout::donation_amount_notice_checkout()
 */
add_action( 'edd_checkout_table_footer_last', array( Charitable_EDD_Checkout::get_instance(), 'donation_amount_notice_checkout' ) );

/**
 * Respond to cart updates by updating the benefit amount.
 *
 * @see     Charitable_EDD_Checkout::ajax_send_benefit_amount()
 */
add_action( 'wp_ajax_charitable_edd_update_benefit_amount', array( Charitable_EDD_Checkout::get_instance(), 'ajax_send_benefit_amount' ) );
add_action( 'wp_ajax_nopriv_charitable_edd_update_benefit_amount', array( Charitable_EDD_Checkout::get_instance(), 'ajax_send_benefit_amount' ) );

/**
 * Set the purchase summary to include information about the donations.
 *
 * @see     Charitable_EDD_Checkout::add_donations_to_purchase_summary()
 */
add_filter( 'edd_get_purchase_summary', array( Charitable_EDD_Checkout::get_instance(), 'add_donations_to_purchase_summary' ) );

/**
 * Add donations to the cart quantity field.
 *
 * @see     Charitable_EDD_Checkout::add_donations_to_cart_quantity()
 */
add_filter( 'edd_get_cart_quantity', array( Charitable_EDD_Checkout::get_instance(), 'add_donations_to_cart_quantity' ) );

/**
 * Add donations to the cart subtotal.
 *
 * @see     Charitable_EDD_Checkout::add_donations_to_cart_subtotal()
 */
add_filter( 'edd_get_cart_subtotal', array( Charitable_EDD_Checkout::get_instance(), 'add_donations_to_cart_subtotal' ) );

/**
 * Remove donations from the cart total, since they were already added in the subtotal.
 *
 * @see     Charitable_EDD_Checkout::remove_donations_from_cart_total()
 */
add_filter( 'edd_get_cart_total', array( Charitable_EDD_Checkout::get_instance(), 'remove_donations_from_cart_total' ) );

/**
 * Create a Charitable donation right after the payment has been created.
 *
 * @see     Charitable_EDD_Payment::create_donation()
 */
add_action( 'edd_insert_payment', array( Charitable_EDD_Payment::get_instance(), 'create_donation' ), 10, 2 );

/**
 * After an EDD payment's status is changed, update the donation status too.
 *
 * @see     Charitable_EDD_Payment::process_payment()
 */
add_action( 'edd_update_payment_status', array( Charitable_EDD_Payment::get_instance(), 'update_donation_status' ), 10, 3 );

/**
 * Display the donations and products purchased on the EDD purchase receipt.
 *
 * @see     Charitable_EDD_Payment::donations_table_receipt()
 */
add_action( 'edd_payment_receipt_after_table', array( Charitable_EDD_Payment::get_instance(), 'donations_table_receipt' ) );
add_action( 'edd_payment_receipt_after_table', array( Charitable_EDD_Payment::get_instance(), 'products_table_receipt' ) );

/**
 * Modifiy the purchase receipt shortcode attributes to hide products table by default.
 *
 * @see     Charitable_EDD_Payment::filter_edd_receipt_attributes()
 */
add_filter( 'shortcode_atts_edd_receipt', array( Charitable_EDD_Payment::get_instance(), 'filter_edd_receipt_attributes' ) );

/**
 * Make sure the download ID is always set in the download file URL args.
 *
 * @see     Charitable_EDD_Payment::set_download_id_in_args()
 */
add_filter( 'edd_download_file_url_args', array( Charitable_EDD_Payment::get_instance(), 'set_download_id_in_args' ), 20 );

/**
 * Add donations to the payment subtotal.
 *
 * @see     Charitable_EDD_Payment::add_donations_to_subtotal()
 */
add_filter( 'edd_get_payment_subtotal', array( Charitable_EDD_Payment::get_instance(), 'add_donations_to_subtotal' ), 10, 2 );

/**
 * Delete payment meta related to donations when a donation is deleted.
 *
 * @see     Charitable_EDD_Payment::delete_donation_meta()
 */
add_action( 'deleted_post', array( Charitable_EDD_Payment::get_instance(), 'delete_donation_meta' ) );
