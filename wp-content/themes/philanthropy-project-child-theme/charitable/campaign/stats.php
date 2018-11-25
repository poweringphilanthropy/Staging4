<?php
/**
 * The template for displaying the campaign's stats. 
 *
 * @author  Studio 164a
 * @package Reach
 * @since   1.0.0
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$campaign = $view_args[ 'campaign' ];

if ( ! $campaign->has_goal() ) {
    return;
}

$currency_helper = charitable_get_currency_helper();

/**
 * Change donors count to be same as widget
 * (withoit distinc /  group donations by the same person)
 * @var array
 */
 
 $campaign_ids = array();
if(function_exists('pp_get_merged_team_campaign_ids')){
$campaign_ids = pp_get_merged_team_campaign_ids($campaign->ID);
}

$query_args = array(
    'number' => -1,
    'output' => 'donors',
    'campaign' => $campaign_ids,
    'distinct_donors' => false,
    'distinct' => false,
);
$donors = new Charitable_Donor_Query( $query_args );

?>
<ul class="campaign-stats">    
    <li class="campaign-raised">
        <?php printf( '<span>%s</span>%s', 
            $currency_helper->get_monetary_amount( $campaign->get_donated_amount() ), 
            __( 'Donated', 'reach' )
        ) ?>
    </li>    
    <li class="campaign-goal">
        <?php printf( '<span>%s</span>%s', 
            $currency_helper->get_monetary_amount( $campaign->get_goal() ),
            __( 'Fundraising Target', 'reach' )
        ) ?>
    </li>    
    <li class="campaign-backers">
        <?php printf( '<span>%d</span>%s',
            // $campaign->get_donor_count(),
            $donors->count(),
            __( 'Supporters', 'reach' )
        ) ?>
    </li>
</ul>