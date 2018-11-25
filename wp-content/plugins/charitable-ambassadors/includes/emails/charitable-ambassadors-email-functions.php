<?php 
/**
 * Charitable Ambassadors Email Functions. 
 *
 * @author      WPCharitable
 * @category    Functions
 * @package     Charitable Ambassadors/Functions/Email
 * @version     1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Register additional emails.  
 *
 * @param   string[] $emails
 * @return  string[]
 * @access  public
 * @since   1.0.0
 */
function charitable_ambassadors_register_emails( $emails ) {
    $new_emails = array(
        'new_campaign'                  => 'Charitable_Ambassadors_Email_New_Campaign',
        'creator_campaign_submission'   => 'Charitable_Ambassadors_Email_Creator_Campaign_Submission', 
        'creator_campaign_ending'       => 'Charitable_Ambassadors_Email_Creator_Campaign_Ending',
        'creator_donation_notification' => 'Charitable_Ambassadors_Email_Creator_Donation_Notification'
    );

    $emails = array_merge( $emails, $new_emails );

    return $emails;
}

/**
 * Add our donation emails to the list of resendable emails.
 *
 * @since  1.1.17
 *
 * @param  array $emails The list of resendable donation emails.
 * @return array
 */
function charitable_ambassadors_resendable_donation_emails( $emails ) {
    return array_merge( $emails, array( 'creator_donation_notification' ) );
}

/**
 * Add success & failure parameters to the shortcode as supported parameters.
 * 
 * @param   array $out 
 * @param   array $pairs Entire list of supported attributes and their defaults.
 * @param   array $atts User defined attributes in shortcode tag.
 * @return  array
 * @since   1.1.0
 */
function charitable_ambassadors_add_email_shortcode_parameters( $out, $pairs, $atts ) {
    foreach ( array( 'success', 'failure' ) as $key ) {
        if ( isset( $atts[ $key ] ) ) {
            $out[ $key ] = $atts[ $key ];
        }
    }
    
    return $out;
}