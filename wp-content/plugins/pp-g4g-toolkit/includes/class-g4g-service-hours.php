<?php
/**
 * G4G_Service_Hours Class.
 *
 * @class       G4G_Service_Hours
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * G4G_Service_Hours class.
 */
class G4G_Service_Hours {

    private $post_types;
    private $taxonomies;
    private $metaboxes;
    private $taxonomy_name = 'college';

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new G4G_Service_Hours();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_filter( 'pp_account_endpoints', array($this, 'add_service_hours_endpoint'), 10, 1 );
        add_filter( 'pp_get_account_menu', array($this, 'add_service_hours_menu'), 10, 1 );

        add_action( 'wp_ajax_get_campaign_options_for_service_hours', array($this, 'get_campaign_options_for_service_hours') );
        add_action( 'wp_ajax_save_user_service_hours', array($this, 'save_user_service_hours') );
        
        add_action( 'init', array($this, 'download_report_service_hours') );

        // leaderboard
        add_action( 'after_dashboard_report_template', array($this, 'load_dashboard_service_hour_template'), 10, 1 );
        add_action( 'pp_before_dashboard_export', array($this, 'download_dashboard_service_hour_report'), 10, 2 );

        // add_filter( 'pp_dashboard_total_service_hours', array($this, 'get_total_campaigns_service_hours'), 10, 3 );
        add_filter( 'pp-leaderboard-report-sections', array($this, 'add_service_hours_report_on_leaderboard') );
        add_filter( 'pp-leaderboard-custom-report-sections-data', array($this, 'custom_service_hours_data_on_leaderboard'), 10, 3 );
    }

    public function load_dashboard_service_hour_template($term){

        $color = get_term_meta( $term->term_id, '_dashboard_color', true );

        $dashboard_link = trailingslashit( get_term_link( $term ) ) . 'report/';
        $report_url = add_query_arg( array(
            'download-dashboard-reports' => $term->term_id,
            'taxonomy' => $term->taxonomy,
            'type' => 'service_hours'
        ), wp_nonce_url( $dashboard_link, 'pp-download-report', 'key' ) );

        g4g_template('reports/dashboard/js-templates/report-service-hours.php', array(
            'download_report_url' => $report_url,
            'color' => $color,
        ) );
    }

    public function download_dashboard_service_hour_report($type, $term){

        if($type != 'service_hours')
            return;
        
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
            'post_status' => 'publish'
        );

        $query = Charitable_Campaigns::query($args);

        $service_hours = g4g()->user_service_hours_db->get_report_service_hours( $query->posts );

        require_once( pp_toolkit()->directory_path . 'includes/exports/class-pp-export.php' );

        $export_args = array(
            'filename'   => 'Service Hours - ' . $term->name,
            'data' => $service_hours,
            'columns' => array(
                'member_name' => 'Member Name',
                'chapter' => 'Chapter',
                'title' => 'Title',
                'service_date' => 'Date',
                'service_hours' => '# Hours',
                'description' => 'Description',
            )
        );

        new PP_Campaigns_Export( $export_args );
        exit();
    }

    public function get_total_campaigns_service_hours($total, $term_id, $dashboard_id){

        $args = array(
            'fields' => 'ids',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'campaign_group',
                    'field' => 'id',
                    'terms' => $term_id,
                    'include_children' => true
                )
            ),
            'post_status' => 'publish'
        );

        $query = Charitable_Campaigns::query($args);

        if(empty($query->posts)){
            return 0;
        }

        $total = g4g()->user_service_hours_db->get_campaigns_total_service_hours( $query->posts );

        return $total;
    }

    public function add_service_hours_report_on_leaderboard($sections){
        $sections[] = 'service_hours';

        return $sections;
    }

    public function custom_service_hours_data_on_leaderboard($data, $type, $campaign_ids){

        if($type != 'service_hours'){
            return $data;
        }

         $data = array(
            'item_name' => 'Service Hours',
            'total_hours' => 0,
            'total_chapters' => 0,
            'total_members' => 0,
            'data' => array()
        );


        $service_hours = g4g()->user_service_hours_db->get_report_service_hours( $campaign_ids );
        
        if(empty($service_hours)){
            return $data;
        }

        $dt_service_hours = array();
        foreach ($service_hours as $s) {
            $chapter = $s['chapter'];
            if(isset($data[$chapter])){
                $dt_service_hours[$chapter]['hours'] = intval($data[$chapter]['hours']) + intval($s['service_hours']);
            } else {
                $dt_service_hours[$chapter] = array(
                    'hours' => intval($s['service_hours']),
                    'chapter_name' => $s['chapter'],
                );
            }
        }

        $data = array(
            'item_name' => 'Service Hours',
            'total_hours' => array_sum( array_map('intval', array_column($service_hours, 'service_hours') ) ),
            'total_chapters' => count(array_unique(array_column($service_hours, 'chapter'))),
            'total_members' => count(array_unique(array_column($service_hours, 'member_name'))),
            'data' => $dt_service_hours
        );

        return $data;
    }

    public function download_report_service_hours(){
        if(!isset($_GET['download-report']) || !isset($_GET['key']))
            return;

        if( ! wp_verify_nonce( $_GET['key'], 'pp-download-report' ) )
            return;

        if($_GET['download-report'] != 'user-service-hours')
            return;

        if(!isset($_GET['user_id']) || ($_GET['user_id'] != get_current_user_id() ) )
            return;

        $service_hours = g4g()->user_service_hours_db->get_all_service_hours_by( 'user_id', $_GET['user_id'] );

        require_once( pp_toolkit()->directory_path . 'includes/exports/class-pp-export.php' );

        $export_args = array(
            'filename'   => 'Service Hours',
            'data' => $service_hours,
            'columns' => array(
                'member_name' => 'Member Name',
                'chapter' => 'Chapter',
                'title' => 'Title',
                'service_date' => 'Date',
                'service_hours' => '# Hours',
                'description' => 'Description',
            )
        );

        new PP_Campaigns_Export( $export_args );

        exit();
    }

    public function get_campaign_options_for_service_hours(){

        $user_id = isset($_REQUEST['user_id']) ? absint( $_REQUEST['user_id'] ) : get_current_user_id();

        $user = new Charitable_User( $user_id );

        $query = $user->get_campaigns( array( 
            'posts_per_page'    => -1, 
            'post_status'       => array( 'publish' ),
            'fields' => 'ids'
        ) );

        $campaigns = array();

        if(!empty($query->posts)):
        foreach ($query->posts as $campaign_id) {

            $post_thumbnail_id = get_post_thumbnail_id( $campaign_id, 'post-thumbnail' );

            $campaigns[] = array(
                'campaign_id' => $campaign_id,
                'image' => !empty($post_thumbnail_id) ? esc_url($post_thumbnail_id) : '',
                'title' => get_the_title( $campaign_id ),
                'description' => get_post_meta( $campaign_id, '_campaign_description', true ),
            );
        }
        endif;

        wp_send_json( $campaigns );
    }

    public function save_user_service_hours(){

        $response = array(
            'success' => false,
            'message' => 'Failed to save service hours'
        );

        try {
            
            if(!isset($_POST['postdata']))
                throw new Exception("Empty request");

            parse_str($_POST['postdata'], $postdata);

            if(!isset($postdata['user_id']) || empty($postdata['user_id']))
                throw new Exception("Please login");

            if(!isset($postdata['_wpnonce']) || !wp_verify_nonce( $postdata['_wpnonce'], 'save-user-service-hours' ))
                throw new Exception("You are not allowed.");

            if ( !isset($postdata['service_date']) || (strtotime($postdata['service_date']) === false) ) {
                throw new Exception("Please use valid date.");
            }

            $data = array(
                'user_id' => intval( $postdata['user_id'] ),
                'campaign_id' => isset( $postdata['campaign_id'] ) ? intval($postdata['campaign_id']) : 0,
                'title' => isset( $postdata['title'] ) ? sanitize_text_field($postdata['title']) : '',
                'description' => isset( $postdata['description'] ) ? sanitize_textarea_field($postdata['description']) : '',
                'service_date' => date("Y-m-d", strtotime($postdata['service_date']) ),
                'service_hours' => isset( $postdata['service_hours'] ) ? intval($postdata['service_hours']) : 0,
                'members' => isset( $postdata['members'] ) ? sanitize_textarea_field($postdata['members']) : '',
            );

            $save = g4g()->user_service_hours_db->add_members($data);
            if($save === false){
                throw new Exception("Failed to save members.");
            }
                
            $response['success'] = true;

        } catch (Exception $e) {
            $response['message'] = $e->getMessage();
        }

        wp_send_json( $response );
    }

    public function add_service_hours_endpoint($endpoints){

        $endpoints['service-hours'] = array(
            'title' => __('Service Hours', 'pp'),
            'callback' => array($this, 'service_hours_callback'),
            'template' => 'page-templates/user-dashboard.php',
        );

        return $endpoints;
    }

    public function add_service_hours_menu($menu_items){

        $menu_items['service-hours'] = array(
            'label' => __('Service Hours', 'pp'),
            'url' => home_url( 'account/service-hours' ),
            'icon' => PP()->get_image_url('accounts/icon-profile.png'),
            'priority' => 40,
        );

        return $menu_items;
    }

    public function service_hours_callback(){

        wp_enqueue_style( 'selectize' );
        wp_enqueue_script( 'selectize' );

        wp_enqueue_script( 'autocomplete' );
        wp_enqueue_script( 'g4g' );

        $actionurl = add_query_arg(array('download-report' => 'user-service-hours', 'user_id' => get_current_user_id() ));
        $download_url = wp_nonce_url( $actionurl, 'pp-download-report', 'key' );

        g4g_template('account/service-hours.php', array(
            'user_id' => get_current_user_id(),
            'service_hours' => $this->get_service_hours( get_current_user_id() ),
            'download_url' => $download_url,
        ) );
    }

    public function get_service_hours($user_id){
        $service_hours = g4g()->user_service_hours_db->get_members_service_hours_from_user_id($user_id);
    
        return $this->get_data_with_total($service_hours, 'Service Hours');
    }

    private function get_data_with_total($data, $item_name = ''){

        $qtys = array_column( $data, 'qty' );
        $amounts = array_column($data, 'amount');

        $formatted = array(
            'item_name' => $item_name,
            'count' => count($data),
            'qty' => !empty($qtys) ? array_sum( array_map('intval', $qtys) ) : 0,
            'amount' => !empty($amounts) ? array_sum( array_map('floatval', $amounts) ) : 0,
            'data' => $data,
        );

        return $formatted;
    }


    public function includes(){

    }

}

G4G_Service_Hours::init();