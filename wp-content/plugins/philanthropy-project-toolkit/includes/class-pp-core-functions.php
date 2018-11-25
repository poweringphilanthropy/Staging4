<?php
/**
 * PP_Core_Functions Class.
 *
 * @class       PP_Core_Functions
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Core_Functions class.
 */
class PP_Core_Functions {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Core_Functions();
        }

        return $instance;
    }

    private $functions_directory_path;

    /**
     * Constructor
     */
    public function __construct() {

        $this->functions_directory_path = PP()->plugin_path() . '/includes/functions';

        $this->includes();
    }

    public function includes(){
        include( $this->functions_directory_path . '/helper-functions.php' );
        include( $this->functions_directory_path . '/campaign-functions.php' );
        include( $this->functions_directory_path . '/account-functions.php' );
    }

}

PP_Core_Functions::init();