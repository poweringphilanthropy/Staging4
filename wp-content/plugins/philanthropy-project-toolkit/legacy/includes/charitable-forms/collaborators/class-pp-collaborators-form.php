<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'PP_Event_Form' ) ) : 

/**
 * PP_Event_Form
 *
 * @since       1.0.0
 */
class PP_volunteers_Form extends Charitable_Form {

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
     * Create class object.
     * 
     * @param   int|null    $event_id
     * @access  public
     * @since   1.0.0
     */
    public function __construct( $event_id = 0 ) {    
        $this->id = uniqid();   
        $this->event_id = $event_id;
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
                'required'      => true, 
                'fullwidth'     => true,
                'rows'          => 4,
                'priority'      => 4,
                'value'         => $this->get_event_value( 'post_content' )
            ),
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
    public static function save_event( $submitted, $campaign_id, $user_id ) {
        $events = isset( $submitted[ 'event' ] ) ? $submitted[ 'event' ] : array();
        error_log('[CAMPAIGN_ID: '.$campaign_id.']');
        error_log('[EVENTS] '.print_r($events,1));
        if ( empty( $events ) ) {
            return;
        }

        $user = new Charitable_User( $user_id );

        $form = new PP_Event_Form();

        add_filter( 'charitable_required_field_exists', array( $form, 'check_image_required_field' ), 10, 5 );

        $form->pre_process_file_data();

        $campaign_events = array();

        foreach ( $events as $index => $event ) {

            /* If required fields are missing, do not save the campaign. */
            if ( ! $form->check_required_fields( $form->get_merged_fields(), $event ) ) {
                continue;
            }   
            
            /* Set up the full array of event data */
            $event_data = apply_filters( 'pp_toolkit_event_data', array(
                'ID'                => $event[ 'ID' ],
                'post_title'        => $event[ 'post_title' ],
                'post_content'      => $event[ 'post_content' ],
                'post_type'         => 'tribe_events',
                'post_status'       => 'publish',
                'EventAllDay'       => isset( $event[ 'all_day' ] ),
                'EventStartDate'    => $event[ 'start_date_time' ][ 'date' ], 
                'EventStartHour'    => $event[ 'start_date_time' ][ 'hour' ], 
                'EventStartMinute'  => $event[ 'start_date_time' ][ 'minute' ], 
                'EventStartMeridian'=> $event[ 'start_date_time' ][ 'meridian' ],
                'EventEndDate'      => $event[ 'end_date_time' ][ 'date' ], 
                'EventEndHour'      => $event[ 'end_date_time' ][ 'hour' ], 
                'EventEndMinute'    => $event[ 'end_date_time' ][ 'minute' ], 
                'EventEndMeridian'  => $event[ 'end_date_time' ][ 'meridian' ], 
                'EventShowMap'      => true, 
                'Venue' => array(
                    'Venue'         => $event[ 'venue_name' ], 
                    'Country'       => 'US',
                    'Address'       => $event[ 'venue_address' ],
                    'City'          => $event[ 'venue_city' ],
                    'State'         => $event[ 'venue_state' ],
                    'Zip'           => $event[ 'venue_zipcode' ],
                    'Phone'         => $event[ 'venue_phone' ], 
                    'URL'           => $event[ 'venue_website' ]
                ),
                'Organizer' => array(
                    'Organizer'     => $event[ 'organizer_name' ],
                    'Email'         => $event[ 'organizer_email' ],
                    'Website'       => $event[ 'organizer_website' ],
                    'Phone'         => $event[ 'organizer_phone' ]
                )
            ), $event, $campaign_id, $user_id, $submitted );

            /* Save the event and get the ID */
            if ( empty( $event[ 'ID' ] ) ) {

                $event_id = tribe_create_event( $event_data );

            }
            else {

                $event_id = tribe_update_event( $event[ 'ID' ], $event_data );

            }

            /* Save thumbnail */
            $thumbnail_id = $form->save_picture( $event_id, $index );

            if ( ! is_wp_error( $thumbnail_id ) ) {

                add_post_meta( $event_id, '_thumbnail_id', $thumbnail_id );

            }

            $campaign_events[] = $event_id;

            /* Delegate the ticket creation to the ticket form model. */
            PP_Ticket_Form::save_tickets( $event_id, $event, $campaign_id, $user_id );
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

endif; // End class_exists check