<?php
/**
 * PP_Rest_Api Class.
 *
 * @class       PP_Rest_Api
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Rest_Api class.
 */
class PP_Rest_Api {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Rest_Api();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ), 10 );
        add_filter( 'rest_url_prefix', array($this, 'change_rest_url_prefix'), 10, 1 );

        add_filter( 'edd_payment_gateways', array($this, 'add_gateway_from_api'), 10, 1 );

        /**
         * Adopt basic auth
         */
        add_filter( 'determine_current_user', array($this, 'json_basic_auth_handler'), 20 );
        add_filter( 'json_authentication_errors', array($this, 'json_basic_auth_error') );
    }

    public function change_rest_url_prefix($prefix){

        $prefix = 'api';

        return $prefix;
    }

    public function add_gateway_from_api($gateway){

        $gateway['rest-api'] = array(
            'admin_label'    => __( 'Rest Api', 'easy-digital-downloads' ),
            // 'checkout_label' => __( 'Rest api', 'easy-digital-downloads' )
        );

        return $gateway;
    }

    public function json_basic_auth_handler( $user ) {
        global $wp_json_basic_auth_error;

        $wp_json_basic_auth_error = null;

        // Don't authenticate twice
        if ( ! empty( $user ) ) {
            return $user;
        }

        // Check that we're trying to authenticate
        if ( !isset( $_SERVER['PHP_AUTH_USER'] ) ) {
            return $user;
        }

        $username = $_SERVER['PHP_AUTH_USER'];
        $password = $_SERVER['PHP_AUTH_PW'];

        /**
         * In multi-site, wp_authenticate_spam_check filter is run on authentication. This filter calls
         * get_currentuserinfo which in turn calls the determine_current_user filter. This leads to infinite
         * recursion and a stack overflow unless the current function is removed from the determine_current_user
         * filter during authentication.
         */
        remove_filter( 'determine_current_user', array($this, 'json_basic_auth_handler'), 20 );

        $user = wp_authenticate( $username, $password );

        add_filter( 'determine_current_user', array($this, 'json_basic_auth_handler'), 20 );

        if ( is_wp_error( $user ) ) {
            $wp_json_basic_auth_error = $user;
            return null;
        }

        $wp_json_basic_auth_error = true;

        return $user->ID;
    }

    public function json_basic_auth_error( $error ) {
        // Passthrough other errors
        if ( ! empty( $error ) ) {
            return $error;
        }

        global $wp_json_basic_auth_error;

        return $wp_json_basic_auth_error;
    }

    /**
     * Register REST API routes.
     * @since 2.6.0
     */
    public function register_rest_routes() {

        $controllers = apply_filters( 'pp_rest_routes', array(
            'PP_Rest_Users',
            'PP_Rest_Campaigns',
            'PP_Rest_Payment',
        ) );

        foreach ( $controllers as $controller ) {
            $this->$controller = new $controller();
            $this->$controller->register_routes();
        }
    }

    public function includes(){
        include_once('api/class-pp-rest-users.php' );
        include_once('api/class-pp-rest-campaigns.php' );
        include_once('api/class-pp-rest-payment.php' );
    }

}

PP_Rest_Api::init();