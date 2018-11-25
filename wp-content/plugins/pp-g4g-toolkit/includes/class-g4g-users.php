<?php
/**
 * G4G_Users Class.
 *
 * @class       G4G_Users
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * G4G_Users class.
 */
class G4G_Users {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new G4G_Users();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_filter( 'charitable_user_fields', array($this, 'g4g_charitable_user_fields'), 30, 2 );

    }

    public function g4g_charitable_user_fields( $fields, $form ) {

        $fields[ 'chapter' ][ 'attrs' ] = array(
            'label' => __('Fraternity/Sorority Name', 'philanthropy'),
            'data-source' => esc_attr( wp_json_encode( g4g_get_parent_campaign_group_names() ) ),
            'class' => 'autocomplete allow_only ',
        );

        $fields[ 'organisation' ][ 'attrs' ] = array(
            'label' => __('College/University Name', 'philanthropy'),
            'data-source' => esc_attr( wp_json_encode( g4g_get_college_names() ) ),
            'class' => 'autocomplete allow_only',
        );

        return $fields;
    }


    public function includes(){

    }

}

G4G_Users::init();