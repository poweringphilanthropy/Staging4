<?php
/**
 * PP_Account_Route Class.
 *
 * @class       PP_Account_Route
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Account_Route class.
 */
class PP_Account_Route {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Account_Route();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'wp_router_generate_routes', array( $this, 'generate_routes'), 10, 1);
        add_filter( 'body_class', array( $this, 'add_body_classes' ) );
    }

    public function add_body_classes($classes){

        if(! get_query_var( 'WP_Route', false )){
            return $classes;
        }

        $route = get_query_var( 'WP_Route', false );
        if( strpos( $route, 'pp-route-account' ) !== false ) {
            $classes[] = 'user-dashboard';
        }
        

        return $classes;
    }

    public function generate_routes(WP_Router $router){

        $routes = apply_filters( 'pp_account_endpoints', array() ); // TODO | Move profile .etc to this routes

        // echo "<pre>";
        // print_r($routes);
        // echo "</pre>";
        // exit();


        $permission = apply_filters( 'pp_account_endpoints_permission', true );

        if(empty($routes))
            return;

        foreach ($routes as $id => $args) {

            $route_args = array(
                'path' => '^account/' . $id,
                'query_vars' => array(
                    'endpoint' => $id,
                ),
                'title' => isset($args['title']) ? $args['title'] : __('Account', 'pp'),
                'access_callback' => $permission,
            );

            if(isset($args['permission'])){
                $route_args['access_callback'] = $args['permission'];
            }

            if(isset($args['callback'])){
                $route_args['page_callback'] = $args['callback'];
            }

            if(isset($args['template'])){
                $route_args['template'] = $args['template'];
            }

            $router->add_route('pp-route-account-' . $id, apply_filters( 'pp_account_endpoint_args', $route_args, $id ) );
        }
    }

    public function includes(){

    }

}

PP_Account_Route::init();