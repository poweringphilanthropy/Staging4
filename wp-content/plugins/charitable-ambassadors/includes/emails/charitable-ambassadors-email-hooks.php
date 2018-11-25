<?php
/**
 * Charitable Ambassadors Email Hooks
 *
 * Activation/filter hooks used for Charitable Ambassadors emails.
 *
 * @version     1.0.0
 * @package     Charitable/Classes/Charitable_Ambassadors_Email_New_Campaign
 * @author      Eric Daams, Rafe Colton
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Register Ambassadors emails.
 *
 * @see charitable_ambassadors_register_emails
 */
add_filter( 'charitable_emails', 'charitable_ambassadors_register_emails' );

/**
 * Add our donation emails to the list of resendable emails.
 *
 * @see charitable_ambassadors_resendable_donation_emails
 */
add_filter( 'charitable_resendable_donation_emails', 'charitable_ambassadors_resendable_donation_emails' );

/**
 * Support the success & failure parameters in the charitable_email shortcode.
 *
 * @see charitable_ambassadors_add_email_shortcode_parameters
 */
add_filter( 'shortcode_atts_charitable_email', 'charitable_ambassadors_add_email_shortcode_parameters', 10, 3 );

/**
 * Add extra email settings fields to the Creator Campaign Ending email.
 *
 * @see Charitable_Ambassadors_Email_Creator_Campaign_Ending::add_email_settings()
 */
add_filter( 'charitable_settings_fields_emails_email_creator_campaign_ending', array( 'Charitable_Ambassadors_Email_Creator_Campaign_Ending', 'add_email_settings' ) );

/**
 * Check for campaigns that need a Creator Campaign Ending email to go out.
 *
 * @see Charitable_Ambassadors_Email_Creator_Campaign_Ending::send_ending_campaign_emails();
 */
add_action( 'charitable_daily_scheduled_events', array( 'Charitable_Ambassadors_Email_Creator_Campaign_Ending', 'send_ending_campaign_emails' ) );

/**
 * Send the New Campaign email.
 *
 * This email is sent to the website admin or other recipients after a new campaign has been created.
 *
 * @see Charitable_Ambassadors_Email_New_Campaign::send_email()
 */
add_action( 'charitable_campaign_submission_save', array( 'Charitable_Ambassadors_Email_New_Campaign', 'send_email' ), 20, 2 );

/**
 * Send an email to the campaign creator after they submitted their campaign.
 *
 * @see Charitable_Ambassadors_Email_Creator_Campaign_Submission::send_email()
 */
add_action( 'charitable_campaign_submission_save', array( 'Charitable_Ambassadors_Email_Creator_Campaign_Submission', 'send_email' ), 20, 2 );

/**
 * Send the Creator Donation Notification email.
 *
 * This email is sent to the campaign creator after a donation has been made to their campaign.
 *
 * @see Charitable_Ambassadors_Email_Creator_Donation_Notification::send_with_donation_id()
 */
add_action( 'charitable_after_save_donation', array( 'Charitable_Ambassadors_Email_Creator_Donation_Notification', 'send_with_donation_id' ) );
add_action( 'save_post_' . Charitable::DONATION_POST_TYPE, array( 'Charitable_Ambassadors_Email_Creator_Donation_Notification', 'send_with_donation_id' ) );

/**
 * Make the Creator Donation Notification email resendable from the log.
 *
 * @see Charitable_Ambassadors_Email_Creator_Donation_Notification::get_email_id_from_log()
 */
add_filter( 'charitable_email_id_from_log', array( 'Charitable_Ambassadors_Email_Creator_Donation_Notification', 'get_email_id_from_log' ) );
