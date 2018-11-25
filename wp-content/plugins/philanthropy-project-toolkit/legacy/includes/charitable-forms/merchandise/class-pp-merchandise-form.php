<?php
/**
 * This is the class responsible for displaying the Merchandise form inside of campaign forms.
 *
 * @package     Philanthropy Project/Classes/PP_Merchandise_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2014, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PP_Merchandise_Form' ) ) : 

/**
 * PP_Merchandise_Form
 *
 * @since       1.0.0
 */
class PP_Merchandise_Form extends Charitable_Form {

    /**
     * @var     string
     * @access  protected
     */
    protected $nonce_action = 'pp_merchandise_form';

    /**
     * @var     string
     * @access  protected
     */
    protected $nonce_name = '_pp_merchandise_form_nonce';

    /**
     * Benefactor relationship object. 
     *
     * @var     Object|null
     * @access  protected
     */
    protected $benefactor;

    /**
     * Create class object.
     * 
     * @param   Object|null $benefactor     Benefactor object. 
     * @param   array $submitted 
     * @access  public
     * @since   1.0.0
     */
    public function __construct( $benefactor = null, $submitted = array() ) {    
        $this->id = uniqid();   
        $this->benefactor = $benefactor;
        $this->submitted = $submitted;
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

        add_filter( 'charitable_form_submitted_values', array( $this, 'get_submitted_values_from_session' ), 10, 2 );
        add_filter( 'charitable_form_field_key', array( $this, 'set_form_field_key' ), 10, 5 );
    }

    /**
     * Return the submitted values from the session. 
     *
     * @param   array $submitted
     * @param   Charitable_Form $form
     * @return  array
     * @access  public
     * @since   1.1.0
     */
    public function get_submitted_values_from_session( $submitted, Charitable_Form $form ) {
        if ( ! is_a( $form, 'PP_Merchandise_Form' ) ) {
            return $submitted;
        }

        if ( is_array( charitable_get_session()->get( 'submitted_merchandise' ) ) ) {
            $submitted = charitable_get_session()->get( 'submitted_merchandise' );
        }

        return $submitted;
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
            $this->product = is_null( $this->benefactor ) ? false : new EDD_Download( $this->benefactor->edd_download_id );
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
    public function get_product_value( $key ) {
        if ( isset( $this->submitted[ $key ] ) ) {
            return $this->submitted[ $key ];
        }
        
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

                case 'image' : 
                    $thumbnail_id = get_post_thumbnail_id( $product->ID );
                    $value = ( $thumbnail_id ) ? $thumbnail_id : 0;

                    /* GUM Edit if ( empty( $thumbnail_id ) ) {
                        $value = '';
                    }
                    else {
                        $value = wp_get_attachment_image( $thumbnail_id );
                    }
                    */
                    
                    break;
                    
                case 'product_image' : 
                    $thumbnail_id = get_post_thumbnail_id( $product->ID );
                    $value = wp_get_attachment_image( $thumbnail_id );
                    break;
                    
                case 'product_thumbnail' : 
                	$value = get_post_thumbnail_id( $product->ID );
                    break;

                case '_variable_pricing' : 
                    $value = $product->has_variable_prices();
                    break;

                case 'variable_prices' : 
                    $value = $product->get_prices();
                    break;

                case 'price' : 
                    $value = $product->get_price();
                    break;

                case 'purchase_limit' : 
                    $value = get_post_meta( $product->ID, '_edd_purchase_limit', true );
                    break;

                // case 'sale_start_date_time' : 
                    // $value = $product->get_price();
                    // break;

                // case 'sale_end_date_time' : 
                    // $value = $product->get_price();
                    // break;

                case 'local_shipping' :
                    $value = get_post_meta( $product->ID, '_edd_shipping_domestic', true );        
                    break;

                case 'international_shipping' : 
                    $value = get_post_meta( $product->ID, '_edd_shipping_international', true );
                    break;

                case 'start_date_time' : 
                    $start_date = get_post_meta( $product->ID, 'merchandise_start_date', true );
                    if (($timestamp = strtotime($start_date)) !== false) {
                        $value = array(
                            'date' => date('Y-m-d', $timestamp), 
                            'hour' => date('h', $timestamp),
                            'minute' => date('i', $timestamp), 
                            'meridian' => date('a', $timestamp)
                        );
                    } else {
                        $value = array(
                            'date' => '', 
                            'hour' => '',
                            'minute' => '', 
                            'meridian' => ''
                        );
                    }
                    break;

                case 'end_date_time' : 
                    $end_date = get_post_meta( $product->ID, 'merchandise_end_date', true );
                    if (($timestamp = strtotime($end_date)) !== false) {
                        $value = array(
                            'date' => date('Y-m-d', $timestamp), 
                            'hour' => date('h', $timestamp),
                            'minute' => date('i', $timestamp), 
                            'meridian' => date('a', $timestamp)
                        );
                    } else {
                        $value = array(
                            'date' => '', 
                            'hour' => '',
                            'minute' => '', 
                            'meridian' => ''
                        );
                    }
                    break;

                default : 
                    $value = $product->$key; // Fallback method taking advantage of the EDD_Download magic getter.
            }
        }

