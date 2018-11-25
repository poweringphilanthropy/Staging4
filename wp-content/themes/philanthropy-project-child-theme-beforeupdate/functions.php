<?php
/*
 * Main theme functions 
 * for pp project
 * @lafifastahdziq
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'PP_Reach' ) ) :

/**
 * Main PP_Reach Class
 *
 * @class PP_Reach
 * @version 1.0
 */
final class PP_Reach {

    /**
     * @var string
     */
    public $version = '1.0';

    /**
     * @var PP_Reach The single instance of the class
     * @since 1.0
     */
    protected static $_instance = null;

    /**
     * Main PP_Reach Instance
     *
     * Ensures only one instance of PP_Reach is loaded or can be loaded.
     *
     * @since 1.0
     * @static
     * @return PP_Reach - Main instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * PP_Reach Constructor.
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();

        do_action( 'pp_reach_loaded' );
    }

    /**
     * Hook into actions and filters
     * @since  1.0
     */
    private function init_hooks() {
        
    }

    /**
     * Define PPR Constants
     */
    private function define_constants() {

        $this->define( 'PP_REACH_THEME_FILE', __FILE__ );
        $this->define( 'PP_REACH_THEME_BASENAME', plugin_basename( __FILE__ ) );
        $this->define( 'PP_REACH_VERSION', $this->version );

        /**
         * not sure, but seems it required
         */
        $this->define( 'PHILANTHROPY_PROJECT_CAMPAIGN_ID', 4574 );
    }

    /**
     * Define constant if not already set
     * @param  string $name
     * @param  string|bool $value
     */
    private function define( $name, $value ) {
        if ( ! defined( $name ) ) {
            define( $name, $value );
        }
    }

    /**
     * What type of request is this?
     * string $type ajax, frontend or admin
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin' :
                return is_admin();
            case 'ajax' :
                return defined( 'DOING_AJAX' );
            case 'cron' :
                return defined( 'DOING_CRON' );
            case 'frontend' :
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    /**
     * Include required core files used in admin and on the frontend.
     */
    public function includes() {
        // all public includes
        include_once( 'inc/functions-pp_reach.php' );
        include_once( 'inc/class-ppr_theme.php' );
        include_once( 'inc/class-ppr_customizer.php' );
        include_once( 'inc/class-ppr_theme_modifications.php' );

        if ( $this->is_request( 'admin' ) ) {
            // include_once( 'inc/admin/..*.php' );
        }

        if ( $this->is_request( 'ajax' ) ) {
            // include_once( 'inc/ajax/..*.php' );
        }

        if ( $this->is_request( 'frontend' ) ) {
            // include_once( 'inc/walker.class.php' );
        }
    }

    /**
     * Get the theme url.
     * @return string
     */
    public function theme_url() {
        return get_stylesheet_directory_uri();
    }

    /**
     * Get the theme path.
     * @return string
     */
    public function theme_path() {
        return get_stylesheet_directory();
    }

    /**
     * Get folder inside theme path
     * @param  string  $type          [description]
     * @param  boolean $url [description]
     * @return [type]                 [description]
     */
    public function get_path( $type = '', $url = false ) {

        $base = $url ? $this->theme_url() : $this->theme_path();
        
        switch ($type) {

            case 'css':
                $path = $base . '/assets/css';
                break;

            case 'js':
                $path = $base . '/assets/js';
                break;
            
            default:
                $path = $base . '/' . $type;
                break;
        }

        return $path;
    }

    /**
     * Get Ajax URL.
     * @return string
     */
    public function ajax_url() {
        return admin_url( 'admin-ajax.php', 'relative' );
    }

}

endif;

/**
 * Returns the main instance of PPR to prevent the need to use globals.
 *
 * @since  1.0
 * @return PP_Reach
 */
function PPR() {
    return PP_Reach::instance();
}

PPR();