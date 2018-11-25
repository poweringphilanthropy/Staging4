<?php
/**
 * PP_Dashboard_Reports Class.
 *
 * @class       PP_Dashboard_Reports
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Dashboard_Reports class.
 */
class PP_Dashboard_Reports {

    protected $post = false;
    protected $campaign_group_id = false;

    /**
     * Constructor
     */
    public function __construct( $dashboard ) {
        $this->includes();

        if(is_numeric( $dashboard )){
            $dashboard = get_post( $dashboard );
        }

        $this->post = $dashboard;
        $this->campaign_group_id = pp_get_dashboard_term_id( $dashboard->ID );

        // default args
        $this->args = array(
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'campaign_group',
                    'field' => 'id',
                    'terms' => $this->campaign_group_id,
                    'include_children' => true
                )
            ),
            'post_status' => 'publish'
        );
    }

    public function get_campaign_ids(){
        $args = array(
            'fields' => 'ids',
        );

        $args = wp_parse_args( $args, $this->args );
        $query = Charitable_Campaigns::query($args);
        return $query->posts;
    }

    public function get_campaigns( $args = array() ){
        $campaigns = array();

        $args = wp_parse_args( $args, $this->args );

        $campaigns_by_amount = Charitable_Campaigns::ordered_by_amount( $args );

        if(!empty( $campaigns_by_amount->posts ) ):
        foreach ( $campaigns_by_amount->posts as $campaign) {
            
            $campaign->total_donation = charitable_get_table( 'campaign_donations' )->get_campaign_donated_amount( $campaign->ID );
            $campaign->formatted_total_donation = charitable_get_currency_helper()->get_monetary_amount($campaign->total_donation);

            $campaigns[] = $campaign;
        }
        endif;

        return $campaigns;
    }

    public function get_donations( $args = array() ){

        $defaults = array(
            'campaign_in' => $this->get_campaign_ids(),
            'status' => 'charitable-completed'
        );

        $query_args = wp_parse_args( $args, $defaults );

        $donations = charitable_get_table( 'campaign_donations' )->get_donations_report( $query_args );
        
        usort($donations, array(__CLASS__, "sort_by_amount"));

        return $donations;
    }

    public function get_total_donation_amount( $formatted = false ){
        if(empty( $this->get_campaign_ids() ))
            return 0;

        $amount = charitable_get_table( 'campaign_donations' )->get_campaign_donated_amount( $this->get_campaign_ids() );

        return ($formatted) ? charitable_get_currency_helper()->get_monetary_amount($amount) : $amount;
    }

    public function get_fundraisers(){
        $query_args = array(
            'number' => -1, // get all
            'output' => 'fundraisers',
            'distinct_fundraisers' => true,
            'orderby' => 'amount',
            'status' => 'charitable-completed',
            'campaign' => $this->get_campaign_ids(),
        );

        $query = new PP_Fundraisers_Query( $query_args );

        return $query->get_fundraisers();
    }

    public function get_total_fundraisers(){
        return count( $this->get_fundraisers() );
    }

    public function get_merchandises(){

        $query_args = array(
            'number' => -1, // get all
            'campaign' => $this->get_campaign_ids(),
            'status' => 'charitable-completed',
        );

        $merchandise_donations = new PP_Merchandise_Donations_Query( $query_args );
        
        return $merchandise_donations->get_donations();
    }

    public function get_tickets(){

        $query_args = array(
            'number' => -1, // get all
            'campaign' => $this->get_campaign_ids(),
            'status' => 'charitable-completed',
        );

        $merchandise_donations = new PP_Ticket_Donations_Query( $query_args );
        
        return $merchandise_donations->get_donations();
    }

    public function get_donors(){
        if(empty( $this->get_campaign_ids() ))
            return 0;

        $query_args = array(
            'number' => -1,
            'output' => 'donations',
            'campaign' => $this->get_campaign_ids(),
            'distinct_donors' => true,
            'distinct' => false,
        );

        $donors = new Charitable_Donor_Query( $query_args );

        // $donors = philanthropy_get_multiple_donors( $this->get_campaign_ids() );
        $donor_lists = $donors->get_donors();

        usort($donor_lists, array(__CLASS__, "sort_by_amount"));

        return $donor_lists;
    }

    public function get_total_donors(){

        $query_args = array(
            'number' => -1,
            'output' => 'donations',
            'campaign' => $this->get_campaign_ids(),
            'distinct_donors' => true,
            'distinct' => false,
        );

        $donors = new Charitable_Donor_Query( $query_args );

        // $donors = philanthropy_get_multiple_donors( $this->get_campaign_ids() );
        return $donors->count();
    }

    public static function sort_by_amount($a, $b) {
        if(is_array($a)){
            return $b["amount"] - $a["amount"];
        } 

        if(is_object($a)){
            return $b->amount - $a->amount;
        }
    }

    public function includes(){

    }

}