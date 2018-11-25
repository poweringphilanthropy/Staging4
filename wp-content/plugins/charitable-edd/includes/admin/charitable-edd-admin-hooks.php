<?php
/**
 * Charitable EDD Admin Hooks
 *
 * Action/filter hooks used to set up the admin.
 *
 * @package     Charitable EDD/Functions/Admin
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Add the EDD settings to the donation options metabox.
 *
 * @see Charitable_EDD_Admin::add_edd_meta_fields()
 */
add_filter( 'charitable_campaign_donation_options_fields', array( Charitable_EDD_Admin::get_instance(), 'add_edd_metabox_fields' ) );

/**
 * Save the additional EDD settings.
 *
 * @see Charitable_EDD_Admin::add_meta_keys()
 */
add_filter( 'charitable_campaign_meta_keys', array( Charitable_EDD_Admin::get_instance(), 'add_meta_keys' ) );

/**
 * Sanitize the checkbox value of the "show contribution options" field.
 *
 * @see Charitable_Campaign::sanitize_checkbox()
 */
add_filter( 'charitable_sanitize_campaign_meta_campaign_campaign_edd_show_contribution_options', array( 'Charitable_Campaign', 'sanitize_checkbox' ) );

/**
 * Set up benefactor form fields.
 *
 * @see Charitable_EDD_Admin::benefactor_form_fields()
 */
add_action( 'charitable_campaign_benefactor_form_extension_fields', array( Charitable_EDD_Admin::get_instance(), 'benefactor_form_fields' ), 10, 3 );

/**
 * Sanitize benefactor data before saving.
 *
 * @see Charitable_EDD_Admin::sanitize_benefactor_data()
 */
add_action( 'charitable_benefactor_data', array( Charitable_EDD_Admin::get_instance(), 'sanitize_benefactor_data' ) );

/**
 * Add EDD settings to the General tab in Charitable settings.
 *
 * @see Charitable_EDD_Admin::add_extension_settings()
 */
add_filter( 'charitable_settings_tab_fields_extensions', array( Charitable_EDD_Admin::get_instance(), 'add_extension_settings' ) );

/**
 * Add EDD settings to the General tab in Charitable settings.
 *
 * @see Charitable_EDD_Admin::add_payment_gateway_settings()
 */
add_filter( 'charitable_settings_tab_fields_gateways', array( Charitable_EDD_Admin::get_instance(), 'add_payment_gateway_settings' ) );

/**
 * Add a campaign payments export to the Export tab in EDD reports.
 *
 * @see Charitable_EDD_Admin::export_box()
 */
add_action( 'edd_reports_tab_export_content_bottom', array( Charitable_EDD_Admin::get_instance(), 'export_box' ) );

/**
 * Register the campaign payments batch exporter.
 *
 * @see Charitable_EDD_Admin::include_exporter()
 */
add_action( 'edd_batch_export_class_include', array( Charitable_EDD_Admin::get_instance(), 'include_exporter' ) );

/**
 * Resync a donation with its linked EDD payment.
 *
 * @see     Charitable_EDD_Admin::resync_donation_from_edd_payment()
 */
add_action( 'charitable_resync_donation_from_edd_payment', array( Charitable_EDD_Admin::get_instance(), 'resync_donation_from_edd_payment' ) );

/**
 * Add re-sync bulk action.
 *
 * @see     Charitable_EDD_Admin::add_resync_bulk_action()
 */
add_filter( 'charitable_donations_table_bulk_actions', array( Charitable_EDD_Admin::get_instance(), 'add_resync_bulk_action' ) );

/**
 * Handle the re-sync bulk action.
 *
 * @see     Charitable_EDD_Admin::handle_resync_bulk_action()
 */
add_filter( 'handle_bulk_actions-edit-donation', array( Charitable_EDD_Admin::get_instance(), 'handle_resync_bulk_action' ), 9, 3 );

/**
 * Check if there are any notices to be displayed in the admin.
 *
 * @see     Charitable_EDD_Admin::add_notices()
 */
add_action( 'admin_notices', array( Charitable_EDD_Admin::get_instance(), 'add_version_update_notices' ) );

if ( version_compare( charitable()->get_version(), '1.5.0', '<' ) ) {
    
    /**
     * For Charitable < 1.5, this loads our own overview view.
     *
     * @see Charitable_EDD_Admin::load_custom_donation_overview_view()
     */
    add_filter( 'charitable_admin_view_path', array( Charitable_EDD_Admin::get_instance(), 'load_custom_donation_overview_view' ), 10, 2 );
    
    /**
     * Add the Re-sync Donation meta box to the donation management page.
     *
     * @see     Charitable_EDD_Admin::add_resync_donation_meta_box()
     */
    add_filter( 'charitable_donation_meta_boxes', array( Charitable_EDD_Admin::get_instance(), 'add_resync_donation_meta_box' ) );
    
} else {
    
    /**
     * Set up a class that customizes how the donation details are shown.
     *
     * @see Charitable_EDD_Admin::load_custom_donation_view_class()
     */
    add_action( 'charitable_donation_details_before_campaign_donations', array( Charitable_EDD_Admin::get_instance(), 'load_custom_donation_view_class' ), 10, 2 );
    
    /**
     * Register the Resync Donation action.
     *
     * @see Charitable_EDD_Admin::register_resync_donation_action()
     */
    add_action( 'admin_init', array( Charitable_EDD_Admin::get_instance(), 'register_resync_donation_action' ) );
    
    /**
     * Register a custom post message for the Resync action.
     *
     * @see Charitable_EDD_Admin::register_resync_post_message()
     */
    add_filter( 'post_updated_messages', array( Charitable_EDD_Admin::get_instance(), 'register_resync_post_message' ) );
    
}