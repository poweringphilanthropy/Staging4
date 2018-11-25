<?php
/**
 * Philanthropy_Frontend Class.
 *
 * @class       Philanthropy_Frontend
 * @version		1.0
 * @author lafif <lafif@astahdziq.in>
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Philanthropy_Frontend class.
 */
class Philanthropy_Frontend {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->includes();

		add_action( 'wp_enqueue_scripts', array($this, 'enqueue_scripts'), 90 );

		// modify default theme display
		add_action( 'wp_head', array($this, 'change_theme_content'), 0 );
		add_action( 'wp_head', array($this, 'change_front_end_content') );

		// helper
		add_action( 'init', array($this, 'empty_cart'), 1 );

		// add_action( 'template_redirect', array($this, 'redirect_to_preview') );
		// add_filter( 'charitable_campaign_submission_redirect_url', array($this, 'redirect_to_edit_before_preview'), 20, 4 );
	}

	public function empty_cart(){
		if(!isset($_GET['empty-cart']))
			return;

		edd_empty_cart();
	}

	public function change_front_end_content(){

		// if(!is_front_page())
		// 	return;

		// remove campaign creator on loop after
		remove_action( 'charitable_campaign_content_loop_after', 'reach_template_campaign_loop_stats', 6 );
		remove_action( 'charitable_campaign_content_loop_after', 'reach_template_campaign_loop_creator', 8 );

		add_action( 'charitable_campaign_content_loop_after', array($this, 'philanthropy_template_campaign_loop_stats'), 6 );
	}

	public function philanthropy_template_campaign_loop_stats( Charitable_Campaign $campaign ) {
		philanthropy_template( 'campaign-loop/loop-stats.php', array( 'campaign' => $campaign ) );
	}

	public function change_theme_content(){

		if(!is_singular( 'campaign' ))
			return;

		// remove image, we will use our own

		// remove_action( 'charitable_campaign_summary_before', 'reach_template_campaign_title', 2 );
		remove_action( 'charitable_campaign_summary_before', 'charitable_template_campaign_description', 4 );
		remove_action( 'charitable_campaign_summary_before', 'reach_template_campaign_media_before_summary', 6 );

		/**
		 * campaign-details cf
		 */
		remove_all_actions( 'charitable_campaign_summary' );
		remove_all_actions( 'charitable_campaign_summary_after' );

		// remove / move video
		remove_action( 'charitable_campaign_content_before', 'reach_template_campaign_media_before_content', 6 );

		// remove comment
		// remove_action( 'charitable_campaign_content_after', 'reach_template_campaign_comments', 12 );

		// barometer
		// remove_action( 'charitable_campaign_summary', 'charitable_template_campaign_finished_notice', 2 );
		// remove_action( 'charitable_campaign_summary', 'charitable_template_donate_button', 2 );
		// remove_action( 'charitable_campaign_summary', 'reach_template_campaign_progress_barometer', 4 );
		// remove_action( 'charitable_campaign_summary', 'reach_template_campaign_stats', 6 );
		// remove_action( 'charitable_campaign_summary', 'charitable_template_campaign_time_left', 10 );
	
		add_action( 'charitable_single_campaign_before', array($this, 'dislay_heading_on_campaign_before'), 3 );
		add_action( 'charitable_single_campaign_before', array($this, 'dislay_content_on_campaign_before'), 5 );

		// display on campaign content
		add_action( 'philanthropy_heading_content_area', 'charitable_template_campaign_description' );
		add_action( 'philanthropy_heading_content_area', array($this, 'display_share'), 11 );

		add_action( 'philanthropy_heading_content_sidebar', 'charitable_template_campaign_finished_notice', 2 );
		// add_action( 'philanthropy_heading_content_sidebar', 'charitable_template_donate_button', 2 );
		add_action( 'philanthropy_heading_content_sidebar', 'reach_template_campaign_progress_barometer', 4 );
		add_action( 'philanthropy_heading_content_sidebar', 'reach_template_campaign_stats', 6 );
		add_action( 'philanthropy_heading_content_sidebar', 'charitable_template_campaign_time_left', 10 );
		// add_action( 'philanthropy_heading_content_after', array($this, 'display_share'), 11 );
	}

	public function display_share($campaign){
		echo '<div class="share-under-desc">';
		reach_template_campaign_share($campaign); 
		echo '</div>';
	}

	public function dislay_heading_on_campaign_before($campaign){
		philanthropy_template( 'campaign-loop/heading-thumbnail.php', array( 'campaign' => $campaign ) );
	}

	public function dislay_content_on_campaign_before($campaign){
		philanthropy_template( 'campaign-loop/heading-content.php', array( 'campaign' => $campaign ) );
	}

	public function enqueue_scripts(){
		wp_enqueue_style( 'philanthropy' );
		wp_enqueue_script( 'philanthropy' );
	}
	
	// public function redirect_to_edit_before_preview($url, $submitted, $campaign_id, $user_id){

	// 	if(isset( $submitted['preview-campaign'] )){
	// 		$url = charitable_get_permalink('campaign_editing_page', ['campaign_id' => $campaign_id]);
	// 		$url = esc_url_raw( add_query_arg( array( 'post_preview' => $campaign_id ), $url ) );
	// 	}

	// 	return $url;
	// }

	// public function redirect_to_preview(){
	// 	if(!isset($_GET['post_preview']) || empty($_GET['post_preview']))
	// 		return;

	// 	$url = get_permalink( $_GET['post_preview'] );
	// 	$url = esc_url_raw( add_query_arg( array( 'preview' => true ), $url ) );

	// 	wp_redirect( $url );
	// 	die();
	// }

	public function includes(){
		
	}

}

$GLOBALS['philanthropy_frontend'] = new Philanthropy_Frontend();