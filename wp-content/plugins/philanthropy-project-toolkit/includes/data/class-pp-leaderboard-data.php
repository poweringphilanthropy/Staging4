<?php
/**
 * Leaderboard model
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Campaign Model
 *
 * @since       1.0.0
 */
class PP_Leaderboard_Data {

	private $campaign_ids;

	/**
	 * Class constructor.
	 * @access  public
	 * @since   1.0.0
	 */
	public function __construct( $campaign_ids = array() ) {
		$this->campaign_ids = $campaign_ids;
	}

	public function get_campaign_ids($with_child = false){
		if($with_child){
			$merged_ids = array();
			foreach ($this->campaign_ids as $campaign_id) {
				$with_children = pp_get_merged_team_campaign_ids($campaign_id);
				$merged_ids = array_merge( $merged_ids, $with_children );
			}

			return array_unique($merged_ids);
		}

		return $this->campaign_ids;
	}

	public function get_total_donors($with_child = true){
		$campaign_ids = $this->get_campaign_ids($with_child);
		return charitable_get_table( 'campaign_donations' )->count_campaign_donors( $campaign_ids, false );
	}

	public function get_total_donations($with_child = true){
		$campaign_ids = $this->get_campaign_ids($with_child);
		return charitable_get_table( 'campaign_donations' )->get_campaign_donated_amount( $campaign_ids, false );
	}

	/**
	 * @todo Create new service hours table
	 * @param  boolean $include_additionals [description]
	 * @return [type]                       [description]
	 */
	public function get_total_service_hours($include_additionals = false){

	}

	public function get_top_fundraisers(){

		if(empty($this->get_campaign_ids( false )))
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
}
