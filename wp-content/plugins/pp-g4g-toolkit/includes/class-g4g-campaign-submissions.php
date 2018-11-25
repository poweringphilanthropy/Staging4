<?php
/**
 * G4G_Campaign_Submissions Class.
 *
 * @class       G4G_Campaign_Submissions
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * G4G_Campaign_Submissions class.
 */
class G4G_Campaign_Submissions {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new G4G_Campaign_Submissions();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_filter( 'pp_toolkit_campaign_submission_campaign_fields', array($this, 'campaign_submission_fields'), 10, 3 );
    
        add_action( 'charitable_campaign_submission_save', array( $this, 'save_campaign_group_from_chapter' ), 10, 3 );
        add_action( 'charitable_campaign_submission_save', array( $this, 'save_college_from_organisation' ), 10, 3 );
        add_action( 'charitable_campaign_submission_save', array( $this, 'save_crm_id' ), 10, 3 );
		add_action( 'charitable_campaign_submission_save', array( $this, 'save_campaign_updates' ), 10, 3 );
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
     * Return the current user's Charitable_User object.
     *
     * @return  Charitable_User
     * @access  public
     * @since   1.0.0
     */
    public function get_user() {
        if (!isset($this->user)) {
            $user_id = (is_user_logged_in()) ? wp_get_current_user() : 0;
            $this->user = new Charitable_User($user_id);
        }

        return $this->user;
    }

    public function campaign_submission_fields($fields, PP_Charitable_Campaign_Form $pp_form, $charitable_form){
        $fields[ 'chapter' ] = [
            'priority'      => 19, 
            'label'         => sprintf(__( 'Fraternity/Sorority Name ', 'pp-toolkit' )),
            'placeholder'   => __('Fraternity/Sorority Name', 'pp-toolkit'),
            'type'          => 'text',
            'attrs'         => array(
                'data-source' => esc_attr( wp_json_encode( g4g_get_parent_campaign_group_names() ) ),
                'class' => 'autocomplete allow_only',
            ),
            'required'      => true,
            'fullwidth'     => false,
            'value'         => $this->get_user_value('chapter'),
            'data_type'     => 'user',
        ];

        $fields[ 'organisation' ] = [
            'priority'      => 20, 
            'label'         => sprintf(__( 'College/University Name', 'pp-toolkit' )),
            'placeholder'   => __('College/University Name', 'pp-toolkit'),
            'type'          => 'text',
            'attrs'         => array(
                'data-source' => esc_attr( wp_json_encode( g4g_get_college_names() ) ),
                'class' => 'autocomplete allow_only',
            ),
            'required'      => true,
            'fullwidth'     => false,
            'value'         => $this->get_user_value('organisation'),
            'data_type'     => 'user',
        ];
		
		$fields[ 'crm_account_id' ] = [
            'label'     => __( 'CRM Account ID', 'philanthropy' ), 
            'type'      => 'hidden', 
            'priority'  => 21, 
            'required'  => false, 
            'value'     => $this->get_user_value( 'crm_account_id' ),
			'data_type'     => 'user',
        ];
		$fields[ '_campaign_updates' ] = [
            'priority'      => 21, 
            'label'         => sprintf(__( 'ONGOING UPDATES', 'pp-toolkit' )),
            'placeholder'   => __('ONGOING UPDATES', 'pp-toolkit'),
            'type'          => 'textarea',
            'attrs'         => array(
            ),
            'required'      => false,
            'fullwidth'     => true,
            'value'         => get_post_meta($this->get_campaign()->ID , '_campaign_updates' , true),
            'data_type'     => 'user',
        ];

        return $fields;
    }

