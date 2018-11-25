<?php
/**
 * PP_Account Class.
 *
 * @class       PP_Account
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Account class.
 */
class PP_Account {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Account();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        // add_filter( 'the_content', array($this, 'fix_empty_paragraph_on_custom_shortcode'), 100);

        // add_filter( 'pp_account_endpoints', array($this, 'account_endpoints'), 10, 1 );
        // add_filter( 'pp_get_account_menu', array($this, 'account_menus'), 10, 1 );

    }

    public function fix_empty_paragraph_on_custom_shortcode($content){
        // define your shortcodes to filter, '' filters all shortcodes
        $shortcodes = array( 'charitable_my_campaigns' );

        foreach ( $shortcodes as $shortcode ) {

            $array = array (
                '<p>[' . $shortcode => '[' .$shortcode,
                '<p>[/' . $shortcode => '[/' .$shortcode,
                $shortcode . ']</p>' => $shortcode . ']',
                $shortcode . ']<br />' => $shortcode . ']'
            );

            $content = strtr( $content, $array );
        }

        return $content;
    }

    public function account_endpoints($endpoints){

        $endpoints['campaigns'] = array(
            'title' => __('Your Campaigns', 'pp'),
            'callback' => array($this, 'campaigns_callback'),
            'template' => 'page-templates/user-dashboard.php',
        );

        return $endpoints;
    }

    public function account_menus($menu_items){

        $menu_items['campaigns'] = array(
            'label' => __('Your Campaigns', 'pp'),
            'url' => home_url( 'account/campaigns' ),
            'icon' => PP()->get_image_url('accounts/icon-your-campaigns.png'),
            'priority' => 20,
        );

        return $menu_items;
    }

    public function campaigns_callback(){
        return do_shortcode( '[charitable_my_campaigns]' );
    }

    public function includes(){
        include_once( PP()->plugin_path() . '/routes/class-pp-account-route.php');
    }

}

PP_Account::init();