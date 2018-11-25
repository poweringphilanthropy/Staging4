<?php
/**
 * This is the class responsible for displaying the Ticket form inside of campaign forms.
 *
 * @package     Philanthropy Project/Classes/PP_Ticket_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2014, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PP_Ticket_Form' ) ) : 

/**
 * PP_Ticket_Form
 *
 * @since       1.0.0
 */
class PP_Ticket_Form extends Charitable_Form {

    /**
     * @var     string
     * @access  protected
     */
    protected $nonce_action = 'pp_ticket_form';

    /**
     * @var     string
     * @access  protected
     */
    protected $nonce_name = '_pp_ticket_form_nonce';

    /**
     * Ticket ID
     *
     * @var     int|null
     * @access  protected
     */
    protected $ticket_id;

    /**
     * Event ID
     *
     * @var     int|null
     * @access  protected
     */
    protected $event_id;

    /**
     * Create class object.
     * 
     * @param   string $namespace
     * @param   int $event_id
     * @param   int $ticket_id
     * @param   array $submitted
     * @access  public
     * @since   1.0.0
     */
    public function __construct( $namespace, $event_id, $ticket_id = 0, $submitted = array() ) {
        $this->id = uniqid();   
        $this->namespace = $namespace;
        $this->event_id = $event_id;
        $this->ticket_id = $ticket_id;
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

        add_filter( 'charitable_form_field_key', array( $this, 'set_form_field_key' ), 10, 5 );
    }

    /**
     * Return the ticket object.
     *
     * @return  TribeEventsTicketObject
     * @access  public
     * @since   1.0.0
     */
    public function get_ticket() {
        if ( ! isset( $this->ticket ) ) {
                $this->ticket = Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->get_ticket( $this->event_id, $this->ticket_id );
        }

        return $this->ticket;
    }

    /**
     * Given a key, returns the current value of the field for the event. If we're creating a 
     * new event, this will always return an empty string. 
     *
     * @param   string      $key
     * @return  mixed
     * @access  public
     * @since   1.0.0
     */
    public function get_ticket_value( $key ) {
        if ( isset( $this->submitted[ $key ] ) ) {
            return $this->submitted[ $key ];
        }
        
        $value = "";

        if ( $this->ticket_id ) {
            switch ( $key ) {
                case 'ID' : 
                    $value = $this->ticket_id;
                    break;

                case 'ticket_name' : 
                    $value = $this->get_ticket()->name;
                    break;

                case 'ticket_description' : 
                    $value = $this->get_ticket()->description;
                    break;

                case 'ticket_price' : 
                    $value = $this->get_ticket()->price;
                    break;

                case 'ticket_start_date' : 
                    $value = $this->get_ticket()->start_date;
                    break;

                case 'ticket_end_date' : 
                    $value = $this->get_ticket()->end_date;
                    break;

                case 'ticket_edd_stock' : 
                    $stock = $this->get_ticket()->stock;
                    $value = $stock < 0 ? 0 : $stock;
                    break;
            }
        }

        return $value;
    }    

