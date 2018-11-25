<?php
/**
 * PP_Ninja_Form Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Ninja_Form
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Ninja_Form class.
 */
class PP_Ninja_Form {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Ninja_Form();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        
        add_action('init', array($this, 'ninja_forms_register_example'));
    }

    public function ninja_forms_register_example() {
        add_action('ninja_forms_post_process', array($this, 'ninja_forms_example'));
    }

    public function ninja_forms_example() {
        global $ninja_forms_processing;

        $form_id = $ninja_forms_processing->get_form_ID();

        // TODO | not use hardcode
        if (!in_array($form_id, [1,9]))
            return;

        switch ($form_id) {
            case 1:
                $cc_user_id = $ninja_forms_processing->get_field_value(17);
                $cc_user_email = get_userdata($cc_user_id)->data->user_email;
                $cc_nonce = $ninja_forms_processing->get_field_value(18);
                $update_field = 16;
                break;

            case 9:
                $cc_user_id = $ninja_forms_processing->get_field_value(20);
                $cc_user_email = get_userdata($cc_user_id)->data->user_email;
                $cc_nonce = $ninja_forms_processing->get_field_value(21);
                $update_field = 19;
                break;
        }


        if (wp_verify_nonce($cc_nonce, $cc_user_email.$cc_user_id)) {
            $ninja_forms_processing->update_field_value($update_field, $cc_user_email);;
        } else {
            $ninja_forms_processing->add_error('are-you-a-bot', 'There was an unknown error with your submission. Please try again later.');
        }

    }

    public function includes(){

    }

}

PP_Ninja_Form::init();