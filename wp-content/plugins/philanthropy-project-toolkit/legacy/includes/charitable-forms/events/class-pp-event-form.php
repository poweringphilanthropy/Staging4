<?php
/**
 * This is the class responsible for displaying the Event form inside of campaign forms.
 *
 * @package     Philanthropy Project/Classes/PP_Event_Form
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2014, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * PP_Event_Form
 *
 * @since       1.0.0
 */
class PP_Event_Form extends Charitable_Form {

    /**
     * @var     string
     * @access  protected
     */
    protected $nonce_action = 'pp_event_form';

    /**
     * @var     string
     * @access  protected
     */
    protected $nonce_name = '_pp_event_form_nonce';

    /**
     * Event ID
     *
     * @var     int
     * @access  protected
     */
    protected $event_id;

    /**
     * Submitted values.
     *
     * @var     array
     * @access  public
     */
    public $submitted;

    /**
     * Create class object.
     * 
     * @param   int|null    $event_id
     * @param   array $submitted 
     * @access  public
     * @since   1.0.0
     */
    public function __construct( $event_id = 0, $submitted = array() ) {    
        $this->id = uniqid();   
        $this->event_id = $event_id;
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
        if ( ! is_a( $form, 'PP_Event_Form' ) ) {
            return $submitted;
        }

        if ( is_array( charitable_get_session()->get( 'submitted_events' ) ) ) {
            $submitted = charitable_get_session()->get( 'submitted_events' );
        }

        return $submitted;
    }