    /**
     * Return the array of fields displayed in the event form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_fields() {
        $ticket_fields = apply_filters( 'pp_event_fields', array(        
            'ID' => array(
                'type'          => 'hidden', 
                'priority'      => 3, 
                'value'         => $this->get_ticket_value( 'ID' ), 
                'data_type'     => 'core'
            ),
            'ticket_name' => array(
                'label'         => __( 'Ticket Name', 'pp-toolkit' ),
                'type'          => 'text',
                'required'      => false, 
                'fullwidth'     => false,
                'priority'      => 2,
                'value'         => $this->get_ticket_value( 'ticket_name' )
            ),
            'ticket_price' => array(
                'label'         => __( 'Price ($)', 'pp-toolkit' ), 
                'type'          => 'number', 
                'required'      => false,
                'fullwidth'     => false,
                'step'          => .01, 
                'min'           => 0,
                'priority'      => 3, 
                'value'         => $this->get_ticket_value( 'ticket_price' ) 
            ),
            'ticket_description' => array(
                'label'         => __( 'Ticket Description', 'pp-toolkit' ), 
                'type'          => 'textarea', 
                'required'      => false,
                'fullwidth'     => true,
                'priority'      => 6, 
                'value'         => $this->get_ticket_value( 'ticket_description' ) 
            ),           
            'ticket_start_date' => array(
                'label'         => __( 'On Sale From', 'pp-toolkit' ), 
                'type'          => 'datepicker', 
                'required'      => false,
                'fullwidth'     => false,
                'priority'      => 8, 
                'value'         => $this->get_ticket_value( 'ticket_start_date' )
            ),
            'ticket_end_date' => array(
                'label'         => __( 'On Sale Until', 'pp-toolkit' ), 
                'type'          => 'datepicker', 
                'required'      => false,
                'fullwidth'     => false,
                'priority'      => 10, 
                'value'         => $this->get_ticket_value( 'ticket_end_date' )
            ),
            'ticket_edd_stock' => array(
                'label'         => __( '# of Tickets Available', 'pp-toolkit' ), 
                'type'          => 'number', 
                'step'          => 1, 
                'min'           => 0,
                'priority'      => 6, 
                'required'      => false,
                'fullwidth'     => false,
                'priority'      => 12, 
                'value'         => $this->get_ticket_value( 'ticket_edd_stock' ) 
            )
        ), $this );

        uasort( $ticket_fields, 'charitable_priority_sort' );

        return $ticket_fields;
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
            $input_name = sprintf( 'event[%s][tickets][%s][%s]', $this->namespace, $this->id, $key );
        }

        return $input_name;
    }

    /**
     * Save event when saving the campaign.  
     *
     * @param   int     $event_id
     * @param   array   $event
     * @param   int     $campaign_id
     * @return  void
     * @access  public
     * @static
     * @since   1.0.0
     */
    public static function save_tickets( $event_id, $event, $campaign_id, $user_id ) { 
        $tickets = isset( $event[ 'tickets' ] ) ? $event[ 'tickets' ] : array();

        /**
         * Find removed tickets, and remove from event
         * @var array
         */
        $submitted_ids = array_filter(wp_list_pluck( $tickets, 'ID' ));
        $ticket_ids = Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->get_tickets_ids( $event_id );
        if(!is_array($ticket_ids) || empty($ticket_ids))
            $ticket_ids = array();
        
        $diff = array_diff($ticket_ids, $submitted_ids );
        
        if(!empty($diff) && is_array($diff)):
        foreach ($diff as $key => $ticket_id) {
            Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->delete_ticket( $event_id, $ticket_id );
            // may need to remove benefactor relationship
            $rels = charitable()->get_db_table('edd_benefactors')->get_benefactors_for_download($ticket_id);
            if(!empty($rels)):
            foreach ($rels as $key => $rel) {
                if(($rel->campaign_id != $campaign_id) || ($rel->edd_download_id != $ticket_id) )
                    continue;

                charitable_get_table( 'benefactors' )->delete( $rel->campaign_benefactor_id );
            }
            endif;

            // $deleted = charitable_get_table( 'benefactors' )->delete( $benefactor_id );
        }
        endif;

        if ( empty( $tickets ) ) {
            return;
        }

        update_post_meta($event_id, '_tribe_hide_attendees_list', false);

        foreach ( $tickets as $ticket ) {
            $ticket_object = new Tribe__Tickets__Ticket_Object();
            $ticket_object->name = $ticket[ 'ticket_name' ];
            $ticket_object->price = $ticket[ 'ticket_price' ];
            $ticket_object->description = $ticket[ 'ticket_description' ];
            $ticket_object->start_date = $ticket[ 'ticket_start_date' ];
            $ticket_object->end_date = $ticket[ 'ticket_end_date' ];
            $ticket_object->stock = $ticket[ 'ticket_edd_stock' ];
			$ticket['stock'] = $ticket[ 'ticket_edd_stock' ];
			$ticket['capacity'] = $ticket[ 'ticket_edd_stock' ];

            if ( ! empty( $ticket[ 'ID' ] ) ) {
                $ticket_object->ID = $ticket[ 'ID' ];
            }
			$ticket['tribe-ticket']  = $ticket;
            Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->save_ticket( $event_id, $ticket_object, $ticket );
        }

        $ticket_ids = Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->get_tickets_ids( $event_id );

        foreach ( $ticket_ids as $ticket_id ) {
            self::save_benefactor_relationships( $ticket_id, $event_id, $campaign_id );

            // Turn on Meta and copy default template
            $fieldset_id = get_posts([
                'name' => 'additional-information',
                'post_type' => 'ticket-meta-fieldset',
                'post_status' => 'publish',
                'posts_per_page' => 1
            ])[0]->ID;

            if ( $fieldset_id !== null ) {
                $meta_template = get_post_meta($fieldset_id, '_tribe_tickets_meta_template', 1);
                update_post_meta($ticket_id, '_tribe_tickets_meta_enabled', 1);
                update_post_meta($ticket_id, '_tribe_tickets_meta', $meta_template);
            }

            do_action( 'charitable_campaign_submission_save_product', $submitted, $ticket_id, $campaign_id, $user_id );
        }
    }

