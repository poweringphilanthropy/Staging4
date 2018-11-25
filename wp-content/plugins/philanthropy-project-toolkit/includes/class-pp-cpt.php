<?php
/**
 * PP_Cpt Class.
 *
 * @class       PP_Cpt
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Gizburdt\Cuztom\Cuztom;
use Gizburdt\Cuztom\Support\Guard;
use Gizburdt\Cuztom\Support\Request;

/**
 * PP_Cpt class.
 */
class PP_Cpt {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Cpt();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        add_action( 'init', array($this, 'create_post_types_and_tax'), 0 );
        add_filter( 'charitable_submenu_pages', array($this, 'add_to_submenu'), 10, 1 );

        add_action('created_campaign_group', array($this, 'saveTerm'), 20);
        add_action('edited_campaign_group', array($this, 'saveTerm'), 20);

        add_filter( 'post_row_actions', array($this, 'remove_row_actions'), 10, 1 );

    }

    public function remove_row_actions( $actions ) {
        if( get_post_type() === 'get_started_page' )
            unset( $actions['view'] );
        
        return $actions;
    }

    public function create_post_types_and_tax(){

        register_cuztom_post_type( 'get_started_page', array(
            'show_in_menu' => false,
            'exclude_from_search' => true,
        ) );

        // create post type
        register_cuztom_taxonomy( 'campaign_group', 'campaign', array(
            'rewrite' => array(
                'slug' => 'dashboard',
                'ep_mask' => EP_LEADERBOARD
            ),
            'public' => true,
            'show_admin_column' => true,
            'admin_column_sortable' => true,
            'admin_column_filter' => true,
            // 'labels' => array(
                
            // )
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
            'campaign_group',
            array(
                'title'  => __('Dashboard Settings', 'philanthropy'),  
                'fields' => apply_filters( 'pp_dashboard_settings_fields', $pp_dashboard_settings_fields )
            ), 
            array('add_form', 'edit_form') 
        );
    }

    /**
     * Save the term.
     *
     * @param int $id
     */
    public function saveTerm($id) {
	//	echo $id; die();  

	update_term_meta($id,'toolkit',$_POST['cuztom']['description_new']);
	update_term_meta($id,'sponsors',$_POST['cuztom']['sponsors']);
	
        if (! Guard::verifyNonce('cuztom_nonce', 'cuztom_meta')) {
            return;
        }

        
        $old_data = pp_get_term_chapters($id);
        $new_data = isset($values['_chapters']) && is_array($values['_chapters']) ? $values['_chapters'] : array();
        
        $removed = array_diff($old_data, $new_data);
        $new = array_filter( array_diff($new_data, $old_data) );

        if(!empty($removed)){
            foreach ($removed as $chapter_id => $name) {
                pp_remove_chapter($chapter_id);
            }
        }
$values = (new Request($_POST))->getAll();
		//echo "Term Id".get_queried_object()->term_id; die();
        if(!empty($new)){
            foreach ($new as $name) {
                pp_insert_term_chapter($id, $name);
            }
        }

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

    public function add_to_submenu($menu){

        $leaderboard_menu = array(
            array(
                'page_title' => __('Getting Started Pages', 'philanthropy'),
                'menu_title' => __('Get Started Pages', 'philanthropy'),
                'menu_slug' => 'edit.php?post_type=get_started_page',
            ),
            array(
                'page_title' => __('Campaign Groups', 'philanthropy'),
                'menu_title' => __('Groups', 'philanthropy'),
                'menu_slug' => 'edit-tags.php?taxonomy=campaign_group&post_type=campaign',
            )
        );

        array_splice( $menu, 3, 0, $leaderboard_menu ); // splice in at position 3

        return $menu;
    }

    public function includes(){
        require_once pp_toolkit()->directory_path . 'helpers/pp-cuztom-fields/ExportServiceHours.php';
    }

}

PP_Cpt::init();