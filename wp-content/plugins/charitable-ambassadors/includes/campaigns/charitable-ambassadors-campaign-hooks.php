<?php
/**
 * Charitable Ambassadors Campaigns Hooks
*
 * @version     1.1.0
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Campaigns
 * @author      Eric Daams
 * @copyright   Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * These filters rely on functionality only present in Charitable 1.3.4+
 */
if ( version_compare( charitable()->get_version(), '1.3.4', '>=' ) ) {

	/**
	 * Change the amount donated results.
	 *
	 * @see     Charitable_Ambassadors_Campaign::get_donated_amount()
	 */
	add_filter( 'charitable_campaign_donated_amount', array( Charitable_Ambassadors_Campaign::get_instance(), 'get_donated_amount' ), 10, 3 );

	/**
	 * Change the donor count.
	 *
	 * @see 	Charitable_Ambassadors_Campaign::get_donor_count()
	 */
	add_filter( 'charitable_campaign_donor_count', array( Charitable_Ambassadors_Campaign::get_instance(), 'get_donor_count' ), 10, 2 );

}

/**
 * Include donors to child campaigns in donors widget.
 *
 * @see 	Charitable_Ambassadors_Campaign::filter_donor_query_args()
 */
add_filter( 'charitable_donors_widget_donor_query_args', array( Charitable_Ambassadors_Campaign::get_instance(), 'filter_donor_query_args' ) );

/**
 * Include child campaigns when calculating how much a donor has contributed to a campaign.
 *
 * @see 	Charitable_Ambassadors_Campaign::get_donor_donation_amount()
 * @see 	Charitable_Ambassadors_Campaign::get_user_donation_amount()
 */
add_filter( 'charitable_donor_donation_amount', array( Charitable_Ambassadors_Campaign::get_instance(), 'get_donor_donation_amount' ), 10, 3 );
add_filter( 'charitable_user_total_donated_query_args', array( Charitable_Ambassadors_Campaign::get_instance(), 'get_user_donation_amount' ), 10, 2 );

/**
 * Correctly order campaigns by amount in the [campaigns] shortcode.
 *
 * @see 	Charitable_Ambassadors_Campaign::filter_campaigns_shortcode_args()
 */
add_filter( 'charitable_campaigns_shortcode_view_args', array( Charitable_Ambassadors_Campaign::get_instance(), 'filter_campaigns_shortcode_args' ), 10, 2 );