    /**
     * Set up campaign benefactor relationships for all the tickets.
     *
     * @param   int     $ticket_id
     * @param   int     $event_id
     * @param   int     $campaign_id
     * @return  void
     * @access  public
     * @static
     * @since   1.0.0
     */
    public static function save_benefactor_relationships( $ticket_id, $event_id, $campaign_id ) {

        $has_relationship = charitable()->get_db_table('edd_benefactors')->count_campaign_download_benefactors($campaign_id, $ticket_id);

        if (!$has_relationship) {

            $benefactor_data = apply_filters( 'pp_toolkit_campaign_ticket_benefactor_data', array(
                'campaign_id'           => $campaign_id, 
                'contribution_amount'   => 100, 
                'contribution_amount_is_percentage' => 1, 
                'contribution_amount_is_per_item' => 1, 
                'benefactor' => array(
                    'edd_download_id'               => $ticket_id, 
                    'edd_is_global_contribution'    => 0, 
                    'edd_download_category_id'      => 0
                )
            ), $campaign_id, $event_id );

            /* The start and end date will be the same as the end date of the campaign. */
            $campaign_start_date = get_post_field( 'post_date', $campaign_id );
            $campaign_end_date = get_post_meta( $campaign_id, '_campaign_end_date', true );

            if ( !empty($campaign_start_date)  ) {
                $benefactor_data['date_created'] = $campaign_start_date;
            }
            
            if ( !empty($campaign_end_date) ) { 
                $benefactor_data['date_deactivated'] = $campaign_end_date;
            }

            
            $start_sell_date = get_post_meta( $ticket_id, '_ticket_start_date', true );
            if(!empty($start_sell_date)){
                // need to parse the date because the ticket date show as like this 'September 30, 2017'
                try {
                    $dt = new DateTime( $start_sell_date );
                    $benefactor_data['date_created'] = $dt->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    // failed
                }
            }

            $end_sell_date = get_post_meta( $ticket_id, '_ticket_end_date', true );
            if(!empty($end_sell_date)){
                // need to parse the date because the ticket date show as like this 'September 30, 2017'
                try {
                    $dt = new DateTime( $end_sell_date );
                    $benefactor_data['date_deactivated'] = $dt->format('Y-m-d H:i:s');
                } catch (Exception $e) {
                    // failed
                }
            }

            charitable()->get_db_table( 'benefactors' )->insert( $benefactor_data );

        }
    } 

    public function get_ticket_id(){
        return $this->ticket_id;
    }   
}

endif; // End class_exists check