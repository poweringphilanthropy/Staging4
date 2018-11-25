<?php
/**
 * G4G_College Class.
 *
 * @class       G4G_College
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * G4G_College class.
 */
class G4G_College {

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
            $instance = new G4G_College();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'init', array($this, 'create_post_types_and_tax'), 11 );
        add_filter( 'charitable_submenu_pages', array($this, 'add_to_submenu'), 10, 1 );

    }

    public function create_post_types_and_tax(){

        // create taxonomy for university
        $this->taxonomies[$this->taxonomy_name] = register_cuztom_taxonomy( $this->taxonomy_name, 'campaign', array(
            'rewrite' => array(
                'slug' => 'college',
                'ep_mask' => EP_LEADERBOARD
            ),
            'public' => true,
            'show_admin_column' => true,
            'admin_column_sortable' => true,
            'admin_column_filter' => true
        ) );

        /**
         * Register term metas for campaign_group
         */
        $pp_dashboard_settings_fields = array(
            array(
                'id'            => '_featured_image',
                'type'          => 'image',
                'label'         => __('Featured Image', 'philanthropy'),
            ),
            array(
                'id'            => '_support_email',
                'type'          => 'text',
                'label'         => __('Support Email', 'philanthropy'),
            ),
            array(
                'id'            => '_report_password',
                'type'          => 'text',
                'label'         => __('Report Page Password', 'philanthropy'),
            ),
            array(
                'id'            => '_get_started_page',
                'label'         => __('Get Started Page', 'philanthropy'),
                'type'          => 'select',
                'options'       => array('' => 'Select Page') + $this->get_get_started_pages(),
            ),
            array(
                'id'            => '_dashboard_color',
                'label'         => __('Color', 'philanthropy'),
                'type'          => 'color',
            ),
            array(
                'id'                    => '_enable_leaderboard',
                'type'                  => 'yesno',
                'label'                 => __('Show Leaderboard'),
                'default_value'         => 'no',
            ),
            array(
                'id'                    => '_show_top_campaigns',
                'type'                  => 'yesno',
                'label'                 => __('Show leaderboard - TOP CAMPAIGNS'),
                'default_value'         => 'no',
            ),
            array(
                'id'                    => '_show_top_fundraisers',
                'type'                  => 'yesno',
                'label'                 => __('Show leaderboard - TOP FUNDRAISERS'),
                'default_value'         => 'no',
            ),
            array(
                'id'                    => '_enable_log_service_hours',
                'type'                  => 'yesno',
                'label'                 => __('Enable Tracking'),
                'default_value'         => 'no',
            ),
            array(
                'id'                    => '_export_service_hour',
                'type'                  => 'export_service_hours',
                'label'                 => __('Export Service Hour'),
            ),
            array(
                'id'                    => '_prepopulate_chapters',
                'type'                  => 'yesno',
                'label'                 => __('Prepopulate Chapters'),
                'default_value'         => 'no',
            ),
            array(
                'id'                    => '_chapters',
                'type'                  => 'text',
                'label'                 => __('Chapters'),
                'repeatable'            => true,
            ),
        );
        
       register_cuztom_term_meta( 
            'dashboard_settings',
            $this->taxonomy_name,
            array(
                'title'  => __('Dashboard Settings', 'philanthropy'),  
                // 'fields' => apply_filters( 'pp_dashboard_settings_fields', $pp_dashboard_settings_fields )
                'fields' => $pp_dashboard_settings_fields
            ), 
            array('add_form', 'edit_form') 
        );
    }

    public function add_to_submenu($menu){

        $leaderboard_menu = array(
            array(
                'page_title' => __('Colleges / Universities', 'philanthropy'),
                'menu_title' => __('Colleges', 'philanthropy'),
                'menu_slug' => 'edit-tags.php?taxonomy='.$this->taxonomy_name.'&post_type=campaign',
            )
        );

        array_splice( $menu, 5, 0, $leaderboard_menu ); // splice in at position 3

        return $menu;
    }

    private function get_get_started_pages(){
        $options = array();

        $args = array(
            'post_type'         => 'get_started_page',
            'posts_per_page'    => -1,
            'cache_results'     => false,
            'no_found_rows'     => true,
            'post_status'       => 'publish',
        );

        $posts = get_posts($args);
        if(!empty($posts)):
        foreach ($posts as $p) {
            $opt_name = '';
            if(!empty($p->post_parent)){
                $opt_name .= get_post_field( 'post_title', $p->post_parent ) . ' - ';
            }

            $opt_name .= $p->post_title;

            $options[$p->ID] = $opt_name;
        }
        endif;

        return $options;
    }


    public function includes(){

    }

}

G4G_College::init();