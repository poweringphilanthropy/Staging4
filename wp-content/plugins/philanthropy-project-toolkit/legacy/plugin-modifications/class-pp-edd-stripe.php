<?php
/**
 * PP_EDD_Stripe Class.
 *
 * @class       PP_EDD_Stripe
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_EDD_Stripe class.
 */
class PP_EDD_Stripe {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_EDD_Stripe();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_filter( 'edds_create_charge_args', array($this, 'change_edd_strpe_args'), 10, 2 );
    }

    public function change_edd_strpe_args($args, $purchase_data){

        if(isset($args['description'])){
            $args['description'] = 'Greeks4Good.com';
        }
        if(isset($args['statement_descriptor'])){
            $args['statement_descriptor'] = 'Greeks4Good.com';
        }

        return $args;
    }

    public function includes(){

    }

}

PP_EDD_Stripe::init();