    /**
     * Returns the value of a particular key.
     *
     * @param   string $key     The key to search for.
     * @param   mixed  $default The default value to return if none is found.
     * @return  mixed
     * @access  public
     * @since   1.0.0
     */
    public function get_user_value( $key, $default = '' ) {
        if ( isset( $_POST[ $key ] ) ) {
            return $_POST[ $key ];
        }

        $user = $this->get_user();
        $value = $default;

        if( $this->get_campaign() ){

            switch ( $key ) {
                case 'chapter' :
                    $term_id = g4g_get_primary_term($this->get_campaign()->ID, 'campaign_group');

                    if(!empty($term_id)){
                        $term = get_term( $term_id, 'campaign_group' );
                        $value = $term->name;
                    }
                    break;
                case 'organisation' :
                    $term_id = g4g_get_primary_term($this->get_campaign()->ID, 'college');

                    if(!empty($term_id)){
                        $term = get_term( $term_id, 'college' );
                        $value = $term->name;
                    }
                    break;
            }

        } elseif ( $user ) {
            if ( in_array($key, array('chapter', 'organisation' , 'crm_account_id') ) && $user->has_prop( $key ) ) {
                $value = $user->{$key};
            }
        }

        return apply_filters( 'charitable_campaign_submission_user_value', $value, $key, $user );
    }

    public function get_field_value($key) {
        if (isset($_POST[ $key ])) {
            return $_POST[ $key ];
        }

        $value = "";

        switch ($key) {
            case 'chapter' :
                if ($this->get_campaign()) {
                    $term_id = g4g_get_primary_term($this->get_campaign()->ID, 'campaign_group');

                    if(!empty($term_id)){
                        $term = get_term( $term_id, 'campaign_group' );
                        $value = $term->name;
                    }
                }
                break;

        }

        return $value;
    }
	public function save_campaign_updates($submitted, $campaign_id, $user_id){
		$campaign_updates = isset($submitted['_campaign_updates']) ? $submitted['_campaign_updates'] : null;
		update_post_meta($campaign_id , '_campaign_updates' , $campaign_updates);
	}
    public function save_campaign_group_from_chapter($submitted, $campaign_id, $user_id){
        
        $org_name = isset($submitted['chapter']) ? $submitted['chapter'] : null;
        $taxonomy = 'campaign_group';

        if(empty($org_name)){
            // clear
            wp_set_object_terms( $campaign_id, null, $taxonomy, false );
        } else {
            $term = get_term_by( 'name', $org_name, $taxonomy );
            if(empty($term)){
                // create?
                $id = wp_insert_term( $org_name, $taxonomy );
            } else {
                $id = $term->term_id;
            }

            wp_set_object_terms( $campaign_id, $id, $taxonomy, false );

            if(class_exists('WPSEO_Primary_Term')){
                $primary_term = new WPSEO_Primary_Term( $taxonomy, $campaign_id );
                $primary_term->set_primary_term( $id );
            }
        }
    }

    public function save_college_from_organisation($submitted, $campaign_id, $user_id){
        
        $org_name = isset($submitted['organisation']) ? $submitted['organisation'] : null;
        $taxonomy = 'college';

        if(empty($org_name)){
            // clear
            wp_set_object_terms( $campaign_id, null, $taxonomy, true );
        } else {
            $term = get_term_by( 'name', $org_name, $taxonomy );
            if(empty($term)){
                // create?
                $id = wp_insert_term( $org_name, $taxonomy );
            } else {
                $id = $term->term_id;
            }

            wp_set_object_terms( $campaign_id, $id, $taxonomy, true );

            if(class_exists('WPSEO_Primary_Term')){

                $primary_term = new WPSEO_Primary_Term( $taxonomy, $campaign_id );

                $primary_term->set_primary_term( $id );
            }
        }
    }


    public function includes(){

    }
	
	public function save_crm_id($submitted, $campaign_id, $user_id){
		$campaign_object = new Charitable_Campaign($campaign_id);
		$crm_account_id = get_user_meta( $campaign_object->get_campaign_creator() , 'crm_account_id', true );
		update_post_meta($campaign_id,'post_sub_campaign_crm_id', $crm_account_id);
	}

}

G4G_Campaign_Submissions::init();