<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PP_Payout_Check_Form' ) ) : 

/**
 * PP_Payout_Check_Form
 *
 * @since       1.0.0
 */
class PP_Payout_Check_Form extends Charitable_Form {

    /**
     * @var     string
     * @access  protected
     */
    protected $nonce_action = 'pp_payout_check_form';

    /**
     * @var     string
     * @access  protected
     */
    protected $nonce_name = '_pp_payout_check_form_nonce';

    public function __construct() {    
        $this->id = uniqid();   
        $this->attach_hooks_and_filters();          
    }

    /**
     * Set up callbacks for actions and filters. 
     *
     * @return  void
     * @access  protected
     * @since   1.0.0
     */
    protected function attach_hooks_and_filters() {
        // parent::attach_hooks_and_filters(); 
    } 

    /**
     * Return the array of fields displayed in the payout_check form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_fields() {
	    
        $payout_check_fields = apply_filters( 'pp_payout_check_fields', array(     
            
            'payout_check_fields' => [
                'legend'   => __('Payout Check', 'pp-toolkit'),
                'type'     => 'fieldset',
                'priority' => 10,
                'page'     => 'campaign_details',
                'fields'   => [
                    'xxxx' => [
                        'label'         => __('xxx:', 'pp-toolkit') ,
                        'type'          => 'text',
                        'priority'      => 43,
                        'required'      => false,
                        'fullwidth'     => true,
                        // 'value'         => $form->get_campaign_value( 'payout_payable_name' ),
                        'data_type'     => 'meta',
                        'editable'      => true,
                    ]
                ]
            ],          
        ), $this );

        uasort( $payout_check_fields, 'charitable_priority_sort' );

        return $payout_check_fields;
    }
}

endif; // End class_exists check