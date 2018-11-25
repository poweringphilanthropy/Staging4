<?php
/**
 * Leaderboard model
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'PP_Charitable_Leaderboard' ) ) :

	/**
	 * Campaign Model
	 *
	 * @since       1.0.0
	 */
	class PP_Charitable_Leaderboard {

		/**
		 * @var WP_Post The WP_Post object associated with this leaderboard.
		 */
		private $post;

		/**
		 * @var array of campaign id associated with this leaderboard
		 */
		private $campaign_ids;
		
		public $term_id;

		public $color;

		public $query;

		private $use_deprecated = false;

		/**
		 * Class constructor.
		 *
		 * @param   mixed   $post       The post ID or WP_Post object for this this campaign.
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( $post ) {
			if ( ! is_a( $post, 'WP_Post' ) ) {
				$post = get_post( $post );
			}

			$this->setup_data($post);
		}

		private function setup_data( $post ){

			$this->post = $post;

			$term_id = $this->get_dashboard_term_id($post->ID);

			if(!empty($term_id)){
				$this->term_id = $term_id;
				$this->color = get_term_meta( $term_id, '_dashboard_color', true );
			} else {
				$this->use_deprecated = true;
				// fallback for deprecated
				$this->term_id = get_post_meta(  $this->get_post_id(), '_campaign_group', true );
				$this->color = get_post_meta( $this->get_post_id(), '_page_color', true );
			}

			$args = array(
				'post_type' => 'campaign',
				'posts_per_page' => -1,
				'tax_query' => array(
					array(
					    'taxonomy' => 'campaign_group',
					    'field' => 'id',
					    'terms' => $this->get_term_id(),
            			'include_children' => true
				    )
				),
				'post_status' => 'publish'
			);

			$this->query = new WP_Query( $args );

			$this->campaign_ids = wp_list_pluck( $this->query->posts, 'ID' );
		}

		private function get_dashboard_term_id($post_id){
			return pp_get_dashboard_term_id($post_id);
		}

		public function get_post_id(){
			return $this->post->ID;
		}

		public function get_term_id(){
			return $this->term_id;
		}

		public function get_color_accent(){
			return $this->color;
		}

		public function get_campaigns_query(){
			return $this->query;
		}

		public function get_campaign_ids(){
			return $this->campaign_ids;
		}

		public function get_total_campaigns(){
			return absint( $this->query->post_count );
		}

		public function get_total_supporters(){
			if(empty( $this->get_campaign_ids() ))
				return 0;

			$donors = philanthropy_get_multiple_donors( $this->get_campaign_ids() );
			return $donors->count();
		}

		public function get_donation_amount(){
			if(empty( $this->get_campaign_ids() ))
				return 0;

			return charitable_get_table( 'campaign_donations' )->get_campaign_donated_amount( $this->get_campaign_ids(), false );
		}

		public function display_leaderboard(){

			$display = get_term_meta( $this->get_term_id(), '_enable_leaderboard', true ) == 'yes';

			if($this->use_deprecated){
				$display = get_post_meta( $this->get_post_id(), '_enable_leaderboard', true ) == 'yes';
			}

			return apply_filters( 'pp_dashboard_display_leaderboard', $display, $this->get_term_id(), $this->get_post_id() );
		}

		public function display_top_campaigns(){

			$display = get_term_meta( $this->get_term_id(), '_show_top_campaigns', true ) == 'yes';

			if($this->use_deprecated){
				$display = get_post_meta( $this->get_post_id(), '_show_top_campaigns', true ) == 'yes';
			}

			if(!$this->display_leaderboard()){
				$display = false;
			}

			return apply_filters( 'pp_dashboard_display_top_campaigns', $display, $this->get_term_id(), $this->get_post_id() );
		}

		public function display_top_fundraisers(){

			$display = get_term_meta( $this->get_term_id(), '_show_top_fundraisers', true ) == 'yes';

			if($this->use_deprecated){
				$display = get_post_meta( $this->get_post_id(), '_show_top_fundraisers', true ) == 'yes';
			}

			if(!$this->display_leaderboard()){
				$display = false;
			}

			return apply_filters( 'pp_dashboard_display_top_fundraisers', $display, $this->get_term_id(), $this->get_post_id() );
		}

		public function get_top_campaigns_by_donor(){

			if(empty($this->get_campaign_ids()))
				return array();

			$query_args = array(
				'posts_per_page' => -1,
				'post__in' => $this->get_campaign_ids(),
			);

			$top_campaigns_query = Charitable_Campaigns::ordered_by_amount( $query_args );

			return $top_campaigns_query->posts;
		}

		public function get_top_fundraisers(){

			if(empty($this->get_campaign_ids()))
				return array();

			$query_args = array(
				'number' => -1, // get all
				'output' => 'fundraisers',
				'distinct_fundraisers' => true,
				'orderby' => 'amount',
				'campaign' => $this->get_campaign_ids(),
			);

			return new PP_Fundraisers_Query( $query_args );
		}

		public function get_campaigns(){}

		public function is_track_service_hours_enable(){

			$enable = get_term_meta( $this->get_term_id(), '_enable_log_service_hours', true ) == 'yes';

			if($this->use_deprecated){
				$enable = get_post_meta( $this->get_post_id(), '_enable_log_service_hours', true ) == 'yes';
			}

			return apply_filters( 'pp_dashboard_is_track_service_hours_enable', $enable, $this->get_term_id(), $this->get_post_id() );
		}

		public function is_prepopulate_chapters_enable(){
			$enable = get_term_meta( $this->get_term_id(), '_prepopulate_chapters', true ) == 'yes';

			if($this->use_deprecated){
				$enable = get_post_meta( $this->get_post_id(), '_prepopulate_chapters', true ) == 'yes';
			}

			return apply_filters( 'pp_dashboard_is_prepopulate_chapters_enable', $enable, $this->get_term_id(), $this->get_post_id() );
		}

		public function get_total_service_hours($include_additionals = false){
			global $wpdb;

			$query = "SELECT SUM(hr.service_hours) total FROM {$wpdb->prefix}chapter_service_hours hr
				INNER JOIN {$wpdb->prefix}chapters ch ON hr.chapter_id = ch.id
				WHERE ch.dashboard_id = '{$this->get_post_id()}'";

			if( !$include_additionals ){
				$query .= "AND hr.parent = 0";
			}

			$total = $wpdb->get_var($query);
			return apply_filters( 'pp_dashboard_total_service_hours', !empty($total) ? $total : 0, $this->get_term_id(), $this->get_post_id() );
		}

		public function get_associated_chapters(){

			$chapter = pp_get_dashboard_chapters($this->get_post_id());

			return $chapter;
		}
	}

endif; // End class_exists check
