<?php
/**
 * PP_Reports Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Reports
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Reports class.
 */
class PP_Reports {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Reports();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'init', array( $this, 'add_rewrite_rule' ), 10 );
        add_filter( 'query_vars', array($this, 'add_query_vars'));
        add_filter( 'wp_title', array($this, 'modify_title'), 100, 1 );

        add_filter( 'charitable_campaign_submission_form_args', array($this, 'hide_charitable_submit_campaign') );
        add_action( 'charitable_submit_campaign_shortcode_hidden', array($this, 'display_pp_action'), 10, 1 );

        add_filter( 'charitable_permalink_campaign_report_page', array($this, 'charitable_get_campaign_report_page_permalink'), 2, 2 ); 
        
        add_action('init', array($this, 'maybe_download_campaign_reports'));
    }

    public function add_query_vars($vars){
        $vars[] = "view_report";
        return $vars;
    }

    public function modify_title($title){

        if(get_query_var( 'view_report', false )){
            $title = __('Campaign Reports', 'pp-toolkit');
        }

        return $title;
    }

    /**
     * Add endpoint for report campaigns.
     *
     * @return  void
     * @access  public
     * @since   1.0.0
     */
    public function add_rewrite_rule() {
        add_rewrite_rule( '(.?.+?)/([0-9]+)/report/?$', 'index.php?pagename=$matches[1]&campaign_id=$matches[2]&view_report=true', 'top' );
    }

    public function hide_charitable_submit_campaign($args){

        if( !get_query_var( 'view_report', false ) || !get_query_var( 'campaign_id', false ) )
            return $args;

        $campaign  = new Charitable_Campaign( get_query_var( 'campaign_id' ) );
        if($campaign->post_author != get_current_user_id())
            return $args;

        $args['hidden'] = true;
        $args['view_report'] = true;
        $args['campaign'] = $campaign;

        return $args;
    }

    public function display_pp_action($args){
        if ( ! $args['view_report'] )
            return;

        pp_toolkit_template('reports/campaign-report.php', array(
            'campaign' => $args['campaign']
        ));
    }

    public function charitable_get_campaign_report_page_permalink( $url, $args = array() ) {
        global $wp_rewrite;
        
        $campaign_id = isset( $args[ 'campaign_id' ] ) ? $args[ 'campaign_id' ] : get_the_ID();
        $base_url = charitable_get_permalink( 'campaign_submission_page' );

        if ( $base_url ) {

            if ( $wp_rewrite->using_permalinks() ) {
                $url = trailingslashit( $base_url ) . $campaign_id . '/report/';
            }
            else {
                $url = esc_url_raw( add_query_arg( array( 'campaign_id' => $campaign_id ), $base_url ) );   
            }

        }    

        return $url;
    }

    public static function sort_by_amount($a, $b) {
        return $b["amount"] - $a["amount"];
    }

    public static function sort_by_last_name($a, $b) {
        return strcmp($a["last_name"], $b["last_name"]);
    }

    /**
     * Get campaign reports
     * @param  [type] $campaign_id [description]
     * @return [type]              [description]
     */
    public static function get_campaign_reports($campaign_id) {
		global $wpdb;

        $campaign_ids = pp_get_merged_team_campaign_ids($campaign_id);

        // echo "<pre>";
        // print_r($campaign_ids);
        // echo "</pre>";

        $donation_ids = array();
        $campaign_benefactor_downloads = array();

        $extension = 'charitable-edd';
        foreach ($campaign_ids as $id) {
            $donation_ids = array_merge($donation_ids, charitable_get_table( 'campaign_donations' )->get_donation_ids_for_campaign( $id ) );
            $benefactors = charitable_get_table( 'benefactors' )->get_campaign_benefactors_by_extension($campaign_id, $extension );
            if(!empty($benefactors)){
                $campaign_benefactor_downloads = array_merge($campaign_benefactor_downloads, wp_list_pluck( $benefactors, 'edd_download_id' ) );
            }
        }

        $payments = array();
    
        if(!empty($donation_ids)):
            
        $query_args = array('number'   => -1); // all data
        $query_args['meta_query'] = array(
            array(
                'key' => 'charitable_donation_from_edd_payment',
                'value' => $donation_ids,
                'compare' => 'IN',
            ),
        );

        $payments = edd_get_payments( $query_args );
        endif;

        $data = array();
        $fundraisers_details = array();
        $donation_details = array();

        $ticket_details = array();
        $ticket_qty_by_options = array();

        $merchandise_details = array();
        $merchandise_qty_by_options = array();

        $total_amount = array(
            'fundraisers' => 0,
            'donations' => 0,
            'tickets' => 0,
            'merchandises' => 0,
        );

        $total_fundraising = 0;

        if(!empty($payments)):
		$i=0;
        foreach ($payments as $payment) {

            $edd_payment    = new EDD_Payment( $payment->ID );
            
            $downloads      = edd_get_payment_meta_cart_details( $payment->ID );
            $fees           = edd_get_payment_fees( $payment->ID, 'item' );
            $user_info      = edd_get_payment_meta_user_info( $payment->ID );
            $shipping_address        = ! empty( $user_info[ 'shipping_info' ] ) ? $user_info[ 'shipping_info' ] : false;
            $payment_meta   = edd_get_payment_meta( $payment->ID );

            $donation_id    = get_post_meta( $payment->ID, 'charitable_donation_from_edd_payment', true );
            if ( ! $donation_id || ( get_post_status ( $donation_id ) != 'charitable-completed' ) ) {
                continue;
            }
            $donation       = charitable_get_donation( $donation_id );
            $donor = $donation->get_donor();

            $title = str_replace(array('&#8217;', '&#8217;' ), "'", get_the_title( $campaign_id ));
            $fundraising_amount = 0;
			
			
			$cam_id = $wpdb->get_row( "SELECT campaign_id FROM {$wpdb->prefix}charitable_campaign_donations WHERE donation_id = ". $donation_id, OBJECT );
			$parent_campaign_id = wp_get_post_parent_id( $cam_id->campaign_id );
			$team_fundraising = get_post_meta( $parent_campaign_id, '_campaign_team_fundraising', true) === 'on';
			// if($team_fundraising){
				// $referral_name = get_the_author_meta('display_name', get_post_field( 'post_author', $cam_id->campaign_id ));
			// }else{
				// echo $payment_meta['referral'];
				// echo $payment->ID;
				$referral_name = (isset($payment_meta['referral']) && !empty($payment_meta['referral'])) ?  pp_get_referrer_name($payment_meta['referral'], false) : 'na';
			// }
			$report_data = array(
                'donation_id' => $donation_id,
                'campaign_id' => 0,
                'campaign_name' => '',
                'email' => $payment_meta['email'],
                'first_name' => isset( $user_info['first_name'] ) ? $user_info['first_name'] : '',
                'last_name' => isset( $user_info['last_name'] ) ? $user_info['last_name'] : '',
                'post_date' => $donation->post_date,
                'post_content' => $donation->post_content,
                'post_status' => $donation->post_status,
                'date' => mysql2date( 'l, F j, Y', $donation->post_date ),
                'time' => mysql2date( 'H:i A', $donation->post_date ),
                'chapter' => (isset($payment_meta['chapter']) && !empty($payment_meta['chapter'])) ? $payment_meta['chapter'] : 'na',
                'referral' => $referral_name,
                'payment_gateway' => $edd_payment->gateway,
                'amount' => 0,
                'purchase_detail' => '',
                'shipping' => 0,
                'shipping_address' => !empty($shipping_address) ? EDD_Simple_Shipping::format_address( $user_info, $shipping_address ) : '',
                'ticket_holder' => '-',
                'qty' => 1,
                'type' => '',
            );

            /**
             * DONATION
             */
            if ( $fees ) {
                foreach ( $fees as $key => $fee ) {
                    if ( ! Charitable_EDD_Cart::fee_is_donation( $fee ) ) {
                        continue;
                    }

                    if(!isset($fee['campaign_id']))
                        continue;

                    $campaign_id = absint( $fee['campaign_id'] );
                    $title = self::get_campaign_title($campaign_id);

                    $donation_data = array(
                        'campaign_id' => $campaign_id,
                        'campaign_name' => $title,
                        'type' => 'donation',
                        'amount' => edd_format_amount($fee['amount']),
                        'purchase_detail' => sprintf(__('Donation for %s', 'pp-toolkit'), $title ),
                    );

                    /**
                     * Display as Mobile donation if donation coming from rest api
                     * @var [type]
                     */
                    if($edd_payment->gateway == 'rest-api'){
                        $donation_data['purchase_detail'] = sprintf(__('Mobile Donation for %s', 'pp-toolkit'), $title );
                    }

                    $total_amount['donations'] += $fee['amount'];
                    $fundraising_amount += $fee['amount'];
                    $donation_details[] = array_merge($report_data, $donation_data);
                }

                // echo "<pre>";
                // print_r($donation_details);
                // echo "</pre>";
            }

            /**
             * DOWNLOADS
             */
            if ( $downloads ) {
                foreach ( $downloads as $key => $download ) {
                    // Download ID
                    $id = isset($download['id']) ? $download['id'] : $download;
                    if( empty($id) || !in_array($id, $campaign_benefactor_downloads))
                        continue;

                    // unique key for grouping
                    $download_unique_key = $id;

                    $download_description = get_the_title( $id );
                    $options = ( isset( $download['item_number'] ) && isset( $download['item_number']['options'] ) ) ? $download['item_number']['options'] : array();

                    if ( !empty($options) ) {
                        $price_id   = isset( $options['price_id'] ) ? $options['price_id'] : null;
                        if ( edd_has_variable_prices( $id ) && isset( $price_id ) ) {
                            // append unique key
                            $download_unique_key .= '-' . $price_id;

                            $download_description .= ' - ' . edd_get_price_option_name( $id, $price_id, $payment->ID );
                        }
                    }

                    $campaign_id = self::get_campaign_id_by_donation( $donation_id );

                    $download_data = array(
                        'campaign_id' => $campaign_id,
                        'campaign_name' => self::get_campaign_title($campaign_id),
                        'amount' => edd_format_amount($download['subtotal']),
                        'purchase_detail' => $download_description,
                        'qty' => $download['quantity'],
                        'options' => $options
                    );

                    // ticket holder
                    if (isset($options['tribe-tickets-meta']) 
                        && is_array($options['tribe-tickets-meta']) 
                        && !empty($options['tribe-tickets-meta']))
                    {
                        $_ticket_holder = wp_list_pluck( $options['tribe-tickets-meta'], 'ticket-holder-name' );
                        $download_data['ticket_holder'] = implode(', ', $_ticket_holder);
                    }

                    $shiping_cost = 0;
                    if($download['fees']):
                    foreach ( $download[ 'fees' ] as $fee_id => $fee ) {
                        if ( false === strpos( $fee_id, 'simple_shipping' ) ) {
                            continue;
                        }

                        $download_data['shipping'] = (isset($fee['amount'])) ? $fee['amount'] : 0;
                        break;
                    }
                    endif;

                    // merge with report data
                    $report_data = array_merge($report_data, $download_data);

                    // ticket
                    if (has_term('ticket', 'download_category', $id )) {

                        $report_data['type'] = 'ticket';

                        $total_amount['tickets'] += $report_data['amount'];
                        $fundraising_amount += $report_data['amount'];

                        // count unique ids
                        if(!isset($ticket_qty_by_options[$download_unique_key])){
                            $ticket_qty_by_options[$download_unique_key] = array(
                                'name' => $download_description,
                                'qty' => $report_data['qty'],
                            );
                        } else {
                            $ticket_qty_by_options[$download_unique_key]['qty'] += $report_data['qty'];
                        }

                        $ticket_details[] = $report_data;

                    } else {

                        $report_data['type'] = 'merchandise';

                        $total_amount['merchandises'] += $report_data['amount'];
                        $fundraising_amount += $report_data['amount'];

                        // count unique ids
                        if(!isset($merchandise_qty_by_options[$download_unique_key])){
                            $merchandise_qty_by_options[$download_unique_key] = array(
                                'name' => $download_description,
                                'qty' => $report_data['qty'],
                            );
                        } else {
                            $merchandise_qty_by_options[$download_unique_key]['qty'] += $report_data['qty'];
                        }

                        $merchandise_details[] = $report_data;
                    }
                }
            }

            // count fundraiser
            // if(!isset($fundraisers_details[$report_data['referral']])){
                // $fundraisers_details[$report_data['referral']] = array(
                    // 'name' => $report_data['referral'],
                    // 'donations' => 1,
                    // 'amount' => $fundraising_amount,
                // );
            // } else {
                // $fundraisers_details[$report_data['referral']]['donations'] += 1;
                // $fundraisers_details[$report_data['referral']]['amount'] += $fundraising_amount;
            // }
			
			if(!isset($fundraisers_details[$campaign_id])){
				$parent_campaign_id = wp_get_post_parent_id( $campaign_id );
				$team_fundraising = get_post_meta( $parent_campaign_id, '_campaign_team_fundraising', true) === 'on';
				if($team_fundraising){
					$referral_name = get_the_author_meta('display_name', get_post_field( 'post_author', $campaign_id ));
				}else{
					$referral_name = $report_data['referral'];
				}
                $fundraisers_details[$campaign_id] = array(
                    'name' => $referral_name,
                    'donations' => 1,
                    'amount' => $fundraising_amount,
                );
            } else {
                $fundraisers_details[$campaign_id]['donations'] += 1;
                $fundraisers_details[$campaign_id]['amount'] += $fundraising_amount;
            }

            $total_amount['fundraisers'] += $fundraising_amount;
			// echo  "***********".$i."******";
			// echo $campaign_id;
			// $i++;
        }
        endif;

        usort($fundraisers_details, array(__CLASS__, "sort_by_amount"));
        usort($donation_details, array(__CLASS__, "sort_by_amount"));
        usort($ticket_details, array(__CLASS__, "sort_by_last_name"));
        usort($merchandise_details, array(__CLASS__, "sort_by_last_name"));


        $data = array(
            'fundraisers' => array(
                'details' => $fundraisers_details,
                'total_amount' => $total_amount['fundraisers']
            ),
            'donations' => array(
                'details' => $donation_details,
                'total_amount' => $total_amount['donations']
            ),
            'tickets' => array(
                'details' => $ticket_details,
                'total_amount' => $total_amount['tickets'],
                'qty_by_options' => $ticket_qty_by_options,
            ),
            'merchandises' => array(
                'details' => $merchandise_details,
                'total_amount' => $total_amount['merchandises'],
                'qty_by_options' => $merchandise_qty_by_options,
            ),
        );
    
        return $data;
    }

    public function maybe_download_campaign_reports(){

        if (!isset($_GET['download_campaign_report']))
            return;

        if (!isset($_GET['campaign_id']) || !isset($_GET['report_nonce']))
            return;

        if ( ! wp_verify_nonce( $_GET['report_nonce'], 'download_campaign_report-' . $_GET['campaign_id'] ) )
            return;

        $report_type = $_GET['download_campaign_report'];
        $campaign_id = absint( $_GET['campaign_id'] );

        switch ($report_type) {
            case 'fundraisers':
                $this->download_report_fundraisers($campaign_id);
                break;

            case 'donations':
                $this->download_report_donations($campaign_id);
                break;

            case 'tickets':
                $this->download_report_tickets($campaign_id);
                break;

            case 'merchandises':
                $this->download_report_merchandises($campaign_id);
                break;
            
            default:
                $this->download_report_all($campaign_id);
                break;
        }
    }

    private function download_report_fundraisers($campaign_id){

        $items = array();

        $filename = sanitize_file_name( get_the_title( $campaign_id ) . ' - Fundraisers' );
        $columns  = array( 
            'name' => __( 'Name' ), 
            'donations' => __( 'Total Donations' ),
            'amount' => __( 'Total Amount' ), 
        );

        /* Output headers so that the file is downloaded rather than displayed */
        $charset  = get_option( 'blog_charset' );
        header( "Content-Type: text/csv; charset=$charset" );
        header( "Content-Disposition: attachment; filename=$filename.csv" );

        /* Create a file pointer connected to the output stream */
        $output = fopen( 'php://output', 'w' );

        /* Print header row */
        fputcsv( $output, array_values( $columns ) );        

        $reports = self::get_campaign_reports($campaign_id);

        if(isset($reports['fundraisers']) && isset($reports['fundraisers']['details'])){
            $items = $reports['fundraisers']['details'];
        }

        /* And echo the data */
        if(!empty($items)):
        foreach ( $items as $item ) {

            $row  = array( 
                'name' => $item['name'], 
                'donations' => $item['donations'],
                'amount' => edd_format_amount($item['amount'], false)
            );

            fputcsv( $output, $row );
        }
        endif;

        fclose( $output );
        exit;
    }

    private function download_report_donations($campaign_id){
        $items = array();

        $filename = sanitize_file_name( get_the_title( $campaign_id ) . ' - Donations' );
        $columns  = array( 
            'donation_id' => __( 'Donation ID' ), 
            'date' => __( 'Donation Date' ), 
            'time' => __( 'Donation Time' ), 
            'first_name' => __( 'First Name' ), 
            'last_name' => __( 'Last Name' ),
            'email' => __('Email'),
            'amount' => __( 'Donation Amount' ), 
            'purchase_detail' => __( 'Purchase Detail' ), 
            'referral' => __( 'Referred By' ), 
        );       

        $reports = self::get_campaign_reports($campaign_id);

        if(isset($reports['donations']) && isset($reports['donations']['details'])){
            $items = $reports['donations']['details'];
        }

        /* Output headers so that the file is downloaded rather than displayed */
        $charset  = get_option( 'blog_charset' );
        header( "Content-Type: text/csv; charset=$charset" );
        header( "Content-Disposition: attachment; filename=$filename.csv" );

        /* Create a file pointer connected to the output stream */
        $output = fopen( 'php://output', 'w' );

        /* Print header row */
        fputcsv( $output, array_values( $columns ) ); 

        /* And echo the data */
        if(!empty($items)):
        foreach ( $items as $item ) {

            $row  = array(
                'donation_id' => $item['donation_id'], 
                'date' => $item['date'], 
                'time' => $item['time'], 
                'first_name' => $item['first_name'], 
                'last_name' => $item['last_name'], 
                'email' => $item['email'],
                'amount' => edd_format_amount($item['amount'], false), 
                'purchase_detail' => $item['purchase_detail'], 
                'referral' => $item['referral'], 
            );

            fputcsv( $output, $row );
        }
        endif;

        fclose( $output );
        exit;
    }

    private function download_report_tickets($campaign_id){
        $items = array();

        $filename = sanitize_file_name( get_the_title( $campaign_id ) . ' - Tickets' );
        $columns  = array(
            'donation_id' => __( 'Donation ID' ), 
            'date' => __( 'Donation Date' ), 
            'time' => __( 'Donation Time' ), 
            'first_name' => __( 'First Name' ), 
            'last_name' => __( 'Last Name' ),
            'email' => __('Email'),
            'qty' => __( 'Qty' ), 
            'amount' => __( 'Amount' ), 
            'ticket_holder' => __( 'Ticket Holder' ), 
            'purchase_detail' => __( 'Purchase Detail' ), 
            'referral' => __( 'Referred By' ), 
        );       

        $reports = self::get_campaign_reports($campaign_id);

        if(isset($reports['tickets']) && isset($reports['tickets']['details'])){
            $items = $reports['tickets']['details'];
        }

        /* Output headers so that the file is downloaded rather than displayed */
        $charset  = get_option( 'blog_charset' );
        header( "Content-Type: text/csv; charset=$charset" );
        header( "Content-Disposition: attachment; filename=$filename.csv" );

        /* Create a file pointer connected to the output stream */
        $output = fopen( 'php://output', 'w' );

        /* Print header row */
        fputcsv( $output, array_values( $columns ) ); 

        /* And echo the data */
        if(!empty($items)):
        foreach ( $items as $item ) {

            $row  = array(
                'donation_id' => $item['donation_id'], 
                'date' => $item['date'], 
                'time' => $item['time'], 
                'first_name' => $item['first_name'], 
                'last_name' => $item['last_name'], 
                'email' => $item['email'],
                'qty' => $item['qty'], 
                'amount' => edd_format_amount($item['amount'], false), 
                'ticket_holder' => $item['ticket_holder'], 
                'purchase_detail' => $item['purchase_detail'],
                'referral' => $item['referral'],  
            );

            fputcsv( $output, $row );
        }
        endif;

        fclose( $output );
        exit;
    }

    private function download_report_merchandises($campaign_id){
        $items = array();

        $filename = sanitize_file_name( get_the_title( $campaign_id ) . ' - Merchandises' );
        $columns  = array(
            'donation_id' => __( 'Donation ID' ), 
            'date' => __( 'Donation Date' ), 
            'time' => __( 'Donation Time' ), 
            'first_name' => __( 'First Name' ), 
            'last_name' => __( 'Last Name' ),
            'email' => __('Email'),
            'qty' => __( 'Qty' ), 
            'amount' => __( 'Amount' ), 
            'shipping' => __( 'Shipping' ), 
            'shipping_address' => __( 'Shipping Address' ), 
            'purchase_detail' => __( 'Purchase Detail' ), 
            'referral' => __( 'Referred By' ), 
        );       

        $reports = self::get_campaign_reports($campaign_id);

        if(isset($reports['merchandises']) && isset($reports['merchandises']['details'])){
            $items = $reports['merchandises']['details'];
        }

        /* Output headers so that the file is downloaded rather than displayed */
        $charset  = get_option( 'blog_charset' );
        header( "Content-Type: text/csv; charset=$charset" );
        header( "Content-Disposition: attachment; filename=$filename.csv" );

        /* Create a file pointer connected to the output stream */
        $output = fopen( 'php://output', 'w' );

        /* Print header row */
        fputcsv( $output, array_values( $columns ) ); 

        /* And echo the data */
        if(!empty($items)):
        foreach ( $items as $item ) {

            $row  = array( 

                'donation_id' => $item['donation_id'], 
                'date' => $item['date'], 
                'time' => $item['time'], 
                'first_name' => $item['first_name'], 
                'last_name' => $item['last_name'], 
                'email' => $item['email'],
                'qty' => $item['qty'], 
                'amount' => edd_format_amount($item['amount'], false), 
                'shipping' => edd_format_amount($item['shipping'], false), 
                'shipping_address' => $item['shipping_address'], 
                'purchase_detail' => $item['purchase_detail'],
                'referral' => $item['referral'],  
            );

            fputcsv( $output, $row );
        }
        endif;

        fclose( $output );
        exit;
    }

    private function download_report_all($campaign_id){
        $items = array();

        $filename = sanitize_file_name( get_the_title( $campaign_id ) . ' - Reports' );
        $columns  = array(
            'donation_id' => __( 'Donation ID' ), 
            'date' => __( 'Donation Date' ), 
            'time' => __( 'Donation Time' ), 
            'type' => __( 'Donation Type' ), 
            'first_name' => __( 'First Name' ), 
            'last_name' => __( 'Last Name' ),
            'email' => __('Email'),
            'qty' => __( 'Qty' ), 
            'amount' => __( 'Amount' ), 
            'ticket_holder' => __( 'Ticket Holder' ), 
            'shipping' => __( 'Shipping' ), 
            'purchase_detail' => __( 'Purchase Detail' ), 
            'referral' => __( 'Referred By' ), 
        );       

        $reports = self::get_campaign_reports($campaign_id);
        if(isset($reports['fundraisers']))
            unset($reports['fundraisers']);

        /* Output headers so that the file is downloaded rather than displayed */
        $charset  = get_option( 'blog_charset' );
        header( "Content-Type: text/csv; charset=$charset" );
        header( "Content-Disposition: attachment; filename=$filename.csv" );

        /* Create a file pointer connected to the output stream */
        $output = fopen( 'php://output', 'w' );

        /* Print header row */
        fputcsv( $output, array_values( $columns ) ); 

        /* And echo the data */
        if(!empty($reports)):
        foreach ( $reports as $r_type => $report ) {

            if(!isset($report['details']))
                continue;

            foreach ($report['details'] as $item) {
				global $wpdb;
                $cam_id = $wpdb->get_row( "SELECT campaign_id FROM {$wpdb->prefix}charitable_campaign_donations WHERE donation_id = ". $item['donation_id'], OBJECT );
                $row  = array(
                    'donation_id' => $item['donation_id'], 
                    'date' => $item['date'], 
                    'time' => $item['time'], 
                    'type' => ucfirst($item['type']), 
                    'first_name' => $item['first_name'], 
                    'last_name' => $item['last_name'], 
                    'email' => $item['email'],
                    'qty' => isset($item['qty']) ? $item['qty'] : '-', 
                    'amount' => edd_format_amount($item['amount'], false), 
                    'ticket_holder' => isset($item['ticket_holder']) ? $item['ticket_holder'] : '-', 
                    'shipping' => isset($item['shipping']) ? edd_format_amount($item['shipping'], false) : '-', 
                    'purchase_detail' => $item['purchase_detail'],
                    'referral' =>  get_the_author_meta('display_name', get_post_field( 'post_author', $cam_id->campaign_id )),  
                );

                fputcsv( $output, $row );
            }
        }
        endif;

        fclose( $output );
        exit;
    }

    public static function get_campaign_title($campaign_id){
        return html_entity_decode(get_the_title( $campaign_id )); 
    }

    public static function get_campaign_id_by_donation($donation_id){
        $donation_log = get_post_meta( $donation_id, 'donation_from_edd_payment_log', true );
    
        if(empty($donation_log))
            return 0;

        $first = current($donation_log);

        // echo "<pre>";
        // print_r($first);
        // echo "</pre>";

        return isset($first['campaign_id']) ? absint( $first['campaign_id'] ) : 0;
    }

    public function includes(){}

}

PP_Reports::init();