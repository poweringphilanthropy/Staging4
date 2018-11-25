<?php
/**
 * PP_Templates Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Templates
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Templates class.
 */
class PP_Templates {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Templates();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_filter( 'charitable_locate_template', array($this, 'change_charitable_locate_template'), 10, 2 );
    }

    /**
     * overrides charitable template to load from our template
     * @param  [type] $template       [description]
     * @param  [type] $template_names [description]
     * @return [type]                 [description]
     */
    public function change_charitable_locate_template($template, $template_names){
        if(in_array($template_names[0], pp_get_override_charitable_templates() )){
            $template = pp_toolkit()->directory_path . 'templates/' . $template_names[0];
        }

        return $template;
    }

    public function includes(){

    }

}

PP_Templates::init();


/**
 * PP_Toolkit_Template
 * Custom template path
 * @since       1.0.0
 */
class PP_Toolkit_Template extends Charitable_Template {      

    /**
     * Set theme template path. 
     *
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    // public function get_theme_template_path() {
    //     return trailingslashit( apply_filters( 'pp_toolkit_theme_template_path', 'pp_toolkit' ) );
    // }

    /**
     * Return the base template path.
     *
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function get_base_template_path() {
        return pp_toolkit()->directory_path . 'templates/';
    }
}

/**
 * Helper function to load our custom charitable template under /templates/charitable
 * @param  [type] $template_name [description]
 * @param  array  $args          [description]
 * @return [type]                [description]
 */
function pp_toolkit_template( $template_name, array $args = array() ) {
    if ( empty( $args ) ) {
        $template = new PP_Toolkit_Template( $template_name );
    } else {
        $template = new PP_Toolkit_Template( $template_name, false );
        $template->set_view_args( $args );
        $template->render();
    }

    return $template;
}

/**
 * Return the template path if the template exists. Otherwise, return default.
 *
 * @param   string|string[] $template
 * @return  string The template path if the template exists. Otherwise, return default.
 * @since   1.0.0
 */
function pp_toolkit_get_template_path( $template, $default = '' ) {
    $t = new PP_Toolkit_Template( $template, false );
    $path = $t->locate_template();

    if ( ! file_exists( $path ) ) {
        $path = $default;
    }

    return $path;
}