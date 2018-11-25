<?php
/**
 * Charitable Donor Comments admin hooks.
 *
 * @package     Charitable Donor Comments/Functions/Admin
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Add a direct link to the Extensions settings page from the plugin row.
 *
 * @see     Charitable_Donor_Comments_Admin::add_plugin_action_links()
 */
add_filter( 'plugin_action_links_' . plugin_basename( charitable_donor_comments()->get_path() ), array( Charitable_Donor_Comments_Admin::get_instance(), 'add_plugin_action_links' ) );

/**
 * Add a "Donor Comments" section to the Extensions settings area of Charitable.
 *
 * @see     Charitable_Donor_Comments_Admin::add_donor_comments_settings()
 */
add_filter( 'charitable_settings_tab_fields_extensions', array( Charitable_Donor_Comments_Admin::get_instance(), 'add_donor_comments_settings' ), 6 );

/**
 * Register admin stylesheet.
 *
 * @see     Charitable_Donor_Comments_Admin::setup_admin_stylesheet()
 */
add_action( 'admin_enqueue_scripts', array( Charitable_Donor_Comments_Admin::get_instance(), 'setup_admin_stylesheet' ) );

/**
 * Add the donor's comment to the donation details page.
 *
 * @see     Charitable_Donor_Comments_Admin::add_comment_to_admin_donation_page
 */
add_action( 'charitable_donation_details_before_campaign_donations', array( Charitable_Donor_Comments_Admin::get_instance(), 'add_comment_to_admin_donation_page' ), 10, 2 );
