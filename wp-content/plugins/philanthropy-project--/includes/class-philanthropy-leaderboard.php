<?php
/**
 * Philanthropy_Leaderboard Class.
 *
 * @class       Philanthropy_Leaderboard
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Philanthropy_Leaderboard class.
 */
class Philanthropy_Leaderboard {

	private $post_types;
	private $taxonomies;
	private $taxonomy_name = 'campaign_group';

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_action( 'init', array($this, 'create_post_types_and_tax'), 11 );
		add_filter( 'charitable_submenu_pages', array($this, 'add_to_submenu'), 10, 1 );
		add_filter( 'template_include', array($this, 'disable_force_user_dashboard_template'), 1, 1 );
		
		add_action( 'philanthropy_after_dashboard_content', array($this, 'display_share'), 10 );
	}

	public function display_share($post){
		echo '<div class="campaign-summary" style="border: none;padding: 0px;">';
		echo '<div class="share-under-desc">';
		philanthropy_template( 'dashboard/share.php', array('dashboard' => $post) );
		echo '</div>';
		echo '</div>';
	}

	public function disable_force_user_dashboard_template($template){

		// var_dump(is_tax($this->taxonomy_name));
		if(is_tax($this->taxonomy_name)){
			add_filter( 'charitable_force_user_dashboard_template', '__return_false', 100 );
		}

		return $template;
	}

	public function create_post_types_and_tax(){

		// create post type
		$this->post_types['dashboard'] = new Cuztom_Post_Type( 'Dashboard', array(
			'supports' => array( 'title', 'editor', 'thumbnail' ),
			'menu_icon' => 'dashicons-awards',
			'show_in_menu' => false
		) );

		$this->taxonomies['group'] = new Cuztom_Taxonomy(
		    $this->taxonomy_name,
		    'campaign',
		    array(
		    	// 'rewrite' => array(
		    	// 	'slug' => 'dashboard'
		    	// ),
		    	'public' => true,
		        'show_admin_column' => true,
		        'admin_column_sortable' => true,
		        'admin_column_filter' => true
		    )
		);

		// $this->taxonomies['group']->add_term_meta( array(
	 //        array(
	 //            'id'                => '_group_image',
	 //            'label'             => __('Featured Image', 'philanthropy'),
	 //            'type'              => 'image',
	 //        ),
	 //        array(
	 //            'id'                => '_group_content',
	 //            'label'             => __('Content', 'philanthropy'),
	 //            'type'              => 'wysiwyg',
	 //        ),
	 //    ) );
	 


		/**
		 * Add metabox
		 */
		$this->post_types['dashboard']->add_meta_box( 'campaign_groups', __('Associated Groups', 'philanthropy'), array(
			array(
		        'id'            => '_campaign_group',
		        'type'          => 'select',
		        'label'         => __('Campaign Group', 'philanthropy'),
		        'options'       => $this->get_campaign_groups(),
		        // 'show_admin_column'     => true,
		        // 'admin_column_sortable' => true,
		        // 'admin_column_filter'   => true,
		    ),
		    array(
		        'id'            => '_support_email',
		        'type'          => 'text',
		        'label'         => __('Support Email', 'philanthropy'),
		        'show_admin_column'     => true,
		        'admin_column_sortable' => true,
		        'admin_column_filter'   => true,
		    )
		), 'side' );

		/**
		 * Page color
		 */
		$this->post_types['dashboard']->add_meta_box( 'display', __('Display', 'philanthropy'), array(
		    array(
	            'id'    => '_page_color',
	            'type'  => 'color',
	            'label' => __('Color', 'philanthropy'),
	        ),
		), 'side' );
	}

	public function add_to_submenu($menu){

		$leaderboard_menu = array(
			array(
				'page_title' => __('Dashboards', 'philanthropy'),
				'menu_title' => __('Dashboards', 'philanthropy'),
				'menu_slug' => 'edit.php?post_type=dashboard',
			),
			array(
				'page_title' => __('Campaign Groups', 'philanthropy'),
				'menu_title' => __('Groups', 'philanthropy'),
				'menu_slug' => 'edit-tags.php?taxonomy='.$this->taxonomy_name.'&post_type=campaign',
			)
		);

		array_splice( $menu, 3, 0, $leaderboard_menu ); // splice in at position 3

		return $menu;
	}
	
	public function get_campaign_groups(){
		$terms = get_terms( array(
		    'taxonomy' => $this->taxonomy_name,
		    'hide_empty' => false,
		    'fields' => 'id=>name'
		) );

		$terms = (is_array($terms) && !is_wp_error( $terms )) ? $terms : array();

		return array('0' => __('Please select group', 'philanthropy') ) + $terms;
	}

	public function includes(){
		include_once( 'cuztom/cuztom.php' );
	}

}

$GLOBALS['philanthropy_leaderboard'] = new Philanthropy_Leaderboard();