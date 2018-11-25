<?php
/************* CRUSTY STUFF *************/
/* THIS CAN BE REMOVED WHEN EDD ADDS A  */
/* FILTER TO THE SUMMARY                */
/****************************************/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}


class PP_EDD_Payment_Fees {
    private static $fees;
    private function __construct(){}
    public static function set( $fees ) {
        self::$fees = $fees;
    }
    public static function get() {
        return self::$fees;
    }
}