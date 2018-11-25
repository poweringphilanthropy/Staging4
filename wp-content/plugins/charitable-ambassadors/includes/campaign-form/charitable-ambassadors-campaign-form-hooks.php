<?php 
/**
 * Charitable Ambassadors Campaign Form Hooks
 *
 * Action/filter hooks used to set up the campaign form.
 *
 * @package     Charitable Ambassadors/Functions/Campaign Form
 * @version     1.0.0
 * @author 		Eric Daams 
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Register recipient types.
 *
 * @see     Charitable_Ambassadors_Ambassador::register()
 * @see     Charitable_Ambassadors_Personal_Cause::register()
 */
add_action( 'init', array( 'Charitable_Ambassadors_Ambassador', 'register' ) );
add_action( 'init', array( 'Charitable_Ambassadors_Personal_Cause', 'register' ) );

/**
 * Perform a recipient search. 
 *
 * @see     Charitable_Ambassadors_Campaign_Recipient_Form::search_recipients()
 */
add_action( 'wp_ajax_charitable_recipient_search', array( Charitable_Ambassadors_Campaign_Recipient_Form::get_instance(), 'search_recipients' ) );
add_action( 'wp_ajax_nopriv_charitable_recipient_search', array( Charitable_Ambassadors_Campaign_Recipient_Form::get_instance(), 'search_recipients' ) );

/**
 * Modify submission form pages.
 *
 * @see     Charitable_Ambassadors_Campaign_Recipient_Form::add_recipient_details_page()
 */
add_filter( 'charitable_campaign_submission_pages', array( Charitable_Ambassadors_Campaign_Recipient_Form::get_instance(), 'add_recipient_details_page' ) );

/**
 * Modify submission fields.
 *
 * @see     Charitable_Ambassadors_Personal_Cause::add_payment_details_fields()
 * @see     Charitable_Ambassadors_Campaign_Recipient_Form::add_recipient_type_fields()
 */
add_filter( 'charitable_campaign_submission_fields', array( 'Charitable_Ambassadors_Personal_Cause', 'add_payment_details_fields' ), 10, 2 );
add_filter( 'charitable_campaign_submission_fields', array( Charitable_Ambassadors_Campaign_Recipient_Form::get_instance(), 'add_recipient_type_fields' ) );

/**
 * Add hidden fields. 
 *
 * @see     Charitable_Ambassadors_Campaign_Recipient_Form::add_hidden_recipient_type_fields()
 */
add_filter( 'charitable_campaign_submission_campaign_fields', array( Charitable_Ambassadors_Campaign_Recipient_Form::get_instance(), 'add_hidden_recipient_type_fields' ), 10, 2 );

/**
 * Set up fields for the recipient type page.
 *
 * @see     Charitable_Ambassadors_Personal_Cause::add_payment_details_fields()
 * @see     Charitable_Ambassadors_Campaign_Recipient_Form::add_recipient_type_fields()
 */
add_filter( 'charitable_recipient_type_fields', array( Charitable_Ambassadors_Campaign_Recipient_Form::get_instance(), 'add_recipient_type_search' ), 10, 3 );

/**
 * Set the page number for the form.
 *
 * @see     Charitable_Ambassadors_Campaign_Form::set_campaign_submission_page()
 */
add_filter( 'charitable_ambassadors_campaign_form_current_page', array( Charitable_Ambassadors_Campaign_Recipient_Form::get_instance(), 'set_campaign_submission_page' ), 10, 2 );

/**
 * Save a submitted campaign.
 *
 * @see     Charitable_Ambassadors_Campaign_Form::save_campaign()
 * @see     Charitable_Ambassadors_Campaign_Form::save_campaign_details()
 */
add_action( 'charitable_save_campaign', array( 'Charitable_Ambassadors_Campaign_Form', 'save_campaign' ) );
add_action( 'charitable_campaign_submission_save_page_campaign_details', array( 'Charitable_Ambassadors_Campaign_Form', 'save_campaign_details' ) );

/**
 * Add data to the campaign metabox to show the PayPal account entered by the user.
 *
 * @see     Charitable_Ambassadors_Personal_Cause::add_campaign_funding_data()
 */
add_filter( 'charitable_ambassadors_campaign_funds_recipient_data', array( 'Charitable_Ambassadors_Personal_Cause', 'add_campaign_funding_data' ), 10, 3 );

