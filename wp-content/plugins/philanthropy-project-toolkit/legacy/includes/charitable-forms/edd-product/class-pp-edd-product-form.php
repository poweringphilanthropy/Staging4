<?php
/**
 * Class that manages the display and processing of the product form.
 *
 * @package     Philanthropy Project/Classes/PP_EDD_Product_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2014, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PP_EDD_Product_Form' ) ) : 

/**
 * PP_EDD_Product_Form
 *
 * @since       1.0.0
 */
class PP_EDD_Product_Form extends Charitable_Form {

    /**
     * Shortcode parameters. 
     *
     * @var     array
     * @access  protected
     */
    protected $shortcode_args;

    /**
     * @var     string
     */
    protected $nonce_action = 'pp_product_submission';

    /**
     * @var     string
     */
    protected $nonce_name = '_pp_product_submission_nonce';

    /**
     * Action to be executed upon form submission. 
     *
     * @var     string
     * @access  protected
     */
    protected $form_action = 'save_product';

    /**
     * The current user. 
     *
     * @var     Charitable_User
     * @access  protected
     */
    protected $user;

    /**
     * Create class object.
     * 
     * @param   array       $args       User-defined shortcode attributes.
     * @access  public
     * @since   1.0.0
     */
    public function __construct( $args = array() ) {    
        $this->id = uniqid();   
        $this->shortcode_args = $args;      
        $this->attach_hooks_and_filters();  

        wp_enqueue_script( 'campaigns' );
    }

    /**
     * Set up callback methods for actions & filters. 
     *
     * @return  void
     * @access  protected
     * @since   1.0.0
     */ 
    protected function attach_hooks_and_filters() {
        // parent::attach_hooks_and_filters();
        
        // add_filter( 'charitable_form_field_template', array( $this, 'use_pp_toolkit_template' ), 10, 2 );
        // add_filter( 'charitable_campaign_meta_key', array( $this, 'set_thumbnail_id_meta_key' ), 10, 2 );
        // add_filter( 'charitable_campaign_submission_meta_data', array( $this, 'save_picture' ), 10, 3 );
    }


    /**
     * Return the current user's Charitable_User object.  
     *
     * @return  Charitable_User
     * @access  public
     * @since   1.0.0
     */
    public function get_user() {
        if ( ! isset( $this->user ) ) {
            $this->user = new Charitable_User( wp_get_current_user() );
        }

        return $this->user;
    }

    /**
     * Return the current campaign's Charitable_Campaign object.  
     *
     * @return  Charitable_Campaign|false
     * @access  public
     * @since   1.0.0
     */
    public function get_campaign() {
        
        if ( ! isset( $this->campaign ) ) {
            $campaign_id = get_query_var( 'campaign_id', false );
            $this->campaign = $campaign_id ? new Charitable_Campaign( $campaign_id ) : false;
        }

        return $this->campaign;
    }

    /**
     * Return the product being edited, or false if we're created a new product.    
     *
     * @return  EDD_Download|false
     * @access  public
     * @since   1.0.0
     */
    public function get_product() {
        
        if ( ! isset( $this->product ) ) {
            $product_id = get_query_var( 'product_id', false );
            $this->product = $product_id ? new EDD_Download( $product_id ) : false;
        }

        return $this->product;
    }

    /**
     * Given a key, returns the current value of the field for the product. If we're creating a 
     * new product, this will always return an empty string. 
     *
     * @param   string      $key
     * @return  mixed
     * @access  public
     * @since   1.0.0
     */
    public function get_product_value() {
        $product = $this->get_product();
        $value = "";

        if ( $product ) {
            switch ( $key ) {
                case 'ID' : 
                    $value = $product->ID;
                    break;

                case 'post_title' : 
                    $value = $product->post_title;
                    break;

                case 'post_content' : 
                    $value = $product->post_content;
                    break;

                case 'download_category' : 
                    $value = wp_get_object_terms( $product->ID, 'download_category', array( 'fields' => 'ids' ) );
                    break;                

                case 'image' : 
                    $thumbnail_id = get_post_thumbnail_id( $product->ID );

                    if ( empty( $thumbnail_id ) ) {
                        $value = '';
                    }
                    else {
                        $src = wp_get_attachment_image_src( $thumbnail_id );
                        $value = $src[ 0 ];
                    }
                    break;

                case '_variable_pricing' : 
                    $values = $product->has_variable_prices();
                    break;

                case 'edd_variable_prices' : 
                    $values = $product->get_prices();
                    break;

                case 'edd_price' : 
                    $values = $product->get_price();
                    break;

                default : 
                    $value = $product->$key; // Fallback method taking advantage of the EDD_Download magic getter.
            }
        }

        return $value;
    }

