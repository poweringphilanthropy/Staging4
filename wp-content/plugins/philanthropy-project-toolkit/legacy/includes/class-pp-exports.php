<?php
/**
 * PP_Exports Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Exports
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Exports class.
 */
class PP_Exports {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Exports();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        
        add_action('init', array($this, 'maybe_download_event_attendees_list'));
        
        /* Modify Campaign Export */
        add_filter( 'charitable_ambassadors_campaign_creator_donations_export_args', array($this, 'campaign_creator_donations_export_args'), 10, 1 );

        /* Custom download handler */
        add_action( 'charitable_creator_download_donations', array( $this, 'download_donations_csv' ), 9 );

        add_action('init', array($this, 'maybe_download_export_service_hours'));
    }

    public function maybe_download_export_service_hours(){
        // The download link sets a nonce, yet we're not checking it?
        if (!isset($_GET['pp_export_service_hours']))
            return false;

        if (!isset($_GET['pp_export_nonce']) || !wp_verify_nonce($_GET['pp_export_nonce'], 'export-service-hours')) 
            return;

        $export_type = $_GET['pp_export_service_hours'];
        switch ($export_type) {
            case 'dashboard':
                if(!isset($_GET['dashboard_id'])){
                    return;
                }

                require_once( pp_toolkit()->directory_path . 'helpers/class-pp-export-service-hours.php' );
                new Philanthropy_Export_Service_Hours( array('export_type' => $export_type, 'dashboard_id' => $_GET['dashboard_id'] ) );
                exit();

                break;
        }
    }

    public function campaign_creator_donations_export_args($args){
        $args['status'] = 'charitable-completed';
        return $args;
    }

    public function maybe_download_event_attendees_list() {

        // The download link sets a nonce, yet we're not checking it?
        if (!isset($_GET['download_attendees']))
            return;

        if (!isset($_GET['event_id']))
            return;

        $event_id = $_GET['event_id'];

        return $this->pp_download_event_attendees($event_id);
    }

    /**
     * Download CSV with event attendees. 
     *
     * @param   int     $event_id
     * @return  void
     * @since   1.0.0
     */
    private function pp_download_event_attendees( $event_id ) {    
        /* Check for nonce existence. */
        if ( ! isset( $_GET[ 'download_attendees_nonce'] ) || ! wp_verify_nonce( $_GET[ 'download_attendees_nonce'], 'download_attendees_' . $event_id ) ) {
            return;
        }

        $author_id = get_post_field( 'post_author', $event_id );
        if ( intval($author_id) !== intval( get_current_user_id() ) ) {
            return;
        }

        $items = TribeEventsTickets::get_event_attendees( $event_id );
        $event = get_post( $event_id );

        if ( ! empty( $items ) ) {

            $charset  = get_option( 'blog_charset' );
            $filename = sanitize_file_name( $event->post_title . '-' . __( 'attendees', 'tribe-events-calendar' ) );
            $columns  = array( 
                'order_id' => __( 'Order #' ), 
                'order_status' => __( 'Order Status' ),
                'purchaser_name' => __( 'Purchaser name' ), 
                'purchaser_email' => __( 'Purchaser email', 'pp-toolkit' ),
                'ticket' => __( 'Ticket type', 'pp-toolkit' ),
                'attendee_id' => __( 'Ticket #', 'pp-toolkit' ),
                'security' => __( 'Security Code', 'pp-toolkit' ),
                'check_in' => __( 'Check in', 'pp-toolkit' ),
                'ticket_holder' => __( 'Attendees', 'pp-toolkit' )
            );

            /* Output headers so that the file is downloaded rather than displayed */
            header( "Content-Type: text/csv; charset=$charset" );
            header( "Content-Disposition: attachment; filename=$filename.csv" );

            /* Create a file pointer connected to the output stream */
            $output = fopen( 'php://output', 'w' );

            /* Print header row */
            fputcsv( $output, array_values( $columns ) );        

            /* And echo the data */
            foreach ( $items as $item ) {

                $attendee_meta = get_post_meta( $item['attendee_id'], Tribe__Tickets_Plus__Meta::META_KEY, true );
                $attendee_name = (isset($attendee_meta['ticket-holder-name'])) ? $attendee_meta['ticket-holder-name'] : '';

                $row  = array( 
                    'order_id' => $item['order_id'], 
                    'order_status' => $item['order_status'],
                    'purchaser_name' => $item['purchaser_name'], 
                    'purchaser_email' => $item['purchaser_email'],
                    'ticket' => $item['ticket'],
                    'attendee_id' => $item['attendee_id'],
                    'security' => $item['security'],
                    'check_in' => '', // not sure
                    'ticket_holder' => $attendee_name
                );

                fputcsv( $output, $row );
            }

            fclose( $output );
            exit;
        }
    }

    public function download_donations_csv(){

        if ( ! isset( $_GET['campaign_id'] ) ) {
                return false;
        }

        $campaign_id = $_GET['campaign_id'];

        /* Check for nonce existence. */
        if ( ! charitable_verify_nonce( 'download_donations_nonce', 'download_donations_' . $campaign_id ) ) {
            return false;
        }

        if ( ! charitable_is_current_campaign_creator( $campaign_id ) ) {
            return false;
        }

        require_once( pp_toolkit()->directory_path . 'helpers/class-philanthropy-export-donations.php' );

        add_filter( 'charitable_export_capability', '__return_true' );

        $export_args = apply_filters( 'charitable_ambassadors_campaign_creator_donations_export_args', array(
            'campaign_id'   => pp_get_merged_team_campaign_ids($campaign_id),
        ) );

        /**
         * Use our custom class to change csv filename, and custom data source (get data from edd payment)
         */
        if(function_exists('ppcde_add_export_data')) 
            remove_filter( 'charitable_export_data_key_value', 'ppcde_add_export_data' );
        
        // add_filter( 'charitable_export_data_key_value', array($this, 'change_export_data_key_value'), 11, 3);
        
        new Philanthropy_Export_Donations( $export_args );

        exit();
    }

    public function change_export_data_key_value($value, $key, $data){

        // if($key == 'purchase_details'){
        //  $matched_items = array();
        //  $row_has_shipping = false;

        //  $donation_log = get_post_meta( $data[ 'donation_id' ], 'donation_from_edd_payment_log', true );
        //  $payment_id = Charitable_EDD_Payment::get_payment_for_donation( $data[ 'donation_id' ] );

  //           foreach ( $donation_log as $idx => $campaign_donation ) {
  //               /* If we have already listed this donation ID, skip */
  //               if ( isset( $matched_items[ $data[ 'donation_id' ] ][ $idx ] ) ) {
  //                   continue;
  //               }

  //               /* If the campaign_id in the donation log entry does not match the current campaign donation record, skip */
  //               if ( $campaign_donation[ 'campaign_id' ] != $data[ 'campaign_id' ] ) {
  //                   continue;
  //               }

  //           //     /* If the amount does not match, skip */
  //           //     if ( $campaign_donation[ 'amount' ] != $data[ 'amount' ] ) {
  //           //         continue;
  //           //     }

  //               /* At this point, we know it matches. Check if it's a fee */
  //               if ( $campaign_donation[ 'edd_fee' ] ) {

  //                   $value = __( 'Donation', 'ppcde' );

  //               }
  //               /* If not, work through the purchased downloads and find the matching one. */
  //               else {
  //                   $payment_id = Charitable_EDD_Payment::get_payment_for_donation( $data[ 'donation_id' ] );

  //                   foreach ( edd_get_payment_meta_cart_details( $payment_id ) as $download ) {

  //                       /* The download ID must match */
  //                       if ( $download[ 'id' ] != $campaign_donation[ 'download_id' ] ) {
  //                           continue;
  //                       }

  //                       // The amount for this particular download must also match 
  //                       if ( $download[ 'subtotal' ] != $data[ 'amount' ] ) {
  //                           continue;
  //                       }
                    
  //                       $download_description = $download[ 'name' ];

  //                       if ( isset( $download[ 'item_number' ][ 'options' ][ 'price_id' ] ) ) {
  //                           $download_description = sprintf( '%s (%s)', $download_description, edd_get_price_option_name( $download[ 'id' ], $download[ 'item_number' ][ 'options' ][ 'price_id' ] ) );
  //                       }

  //                       /* If we get here, we have a match. */
  //                       $value = sprintf( '%s x %d', strip_tags( $download_description ), $download[ 'quantity' ] );

  //                       /* Check for shipping fees associated with this download */
  //                       if ( empty( $download[ 'fees' ] ) ) {
  //                           break;
  //                       }
                        
  //                       foreach ( $download[ 'fees' ] as $fee_id => $fee ) {
  //                           if ( false === strpos( $fee_id, 'simple_shipping' ) ) {
  //                               continue;
  //                           }

  //                           /* This row has shipping, so store the $fee in our static variable */
  //                           $row_has_shipping = $fee;
  //                           break;
  //                       }

  //                       break;

  //                       die;
  //                   }
  //               }

  //               // $matched_items[ $data[ 'donation_id' ] ][ $idx ] = $value;
  //           }
            
  //            $value = array(
  //                'donation_log' => $donation_log,
  //                'payment_id' => $payment_id,
  //                'cart' =>  edd_get_payment_meta_cart_details( $payment_id )
  //            );
        // }

        return $value;
    }

    public function includes(){

    }

}

PP_Exports::init();