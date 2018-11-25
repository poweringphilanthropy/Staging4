<?php
/**
 * G4G_Covers_Fee Class.
 *
 * @class       G4G_Covers_Fee
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * G4G_Covers_Fee class.
 */
class G4G_Covers_Fee {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new G4G_Covers_Fee();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_filter( 'pp_dashboard_settings_fields', array($this, 'g4g_dashboard_settings_fields'), 10, 1 );
        add_filter( 'pp_is_donor_cover_fee_enabled_for_campaign', array($this, 'maybe_leaderboard_enable_donor_cover_fee'), 10, 2 );
        add_filter( 'pp_get_platform_fee', array($this, 'maybe_change_with_leaderboard_fee'), 10, 2 );
    }

    public function maybe_leaderboard_enable_donor_cover_fee($enabled, $campaign_id){

        if($enabled){
            return $enabled;
        }

        $primary_term_id = g4g_get_primary_term($campaign_id, 'campaign_group');
        if(empty($primary_term_id)){
            return $enabled;
        }

        $group_enabled = get_term_meta( $primary_term_id, '_enable_donor_covers_fee', true );
        $enabled = $group_enabled == 'on';

        return $enabled;
    }

    public function maybe_change_with_leaderboard_fee($platform_fee, $campaign_id){

        if( ! function_exists('edd_get_option') ){
            return $platform_fee;
        }

        $primary_term_id = g4g_get_primary_term($campaign_id, 'campaign_group');
        if(empty($primary_term_id)){
            return $platform_fee;
        }
        
        $group_platform_fee = get_term_meta( $primary_term_id, '_platform_fee', true );

        if( !empty($group_platform_fee) ){
            $platform_fee = floatval($group_platform_fee);
        }
        
        return $platform_fee;
    }

    public function g4g_dashboard_settings_fields($fields){

        if( function_exists('edd_get_option') && empty( edd_get_option('donor_covers_fee') ) ){
            $fields[] = array(
                'id'            => '_enable_donor_covers_fee',
                'type'          => 'checkbox',
                'label'         => __('Enable Donor Cover Fee', 'philanthropy'),
            );
        }

        $fields[] = array(
            'id'            => '_platform_fee',
            'type'          => 'text',
            'label'         => __('Platform Fee', 'philanthropy'),
        );

        return $fields;
    }


    public function includes(){

    }

}

G4G_Covers_Fee::init();