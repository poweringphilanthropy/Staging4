<?php
/**
 * PP_Merchandise Class.
 *
 * @class       PP_Merchandise
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Merchandise class.
 */
class PP_Merchandise extends PP_Campaign_Field {

    public $meta_key = 'merchandise';

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Merchandise();
        }

        return $instance;
    }

    public function __construct() {

        parent::__construct();

        /**
         * Hooks
         */
        
        add_filter( 'pp_custom_form_field_template', array($this, 'custom_form_field_template'), 10, 1 );
        add_filter( 'pp_toolkit_submission_vars', array($this, 'template_row'), 10, 1 );

        // not used for now, change to filter below, the filter run after nonce validated
        // add_action( 'charitable_campaign_submission_save_page_' . $this->page, array($this, 'save_fields'), 10, 1 );
        
        /**
         * Filter below run after nonce and required fields validated.
         */
        add_filter( 'charitable_campaign_submission_values', array($this, 'change_post_values'), 10, 3 );
        add_filter( 'charitable_campaign_submission_fields_map', array($this, 'submission_fields_map'), 10, 3 );
        
        // saving meta
        // add_filter( 'charitable_campaign_submission_meta_data', array($this, 'change_meta_post_values' ), 10, 4 );
        // add_filter( 'charitable_campaign_meta_key', array($this, 'change_meta_key' ), 10, 3 );
        // add_filter( 'charitable_sanitize_campaign_meta' . $this->meta_key, array($this, 'change_meta_value' ), 10, 3 );

        // add_action( 'charitable_campaign_submission_save', array($this, 'after_all_saved'), 10, 4 );
    }

    public function custom_form_field_template($templates){

        $templates[] = 'merchandise';

        return $templates;
    }

    public function template_row($vars){
        ob_start();
        $form = new Charitable_Ambassadors_Campaign_Form();
        pp_toolkit_template( 'form-fields/merchandise-row.php', array(
            'index'       => '{?}',
        ) );

        $vars['merchandise_row'] = ob_get_clean();

        return $vars;
    }

    public function register_fieldset($sections, $Charitable_Ambassadors_Campaign_Form){

        $sections['merchandise_fields'] = [
            'legend'   => __('Merchandise', 'pp-toolkit'),
            'type'     => 'fieldset',
            'priority' => 50,
            'page'     => 'campaign_details',
            'fields'   => [
                'explanation' => [
                    'type'     => 'paragraph',
                    'content'  => __('You can sell <strong>merchandise</strong> to raise money for your campaign goal.', 'pp-toolkit'),
                    'priority' => 32
                ],
                'merchandise' => [
                    'type'     => 'merchandise',
                    'priority' => 34,
                    'value'    => $this->get_merchandise(),
                    'fields'   => $this->get_fields(),
                ]
            ]
        ];

        return $sections;
    }

    /**
     * Return the array of fields displayed in the merchandise form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_fields() {
        
        $product_fields = apply_filters( 'pp_merchandise_fields', array(   
            
            'post_title' => array(
                'label'         => __( 'Product Name', 'pp-toolkit' ),
                'type'          => 'text',
                'required'      => true, 
                'fullwidth'     => true,
                'priority'      => 2,
                'placeholder'   => __( '', 'pp-toolkit' ), 
                'value'         => $this->get_value( 'post_title' ), 
                'data_type'     => 'core'
            ),                
        ), $this );

        uasort( $product_fields, 'charitable_priority_sort' );

        return $product_fields;
    }

    /**
     * Return any products that have been created that are linked to this campaign as "benefactors".
     *
     * @return  array       Empty array if no merchandise.
     * @access  public
     * @since   1.0.0
     */
    public function get_merchandise() {

        /**
         * If the submitted data was set in the session, grab it.
         */
        if (is_array(charitable_get_session()->get('submitted_merchandise'))) {
            $submitted = charitable_get_session()->get('submitted_merchandise');

            charitable_get_session()->set('submitted_merchandise', false);
        } elseif (isset($_POST['merchandise'])) {
            $submitted = $_POST['merchandise'];
        }

        if (isset($submitted)) {
            $merchandise = [];

            foreach ($submitted as $merch) {
                $merchandise[] = ['POST' => $merch];
            }

            return $merchandise;
        }

        if (false === $this->get_campaign()) {
            return [];
        }

        $merchandise = [];
        
        /**
         * We should change the way, to avoid Expired downloads not showing
         * @var [type]
         */
        $downloads = charitable()->get_db_table('edd_benefactors')->get_single_download_campaign_benefactors($this->get_campaign()->ID);
        $download_ids = wp_list_pluck($downloads, 'edd_download_id');

        foreach ($download_ids as $key => $download_id) {
            if (has_term('Merchandise', 'download_category', $download_id)) {
                $merchandise[ $download_id ] = $downloads[ $key ];
            }
        }

        return $merchandise;
    }

    public function change_post_values($post_data, $fields, Charitable_Ambassadors_Campaign_Form $form){


        $attach_id = pp_save_image_from_datauri($_POST['test_datauri'], 'test');
        echo $attach_id;
        echo wp_get_attachment_image( $attach_id, 'full' );

        exit();
        return $post_data;
    }

    
    public function submission_fields_map($fields, $submitted, Charitable_Ambassadors_Campaign_Form $form){
        
       

        return $fields;
    }
}

PP_Merchandise::init();