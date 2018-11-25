<?php
/**
 * PP_Widgets Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Widgets
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Widgets class.
 */
class PP_Widgets {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Widgets();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        add_action( 'widgets_init', array( $this, 'register_widget' ) );
    }

    /**
     * Register the Campaign Events widget.
     *
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function register_widget() {
        register_widget( 'PP_Widget_Campaign_Events' );
        register_widget( 'PP_Widget_Campaign_Sponsors' );
        register_widget( 'PP_Widget_Top_Fundraiser' );
        register_widget( 'wpb_widget' );
        register_widget( 'wpb_fp_widget' );
    }

    public function includes(){
        include_once( 'widgets/widget-campaign-events.php' );
        include_once( 'widgets/widget-campaign-sponsors.php' );
        include_once( 'widgets/widget-top-fundraiser.php' );
        include_once( 'widgets/widget-wpb.php' );
        include_once( 'widgets/widget-fp-wpb.php' );
    }

}

PP_Widgets::init();