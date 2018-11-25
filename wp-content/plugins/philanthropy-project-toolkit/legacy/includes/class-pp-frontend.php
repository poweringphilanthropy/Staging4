<?php
/**
 * PP_Frontend Class.
 * Overrides plugin dependencies template
 *
 * @class       PP_Frontend
 * @version     1.0
 * @author lafif <hello@lafif.me>
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * PP_Frontend class.
 */
class PP_Frontend {

    /**
     * Singleton method
     *
     * @return self
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new PP_Frontend();
        }

        return $instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();

        /**
         * Modify default theme reach display
         */
        add_action( 'wp_head', array($this, 'change_theme_content'), 0 );
        add_action( 'wp_head', array($this, 'change_front_end_content') );
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

    public function dislay_heading_on_campaign_before($campaign){
        pp_toolkit_template( 'campaign-loop/heading-thumbnail.php', array( 'campaign' => $campaign ) );
    }

    public function dislay_content_on_campaign_before($campaign){
        pp_toolkit_template( 'campaign-loop/heading-content.php', array( 'campaign' => $campaign ) );
    }
    
    public function display_share($campaign){
        echo '<div class="share-under-desc">';
        reach_template_campaign_share($campaign); 
        echo '</div>';
    }

    public function change_front_end_content(){

        // if(!is_front_page())
        //  return;

        // remove campaign creator on loop after
        remove_action( 'charitable_campaign_content_loop_after', 'reach_template_campaign_loop_stats', 6 );
        remove_action( 'charitable_campaign_content_loop_after', 'reach_template_campaign_loop_creator', 8 );

        add_action( 'charitable_campaign_content_loop_after', array($this, 'philanthropy_template_campaign_loop_stats'), 6 );
    }

    public function philanthropy_template_campaign_loop_stats( Charitable_Campaign $campaign ) {
        pp_toolkit_template( 'campaign-loop/loop-stats.php', array( 'campaign' => $campaign ) );
    }


    public function includes(){
        include_once( 'frontend/class-pp-modal.php');
    }

}

PP_Frontend::init();