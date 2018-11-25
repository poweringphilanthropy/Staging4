<?php
/**
 * PPR_Theme Class.
 *
 * @class       PPR_Theme
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * PPR_Theme class.
 */
class PPR_Theme {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PPR_Theme();
        }

        return $instance;
    }

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_action( 'wp_enqueue_scripts', array($this, 'reach_child_load_styles'), 100 );
		add_action('after_setup_theme', array($this, 'after_setup_theme'));
	}

	public function reach_child_load_styles(){

		wp_enqueue_style( 'raleway-font', '//fonts.googleapis.com/css?family=Raleway:100,200,300,regular,500,600,700,800,900' );

		wp_dequeue_style( 'reach-style' );
    	wp_enqueue_style( 'reach-child-styles', PPR()->get_path('css', true) . "/reach-child.css", array('reach-base'), PPR()->version );

    	wp_enqueue_script('jscroll', PPR()->get_path('js', true) .'/jquery.jscroll.min.js', false, '2.3.5');
    	wp_enqueue_script('philanthropy-project-scripts', PPR()->get_path('js', true) .'/custom.js', false, PPR()->version );
	}

	public function after_setup_theme() {
		register_sidebar( array(
			'id'              => 'sidebar_get_inspired',
			'name'            => __( 'Get inspired sidebar', 'reach' ),
			'description'     => __( 'The get inspired sidebar.', 'reach' ),
			'before_widget'   => '<aside id="%1$s" class="widget cf %2$s">',
			'after_widget'    => '</aside>',
			'before_title'    => '<div class="title-wrapper"><h4 class="widget-title">',
			'after_title'     => '</h4></div>'
		));

	    register_sidebar( array(
	        'id'              => 'sidebar_tpp_campaign',            
	        'name'            => __( 'Greeks4Good Campaign - sidebar', 'reach' ),
	        'description'     => __( 'The sidebar on Greeks4Good campaign.', 'reach' ),
	        'before_widget'   => '<aside id="%1$s" class="widget cf %2$s">',
	        'after_widget'    => '</aside>',
	        'before_title'    => '<div class="title-wrapper"><h4 class="widget-title">',
	        'after_title'     => '</h4></div>'
	    ));

	    register_sidebar( array(
	        'id'            => 'tpp_campaign_after_content',            
	        'name'          => __( 'Greeks4Good Campaign - below content', 'reach' ),
	        'description'   => __( 'Displayed below the Greeks4Good campaign\'s content, but above the comment section.', 'reach' ),
	        'before_widget' => '<aside id="%1$s" class="widget block content-block cf %2$s">',
	        'after_widget'  => '</aside>',
	        'before_title'  => '<div class="title-wrapper"><h3 class="widget-title">',
	        'after_title'   => '</h3></div>'
	    ));    
	    register_sidebar( array(
	        'id'            => 'leaderboard',            
	        'name'          => __( 'Leaderboard', 'reach' ),
	        'description'   => __( 'Displayed only on leaderboard pages.', 'reach' ),
	        'before_widget' => '<aside id="%1$s" class="widget block content-block cf %2$s">',
	        'after_widget'  => '</aside>',
	        'before_title'  => '<div class="title-wrapper"><h3 class="widget-title">',
	        'after_title'   => '</h3></div>'
	    ));     


	    // Add featured image size for the leaderboard
		add_image_size( 'leaderboard-featured-img', 630, 400, true );

		/* HIDE ADMIN BAR */
		if(!current_user_can('administrator'))
		    add_filter( 'show_admin_bar', '__return_false' );
	}

	public function includes(){

	}

}

PPR_Theme::init();