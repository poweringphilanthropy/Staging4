<?php
/**
 * PP_Toolkit_Shortcodes Class.
 *
 * @class       PP_Toolkit_Shortcodes
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Toolkit_Shortcodes class.
 */
class PP_Toolkit_Shortcodes {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Toolkit_Shortcodes();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        
    }

    public function includes(){

    }

}

PP_Toolkit_Shortcodes::init();