<?php
/**
 * PPR_Theme_Modifications Class.
 *
 * @class       PPR_Theme_Modifications
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PPR_Theme_Modifications class.
 */
class PPR_Theme_Modifications {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PPR_Theme_Modifications();
        }

        return $instance;
    }

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

        add_filter( 'reach_script_dependencies', array( $this, 'setup_script_dependencies' ) );

	}

    /**
     * Register scripts required for crowdfunding functionality.
     *
     * @param   array       $dependencies
     * @return  array
     * @access  public
     * @since   1.0.0
     */
    public function setup_script_dependencies( $dependencies ) {
        
        if ( is_page('your-campaigns') || charitable_is_page( 'campaign_editing_page' ) ) {
            wp_register_script( 'countdown-plugin', get_template_directory_uri() . '/js/vendors/jquery-countdown/jquery.plugin.min.js', array( 'jquery' ), reach_get_theme()->get_theme_version(), true );
            wp_register_script( 'countdown', get_template_directory_uri() . '/js/vendors/jquery-countdown/jquery.countdown.min.js', array( 'countdown-plugin' ), reach_get_theme()->get_theme_version(), true );

            $dependencies[] = 'countdown';
        }

        return $dependencies;
    }

	public function includes(){

	}

}

PPR_Theme_Modifications::init();