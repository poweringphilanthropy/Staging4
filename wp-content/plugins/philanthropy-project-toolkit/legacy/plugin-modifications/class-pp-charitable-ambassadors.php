<?php
/**
 * PP_Charitble_Ambassadors Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Charitble_Ambassadors
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Charitble_Ambassadors class.
 */
class PP_Charitble_Ambassadors {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Charitble_Ambassadors();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'wp_ajax_create_dummy_post', array($this, 'create_dummy_post_id') );
        add_filter( 'charitable_ambassadors_form_submission_buttons_primary_new_text', array($this, 'form_submission_buttons_primary_new_text'), 10, 1 );

        add_action( 'charitable_ambassadors_shortcodes_start', array($this, 'on_charitable_ambassadors_start'), 10, 1 );
    }

    public function on_charitable_ambassadors_start(Charitable_Ambassadors_Shortcodes $class){
        remove_shortcode( 'charitable_my_campaigns' );
        add_shortcode( 'charitable_my_campaigns', array( $this, 'charitable_my_campaigns_shortcode' ) );
    }


    /**
     * The callback method for the charitable_my_campaigns shortcode.
     *
     * This receives the user-defined attributes and passes the logic off to the template.
     *
     * @param   array $atts User-defined shortcode attributes.
     * @return  string
     * @access  public
     * @static
     * @since   1.0.0
     */
    public function charitable_my_campaigns_shortcode( $atts ) {
        $args = shortcode_atts( array(), $atts, 'charitable_my_campaigns' );

        /* If the user is logged out, redirect to login/registration page. */
        if ( ! is_user_logged_in() ) {

            echo Charitable_Login_Shortcode::display();

            return;
        }

        ob_start();

        pp_toolkit_template( 'shortcodes/my-campaigns.php', $args );

        return apply_filters( 'charitable_my_campaigns_shortcode', ob_get_clean() );

    }

    /**
     * Create dummy post id for preview campaign
     * @return [type] [description]
     */
    public function create_dummy_post_id(){
        $id = wp_insert_post(array('post_type' => 'campaign'));
        if( is_wp_error( $id ) ) {
            $id = 0;
        }

        echo $id;
        exit();
    }

    public function form_submission_buttons_primary_new_text($text){
        $text = __('Launch Campaign', 'philanthropy');
        return $text;
    }

    public function includes(){

    }

}

PP_Charitble_Ambassadors::init();