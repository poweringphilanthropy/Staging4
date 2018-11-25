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
    
        add_filter( 'edd_export_csv_cols_charitable_campaign_payments', array($this, 'add_additional_export_columns'), 10, 1 );
        add_filter( 'edd_export_get_data_charitable_campaign_payments', array($this, 'add_additional_export_data'), 10 );
    
        add_action( 'pp_before_dashboard_export', array($this, 'download_dashboard_transactions_report'), 10, 2 );
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

    public function add_additional_export_columns($cols){

        $cols['covered_fees'] = __('Covered Fees', 'pp');
        $cols['total_fees'] = __('Total Fees', 'pp');

        return $cols;
    }

    public function add_additional_export_data($data){
		print_r($data);
		exit;
        if(!empty($data) && is_array($data)):
        foreach ($data as $key => $dt) {
            if(!isset($dt['id']))
                continue;

            $charge_details = edd_get_payment_meta( $dt['id'], 'charge_details' );
            $total_fees = isset($charge_details['total-fee-amount']) ? $charge_details['total-fee-amount'] : 0;
            $covered_fees = isset($charge_details['donor-covers-fee']) && ($charge_details['donor-covers-fee'] == 'yes') ? $total_fees : 0;
            
            $data[$key]['covered_fees'] = html_entity_decode( edd_format_amount( $covered_fees ) );
            $data[$key]['total_fees'] = html_entity_decode( edd_format_amount( $total_fees ) );
        }
        endif; 

        return $data;
    }

    public function download_dashboard_transactions_report($type, $term){

        if($type != 'transactions')
            return;

        require_once( pp_toolkit()->directory_path . 'includes/exports/class-pp-export.php' );

        $cols = array(
            'id'                => __( 'Payment ID', 'charitable-edd' ), // unaltered payment ID (use for querying)
            'seq_id'            => __( 'Payment Number', 'charitable-edd' ), // sequential payment ID
            'donation_id'       => __( 'Donation ID', 'charitable-edd' ),
            'email'             => __( 'Email', 'charitable-edd' ),
            'first'             => __( 'First Name', 'charitable-edd' ),
            'last'              => __( 'Last Name', 'charitable-edd' ),
            'address1'          => __( 'Address', 'charitable-edd' ),
            'address2'          => __( 'Address (Line 2)', 'charitable-edd' ),
            'city'              => __( 'City', 'charitable-edd' ),
            'state'             => __( 'State', 'charitable-edd' ),
            'country'           => __( 'Country', 'charitable-edd' ),
            'zip'               => __( 'Zip Code', 'charitable-edd' ),
            'campaign_id'       => __( 'Campaign ID', 'charitable-edd' ),
            'campaign_name'     => __( 'Campaign Name', 'charitable-edd' ),
            'campaign_amount'   => __( 'Campaign Donation Amount', 'charitable-edd' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
            'products'          => __( 'Products', 'charitable-edd' ),
            'quantity'          => __( 'Quantity', 'charitable-edd' ),
            'skus'              => __( 'SKUs', 'charitable-edd' ),
            'amount'            => __( 'Total Payment Amount', 'charitable-edd' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
            'tax'               => __( 'Tax', 'charitable-edd' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
            'discount'          => __( 'Discount Code', 'charitable-edd' ),
            'discount_amount'   => __( 'Total Discount', 'charitable-edd' ) . ' (' . html_entity_decode( edd_currency_filter( '' ) ) . ')',
            'gateway'           => __( 'Payment Method', 'charitable-edd' ),
            'trans_id'          => __( 'Transaction ID', 'charitable-edd' ),
            'key'               => __( 'Purchase Key', 'charitable-edd' ),
            'date'              => __( 'Date', 'charitable-edd' ),
            'user'              => __( 'User', 'charitable-edd' ),
            'status'            => __( 'Status', 'charitable-edd' ),
        );

        if ( ! edd_use_skus() ) {
            unset( $cols['skus'] );
        }
        if ( ! edd_get_option( 'enable_sequential' ) ) {
            unset( $cols['seq_id'] );
        }

        $columns = apply_filters( 'edd_export_csv_cols_charitable_campaign_payments', $cols );

        $data = $this->get_dashboard_transactions_data($term);

        $export_args = array(
            'filename'   => 'All Transactions',
            'data' => $data,
            'columns' =>  $columns
        );

        new PP_Campaigns_Export( $export_args );
        exit();
    }

    private function get_dashboard_transactions_data($term){

        global $wpdb;

        $data = array();

        set_time_limit(0);

        $args = array(
            'fields' => 'ids',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => $term->taxonomy,
                    'field' => 'id',
                    'terms' => $term->term_id,
                    'include_children' => true
                )
            ),
            'post_status' => 'publish',
            'post_parent' => 0,
        );

        $query = Charitable_Campaigns::query($args);

        if(!empty($query->posts)){
            foreach ($query->posts as $campaign_id) {

                $args = array(
                    'number'   => -1,
                    'page'     => 1,
                    'status'   => 'any',
                );

                $start = '';
                $end = '';
                $export_type = 'charitable_campaign_payments';

                if ( ! empty( $start ) || ! empty( $end ) ) {

                    $args['date_query'] = array(
                        array(
                            'after'     => date( 'Y-m-d H:i:s', strtotime( $start ) ),
                            'before'    => date( 'Y-m-d H:i:s', strtotime( $end ) ),
                            'inclusive' => true,
                        ),
                    );

                }

                if ( $campaign_id ) {
                    $donation_ids = charitable_get_table( 'campaign_donations' )->get_donation_ids_for_campaign( $campaign_id );

                    $args['meta_query'] = array(
                        array(
                            'key' => 'charitable_donation_from_edd_payment',
                            'value' => $donation_ids,
                            'compare' => 'IN',
                        ),
                    );
                }

                $payments = edd_get_payments( $args );

                if ( ! $payments ) {
                    return false;
                }

                foreach ( $payments as $payment ) {
                    $payment_meta   = edd_get_payment_meta( $payment->ID );
                    $user_info      = edd_get_payment_meta_user_info( $payment->ID );
                    $total          = edd_get_payment_amount( $payment->ID );
                    $shipping_info  = isset( $user_info['shipping_info'] ) ? $user_info['shipping_info'] : array();
                    $user_id        = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];
                    $products       = array();
                    $donation_id    = get_post_meta( $payment->ID, 'charitable_donation_from_edd_payment', true );
                    $discounts      = wp_list_pluck( edd_get_payment_meta_cart_details( $payment->ID ), 'discount' );
                    $discount_amount = count( $discounts ) ? array_sum( $discounts ) : 0;

                    if ( ! $donation_id ) {
                        continue;
                    }
                    
                    $edd_campaign_donations = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
                    
                    if ( ! $edd_campaign_donations ) {
                        continue;
                    }

                    if ( is_numeric( $user_id ) ) {
                        $user = get_userdata( $user_id );
                    } else {
                        $user = false;
                    }
                    
                    foreach ( $edd_campaign_donations as $edd_campaign_donation ) {
                        
                        $campaign_id   = $edd_campaign_donation['campaign_id'];
                        $campaign_name = get_the_title( $campaign_id );
                        $quantity      = array_key_exists( 'quantity', $edd_campaign_donation ) ? $edd_campaign_donation['quantity'] : '';

                        /* This was a straight donation, not a product purchase. */
                        if ( $edd_campaign_donation['edd_fee'] ) {

                            $product = sprintf( _x( '%s donation to %s', '$ donation to campaign', 'charitable-edd' ),
                                html_entity_decode( charitable_format_money( $edd_campaign_donation['amount'] ) ),
                                get_the_title( $edd_campaign_donation['campaign_id'] )
                            );
                            
                        } else {
                            
                            if ( array_key_exists( 'price_id', $edd_campaign_donation ) ) {

                                $product = sprintf( '%s - %s - %s',
                                    get_the_title( $edd_campaign_donation['download_id'] ),
                                    edd_get_price_option_name( $edd_campaign_donation['download_id'], $edd_campaign_donation['price_id'] ),
                                    edd_get_price_option_amount( $edd_campaign_donation['download_id'], $edd_campaign_donation['price_id'] )
                                );

                            } else {

                                $product = sprintf( '%s - %s', 
                                    get_the_title( $edd_campaign_donation['download_id'] ),
                                    edd_get_download_price( $edd_campaign_donation['download_id'] )
                                );
                                
                            }

                            if ( edd_use_skus() ) {
                                $sku = edd_get_download_sku( $edd_campaign_donation['download_id'] );
                            } else {
                                $sku = false;
                            }
                            
                            $product = strip_tags( $product );

                        }

                        $data[] = array(
                            'id'                => $payment->ID,
                            'seq_id'            => edd_get_payment_number( $payment->ID ),
                            'donation_id'       => $donation_id,
                            'email'             => $payment_meta['email'],
                            'first'             => isset( $user_info['first_name'] )            ? $user_info['first_name']          : '',
                            'last'              => isset( $user_info['last_name'] )             ? $user_info['last_name']           : '',
                            'address1'          => isset( $user_info['address']['line1'] )      ? $user_info['address']['line1']    : '',
                            'address2'          => isset( $user_info['address']['line2'] )      ? $user_info['address']['line2']    : '',
                            'city'              => isset( $user_info['address']['city'] )       ? $user_info['address']['city']     : '',
                            'state'             => isset( $user_info['address']['state'] )      ? $user_info['address']['state']    : '',
                            'country'           => isset( $user_info['address']['country'] )    ? $user_info['address']['country']  : '',
                            'zip'               => isset( $user_info['address']['zip'] )        ? $user_info['address']['zip']      : '',
                            'campaign_id'       => $campaign_id,
                            'campaign_name'     => $campaign_name,
                            'campaign_amount'   => $edd_campaign_donation['amount'],
                            'products'          => $product,
                            'quantity'          => $quantity,
                            'skus'              => $sku === false ? '' : $sku,
                            'amount'            => html_entity_decode( edd_format_amount( $total ) ),
                            'tax'               => html_entity_decode( edd_format_amount( edd_get_payment_tax( $payment->ID, $payment_meta ) ) ),
                            'discount'          => isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ? $user_info['discount'] : __( 'none', 'charitable-edd' ),
                            'discount_amount'   => html_entity_decode( edd_format_amount( $discount_amount ) ),
                            'gateway'           => edd_get_gateway_admin_label( get_post_meta( $payment->ID, '_edd_payment_gateway', true ) ),
                            'trans_id'          => edd_get_payment_transaction_id( $payment->ID ),
                            'key'               => $payment_meta['key'],
                            'date'              => $payment->post_date,
                            'user'              => $user ? $user->display_name : __( 'guest', 'charitable-edd' ),
                            'status'            => edd_get_payment_status( $payment, true ),
                        );

                    }

                    $data = apply_filters( 'edd_export_get_data', $data );
                    $data = apply_filters( 'edd_export_get_data_' . $export_type, $data );
                }
            }
        }

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // exit();
            
        return $data;
    }

    public function includes(){

    }

}

PP_Exports::init();