    /**
     * Return the product fields. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_product_fields() {     
        $product_fields = apply_filters( 'pp_product_submission_product_fields', array(        
            'ID' => array(
                'type'          => 'hidden', 
                'priority'      => 0, 
                'value'         => $this->get_product_value( 'ID' ), 
                'data_type'     => 'core'
            ),
            'post_title' => array(
                'label'         => __( 'Product name', 'pp-toolkit' ),
                'type'          => 'text',
                'required'      => true, 
                'fullwidth'     => true,
                'priority'      => 2,
                'placeholder'   => __( '', 'pp-toolkit' ), 
                'value'         => $this->get_product_value( 'post_title' ), 
                'data_type'     => 'core'
            ),
            'post_content' => array(
                'label'         => __( 'Description', 'pp-toolkit' ),
                'type'          => 'editor',
                'required'      => true, 
                'fullwidth'     => true,
                'priority'      => 4,
                'value'         => $this->get_product_value( 'post_content' ), 
                'data_type'     => 'core'
            ),        
            'download_category' => array(
                'label'         => __( 'Category', 'pp-toolkit' ),
                'type'          => 'multi-checkbox',
                'required'      => true, 
                'priority'      => 8,
                'placeholder'   => __( '', 'pp-toolkit' ), 
                'options'       => get_terms( 'download_category', array( 'hide_empty' => false, 'fields' => 'id=>name' ) ), 
                'value'         => $this->get_product_value( 'category' ), 
                'data_type'     => 'taxonomy'
            ), 
            'image' => array(
                'label'         => __( 'Image', 'pp-toolkit' ),
                'type'          => 'picture',
                'required'      => true, 
                'priority'      => 10,
                'placeholder'   => __( '', 'pp-toolkit' ), 
                'value'         => $this->get_product_value( 'image' ), 
                'data_type'     => 'meta'
            )
        ), $this );

        uasort( $product_fields, 'charitable_priority_sort' );

        return $product_fields;
    }

    /**
     * Return the pricing fields. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_pricing_fields() {
        $pricing_fields = apply_filters( 'pp_product_submission_pricing_fields', array(
            '_variable_pricing' => array(
                'label'         => __( 'Enable variable pricing', 'pp-toolkit' ),
                'type'          => 'checkbox',
                'fullwidth'     => true,
                'priority'      => 22,
                'value'         => $this->get_product_value( '_variable_pricing' ), 
                'data_type'     => 'meta'
            ),            
            'edd_variable_prices' => array(
                'type'          => 'variable-prices',
                'priority'      => 24, 
                'value'         => $this->get_product_value( 'edd_variable_prices' ), 
                'data_type'     => 'meta'
            ), 
            'edd_price' => array(
                'label'         => __( 'Price', 'pp-toolkit' ),
                'type'          => 'number',
                'min'           => 0, 
                'step'          => 0.01,
                'required'      => false, 
                'priority'      => 26,
                'value'         => $this->get_product_value( 'edd_price' ), 
                'data_type'     => 'meta'
            )
        ), $this );

        uasort( $pricing_fields, 'charitable_priority_sort' );

        return $pricing_fields;
    }

    /**
     * Return the shipping fields. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_shipping_fields() {
        $shipping_fields = apply_filters( 'pp_product_submission_shipping_fields', array(
            'local_shipping' => array(
                'label'         => __( 'Local shipping rate', 'pp-toolkit' ),
                'type'          => 'number',
                'required'      => true, 
                'priority'      => 42,
                'min'           => 0, 
                'step'          => 0.01,
                'value'         => $this->get_product_value( 'local_shipping' ), 
                'data_type'     => 'meta'
            ),
            'international_shipping' => array(
                'label'         => __( 'International shipping rate', 'pp-toolkit' ),
                'type'          => 'number',
                'required'      => true,
                'priority'      => 44,
                'min'           => 0, 
                'step'          => 0.01,
                'value'         => $this->get_product_value( 'international_shipping' ), 
                'data_type'     => 'core'
            )
        ), $this );

        uasort( $shipping_fields, 'charitable_priority_sort' );

        return $shipping_fields;
    }

    /**
     * Return the campaign fields to be displayed.
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_campaign_fields() {
        $campaign = $this->get_campaign();

        if ( false === $campaign ) {

            $campaign_fields = apply_filters( 'pp_product_submission_campaign_fields', array(
                'explanation' => array(                
                    'type'          => 'paragraph',
                    'content'       => __( 'Proceeds from sales of this product will contribute to a campaign you have created.', 'pp-toolkit' ),
                    'priority'      => 62
                ),
                'campaign' => array(
                    'label'         => __( 'Campaign', 'pp-toolkit' ),
                    'type'          => 'select',
                    'required'      => true, 
                    'priority'      => 64,
                    'options'       => $this->get_user_campaign_options(), 
                    'data_type'     => 'campaign'
                ), 
                'contribution_percentage' => array(
                    'label'         => __( 'The percentage of the sale that will be contributed to your campaign.', 'pp-toolkit' ), 
                    'type'          => 'number', 
                    'required'      => true, 
                    'priority'      => 66, 
                    'default'       => 100, 
                    'min'           => 1, 
                    'max'           => 100, 
                    'data_type'     => 'campaign'
                )
            ), $this, $campaign );

        }
        else {

             $campaign_fields = apply_filters( 'pp_product_submission_campaign_fields', array(
                'explanation' => array(                
                    'type'          => 'paragraph',
                    'content'       => sprintf( __( 'Proceeds from sales of this product will contribute to the %s campaign.', 'pp-toolkit' ), '<strong>' . $campaign->post_title . '</strong>' ),
                    'priority'      => 62
                ),
                'campaign' => array(
                    'type'          => 'hidden',
                    'required'      => true, 
                    'priority'      => 64,
                    'value'         => $campaign->ID, 
                    'data_type'     => 'campaign'
                ), 
                'contribution_percentage' => array(
                    'label'         => __( 'The percentage of the sale that will be contributed to your campaign', 'pp-toolkit' ), 
                    'type'          => 'number', 
                    'required'      => true, 
                    'priority'      => 66, 
                    'default'       => 100, 
                    'min'           => 1, 
                    'max'           => 100, 
                    'value'         => '',
                    'data_type'     => 'campaign'
                )
            ), $this, $campaign );

        }

        uasort( $campaign_fields, 'charitable_priority_sort' );

        return $campaign_fields;
    }

    /**
     * Fields to be displayed.      
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_fields() {
        $fields = apply_filters( 'pp_product_submission_fields', array(
            'product_fields' => array(
                'legend'        => __( 'Product Details', 'pp-toolkit' ), 
                'type'          => 'fieldset', 
                'fields'        => $this->get_product_fields(),
                'priority'      => 0
            ),
            'pricing_fields' => array(
                'legend'        => __( 'Pricing', 'pp-toolkit' ), 
                'type'          => 'fieldset', 
                'fields'        => $this->get_pricing_fields(), 
                'priority'      => 20,
            ),            
            'shipping_fields' => array(
                'legend'        => __( 'Shipping Rates', 'pp-toolkit' ), 
                'type'          => 'fieldset', 
                'fields'        => $this->get_shipping_fields(), 
                'priority'      => 40
            ), 
            'campaign_fields' => array(
                'legend'        => __( 'Campaign Settings', 'pp-toolkit' ), 
                'type'          => 'fieldset', 
                'fields'        => $this->get_campaign_fields(), 
                'priority'      => 60
            )
        ), $this );             

        uasort( $fields, 'charitable_priority_sort' );

        return $fields;
    }

    /**
     * Returns an array in the format of id=>post_title with the user's campaigns. 
     *
     * @return  string[]
     * @access  public
     * @since   1.0.0
     */
    public function get_user_campaign_options() {
        $ret = array();
        $campaigns = $this->get_user()->get_current_campaigns();

        if ( $campaigns->have_posts() ) {
            while ( $campaigns->have_posts() ) {
                $campaigns->the_post();
                $ret[ get_the_ID() ] = get_the_title();
            }
        }

        return $ret;
    }

