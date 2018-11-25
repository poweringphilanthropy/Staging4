<?php
/**
 * PP_Shortcodes Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Shortcodes
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Shortcodes class.
 */
class PP_Shortcodes {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Shortcodes();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        
        /* Shortcodes */
        add_shortcode( 'pp_edd_product_form', array($this, 'pp_edd_product_form_shortcode') );
        add_shortcode( 'charitable_my_edd_products', array($this, 'pp_charitable_my_edd_products_shortcode') );
        add_shortcode( 'charitable_my_tribe_events', array($this, 'pp_charitable_my_tribe_events_shortcode') );
    }

    /**
     * Callback for the 'pp_edd_product_form' shortcode.
     *
     * @param   array   $atts
     * @return  string
     * @since   1.0.0
     */
    public function pp_edd_product_form_shortcode( $atts ) {
        $args = shortcode_atts( array(), $atts, 'pp_edd_product_form' );     

        /* If the user is logged out, redirect to login/registration page. */
        if ( ! is_user_logged_in() ) {

            $url = charitable_get_permalink( 'login_page' );
            $url = esc_url_raw( add_query_arg( array( 'redirect_to' => charitable_get_current_url() ), $url ) );

            wp_safe_redirect( $url );

            exit();
        }

        /* If the user has not created any campaigns yet, do not display the form to them. */
        $user = new Charitable_User( wp_get_current_user() );
        
        if ( ! $user->has_current_campaigns() ) {
            $template_name = 'shortcodes/product-form/no-campaigns.php';
        }
        else {
            $template_name = 'shortcodes/product-form.php';
        } 


        ob_start();

        $template = new PP_Toolkit_Template( 'shortcodes/product-form.php', false );
        $template->set_view_args( array( 
            'form' => new PP_EDD_Product_Form( $args ) 
        ) );
        $template->render();

        return apply_filters( 'pp_edd_product_form_shortcode', ob_get_clean() );        
    }

    /**
     * Callback for the charitable_my_edd_products shortcode.
     * 
     * @param   array   $atts
     * @return  string
     * @since   1.0.0
     */
    public function pp_charitable_my_edd_products_shortcode( $atts ) {
        $args = shortcode_atts( array(), $atts, 'charitable_my_edd_products' );       

        /* If the user is logged out, redirect to login/registration page. */
        if ( ! is_user_logged_in() ) {

            $url = charitable_get_permalink( 'login_page' );
            $url = esc_url_raw( add_query_arg( array( 'redirect_to' => charitable_get_current_url() ), $url ) );

            wp_safe_redirect( $url );

            exit();
        } 

        ob_start();

        $template = new PP_Toolkit_Template( 'shortcodes/my-products.php', false );
        $template->set_view_args( $args );
        $template->render();

        return apply_filters( 'charitable_my_edd_products_shortcode', ob_get_clean() );
    }

    /**
     * Callback for the charitable_my_tribe_events shortcode.
     * 
     * @param   array   $atts
     * @return  string
     * @since   1.0.0
     */
    public function pp_charitable_my_tribe_events_shortcode( $atts ) {
        $args = shortcode_atts( array(), $atts, 'charitable_my_tribe_events' );       

        /* If the user is logged out, redirect to login/registration page. */
        if ( ! is_user_logged_in() ) {

            $url = charitable_get_permalink( 'login_page' );
            $url = esc_url_raw( add_query_arg( array( 'redirect_to' => charitable_get_current_url() ), $url ) );

            wp_safe_redirect( $url );

            exit();
        } 

        ob_start();

        $template = new PP_Toolkit_Template( 'shortcodes/my-events.php', false );
        $template->set_view_args( $args );
        $template->render();

        return apply_filters( 'charitable_my_tribe_events_shortcode', ob_get_clean() );
    }

    public function includes(){

    }

}

PP_Shortcodes::init();