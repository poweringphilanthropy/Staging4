<?php
/**
 * PP_Leaderboard Class.
 *
 * @class       PP_Leaderboard
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Leaderboard class.
 */
class PP_Leaderboard {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Leaderboard();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action('pre_get_posts', array($this, 'leaderboard_query'), 11);

        add_filter( 'template_include', array($this, 'load_leaderboard_template'), 99, 1 );

        // report
        add_action( 'init', array($this, 'add_leaderboard_endpoints'));
        add_filter( 'request', array($this, 'change_leaderboard_report_request'), 10, 1 );
        add_filter( 'query_vars', array($this, 'add_leaderboard_query_vars'), 10, 1);

        add_filter( 'post_password_required', array($this, 'leaderboard_password_required'), 10, 2 );

        add_filter( 'wp_title', array($this, 'change_wp_title_for_leaderboard_report'), 100, 3 );
        add_filter( 'pp_campaign_report_item_name', array($this, 'change_item_name_on_dashboard_reports'), 10, 3 );

        add_action( 'wp_ajax_get_dashboard_report_data', array($this, 'load_dashboard_report'), 10, 1 );
        add_action( 'wp_ajax_nopriv_get_dashboard_report_data', array($this, 'load_dashboard_report'), 10, 1  );
        add_action( 'wp_ajax_get_report_data', array($this, 'get_report_data'), 10, 1 );
        add_action( 'wp_ajax_nopriv_get_report_data', array($this, 'get_report_data'), 10, 1  );