    /**
     * Returns all fields as a merged array. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_merged_fields() {
        $fields = array();

        foreach ( $this->get_fields() as $section ) {
            $section_fields = isset( $section[ 'fields' ] ) ? $section[ 'fields' ] : array();
            $fields = array_merge( $fields, $section_fields );
        }

        return $fields;
    }

    /**
     * Verify that the current user can create or edit this campaign. 
     *
     * @return  boolean
     * @access  public
     * @since   1.0.0
     */
    public function current_user_can_edit_campaign() {
        return true;
    }

    /**
     * Organize fields by data type, also filtering out unused parameters (we just need the key and the type). 
     *
     * @param   string      $key
     * @param   array       $field   
     * @param   array       $ret
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function sort_field_by_data_type( $key, $field, $ret ) {
        /* Filter out paragraphs and fields without a type. */
        if ( ! isset( $field[ 'type' ] ) || 'paragraph' == $field[ 'type' ] ) {
            return $ret;
        }

        /* Get the data type. Default to meta if no type is set. */
        $data_type = isset( $field[ 'data_type' ] ) ? $field[ 'data_type' ] : 'meta';

        $ret[ $data_type ][ $key ] = $field[ 'type' ];

        return $ret;
    }

    /**
     * Save product after form submission. 
     *
     * @return  void
     * @access  public
     * @static
     * @since   1.0.0
     */
    public static function save_product() {
        $form = new PP_EDD_Product_Form();     

        if ( ! $form->validate_nonce() ) {
            return;
        }

        /* Confirm that the current user has permission to create/edit this campaign. */
        if ( ! $form->current_user_can_edit_campaign() ) {
            return;
        }

        if ( ! $form->check_required_fields( $form->get_merged_fields() ) ) {

            /**
             * @todo Send error to say that some required fields are missing. 
             */
            return;

        }   

        $submitted = $_POST;

        $fields = array();

        foreach ( $form->get_merged_fields() as $key => $field ) {

            /* Organize fields into arrays of core, meta, taxonomy and user data. */
            $fields = $form->sort_field_by_data_type( $key, $field, $fields );

        }

        /* Save campaign data */
        // $campaign_id = $form->save_core_campaign_data( $fields, $submitted, $user_id );
                
        /* Save taxonomy data */
        // $form->save_campaign_taxonomies( $fields, $submitted, $campaign_id );

        /* Save campaign meta */
        // $form->save_campaign_meta( $fields, $submitted, $campaign_id );
        
        /* Redirect */
        $url = "";

        wp_safe_redirect( $url );

        exit();
    }

    /**
     * Create the campaign as a new object in the wp_posts table. 
     *
     * @param   array[]     $fields
     * @param   array       $submitted
     * @param   int         $user_id
     * @return  int         $campaign_id
     * @access  public
     * @since   1.0.0
     */
    public function save_core_campaign_data( $fields, $submitted, $user_id ) {
    
    }   

    /**
     * Save the campaign taxonomy data. 
     *
     * @param   array[]     $fields
     * @param   array       $submitted
     * @param   int         $campaign_id
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function save_campaign_taxonomies( $fields, $submitted, $campaign_id ) {

    }

    /**
     * Save the meta fields for the newly created campaign. 
     *
     * @param   array[]     $fields
     * @param   array       $submitted
     * @param   int         $campaign_id    
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function save_campaign_meta( $fields, $submitted, $campaign_id ) {
        
    
    }
}

endif; // End class_exists check