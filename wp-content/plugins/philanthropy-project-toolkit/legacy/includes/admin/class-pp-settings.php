<?php
/**
 * PP_Settings Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Settings
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Settings class.
 */
class PP_Settings {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Settings();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_filter( 'charitable_settings_tabs', array( $this, 'add_pp_setting_section' ), 20 );
        // add_filter( 'charitable_pp_toolkit_admin_settings_groups', array( $this, 'add_pp_toolkit_settings_group' ), 20 );

        add_filter( 'charitable_settings_tab_fields_pp_toolkit', array( $this, 'add_pp_toolkit_settings_fields' ) );
    }

    public function add_pp_setting_section($sections){

        $sections['pp_toolkit'] = __('PP Toolkit', 'pp-toolkit');

        return $sections;
    }

    public function add_pp_toolkit_settings_group( $groups ) {
        $groups[] = 'pp_toolkit';

        echo "<pre>";
        print_r($groups);
        echo "</pre>";
        exit();

        return $groups;
    }

    public function add_pp_toolkit_settings_fields( $fields ) {
        if ( ! charitable_is_settings_view( 'pp_toolkit' ) ) {
            return array();
        }

        $fields = array(
            'section_pp_toolkit_payout' => array(
                'title'             => __( 'Payout Settings', 'pp-toolkit' ),
                'type'              => 'heading',
                'priority'          => 2,
            ),
            'nonprofit_endpoint' => array(
                'title'             => __( 'Non profit options endpoint', 'pp-toolkit' ),
                'type'              => 'text',
                'priority'          => 4,
                'default'           => 'https://poweringphilanthropy.com/wp-json/pwph/get_non_profit_options',
            ),
            'platform_fee' => array(
                'title'             => __( 'Platform Fee', 'pp-toolkit' ),
                'type'              => 'number',
                'priority'          => 5,
                'default'           => 5,
                'min'               => 0,
                'max'               => 1000,
                'required'          => true,
            )
        );

        return $fields;
    }

    public function includes(){

    }

}

PP_Settings::init();