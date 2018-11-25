<?php
/**
 * Charitable Donor Comments donation hooks.
 *
 * @package     Charitable Donor Comments/Functions/Donation
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Add the Donor Comments section to the donation form.
 *
 * @see Charitable_Donor_Comments_Donation::add_comment_field
 */
add_filter( 'charitable_donation_form_donor_fields_after', array( Charitable_Donor_Comments_Donation::get_instance(), 'add_comment_field' ), 5 );

/**
 * Add the donor comment to the values to save.
 *
 * @see Charitable_Donor_Comments_Donation::add_comment_value
 */
add_filter( 'charitable_donation_form_submission_values', array( Charitable_Donor_Comments_Donation::get_instance(), 'add_comment_value' ), 10, 2 );

/**
 * Save the comment after the donation has been saved.
 *
 * @see Charitable_Donor_Comments_Donation::save_donor_comment
 */
add_action( 'charitable_after_save_donation', array( Charitable_Donor_Comments_Donation::get_instance(), 'save_donor_comment' ), 10, 2 );

/**
 * Add the comment to the donor widget/shortcode.
 *
 * @see Charitable_Donor_Comments_Donation::show_donor_comment_in_widget
 */
add_action( 'charitable_donor_loop_after_donor', array( Charitable_Donor_Comments_Donation::get_instance(), 'show_donor_comment_in_widget' ), 10, 2 );

/**
 * Display the comment_author as the name in the donor widget/shortcode.
 *
 * @see Charitable_Donor_Comments_Donation::set_donor_comment_author_as_name_in_widget
 */
add_filter( 'charitable_donor_loop_donor_name', array( Charitable_Donor_Comments_Donation::get_instance(), 'set_donor_comment_author_as_name_in_widget' ), 10, 2 );

/**
 * Display the comment author as Anonymous if it's blank, even if a logged in user made the comment.
 *
 * @see Charitable_Donor_Comments_Donation::set_anonymous_donor_comment_author_name
 */
add_filter( 'get_comment_author', array( Charitable_Donor_Comments_Donation::get_instance(), 'set_anonymous_donor_comment_author_name' ), 10, 3 );

/**
 * Add comment email tag.
 *
 * @see Charitable_Donor_Comments_Donation::add_email_tag()
 */
add_filter( 'charitable_email_donation_fields', array( Charitable_Donor_Comments_Donation::get_instance(), 'add_email_tag' ) );