        add_action( 'init', array($this, 'download_dashboard_reports'), 10 );

    }

    public function download_dashboard_reports(){
        if( !isset($_GET['download-dashboard-reports']) || !isset($_GET['key']) ){
            return;
        }

        if( !wp_verify_nonce( $_GET['key'], 'pp-download-report' ) )
            return;

        $term = get_term( $_GET['download-dashboard-reports'], $_GET['taxonomy'] );

        $type = isset($_GET['type']) ? $_GET['type'] : '';

        do_action( 'pp_before_dashboard_export', $type, $term);

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

        $report = new PP_Campaign_Donation_Reports($query->posts, false);
        
        $dashboard_title = $term->name;

        require_once( pp_toolkit()->directory_path . 'includes/exports/class-pp-export.php' );
        
        switch ($type) {
            case 'campaigns':

                $_data = $report->get_campaign_data();
                $data = $_data['data'];

                $export_args = array(
                    'filename'   => 'Campaigns - ' . $dashboard_title,
                    'data' => $data,
                    'columns' => array(
                        'item_name' => 'Campaign',
                        'count' => 'Total Donations',
                        'amount' => 'Total Amount',
                    )
                );
                break;

            case 'fundraisers':

                $_data = $report->get_referral_data();
                $data = $_data['data'];

                $export_args = array(
                    'filename'   => 'Fundraisers - ' . $dashboard_title,
                    'data' => $data,
                    'columns' => array(
                        'item_name' => 'Fundraiser',
                        'count' => 'Total Donations',
                        'amount' => 'Total Amount',
                    )
                );
                break;

            case 'donations':

                $_data = $report->get_donation_data();
                $data = $_data['data'];

                $export_args = array(
                    'filename'   => 'Donations - ' . $dashboard_title,
                    'data' => $data,
                    'columns' => array(
                        'first_name' => 'First Name',
                        'last_name' => 'Last Name',
                        'campaign_name' => 'Campaign Name',
                        'amount' => 'Amount',
                    )
                );
                break;

            case 'merchandises':

                $data = $report->get_data_by('type', 'merchandise');

                $export_args = array(
                    'filename'   => 'Merchandises - ' . $dashboard_title,
                    'data' => $data,
                    'columns' => array(
                        'purchase_detail' => 'Merchandise',
                        'qty' => 'Quantity',
                        'amount' => 'Amount',
                        'shipping' => 'Shipping',
                    )
                );
                break;

            case 'tickets':

                $data = $report->get_data_by('type', 'ticket');

                $export_args = array(
                    'filename'   => 'Tickets - ' . $dashboard_title,
                    'data' => $data,
                    'columns' => array(
                        'purchase_detail' => 'Ticket',
                        'qty' => 'Quantity',
                        'amount' => 'Amount',
                        'ticket_holder' => 'Ticket Holders',
                    )
                );
                break;
        }

        // echo "<pre>";
        // print_r($data);
        // echo "</pre>";
        // exit(); 
        
        new PP_Campaigns_Export( $export_args );

        exit();
    }

    public function leaderboard_query($query){

        if ( is_admin() || ! $query->is_main_query() || ! $this->is_leaderboard_page() )
            return;

        $query->set( 'post_parent', 0 ); // exclude childs
        $query->set( 'posts_per_page', -1 ); // exclude childs

        // echo "<pre>";
        // print_r($query);
        // echo "</pre>"; 
        // exit();
    }

    public function add_leaderboard_endpoints(){
        add_rewrite_endpoint('report', EP_LEADERBOARD );
        add_rewrite_endpoint('getting-started', EP_LEADERBOARD );
    }

    public function change_leaderboard_report_request($vars){

        // echo "<pre>";
        // var_dump($vars);
        // echo "</pre>";
        // exit();

        if( isset( $vars['report'] ) ){
            $vars['report'] = true;
        }
        if( isset( $vars['getting-started'] ) ){

            try {
                $_found = array_intersect(array_keys($vars), $this->get_registered_leaderboard_tax());
                if(empty($_found))
                    throw new Exception("Not found");

                $tax = current($_found);
                $term = get_term_by( 'slug', $vars[$tax], $tax );
                if(empty($term))
                    throw new Exception("Not found");

                $get_started_page = get_term_meta( $term->term_id, '_get_started_page', true );
                if(empty($get_started_page))
                    throw new Exception("Not found");

                $page = get_post($get_started_page); 
                if(empty($page))
                    throw new Exception("Not found");

                $slug = $page->post_name;

                $vars['getting-started'] = true;
                $vars['term'] = $vars[$tax];
                $vars['taxonomy'] = $tax;
                $vars['post_type'] = 'get_started_page';
                $vars['name'] = $page->post_name;

                // var_dump($get_started_page);
                // exit();
                    
            } catch (Exception $e) {}
        }

        return $vars;
    }

    public function add_leaderboard_query_vars($vars){
        $vars[] = "report";
        $vars[] = "getting-started";
        return $vars;
    }

    public function leaderboard_password_required($required, $post){

        // echo "<pre>";
        // print_r($post);
        // echo "</pre>";
        // exit();

        if( ! $this->is_leaderboard_page('report') ){
            return $required;
        }

        if ( ! isset( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] ) ) {
            /** This filter is documented in wp-includes/post.php */
            return true;
        }

        $password = get_term_meta( get_queried_object_id() , '_report_password', true );

        require_once ABSPATH . WPINC . '/class-phpass.php';
        $hasher = new PasswordHash( 8, true );

        $hash = wp_unslash( $_COOKIE[ 'wp-postpass_' . COOKIEHASH ] );
        if ( 0 !== strpos( $hash, '$P$B' ) ) {
            $required = true;
        } else {
            $required = ! $hasher->CheckPassword( $password, $hash );
        }

        return $required;
    }

    public function change_wp_title_for_leaderboard_report($wp_title, $sep, $seplocation){

        if( $this->is_leaderboard_page('report') ){
            $wp_title = 'Report of ' . $wp_title;
        }

        return $wp_title;
    }

    public function change_item_name_on_dashboard_reports($item_name, $sort_key, $sort_value){

        if( ($sort_key == 'campaign_id') ){
            if(is_numeric($item_name)){
                $item_name = html_entity_decode( get_the_title( $item_name ) );
            } else {
                $item_name = 'Campaigns';
            }
            
        } elseif ($sort_key == 'type'){
            $item_name = ucfirst($item_name);
        } elseif ($sort_key == 'unique_key'){
            $_exp = explode('-', $item_name);
            $download_id = $_exp[0];
            $price_id = isset($_exp[1]) ? $_exp[1] : false;

            $item_name = html_entity_decode( get_the_title( $download_id ) );

            if($price_id !== false){
                $item_name .= ' - ' . edd_get_price_option_name( $download_id, $price_id );
            }
        } elseif($sort_key == 'referral'){
            // $item_name = 'Fundraisers';
        }

        return $item_name;
    }

    public function load_leaderboard_template($template){
        global $post;

        if( ! $this->is_leaderboard_page() ){
            return $template;
        }  

        // Enqueue script
        wp_enqueue_style( 'philanthropy-modal' );
        wp_enqueue_script( 'philanthropy-modal' );

        // var_dump(wp_script_is( 'philanthropy-modal', 'enqueued' ));
        // exit();
        wp_enqueue_script( 'jquery-ui-datepicker' );
        wp_enqueue_script( 'philanthropy-modal' );
        wp_localize_script( 'philanthropy-modal', 'PHILANTHROPY_MODAL', array(
            'ajax_url' => admin_url( 'admin-ajax.php', 'relative' ),
            'default_error_message' => __('Unable to process request.', 'pp-toolkit')
        ) );

        if($this->is_leaderboard_page('report')){
            wp_enqueue_script( 'pp-reports' );
            wp_localize_script( 'pp-reports', 'PP_DASHBOARD_REPORT', array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
            ) );
        }

        $template_name = 'leaderboard.php';

        if($this->is_leaderboard_page('report')){
            $template_name = 'leaderboard-report.php';
        } elseif($this->is_leaderboard_page('getting-started')){
            $template_name = 'leaderboard-getting-started.php';
        }

        $new_template = locate_template( array( $template_name ) );
        if ( '' != $new_template ) {
            return $new_template;
        }

        return $template;
    }

    public function is_leaderboard_page($endpoint = ''){

        $leaderboard_tax = $this->get_registered_leaderboard_tax();

        switch ($endpoint) {
            case 'report':
                $return = is_tax( $leaderboard_tax ) && get_query_var( 'report', false );
                break;

            case 'getting-started':
                $return = is_tax( $leaderboard_tax ) && get_query_var( 'getting-started', false );
                break;
            
            default:
                $return = is_tax( $leaderboard_tax );
                break;
        }

        return $return;
    }

    private function get_registered_leaderboard_tax(){
        return apply_filters( 'get_registered_leaderboard_tax', array('campaign_group', 'college') );
    }

    public function load_dashboard_report(){

        $campaign_ids = $_REQUEST['campaign_ids'];

        $reports = new PP_Campaign_Donation_Reports($campaign_ids);

        $data = $reports->get_data();

        // store to session 
        // $timestamp = $_REQUEST['timestamp'];
        // $hash = 'dashboard-report-' . md5( maybe_serialize( $campaign_ids ) ) . '-' . $timestamp;
        // $hash = 'dashboard-report-' . md5( maybe_serialize( $campaign_ids ) );
        // EDD()->session->set($hash, $data);

        $results = array(
            'success' => true,
            'data' => $data,
            'to_process' => apply_filters( 'pp-leaderboard-report-sections', array('campaigns', 'fundraisers', 'donations', 'merchandises', 'tickets'), $campaign_ids ),
        );

        wp_send_json( $results );
    }

    public function get_report_data(){

        $type = $_REQUEST['report_type'];
        $campaign_ids = $_REQUEST['campaign_ids'];

        $custom_data = apply_filters( 'pp-leaderboard-custom-report-sections-data', array(), $type, $campaign_ids );
        if(!empty($custom_data)){
            $results = array(
                'success' => true,
                'data' => $custom_data,
            );

            wp_send_json( $results );
        }


        $raw_data = json_decode( stripslashes($_REQUEST['data']), true);

        $reports = new PP_Campaign_Donation_Reports($campaign_ids);

        $reports->set_data($raw_data);

        switch ($type) {
            case 'campaigns':
                $data = $reports->get_campaign_data();
                break;
                
            case 'fundraisers':
                $data = $reports->get_referral_data();
                break;
                
            case 'donations':
                $data = $reports->get_donation_data();
                break;
                
            case 'merchandises':
                $data = $reports->get_merchandise_data();
                break;

            case 'tickets':
                $data = $reports->get_ticket_data();
                break;
            
            default:
                $data = $reports->get_all_donation_data();
                break;
        }

        $results = array(
            'success' => true,
            'data' => $data,
        );

        wp_send_json( $results );
    }

    public function includes(){
        include_once( pp_toolkit()->directory_path . 'includes/reports/class-pp-dashboard-reports.php');
    }

}

PP_Leaderboard::init();