    /**
     * Return the event being edited, or false if we're created a new event.    
     *
     * @return  EDD_Download|false
     * @access  public
     * @since   1.0.0
     */
    public function get_event_id() {        
        return $this->event_id;
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
    public function get_event_value( $key ) {

        if ( isset( $this->submitted[ $key ] ) ) {
            return $this->submitted[ $key ];
        }

        $value = "";

        if ( $this->event_id ) {
            switch ( $key ) {
                case 'ID' : 
                    $value = $this->event_id;
                    break;

                case 'post_title' : 
                    $value = get_the_title( $this->event_id );
                    break;

                case 'post_content' : 
                    $value = get_post_field( 'post_content', $this->event_id, 'raw' );
                    break;

                case 'image' : 
                
                	/* GUM Edit
                    $thumbnail_id = get_post_thumbnail_id( $this->event_id );

                    if ( empty( $thumbnail_id ) ) {
                        $value = '';
                    }
                    else {
	                   
                        $value = wp_get_attachment_image( $thumbnail_id );
                        
                    }
                    */
                    //$value = $thumbnail_id;
                    $thumbnail_id = get_post_thumbnail_id( $this->event_id );
                    
                    $value = ( $thumbnail_id ) ? $thumbnail_id : 0;
                    
                    break;
                    
                case 'event_image' : 
                	$thumbnail_id = get_post_thumbnail_id( $this->event_id );
                    $value = wp_get_attachment_image( $thumbnail_id );
                    break;
                
                case 'event_thumbnail' : 
                    $value = get_post_thumbnail_id( $this->event_id );
                    break;
                case 'all_day' : 
                    $value = tribe_event_is_all_day( $this->event_id );
                    break;

                case 'start_date_time' : 
                    $start_date = get_post_meta( $this->event_id, '_EventStartDate', true );
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

                    // list( $date, $time ) = explode( ' ', $start_date );
                    // list( $hour, $minute, $seconds ) = explode( ':', $time );
                    // $meridian = $hour > 12 ? 'pm' : 'am';
                    // $hour = $hour > 12 ? $hour-12 : $hour;
                    // $value = array(
                    //     'date' => $date, 
                    //     'hour' => sprintf("%02d", $hour),
                    //     'minute' => $minute, 
                    //     'meridian' => $meridian 
                    // );
                    break;

                case 'end_date_time' : 
                    $end_date = get_post_meta( $this->event_id, '_EventEndDate', true );
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

                case 'venue_name' :
                    $value = tribe_get_venue( $this->event_id );
                    break;

                case 'venue_address' : 
                    $value = tribe_get_address( $this->event_id );
                    break;

                case 'venue_city' : 
                    $value = tribe_get_city( $this->event_id );
                    break;

                case 'venue_state' : 
                    $value = tribe_get_state( $this->event_id );
                    break;

                case 'venue_zipcode' : 
                    $value = tribe_get_zip( $this->event_id );
                    break;

                case 'venue_phone' : 
                    $value = tribe_get_phone( $this->event_id );
                    break;

                case 'venue_website' : 
                    $vanue_id = tribe_get_venue_id( $this->event_id );
                    $value = tribe_get_event_meta( $vanue_id, '_VenueURL', true );
                    break;

                case 'organizer_name' :
                    $value = tribe_get_organizer( $this->event_id );
                    break;

                case 'organizer_phone' : 
                    $value = tribe_get_organizer_phone( $this->event_id );
                    break;

                case 'organizer_website' : 
                    $value = tribe_get_organizer_website_url( $this->event_id );
                    break;  

                case 'organizer_email' : 
                    $value = tribe_get_organizer_email( $this->event_id );
                    break;                    
            }
        }

        return $value;
    }    

    /**
     * Return any tickets for this event. 
     *
     * @return  array
     * @access  public
     * @since   1.0.0
     */
    public function get_event_tickets() {        
        $tickets = array();

        if ( isset( $this->submitted[ 'tickets' ] ) ) {
            foreach ( $this->submitted[ 'tickets' ] as $submitted ) {
                $tickets[] = array( 'POST' => $submitted );
            }         
        }

        if ( empty( $tickets ) && $this->event_id ) {
            $tickets = pp_get_event_tickets_ids( $this->event_id );
        }

        return $tickets;
    }

    /**
     * Return the array of date fields displayed in the event form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_date_fields() {
        $date_fields = apply_filters( 'pp_event_date_fields', array(
            /* GUM Edit 
	        'all_day' => array(
                'label'     => __( 'All day event', 'pp-toolkit' ),
                'type'      => 'checkbox', 
                'required'  => false,
                'checked'    => $this->get_event_value( 'all_day' ), 
                'priority'   => 2, 
                'fullwidth' => true
            ),
            */ 
            'start_date_time' => array(
                'label'     => __( 'Start Date & Time', 'pp-toolkit' ),
                'type'      => 'datetime', 
                'required'  => true,
                'value'     => $this->get_event_value( 'start_date_time' ), 
                'priority'   => 4
            ),
            'end_date_time' => array(
                'label'     => __( 'End Date & Time', 'pp-toolkit' ),
                'type'      => 'datetime', 
                'required'  => true,
                'value'     => $this->get_event_value( 'end_date_time' ), 
                'priority'   => 6
            )
        ), $this );

        uasort( $date_fields, 'charitable_priority_sort' );

        return $date_fields;
    }

    /**
     * Return the array of venue fields displayed in the event form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_venue_fields() {
        $venue_fields = apply_filters( 'pp_event_venue_fields', array(
            'venue_name' => array(
                'label'     => __( 'Venue Name', 'pp-toolkit' ), 
                'type'      => 'text',
                'required'  => false,
                'value'     => $this->get_event_value( 'venue_name' ), 
                'priority'  => 0
            ),
            'venue_address' => array(
                'label'     => __( 'Address', 'pp-toolkit' ), 
                'type'      => 'text', 
                'required'  => false, 
                'value'     => $this->get_event_value( 'venue_address' ), 
                'priority'  => 2
            ), 
            'venue_city' => array(
                'label'     => __( 'City', 'pp-toolkit' ), 
                'type'      => 'text', 
                'required'  => false, 
                'value'     => $this->get_event_value( 'venue_city' ), 
                'priority'  => 4
            ),
            'venue_state' => array(
                'label'     => __( 'State', 'pp-toolkit' ), 
                'type'      => 'select', 
                'required'  => false, 
                'value'     => $this->get_event_value( 'venue_state' ), 
                'priority'  => 8, 
                'options'   => array('' => 'Please select') + charitable_get_location_helper()->get_states_for_country( 'US' )
            ),
            'venue_zipcode' => array(
                'label'     => __( 'Postal Code', 'pp-toolkit' ), 
                'type'      => 'text', 
                'required'  => false, 
                'value'     => $this->get_event_value( 'venue_zipcode' ), 
                'priority'  => 10
            ), 
            'venue_phone' => array(
                'label'     => __( 'Phone', 'pp-toolkit' ), 
                'type'      => 'text', 
                'required'  => false, 
                'value'     => $this->get_event_value( 'venue_phone' ), 
                'priority'  => 12
            ),
            'venue_website' => array(
                'label'     => __( 'Website', 'pp-toolkit' ), 
                'type'      => 'text', 
                'required'  => false, 
                'value'     => $this->get_event_value( 'venue_website' ), 
                'priority'  => 14
            )
        ), $this );

        uasort( $venue_fields, 'charitable_priority_sort' );

        return $venue_fields;
    }

    /**
     * Return the array of organizer fields displayed in the event form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_organizer_fields() {
        $organizer_fields = apply_filters( 'pp_event_organizer_fields', array(
            'organizer_name' => array(
                'label'     => __( 'Organizer Name', 'pp-toolkit' ), 
                'type'      => 'text',
                'required'  => false,
                'value'     => $this->get_event_value( 'organizer_name' ), 
                'priority'  => 0
            ),
            'organizer_email' => array(
                'label'     => __( 'Email', 'pp-toolkit' ), 
                'type'      => 'email',
                'required'  => false,
                'value'     => $this->get_event_value( 'organizer_email' ), 
                'priority'  => 0
            ),
            'organizer_phone' => array(
                'label'     => __( 'Phone', 'pp-toolkit' ), 
                'type'      => 'text', 
                'required'  => false, 
                'value'     => $this->get_event_value( 'organizer_phone' ), 
                'priority'  => 12
            ),
            'organizer_website' => array(
                'label'     => __( 'Website', 'pp-toolkit' ), 
                'type'      => 'text', 
                'required'  => false, 
                'value'     => $this->get_event_value( 'organizer_website' ), 
                'priority'  => 14
            )
        ), $this );

        uasort( $organizer_fields, 'charitable_priority_sort' );

        return $organizer_fields;
    }

    /**
     * Return the array of ticket fields displayed in the event form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_ticket_fields() {
        $ticket_fields = apply_filters( 'pp_event_ticket_fields', array(
            'ticket' => array(
                'priority'  => 2, 
                'type'      => 'ticket', 
                'value'     => $this->get_event_tickets()
            ) 
        ), $this );

        uasort( $ticket_fields, 'charitable_priority_sort' );

        return $ticket_fields;
    }

    /**
     * Return the array of fields displayed in the event form. 
     *
     * @return  array[]
     * @access  public
     * @since   1.0.0
     */
    public function get_fields() {
	    
	    $thumbnail_id = get_post_thumbnail_id( $this->event_id );
        $thumbnail_html = wp_get_attachment_image( $thumbnail_id );
	    
        $event_fields = apply_filters( 'pp_event_fields', array(
            'form_id' => array(
                'type'          => 'hidden',
                'priority'      => 0,
                'value'         => $this->id
            ),
            'ID' => array(
                'type'          => 'hidden', 
                'priority'      => 0, 
                'value'         => $this->get_event_value( 'ID' )
            ),
            'post_title' => array(
                'label'         => __( 'Event Name', 'pp-toolkit' ),
                'type'          => 'text',
                'required'      => true, 
                'fullwidth'     => true,
                'priority'      => 2,
                'placeholder'   => __( '', 'pp-toolkit' ), 
                'value'         => $this->get_event_value( 'post_title' )
            ),
            'post_content' => array(
                'label'         => __( 'Event Description', 'pp-toolkit' ),
                'type'          => 'textarea',
                'required'      => false, 
                'fullwidth'     => true,
                'rows'          => 4,
                'priority'      => 4,
                'value'         => $this->get_event_value( 'post_content' )
            ),

            //* GUM - code edit 
            /*
            'image' => array(
                'label'         => __( 'Event Image', 'pp-toolkit' ),
                'type'          => 'picture',
                'required'      => false,
                'fullwidth'     => false,
                'priority'      => 6,
                'parent_id'   	=> $this->get_event_value( 'ID' ), //* GUM - code edit 
                'value'         => $this->get_event_value( 'image' )
            ),
            */
            // 'event_image' => array(
            //     'label'         => __( 'Upload Image File', 'pp-toolkit' ),
            //     'type'          => 'file',
            //     'required'      => false,
            //     'fullwidth'     => false,
            //     'priority'      => 5,
            //     'parent_id'   	=> $this->get_event_value( 'ID' ),
            //     //'value'         => $this->get_event_value( 'event_image' )
            // ),
            
            // 'event_image_name' => array (
            //     'type'          => 'paragraph',
            //     'priority'      => 5,
            //     'fullwidth'     => false,
            //     'content'       => __( 'Event Image <br />', 'pp-toolkit' ) . $thumbnail_html,
            // ),
            
            // change to ajax upload 
            'event_thumbnail' => array(
                'label'         => __( 'Event Image', 'pp-toolkit' ),
                'type'          => 'image-crop',
                'editor-setting'          => array(
                    'expected-height' => 400,
                    'expected-width' => 600,
                    'max-preview-width' => '650px',
                    'placeholder' => '(Ideal size is 600 wide by 400px high)',
                ),
                'required'      => false,
                'fullwidth'     => true,
                'priority'      => 5,
                'parent_id'     => $this->get_event_value( 'ID' ),
                'value'         => $this->get_event_value( 'event_thumbnail' )
            ),
            
            'date_fields' => array(
                'legend'        => __( 'Date & Time', 'pp-toolkit' ), 
                'type'          => 'fieldset', 
                'fields'        => $this->get_date_fields(),
                'priority'      => 8,
                'class'         => 'header_black_text header_larger-font',
            ), 
            'venue_fields' => array(
                'legend'        => __( 'Venue Details', 'pp-toolkit' ), 
                'type'          => 'fieldset', 
                'fields'        => $this->get_venue_fields(), 
                'priority'      => 10,
                'class'         => 'header_black_text header_larger-font',
            ), 
            'organizer_fields' => array(
                'legend'        => __( 'Organizer Details', 'pp-toolkit' ), 
                'type'          => 'fieldset', 
                'fields'        => $this->get_organizer_fields(), 
                'priority'      => 12,
                'class'         => 'header_black_text header_larger-font',
            ),
            'ticket_fields' => array(
                'legend'        => __( 'Tickets', 'pp-toolkit' ), 
                'type'          => 'fieldset', 
                'fields'        => $this->get_ticket_fields(), 
                'priority'      => 14,
                'class'         => 'header_black_text header_larger-font',
            )
        ), $this );

        uasort( $event_fields, 'charitable_priority_sort' );

        return $event_fields;
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
            $input_name = sprintf( 'event[%s][%s]', $this->id, $key );
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

        foreach ( array( 'venue_fields', 'organizer_fields', 'ticket_fields', 'date_fields' ) as $fieldset ) {
            $fields = array_merge( $fields, $fields[ $fieldset ][ 'fields' ] );
            unset( $fields[ $fieldset ] );
        }
        
        return $fields;
    }


    public static function check_event_required_fields($missing, $form, $fields, $submitted) {

        // Check if there are any events to validate
        $events = isset($submitted['event']) ? $submitted['event'] : '';
        if (empty($events))
            return $missing;

        $required_fields = [
            'post_title',
            'start_date_time',
            'end_date_time',
            
        ];

        $errors = false;

        foreach ($events as $event) {

            if ($errors)
                continue;

            foreach ($event as $key => $value) {
                if (!in_array($key, $required_fields) || $errors)
                    continue;

                if (!is_array($value) && empty($value)) {
                    $errors = true;
                } else if (is_array($value) && empty($value['date'])) {
                    $errors = true;
                }
            }


        }

        if ($errors)
            $missing[] = 'There are missing required fields for one or more of your Events.';

        return $missing;
    }


    /**
     * Save event when saving the campaign.  
     *
     * @param   array   $submitted
     * @param   int     $campaign_id
     * @param   int     $user_id
     * @return  void
     * @access  public
     * @static
     * @since   1.0.0
     */
    public static function save_event($submitted, $campaign_id, $user_id) {

        $events = isset($submitted['event']) ? $submitted['event'] : array();

        /**
         * Find removed tickets, and remove from event
         * @var array
         */
        $submitted_ids = array_filter(wp_list_pluck( $events, 'ID' ));
        $old_events = get_post_meta( $campaign_id, '_campaign_events', true );
        if(empty($old_events) || !is_array($old_events))
            $old_events = array();

        $diff = array_diff($old_events, $submitted_ids );

        // maybe delete old events
        if(!empty($diff) && is_array($diff)):
        foreach ($diff as $key => $old_event) {
            // delete tickets
            $ticket_ids = Tribe__Tickets_Plus__Commerce__EDD__Main::get_tickets_ids( $old_event );
            if(!empty($ticket_ids) && is_array($ticket_ids)):
            foreach ($ticket_ids as $key => $ticket_id) {
                Tribe__Tickets_Plus__Commerce__EDD__Main::get_instance()->delete_ticket( $old_event, $ticket_id );
            }
            endif;

            // tribe_delete_event( $postId, $force_delete = false );
            tribe_delete_event( $old_event, true );
        }

        // remove from post meta
        update_post_meta( $campaign_id, '_campaign_events', array() );

        endif;

        if (empty($events)){
        	return;
        }

        $form = new PP_Event_Form();

        add_filter('charitable_required_field_exists', array($form, 'check_image_required_field'), 10, 5);

        $form->pre_process_file_data();        

        $campaign_events = array();

        foreach ($events as $index => $event) {

            /* If required fields are missing, do not save the event and make sure we redirect to the campaign edit page. */
            if ( !$form->check_required_fields( $form->get_merged_fields(), $event) ) {

                // If this was a new campaign submission, set the status back to draft.
                if ( ! isset( $submitted[ 'ID' ] ) || empty( $submitted[ 'ID' ] ) ) {
                    wp_update_post( array(
                        'ID' => $campaign_id, 
                        'post_status' => 'draft'
                    ) );
                }

                charitable_get_session()->set( 'submitted_events', $events );

                add_filter( 'charitable_campaign_submission_redirect_url', array( 'PP_Charitable_Campaign_Form', 'redirect_submission_to_edit_page' ), 10, 3 );

                continue;
            }

            /* Set up the full array of event data */
            $event_data = apply_filters( 'pp_toolkit_event_data', array(
                'ID'                => $event['ID'],
                'post_title'        => $event['post_title'],
                'post_content'      => $event['post_content'],
                'post_type'         => 'tribe_events',
                'post_status'       => 'publish',
                'EventAllDay'       => isset( $event['all_day'] ),
                'EventStartDate'    => date('Y-m-d', strtotime($event['start_date_time']['date'])),
                'EventStartHour'    => $event['start_date_time']['hour'], 
                'EventStartMinute'  => $event['start_date_time']['minute'], 
                'EventStartMeridian'=> $event['start_date_time']['meridian'],
                'EventEndDate'      => date('Y-m-d', strtotime($event['end_date_time']['date'])),
                'EventEndHour'      => $event['end_date_time']['hour'], 
                'EventEndMinute'    => $event['end_date_time']['minute'], 
                'EventEndMeridian'  => $event['end_date_time']['meridian'], 
                'EventShowMap'      => true, 
                'Venue' => array(
                    'Venue'         => $event['venue_name'], 
                    'Country'       => 'US',
                    'Address'       => $event['venue_address'],
                    'City'          => $event['venue_city'],
                    'State'         => $event['venue_state'],
                    'Zip'           => $event['venue_zipcode'],
                    'Phone'         => $event['venue_phone'], 
                    'URL'           => $event['venue_website']
                ),
                'Organizer' => array(
                    'Organizer'     => $event['organizer_name'],
                    'Email'         => $event['organizer_email'],
                    'Website'       => $event['organizer_website'],
                    'Phone'         => $event['organizer_phone']
                )
            ), $event, $campaign_id, $user_id, $submitted );

            /* Save the event and get the ID */
            if (empty($event['ID'])) {
                $event_id = tribe_create_event($event_data);
            } else {
                $event_id = tribe_update_event( $event[ 'ID' ], $event_data );
            }

            /* Save thumbnail */
            // $thumbnail_id = $form->save_picture( $event_id, $index );

            // change with event_thumbnail
            $thumbnail_id = (isset($event[ 'event_thumbnail' ])) ? $event['event_thumbnail'] : '';
            if(!empty($thumbnail_id)){
                set_post_thumbnail( $event_id, $thumbnail_id );
            }

            
            // echo $event['ID'];
            // echo "<pre>";
            // print_r($event);
            // echo "</pre>";
            // exit();
            // if ( ! is_wp_error( $thumbnail_id ) ) {

            //     //* GUM - code edit 
            //     //add_post_meta( $event_id, '_thumbnail_id', $thumbnail_id );
            //     $exists = has_post_thumbnail( $event_id );
            //     if ( $exists ) {
	           //      update_post_meta( $event_id, '_thumbnail_id', $thumbnail_id );
            //     } else {
	           //      add_post_meta( $event_id, '_thumbnail_id', $thumbnail_id );
            //     }
				

            // }

            $campaign_events[] = $event_id;

            /* Delegate the ticket creation to the ticket form model. */
            PP_Ticket_Form::save_tickets( $event_id, $event, $campaign_id, $user_id ); //original
            
            update_post_meta( $event_id, '_event_linked_campaign', $campaign_id );
        }

        /* Associate the events with the campaign */
        update_post_meta( $campaign_id, '_campaign_events', $campaign_events );

    }

    /**
     * When images are uploaded for the events, they are slotted into a deeper 
     * array that won't work with media_handle_upload, so we reformat the $_FILES array.
     *
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function pre_process_file_data() {
        if ( isset( $_FILES ) && isset( $_FILES[ 'event' ] ) ) {
          
            $files = $_FILES;

            foreach ( $_FILES[ 'event' ] as $key => $file_array ) {
                foreach ( $file_array as $index => $values ) {
                    foreach ( $values as $value_key => $value ) {
                        $files[ 'event_' . $index ][ $key ] = $value;
                    }
                }
            }

            unset( $files[ 'event' ] );

            /* Update the global to our reformatted array. */
            $_FILES = $files;
        }

    }

    /**
     * Upload event thumbnail and add file field to the submitted fields. 
     *
     * @param   int         $event_id
     * @param   int         $index
     * @return  int         The ID of the thumbnail. 
     * @access  public
     * @since   1.0.0
     */
    public function save_picture( $event_id, $index ) {        
        if ( ! isset( $_FILES ) || ! isset( $_FILES[ 'event_' . $index ] ) ) {
            return 0;
        }

        $attachment_id = $this->upload_post_attachment(  'event_' . $index, $event_id );
        
        if ( is_wp_error( $attachment_id ) ) {
            charitable_get_notices()->add_error( __( 'There was an error uploading your event photo.', 'pp-toolkit' ) );
        }

        return $attachment_id;
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

        if ( is_a( $form, 'PP_Event_Form' ) && $key == 'image' ) {
            $form_id = $submitted[ 'form_id' ];
            
            $key = 'event_' . $form_id;

            $exists = isset( $_FILES[ $key ] ) && ! empty( $_FILES[ $key ][ 'name' ] );

            if ( ! $exists && ! empty( $submitted[ 'ID' ] ) ) {

                $exists = has_post_thumbnail( $submitted[ 'ID' ] );

            }
        }                

        return $exists;
    }
}

