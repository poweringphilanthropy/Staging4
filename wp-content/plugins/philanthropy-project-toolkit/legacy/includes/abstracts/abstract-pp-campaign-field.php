<?php
/**
 * PP_Campaign_Field Class.
 *
 * @class       PP_Campaign_Field
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Campaign_Field class.
 */
class PP_Campaign_Field {

    private $page = 'campaign_details';

    private $campaign = false;

    private $values = false;

    public function __construct() {
        add_filter( 'charitable_campaign_submission_fields', array($this, 'register_fieldset'), 10, 2 );
    }

    public function register_fieldset($sections, $Charitable_Ambassadors_Campaign_Form){
        return $sections;
    }

    public function get_campaign(){
        if (!$this->campaign) {
            $campaign_id = get_query_var('campaign_id', false);

            $this->campaign = $campaign_id ? new Charitable_Campaign($campaign_id) : false;
        }

        return $this->campaign;
    }

    public function setup_values($values){
        $this->values = $values;
    }

    public function get_value($key){
        $value = (isset($this->values[$key])) ? $this->values[$key] : '';

        return apply_filters( 'pp_toolkit_campaign_field_value', $value, $key );
    }
}