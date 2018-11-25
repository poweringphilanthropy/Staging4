<?php
/**
 * PP_Chapters Class.
 *
 * @class       PP_Chapters
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Chapters class.
 */
class PP_Chapters {

    public $db_chapters;
    public $db_chapter_service_hours;

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Chapters();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        $this->db_chapters = new PP_DB_Chapters();
        $this->db_chapter_service_hours = new PP_DB_Chapter_Service_Hours();

        add_action( 'template_redirect', array($this, 'catch_save_chapter_service_hours') );
    }

    public function catch_save_chapter_service_hours(){
        if (! isset( $_POST['_save_service_hours_nonce'] ) || ! wp_verify_nonce( $_POST['_save_service_hours_nonce'], 'save_service_hours' ) )
            return;

        $chapter_type = false;
        if(isset($_POST['dashboard_id']) && !empty($_POST['dashboard_id'])){
            $chapter_type = 'dashboard';
        }
        if(isset($_POST['term_id']) && !empty($_POST['term_id'])){
            $chapter_type = 'term';
        }

        if(!$chapter_type){
            charitable_get_notices()->add_error( __( 'Not on valid dashboard page', 'pp-toolkit' ) );
            return;
        }

        if( !isset($_POST['chapter_id']) && (!isset($_POST['chapter_name']) || empty($_POST['chapter_name']) ) ){
            charitable_get_notices()->add_error( __( 'Chapter name is required for add service hours.', 'pp-toolkit' ) );
            return;
        }

        if(!isset($_POST['service_hours']) || empty($_POST['service_hours'])){
            charitable_get_notices()->add_error( __( 'Please fill in the number of service hours', 'pp-toolkit' ) );
            return;
        }

        $save_hours = pp_save_chapter_service_hours($_POST);
        if($save_hours){
            charitable_get_notices()->add_success( __( 'Service hours added.', 'pp-toolkit' ) );
        } else {
            charitable_get_notices()->add_error( __( 'Failed to save service hours', 'pp-toolkit' ) );
        }
    }

    public function includes(){
        include_once( 'db/class-pp-db-chapters.php' );
        include_once( 'db/class-pp-db-chapter-service-hours.php' );
    }

}

PP_Chapters::init();