        return $value;
    }    

    /**
     * Get fields related to shipping rates. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_shipping_fields() {
        $shipping_fields = apply_filters( 'pp_merchandise_shipping_fields', array( 
            'shipping_explanation' => array(
                'type'          => 'paragraph',
                'content'       => __( 'Shipping charges allow for merchandise sales to out-of-town supporters. The shipping rate entered here will be added to the cart total when a donor completes their transaction.', 'pp-toolkit' ),
                'priority'      => 13
            ),
            'local_shipping' => array(
                'label'         => __( 'Shipping rate (per checkout)', 'pp-toolkit' ),
                'type'          => 'number',
                'required'      => false, 
                'fullwidth'     => true,
                'priority'      => 14,
                'min'           => 0, 
                'step'          => 0.01,
                'value'         => $this->get_product_value( 'local_shipping' ), 
                'data_type'     => 'meta'
            ),
            // 'international_shipping' => array(
            //     'label'         => __( 'International shipping rate (per checkout)', 'pp-toolkit' ),
            //     'type'          => 'number',
            //     'required'      => false,
            //     'priority'      => 16,
            //     'min'           => 0, 
            //     'step'          => 0.01,
            //     'value'         => $this->get_product_value( 'international_shipping' ), 
            //     'data_type'     => 'core'
            // )
        ), $this );

        uasort( $shipping_fields, 'charitable_priority_sort' );

        return $shipping_fields;
    }

    /**
     * Return fields related to product pricing. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_pricing_fields() {
        $pricing_fields = apply_filters( 'pp_merchandise_pricing_fields', array( 
            'price_fields' => array(
                'type'          => 'fieldset',
                'priority'      => 6,
                'class'         => 'standard-pricing',
                'fields'        => array(
                    'price' => array(
                        'label'         => __( 'Product Price', 'pp-toolkit' ),
                        'type'          => 'number',
                        'min'           => 0, 
                        'step'          => 0.01,
                        'required'      => false, 
                        'priority'      => 6,
                        'value'         => $this->get_product_value( 'price' ), 
                    ),
                    'purchase_limit' => array(
                        'label'         => __( 'Quantity Available', 'pp-toolkit' ),
                        'type'          => 'number',
                        'min'           => -1, 
                        'required'      => false, 
                        'fullwidth'     => false,
                        'priority'      => 7,
                        'value'         => $this->get_product_value( 'purchase_limit' ), 
                    ),
                )
            ),
            '_variable_pricing' => array(
                'label'         => __( 'Allow multiple price options', 'pp-toolkit' ),
                'type'          => 'checkbox',
                'fullwidth'     => true,
                'priority'      => 2,
                'checked'       => ($this->get_product()) ? $this->get_product_value( '_variable_pricing' ) : true, // default checked
                'class'         => 'toggle-variable-pricing hidden' // hide for now
            ),            
            'variable_prices' => array(
                'type'          => 'variable-prices',
                'priority'      => 4, 
                'value'         => $this->get_product_value( 'variable_prices' )
            ),     
        ), $this );

        uasort( $pricing_fields, 'charitable_priority_sort' );

        return $pricing_fields;
    
    }

    /**
     * Return fields related to on_sale_fields pricing. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_date_fields() {
        $pricing_fields = apply_filters( 'pp_merchandise_on_sale_fields', array( 
            'explanation' => array(
                'type'     => 'paragraph',
                'content'  => __('You can specify "on sale" dates for your merchandise. This is helpful when you need to close merchandise sales to submit an order to your merchandise vendor.', 'pp-toolkit'),
                'priority' => 4
            ),
            'start_date_time' => array(
                'label'     => __( 'Start Date & Time', 'pp-toolkit' ),
                'type'      => 'datetime', 
                'required'  => false,
                'value'     => $this->get_product_value( 'start_date_time' ), 
                'priority'   => 5
            ),
            'end_date_time' => array(
                'label'     => __( 'End Date & Time', 'pp-toolkit' ),
                'type'      => 'datetime', 
                'required'  => false,
                'value'     => $this->get_product_value( 'end_date_time' ), 
                'priority'   => 6
            )            
        ), $this );

        uasort( $pricing_fields, 'charitable_priority_sort' );

        return $pricing_fields;
    
    }

    /**
     * Return the array of fields displayed in the merchandise form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_fields() {
	    
	    $thumbnail_id = get_post_thumbnail_id( $this->get_product_value( 'ID' ) );
        $thumbnail_html = wp_get_attachment_image( $thumbnail_id );
	    
        $product_fields = apply_filters( 'pp_merchandise_fields', array(     
            'form_id' => array(
                'type'          => 'hidden',
                'priority'      => 0,
                'value'         => $this->id
            ),   
            'campaign_benefactor_id' => array(
                'type'          => 'hidden', 
                'priority'      => 0, 
                'value'         => is_null( $this->benefactor ) ? 0 : $this->benefactor->campaign_benefactor_id
            ),
            'ID' => array(
                'type'          => 'hidden', 
                'priority'      => 0, 
                'value'         => $this->get_product_value( 'ID' ), 
                'data_type'     => 'core'
            ),
            'post_title' => array(
                'label'         => __( 'Product Name', 'pp-toolkit' ),
                'type'          => 'text',
                'required'      => true, 
                'fullwidth'     => true,
                'priority'      => 2,
                'placeholder'   => __( '', 'pp-toolkit' ), 
                'value'         => $this->get_product_value( 'post_title' ), 
                'data_type'     => 'core'
            ),
            'post_content' => array(
                'label'         => __( 'Product Description', 'pp-toolkit' ),
                'type'          => 'textarea',
                'required'      => false, 
                'fullwidth'     => true,
                'rows'          => 4,
                'priority'      => 4,
                'value'         => $this->get_product_value( 'post_content' ), 
                'data_type'     => 'core'
            ),
            
             //* GUM - code edit 
             /*
	             'image' => array(
                'label'         => __( 'Product Image', 'pp-toolkit' ) ,
                'type'          => 'picture',
                'required'      => false, 
                'priority'      => 12,
                'placeholder'   => __( '', 'pp-toolkit' ), 
                'value'         => $this->get_product_value( 'image' ), 
                'parent_id'   	=> $this->get_product_value( 'ID' ), //* GUM - code edit 
                'data_type'     => 'meta'
            ),
            */
            //  'product_image' => array(
            //     'label'         => __( 'Upload Image File', 'pp-toolkit' ),
            //     'type'          => 'file',
            //     'required'      => false,
            //     'fullwidth'     => false,
            //     'priority'      => 5,
            //     'parent_id'   	=> $this->get_product_value( 'ID' ),
            //     //'value'         => $this->get_event_value( 'event_image' )
            // ),
            
            // 'product_image_name' => array (
            //     'type'          => 'paragraph',
            //     'priority'      => 5,
            //     'fullwidth'     => false,
            //     'content'       => __( 'Product Image <br />', 'pp-toolkit' ) . $thumbnail_html,
            // ),
            
            // 'product_image' => array(
            //     'label'         => __( 'Product Image File', 'pp-toolkit' ),
            //     'type'          => 'file',
            //     'required'      => false,
            //     'fullwidth'     => true,
            //     'priority'      => 7,
            //     'parent_id'     => $this->get_product_value( 'ID' ),
            //     //'value'         => $this->get_event_value( 'event_image' )
            // ),
            // change with ajax upload
            'product_thumbnail' => array(
                'label'         => __( 'Product Image', 'pp-toolkit' ),
                // 'type'          => 'picture',
                'type'          => 'image-crop',
                'editor-setting'          => array(
                    'expected-height' => 400,
                    'expected-width' => 600,
                    'max-preview-width' => '650px',
                    'placeholder' => '(Ideal size is 600 wide by 400px high)',
                ),
                'required'      => false,
                'fullwidth'     => true,
                'priority'      => 7,
                'parent_id'   	=> $this->get_product_value( 'ID' ),
                'value'         => $this->get_product_value( 'product_thumbnail' )
            ),
            
            'pricing_fields' => array(
                'legend'        => __( 'Pricing Options', 'pp-toolkit' ), 
                'type'          => 'fieldset',
                'priority'      => 14, 
                'fields'        => $this->get_pricing_fields(),
                'class'         => 'header_black_text header_larger-font',
            ),   
            
            'date_fields' => array(
                'legend'        => __( 'Availability', 'pp-toolkit' ), 
                'type'          => 'fieldset',
                'priority'      => 15, 
                'fields'        => $this->get_date_fields(),
                'class'         => 'header_black_text header_larger-font',
            ),             
            'shipping_fields' => array(
                'legend'        => __( 'Optional Shipping', 'pp-toolkit' ),
                'type'          => 'fieldset',
                'priority'      => 16,
                'fields'        => $this->get_shipping_fields(),
                'class'         => 'header_black_text header_larger-font',
            )                
        ), $this );

        uasort( $product_fields, 'charitable_priority_sort' );

        return $product_fields;
    }

    /**
     * Prepend the input names with our namespace.
     *
     * @param   string  $input_name
     * @param   string  $key
     * @param   string  $namespace
     * @param   Charitable_Form $form
     * @param   int     $index
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function set_form_field_key( $input_name, $key, $namespace, $form, $index ) {
        if ( $form->is_current_form( $this->id ) ) {
            $input_name = sprintf( 'merchandise[%s][%s]', $this->id, $key );
        }

        return $input_name;
    }

    /**
     * Returns all individual fields (no fieldsets).
     *
     * @return  array[] 
     * @access  public
     * @since   1.0.0
     */
    public function get_merged_fields() {
        $fields = $this->get_fields();

        foreach ( array( 'pricing_fields', 'shipping_fields' ) as $fieldset ) {
            $fields = array_merge( $fields, $fields[ $fieldset ][ 'fields' ] );
            unset( $fields[ $fieldset ] );
        }
        
        return $fields;
    }

    /**
     * Save merchandise when saving the campaign.  
     *
     * @param   array   $submitted
     * @param   int     $campaign_id
     * @param   int     $user_id
     * @return  void
     * @access  public
     * @static
     * @since   1.0.0
     */
    public static function save_merchandise( $submitted, $campaign_id, $user_id ) {
        $merchandise = isset( $submitted[ 'merchandise' ] ) ? $submitted[ 'merchandise' ] : array();
        $submitted_ids = array_filter(wp_list_pluck( $merchandise, 'ID' ));
        $merchandise_ids = philanthropy_get_campaign_merchandise_ids($campaign_id);
        if(empty($merchandise_ids) || !is_array($merchandise_ids))
            $merchandise_ids = array();

        $diff = array_diff($merchandise_ids, $submitted_ids );

        // echo "<pre>";
        // print_r($merchandise_ids);
        // echo "</pre>";

        // echo "<pre>";
        // print_r($submitted_ids);
        // echo "</pre>";

        // echo "<pre>";
        // print_r($diff);
        // echo "</pre>";

        // exit();

        // remove old
        if(!empty($diff) && is_array($diff)):
        foreach ($diff as $key => $merchandise_id) {
            // delete product
            wp_delete_post( $merchandise_id, true );

            // may need to remove benefactor relationship
            $rels = charitable()->get_db_table('edd_benefactors')->get_benefactors_for_download($merchandise_id);
            if(!empty($rels)):
            foreach ($rels as $key => $rel) {
                if(($rel->campaign_id != $campaign_id) || ($rel->edd_download_id != $merchandise_id) )
                    continue;

                charitable_get_table( 'benefactors' )->delete( $rel->campaign_benefactor_id );
            }
            endif;

            // $deleted = charitable_get_table( 'benefactors' )->delete( $benefactor_id );
        }
        endif;

        if ( empty( $merchandise ) ) {
            return;
        }

        $form = new PP_Merchandise_Form();

        add_filter( 'charitable_required_field_exists', array( $form, 'check_image_required_field' ), 10, 5 );

        $form->pre_process_file_data();

        foreach ( $merchandise as $index => $product ) {

            /* If required fields are missing, do not save the campaign. */
            if ( ! $form->check_required_fields( $form->get_merged_fields(), $product ) ) {
                /**
                 * If this was a new campaign submission, set the status back to draft.
                 */
                if ( ! isset( $submitted[ 'ID' ] ) || empty( $submitted[ 'ID' ] ) ) {
                    wp_update_post( array(
                        'ID' => $campaign_id, 
                        'post_status' => 'draft'
                    ) );
                }

                charitable_get_session()->set( 'submitted_merchandise', $merchandise );

                add_filter( 'charitable_campaign_submission_redirect_url', array( 'PP_Charitable_Campaign_Form', 'redirect_submission_to_edit_page' ), 10, 3 );

                continue;
            }   

            $product_id = $form->save_core_product_data( $product, $user_id );

            $form->save_product_meta( $product, $product_id, $index );

            $form->save_product_taxonomy( $product_id );

            if ( ! isset( $product[ 'campaign_benefactor_id' ] ) || 0 == $product[ 'campaign_benefactor_id' ] ) {

                $form->save_benefactor_relationship( $campaign_id, $product_id );

            }

            do_action( 'charitable_campaign_submission_save_product', $submitted, $product_id, $campaign_id, $user_id );
        }
    }

    /**
     * When images are uploaded for the products, they are slotted into a deeper 
     * array that won't work with media_handle_upload, so we reformat the $_FILES array.
     *
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function pre_process_file_data() {
        if ( isset( $_FILES ) && isset( $_FILES[ 'merchandise' ] ) ) {
          
            $files = $_FILES;

            foreach ( $_FILES[ 'merchandise' ] as $key => $file_array ) {
                foreach ( $file_array as $index => $values ) {
                    foreach ( $values as $value_key => $value ) {
                        $files[ 'merchandise_' . $index ][ $key ] = $value;
                    }
                }
            }

            unset( $files[ 'merchandise' ] );

            /* Update the global to our reformatted array. */
            $_FILES = $files;
        }
    }

    /**
     * Save the core data for the product added. 
     *
     * @param   array   $submitted
     * @param   int     $user_id
     * @return  int     $user_id
     * @access  public
     * @since   1.0.0
     */
    public function save_core_product_data( $submitted, $user_id ) {
        $values = array(
            'post_type'     => 'download',
            'post_author'   => $user_id, 
            'post_title'    => $submitted[ 'post_title' ], 
            'post_content'    => $submitted[ 'post_content' ]
        );

        /* Updating an exiting download, so set the ID. */
        if ( isset( $submitted[ 'ID' ] ) && strlen( $submitted[ 'ID' ] ) ) {
            $values[ 'ID' ] = $submitted[ 'ID' ];
            $values[ 'post_status' ] = get_post_status( $submitted[ 'ID' ] );
        }
        /* New download, so set initial post status. */
        else {
            $values[ 'post_status' ] = apply_filters( 'pp_download_submission_initial_status', 'publish' );
        }

        /* Update post if ID is set. */
        if ( isset( $values[ 'ID' ] ) ) {
            return wp_update_post( $values );
        }

        return wp_insert_post( $values );
    }

    /**
     * Save the meta for the product. 
     *
     * @param   array   $submitted
     * @param   int     $product_id
     * @param   int     $index
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function save_product_meta( $submitted, $product_id, $index ) {

        $values = array(
            'edd_price' => edd_sanitize_amount( $submitted[ 'price' ] ), 
            '_edd_purchase_limit' => sanitize_text_field( $submitted[ 'purchase_limit' ] ), 
            '_variable_pricing' => isset( $submitted[ '_variable_pricing' ] ) && $submitted[ '_variable_pricing' ],
            'edd_variable_prices' => isset( $submitted[ 'variable_prices' ] ) ? $submitted[ 'variable_prices' ] : array()            
        );

        $domestic_shipping = $submitted[ 'local_shipping' ];
        $intl_shipping = isset($submitted[ 'international_shipping' ]) ?  $submitted[ 'international_shipping' ] : 0;

        if ( $domestic_shipping || $intl_shipping ) {
            $values[ '_edd_shipping_domestic' ] = edd_sanitize_amount( $domestic_shipping );
            $values[ '_edd_shipping_international' ] = edd_sanitize_amount( $intl_shipping );
            $values[ '_edd_enable_shipping' ] = 1;

            if ( ! empty( $values[ 'edd_variable_prices' ] ) ) {
                foreach ( $values[ 'edd_variable_prices'] as $key => $price ) {
                    $price[ 'shipping' ] = 1;
                    $values[ 'edd_variable_prices' ][ $key ] = $price;
                }
            }
        }

        /**
         * Build start and end date time
         */
        if(isset($submitted['start_date_time']['date']) && !empty($submitted['start_date_time']['date'])){
            $start_date_time = $submitted['start_date_time']['date'].' '.$submitted['start_date_time']['hour'].':'.$submitted['start_date_time']['minute'].' '.$submitted['start_date_time']['meridian'];
            if (($start_timestamp = strtotime($start_date_time)) !== false) {
                $values[ 'merchandise_start_date' ] = date('Y-m-d H:i:s', $start_timestamp);
            } else {
                $values[ 'merchandise_start_date' ] = '';
            }
        } else {
            $values[ 'merchandise_start_date' ] = '';
        }

        if(isset($submitted['end_date_time']['date']) && !empty($submitted['end_date_time']['date'])){
            $end_date_time = $submitted['end_date_time']['date'].' '.$submitted['end_date_time']['hour'].':'.$submitted['end_date_time']['minute'].' '.$submitted['end_date_time']['meridian'];
            if (($end_timestamp = strtotime($end_date_time)) !== false) {
                $values[ 'merchandise_end_date' ] = date('Y-m-d H:i:s', $end_timestamp);
            } else {
                $values[ 'merchandise_end_date' ] = '';
            }
        } else {
            $values[ 'merchandise_end_date' ] = '';
        }

        // echo "<pre>";
        // print_r($values);
        // echo "</pre>";

        // echo "<pre>";
        // print_r($submitted);
        // echo "</pre>";
        // exit();

        // $thumbnail_id = $this->save_picture( $product_id, $index );
        
        // change with product_thumbnail
        $thumbnail_id = (isset($submitted[ 'product_thumbnail' ])) ? $submitted['product_thumbnail'] : '';

        if ( ! is_wp_error( $thumbnail_id ) ) {
            $values[ '_thumbnail_id' ] = $thumbnail_id;
            set_post_thumbnail( $product_id, $thumbnail_id );
        }
        
        //* GUM - code edit 
        // if ( ! is_wp_error( $thumbnail_id ) ) {

        //     $exists = has_post_thumbnail( $product_id );
        //     if ( $exists ) {
        //         update_post_meta( $product_id, '_thumbnail_id', $thumbnail_id );
        //     } else {
        //         add_post_meta( $product_id, '_thumbnail_id', $thumbnail_id );
        //     }
			

        // }

        foreach ( $values as $meta_key => $meta_value ) {
            update_post_meta( $product_id, $meta_key, $meta_value );
        }
    }

    /**
     * Save the category for the produc.t 
     *
     * @param   int     $product_id 
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function save_product_taxonomy( $product_id ) {
        wp_set_object_terms( $product_id, 'Merchandise', 'download_category', true );
    }    

    /**
     * Upload product thumbnail and add file field to the submitted fields. 
     *
     * @param   int         $product_id
     * @param   int         $index
     * @return  int         The ID of the thumbnail. 
     * @access  public
     * @since   1.0.0
     */
    public function save_picture( $product_id, $index ) {        
        if ( ! isset( $_FILES ) || ! isset( $_FILES[ 'merchandise_' . $index ] ) ) {
            return 0;
        }

        $attachment_id = $this->upload_post_attachment(  'merchandise_' . $index, $product_id );

        if ( is_wp_error( $attachment_id ) ) {
            charitable_get_notices()->add_error( __( 'There was an error uploading your product image.', 'pp-toolkit' ) );
        }

        return $attachment_id;
    } 

    /**
     * Save the benefactor relationship between the campaign and the product. 
     *
     * @param   int     $campaign_id
     * @param   int     $product_id
     * @return  int     $campaign_benefactor_id
     * @access  public
     * @since   1.0.0
     */
    public function save_benefactor_relationship( $campaign_id, $product_id ) {

        $has_relationship = charitable()->get_db_table('edd_benefactors')->count_campaign_download_benefactors($campaign_id, $product_id);
        
        if (!$has_relationship) {
            $benefactor_data = apply_filters( 'pp_toolkit_campaign_product_benefactor_data', array(
                'campaign_id'           => $campaign_id, 
                'contribution_amount'   => 100, 
                'contribution_amount_is_percentage' => 1, 
                'contribution_amount_is_per_item' => 1, 
                'benefactor' => array(
                    'edd_download_id'               => $product_id, 
                    'edd_is_global_contribution'    => 0, 
                    'edd_download_category_id'      => 0
                )
            ), $campaign_id, $product_id );

            /* The start and end date will be the same as the end date of the campaign. */
            $campaign_start_date = get_post_field( 'post_date', $campaign_id );
            $campaign_end_date = get_post_meta( $campaign_id, '_campaign_end_date', true );

            if ( !empty($campaign_start_date) ) {
                $benefactor_data['date_created'] = $campaign_start_date;
            }
            
            if ( !empty($campaign_end_date) ) {
                $benefactor_data['date_deactivated'] = $campaign_end_date;
            }

            // if
            $start_sell_date = get_post_meta( $product_id, 'merchandise_start_date', true );
            if(!empty($start_sell_date)){
                $benefactor_data['date_created'] = $start_sell_date;
            }

            $end_sell_date = get_post_meta( $product_id, 'merchandise_end_date', true );
            if(!empty($end_sell_date)){
                $benefactor_data['date_deactivated'] = $end_sell_date;
            }

            return charitable()->get_db_table( 'benefactors' )->insert( $benefactor_data );
        }

        return false;
    }    

    /**
     * Checks whether the image is required. 
     *
     * @param   boolean $exists
     * @param   string  $key
     * @param   array   $field
     * @return  boolean
     * @access  public
     * @since   1.0.0
     */
    public function check_image_required_field( $exists, $key, $field, $submitted, $form ) {
        if ( is_a( $form, 'PP_Merchandise_Form' ) && $key == 'image' ) {        
            $form_id = $submitted[ 'form_id' ];

            $key = 'merchandise_' . $form_id;
            
            $exists = isset( $_FILES[ $key ] ) && ! empty( $_FILES[ $key ][ 'name' ] );

            if ( ! $exists && ! empty( $submitted[ 'ID' ] ) ) {

                $exists = has_post_thumbnail( $submitted[ 'ID' ] );

            }
        }        

        return $exists;
    }
}

endif; // End class_exists check