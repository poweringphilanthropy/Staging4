<?php
/**
 * The class that is responsible for augmenting the campaign submission form, adding the 
 * updates field when editing the campaign.
 *
 * @package     Charitable Simple Updates/Classes/Charitable_Simple_Updates_Campaign_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2014, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Charitable_Simple_Updates_Campaign_Form' ) ) : 

/**
 * Charitable_Simple_Updates_Campaign_Form
 *
 * @since       1.0.0
 */
class Charitable_Simple_Updates_Campaign_Form {

    /**
     * Create object instance.  
     *
     * @return  Charitable_Simple_Updates_Campaign_Form
     * @access  public
     * @static
     * @since   1.0.0
     */
    public static function start( Charitable_Simple_Updates $charitable_su ) {
        if ( ! $charitable_su->is_start() ) {
            return;
        }

        return new Charitable_Simple_Updates_Campaign_Form();
    }

    /**
     * Create class object.
     * 
     * @access  protected
     * @since   1.0.0
     */
    protected function __construct() {
        add_filter( 'charitable_campaign_submission_fields', array( $this, 'add_updates_section' ), 10, 2 );
    }

    /**
     * Add a Campaign Updates section to the Ambassadors form.
     *
     * @param   array[] $sections
     * @param   Charitable_Ambassadors_Campaign_Form $form
     * @return  array[] $sections
     * @access  public
     * @since   1.1.0
     */
    public function add_updates_section( $sections, Charitable_Ambassadors_Campaign_Form $form ) {
        if ( ! $form->get_campaign() ) {
            return $sections;
        }

        $sections[ 'update_fields' ] = apply_filters( 'charitable_simple_updates_campaign_form_section', array(
            'legend'        => __( 'Updates', 'charitable-simple-updates' ), 
            'type'          => 'fieldset',
            'fields'        => $this->get_updates_field( $form ),
            'priority'      => 80,
            'page'          => 'campaign_details'
        ) );

        return $sections;
    }

    /**
     * Return the updates field to the campaign submission form.  
     *
     * @param   Charitable_Ambassadors_Campaign_Form $form
     * @return  array[]
     * @access  public
     * @since   1.1.0
     */
    public function get_updates_field( Charitable_Ambassadors_Campaign_Form $form ) {
        return array(
            'updates' => array(
                'label'         => __( 'Share an update about your campaign', 'charitable-simple-updates' ), 
                'type'          => 'editor', 
                'priority'      => 4, 
                'required'      => false, 
                'fullwidth'     => true, 
                'update_only'   => true,
                'value'         => $form->get_campaign_value( 'updates' ), 
                'data_type'     => 'meta'
            )
        );
    }

    /**
     * @deprecated  1.1.0
     */
    public function add_updates_field( Charitable_Ambassadors_Campaign_Form $form ) {
        _deprecated_function( __METHOD__, '1.1.0' );        
    }    
}

endif; // End class_exists check