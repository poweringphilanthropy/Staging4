<?php
/**
 * PP_Toolkit_Admin Class.
 *
 * @class       PP_Toolkit_Admin
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Toolkit_Admin class.
 */
class PP_Toolkit_Admin {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Toolkit_Admin();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_filter( 'parent_file', array($this, 'change_menu'), 10, 1 );

        add_filter( 'charitable_campaign_meta_boxes', array($this, 'add_metaboxes'), 10, 1 );
        add_filter( 'charitable_admin_view_path', array( $this, 'admin_view_path' ), 10, 3 );

        add_action( 'charitable_campaign_save', array($this, 'save_metaboxes'), 10, 1 );
        add_action( 'charitable_campaign_save', array($this, 'save_downloads_benefactor'), 100, 1 );
    }

    public function change_menu($parent_file){
        global $menu, $submenu;

        $charitable = array_search('charitable', array_column($menu, 2));
        $events = array_search('edit.php?post_type=tribe_events', array_column($menu, 2));

        move_menu($charitable, 7);
        move_menu($events, 8);

        // echo "<pre>";
        // print_r($menu);
        // echo "</pre>";

        return $parent_file;
    }

    public function add_metaboxes($meta_boxes){

        $meta_boxes[] = array(
            'id'                => 'campaign-merchandises',
            'title'             => __( 'Merchandises', 'charitable-ambassadors' ),
            'context'           => 'campaign-advanced',
            'priority'          => 'high',
            'view'              => 'metaboxes/campaign-merchandises',
            'view_source'       => 'pp-toolkit',
        );

        $meta_boxes[] = array(
            'id'                => 'campaign-events',
            'title'             => __( 'Events', 'charitable-ambassadors' ),
            'context'           => 'campaign-advanced',
            'priority'          => 'high',
            'view'              => 'metaboxes/campaign-events',
            'view_source'       => 'pp-toolkit',
        );

        return $meta_boxes;
    }

    /**
     * Set the admin view path to our views folder for any of our views.
     *
     * @param   string $path
     * @param   string $view
     * @param   array  $view_args
     * @return  string
     * @access  public
     * @since   1.0.0
     */
    public function admin_view_path( $path, $view, $view_args ) {
        if ( ! isset( $view_args['view_source'] ) ) {
            return $path;
        }

        if ( 'pp-toolkit' == $view_args['view_source'] ) {
            $path = PP_Toolkit()->plugin_path() . '/includes/admin/views/' . $view . '.php';
        }

        return $path;
    }

    public function save_metaboxes($post){

    }

    /**
     * Save downloads (Merchandise and Tickets)
     * hooked on charitable_campaign_save but very late, 
     * so we can get data after all saved to benefactor db
     * @param  [type] $post [description]
     * @return [type]       [description]
     */
    public function save_downloads_benefactor($post){

        /**
         * TODO | Get downloads from benefactor table and save to meta
         */
        $benefactors = charitable_get_table( 'benefactors' )->get_campaign_benefactors_by_extension( $post->ID, 'charitable-edd' );
        // or 
        $ret = charitable_get_table( 'edd_benefactors' )->get_campaign_benefactors( $campaign_id, false );
    }

    public function includes(){

    }

}

PP_Toolkit_Admin::init();