<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PP_Payout_Options_Form' ) ) : 

/**
 * PP_Payout_Options_Form
 *
 * @since       1.0.0
 */
class PP_Payout_Options_Form extends Charitable_Form {


    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Payout_Options_Form();
        }

        return $instance;
    }

    public function __construct() {    
        $this->id = 'payout_options';   
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

        add_action('wp_ajax_add_payout_options_form', [$this, 'render_payout_options_form']);
        add_action('wp_ajax_nopriv_add_payout_options_form', [$this, 'render_payout_options_form']);

        // add_action( 'charitable_campaign_submission_save_page_campaign_details', array( $this, 'check_required' ), 9, 1 );

        add_filter( 'charitable_campaign_submission_fields_map', array($this, 'register_our_data_type'), 10, 3 );
        add_filter( 'charitable_form_missing_fields', array($this, 'check_form_missing_fields'), 10, 4 );
    }

    public function check_form_missing_fields($missing, Charitable_Form $form, $fields, $submitted){
        
        if($form->get_form_action() != 'save_campaign'){
            return $missing;
        }

        if(isset($submitted['payment_option_locked']) && ($submitted['payment_option_locked'] == 'yes') ){
            return $missing;
        }

        $fields = $this->get_merged_fields();

        foreach ( $this->get_required_fields( $fields ) as $key => $field ) {

            $label = isset( $field['label'] ) ? $field['label'] : $key;

            if($field['type'] == 'checkbox'){
                $value  = (isset($submitted[ $key ])) ? $submitted[ $key ] : '';
                if((empty($value) || ($value != $field['value']))){
                    $missing[] = $label;
                }

            }

            /* We already have a value for this field. */
            if ( ! empty( $field['value'] ) ) {
                continue;
            }

            $exists = isset( $submitted[ $key ] );

            /* Verify that a value was provided. */
            if ( $exists ) {
                $value  = $submitted[ $key ];
                $exists = ! empty( $value ) || ( is_string( $value ) && strlen( $value ) );
            }

            /* If a value was not provided, check if it's in the $_FILES array. */
            if ( ! $exists ) {
                $exists = ( 'picture' == $field['type'] && isset( $_FILES[ $key ] ) && ! empty( $_FILES[ $key ]['name'] ) );
            }

            // new organization fields
            $new_orgs_fields = array('payout_organization_name', 'payout_address');
            if ( ! $exists && in_array($key, $new_orgs_fields) ) {
                $exists = isset($submitted['connected_stripe_id']) && !empty($submitted['connected_stripe_id']);
            }

            // $exists = apply_filters( 'charitable_required_field_exists', $exists, $key, $field, $submitted, $this );

            if ( ! $exists ) {

                $label = isset( $field['label'] ) ? $field['label'] : $key;

                $missing[] = $label;
            }
        }

        return $missing;
    }

    // public function check_required(Charitable_Ambassadors_Campaign_Form $form){

    //     if(!isset($_POST['payout_option_type']))
    //         return;

    //     /* If required fields are missing, stop. */
    //     if ( ! $form->check_required_fields( $this->get_merged_fields() ) ) {
    //         remove_action( 'charitable_campaign_submission_save_page_campaign_details', array( 'Charitable_Ambassadors_Campaign_Form', 'save_campaign_details' ), 10 );
    //         return;
    //     }
    // }
 
    public function get_merged_fields(){
        if(isset($_POST['payout_option_type']) && !empty($_POST['payout_option_type'])){
            $all_fields = $this->get_fields_by_type($_POST['payout_option_type']);
        } else {
            // get all fields
            $all_fields = array_merge(
                $this->get_payout_check_fields(), 
                $this->get_payout_venmo_fields(), 
                $this->get_payout_direct_fields() 
            );
        }

        // merge fields with fieldset fields
        $merged_fields = array();
        foreach ($all_fields as $key => $section ) {
            if ( isset( $section['fields'] ) ) {
                $merged_fields = array_merge( $merged_fields, $section['fields'] );
            } else {
                $merged_fields[ $key ] = $section;
            }
        }

        return $merged_fields;
    }

    public function register_our_data_type($fields, $submitted, Charitable_Ambassadors_Campaign_Form $form){

        foreach ( $this->get_merged_fields() as $key => $field ) {
            $fields = $form->sort_field_by_data_type( $key, $field, $fields );
        }

        return $fields;
    }

    /**
     * Display the payout form. This is called by an AJAX action.
     *
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function render_payout_options_form() {
        /* Run a security check first to ensure we initiated this action. */
        check_ajax_referer('pp-payout-options-form', 'nonce');
	
        $key = $_REQUEST['key'];
        $template = new PP_Toolkit_Template("form-fields/payout-form.php", false);
        $template->set_view_args([
             'form'  => PP_Payout_Options_Form::init(),
             'fields' => $this->get_fields_by_type($key)
         ]);
        $template->render();

        wp_die();
    }

    public function get_fields_by_type($type){
        $fields = array();

        switch ($type) {
            case 'check':
                $fields = $this->get_payout_check_fields();
                break;

            case 'venmo':
                $fields = $this->get_payout_venmo_fields();
                break;

            case 'direct':
                $fields = $this->get_payout_direct_fields();
                break;
        }

        return apply_filters( 'pp_get_payout_type_fields', $fields, $type );
    }

    /**
     * Return the array of fields displayed in the payout_check form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_payout_check_fields() {
        
        $payout_check_fields = apply_filters( 'pp_payout_check_fields', array(     
            
            'payout_check_fields' => [
                'legend'   => __('Payment to you via check', 'pp-toolkit'),
                'type'     => 'fieldset',
                'priority' => 10,
                'page'     => 'campaign_details',
                'fields'   => [
                    'payout_option_type' => array(
                        'type'          => 'hidden',
                        'priority'      => 0,
                        'value'         => 'check'
                    ),
                    'payout_payable_name' => [
                        'label'         => __('Make check payable to:', 'pp-toolkit') ,
                        'type'          => 'text',
                        'priority'      => 10,
                        'required'      => false,
                        'fullwidth'     => true,
                        'value'         => $this->get_field_value( 'payout_payable_name' ),
                        'data_type'     => 'meta',
                        'editable'      => true,
                        'class'         => 'black_text larger-font',
                    ],
                ]
            ],  
            'mail_check_to' => [
                'legend'   => __('Mail My Check To: ', 'pp-toolkit'),
                'type'     => 'fieldset',
                'priority' => 20,
                'page'     => 'campaign_details',
                'class'         => 'header_black_text header_larger-font',
                'fields'   => [
                    'payout_first_name' => [
                        'label'         => __('First Name', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 21,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_first_name' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    'payout_last_name' => [
                        'label'         => __('Last Name', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 22,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_last_name' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    'payout_address' => [
                        'label'         => __('Address', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 23,
                        'required'      => false,
                        'fullwidth'     => true,
                        'value'         => $this->get_field_value( 'payout_address' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    'payout_city' => [
                        'label'         => __('City', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 24,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_city' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    
                    'payout_state' => [
                        'label'         => __('State', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 25,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_state' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    
                    'payout_zipcode' => [
                        'label'         => __('Zip Code', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 26,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_zipcode' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                ]
            ],           
        ), $this );

        uasort( $payout_check_fields, 'charitable_priority_sort' );

        return $payout_check_fields;
    }

    /**
     * Return the array of fields displayed in the payout_check form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_payout_venmo_fields() {
        
        $payout_venmo_fields = apply_filters( 'pp_payout_venmo_fields', array(     
            
            'payout_venmo_fields' => [
                'legend'   => __('Payment to you via Venmo', 'pp-toolkit'),
                'type'     => 'fieldset',
                'priority' => 10,
                'page'     => 'campaign_details',
                'fields'   => [
                    'payout_option_type' => array(
                        'type'          => 'hidden',
                        'priority'      => 0,
                        'value'         => 'venmo'
                    ),
                    'payout_venmo_id' => [
                        'label'         => __('Venmo ID', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 18,
                        'required'      => false,
                        'fullwidth'     => true,
                        'value'         => $this->get_field_value( 'payout_venmo_id' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    'payout_first_name' => [
                        'label'         => __('First Name', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 21,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_first_name' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    'payout_last_name' => [
                        'label'         => __('Last Name', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 22,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_last_name' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    'payout_email' => [
                        'label'         => __('Email Address', 'pp-toolkit') ,
                        'type'          => 'text',
                        'priority'      => 23,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_email' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    'payout_phone' => [
                        'label'         => __('Phone Number', 'pp-toolkit') ,
                        'type'          => 'text',
                        'priority'      => 24,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_phone' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                ]
            ],          
        ), $this );

        uasort( $payout_venmo_fields, 'charitable_priority_sort' );

        return $payout_venmo_fields;
    }

    /**
     * Return the array of fields displayed in the payout_check form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_payout_direct_fields() {
	    
        $payout_direct_fields = apply_filters( 'pp_payout_direct_fields', array(     

            'payout_direct_fields' => [
                'legend'   => __('Registered Non Profits:', 'pp-toolkit'),
                'type'     => 'fieldset',
                'priority' => 10,
                'page'     => 'campaign_details',
                'class'         => 'header_black_text header_larger-font',
                'id'            => 'registered_nonprofit-container',
                'fields'   => [
                    'payout_option_type' => array(
                        'type'          => 'hidden',
                        'priority'      => 0,
                        'value'         => 'direct'
                    ),
                    'connected_stripe_id' => [
                        'label'         => __('Organization Name:', 'pp-toolkit') ,
                        'type'          => 'select-non-profit',
                        'priority'      => 10,
                        'required'      => false,
                        'fullwidth'     => true,
                        'value'         => $this->get_field_value( 'connected_stripe_id' ),
                        'data_type'     => 'meta',
                        'editable'      => true,
                        'id'            => 'select-non-profit',
                        'class'         => 'black_text larger-font select-non-profit',
                    ],
                ]
            ],
            'mail_check_to' => [
                'legend'   => __('Unregistered Nonprofits: ', 'pp-toolkit'),
                'type'     => 'fieldset',
                'priority' => 20,
                'page'     => 'campaign_details',
                'class'         => 'header_black_text header_larger-font',
                'id'            => 'unregistered_nonprofit-container',
                'fields'   => [
                    'payout_organization_name' => [
                        'label'         => __('Organization Name:', 'pp-toolkit') ,
                        'type'          => 'text',
                        'priority'      => 10,
                        'required'      => false,
                        'fullwidth'     => true,
                        'value'         => $this->get_field_value( 'payout_organization_name' ),
                        'data_type'     => 'meta',
                        'editable'      => true,
                        'class'         => '',
                    ],
                    'payout_address' => [
                        'label'         => __('Address', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 23,
                        'required'      => false,
                        'fullwidth'     => true,
                        'value'         => $this->get_field_value( 'payout_address' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    'payout_city' => [
                        'label'         => __('City', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 24,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_city' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    
                    'payout_state' => [
                        'label'         => __('State', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 25,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_state' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    
                    'payout_zipcode' => [
                        'label'         => __('Zip Code', 'pp-toolkit'),
                        'type'          => 'text',
                        'priority'      => 26,
                        'required'      => false,
                        'fullwidth'     => false,
                        'value'         => $this->get_field_value( 'payout_zipcode' ),
                        'data_type'     => 'meta',
                        'editable'      => true
                    ],
                    
                    'info' => [
                        'type'          => 'paragraph',
                        'class'         => 'p-info p-bolder',
                        'content'      => sprintf('*** If you\'d like to invite this nonprofit to accept direct payments for your campaign so that your donations can be considered tax deductible, please ask the non profit to register here: <a href="%s" target="_blank">%s</a>', 'https://poweringphilanthropy.com/non-profit-registration/', 'https://poweringphilanthropy.com/non-profit-registration/'),
                    ],
                ]
            ], 
        ), $this );

        uasort( $payout_direct_fields, 'charitable_priority_sort' );

        return $payout_direct_fields;
    }


    /**
     * Return the current campaign's Charitable_Campaign object.
     *
     * @return  Charitable_Campaign|false
     * @access  public
     * @since   1.0.0
     */
    public function get_campaign() {
        if (!isset($this->campaign)) {
            $campaign_id = get_query_var('campaign_id', false);
            $this->campaign = $campaign_id ? new Charitable_Campaign($campaign_id) : false;
        }

        return $this->campaign;
    }

    /**
     * Return the value for a particular field.
     *
     * @param   string $key
     *
     * @return  mixed
     * @access  public
     * @since   1.0.0
     */
    public function get_field_value($key) {
        if (isset($_POST[ $key ])) {
            return $_POST[ $key ];
        }

        $value = "";

        if ($this->get_campaign()) {
            $value = $this->get_campaign()->get($key);
        }

        return $value;
    }
}

PP_Payout_Options_Form::init();

endif; // End class_exists check