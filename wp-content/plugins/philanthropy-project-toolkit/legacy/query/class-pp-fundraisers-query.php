<?php
/**
 * The class responsible for querying donors by referral.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'PP_Fundraisers_Query' ) ) :

	/**
	 * PP_Fundraisers_Query
	 *
	 * @since       1.0.0
	 */
	class PP_Fundraisers_Query extends Charitable_Query {

		/**
		 * Create new query object.
		 *
		 * @param   array $args
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( $args = array() ) {
			$defaults = apply_filters( 'charitable_referral_donor_query_default_args', array(
				'output'          => 'fundraisers',
				'status'          => array( 'charitable-completed', 'charitable-preapproved' ),
				'orderby'         => 'date',
				'order'           => 'DESC',
				'number'          => 20,
				'paged'           => 1,
				'fields'          => 'all',
				'campaign'        => 0,
				'distinct_fundraisers' => true,
				'donor_id'        => 0,
				'exclude_na'      => false,
			) );

			$this->args = wp_parse_args( $args, $defaults );

			$this->position = 0;
			$this->prepare_query();
			$this->results = $this->get_fundraisers();
		}

		/**
		 * Return list of donor IDs together with the number of donations they have made.
		 *
		 * @return  object[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_fundraisers() {
			$records = $this->query();

			if ( 'fundraisers' != $this->get( 'output' ) ) {
				return $records;
			}

			$objects = array();

			$i = 0;
			foreach ( $records as $row ) {
				
				$objects[$i] = $row;

				// $donation_id = isset( $row->donation_id ) ? $row->donation_id : false;
				// $objects[$i]['object'] = new Charitable_Donor( $row->donor_id, $donation_id );

			$i++;
			}

			return $objects;
		}

		public function query() {
			if ( ! isset( $this->query ) ) {
				global $wpdb;

				do_action( 'charitable_pre_query', $this );

				$this->parameters = array();

				$sql = "SELECT cd.campaign_id ,{$this->fields()} {$this->from()} {$this->join()} {$this->where()} {$this->groupby()} {$this->orderby()} {$this->order()} {$this->limit()} {$this->offset()};";
				// echo $wpdb->prepare( $sql, $this->parameters ); exit();
				
				$this->query = $wpdb->get_results( $wpdb->prepare( $sql, $this->parameters ) );

				do_action( 'charitable_post_query', $this );
			}

			return $this->query;
		}

		/**
		 * Merged donor ids
		 * @param  [type] $select_statement [description]
		 * @return [type]                   [description]
		 */
		public function donor_ids_fields( $select_statement ) {
			$select_statement .= ", GROUP_CONCAT(DISTINCT d.donor_id SEPARATOR ', ') AS donor_ids";
			return $select_statement;
		}

		/**
		 * Set up fields query argument.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function setup_fields() {
			if ( ! $this->get( 'distinct_fundraisers', true ) ) {
				add_filter( 'charitable_query_fields', array( $this, 'donation_fields' ), 4 );
			}

			$fields = $this->get( 'fields', 'all' );

			if ( 'all' == $fields ) {
				add_filter( 'charitable_query_fields', array( $this, 'donation_calc_fields' ), 5 );

				if ( $this->get( 'distinct_fundraisers', true ) ) {
					add_filter( 'charitable_query_fields', array( $this, 'donor_ids_fields' ), 6 );
				} else {
					add_filter( 'charitable_query_fields', array( $this, 'donor_fields' ), 6 );
				}
			}

			if ( is_array( $fields ) ? in_array( 'amount', $fields ) : 'amount' == $fields ) {
				add_filter( 'charitable_query_fields', array( $this, 'donation_amount_sum_field' ), 6 );
			}
		}

		/**
		 * Set up orderby query argument.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function setup_orderby() {
			$orderby = $this->get( 'orderby', false );

			if ( ! $orderby ) {
				return;
			}

			switch ( $orderby ) {
				case 'date' :
					add_filter( 'charitable_query_orderby', array( $this, 'orderby_date' ) );
					break;

				case 'donations' :
					add_filter( 'charitable_query_orderby', array( $this, 'orderby_count' ) );
					break;

				case 'amount' :
					add_filter( 'charitable_query_orderby', array( $this, 'orderby_donation_amount' ) );
					break;
			}
		}

		/**
		 * Set up query grouping.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function setup_grouping() {
			if ( $this->get( 'distinct_fundraisers', false ) ) {
				add_filter( 'charitable_query_groupby', array( $this, 'groupby_referral' ) );
			} else {
				add_filter( 'charitable_query_groupby', array( $this, 'groupby_donor_id' ) );
			}

			
		}

		public function groupby_referral(){
			return 'GROUP BY referral';
			// return 'GROUP BY campaign_id';
		}

		public function referral_fields($fields){
			global $wpdb;
			$fields .= ", IF(pm_referral.meta_value IS NULL or pm_referral.meta_value = '', 'na', pm_referral.meta_value) AS referral ";
			return $fields;
		}

		public function join_meta_referral_table($join_statement){
			global $wpdb;
			$join_statement .= " LEFT JOIN {$wpdb->postmeta} pm_referral ON (pm_referral.post_id = $wpdb->posts.ID) AND (pm_referral.meta_key = 'referral') ";
			return $join_statement;
		}

		public function where_referal_not_empty( $where_statement ){

			if ( !$this->get( 'exclude_na', false )  ) {
				return $where_statement;
			}

			$where_statement .= " AND IF(pm_referral.meta_value IS NULL or pm_referral.meta_value = '', 'na', pm_referral.meta_value) != 'na'";
			return $where_statement;
		}

		/**
		 * Remove any hooks that have been attached by the class to prevent contaminating other queries.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function unhook_callbacks() {
			remove_action( 'charitable_pre_query',     array( $this, 'setup_fields' ) );
			remove_action( 'charitable_pre_query',     array( $this, 'setup_orderby' ) );
			remove_action( 'charitable_pre_query',     array( $this, 'setup_grouping' ) );

			remove_filter( 'charitable_query_fields',  array( $this, 'donation_fields' ), 4 );
			remove_filter( 'charitable_query_fields',  array( $this, 'donation_calc_fields' ), 5 );
			remove_filter( 'charitable_query_fields',  array( $this, 'donor_fields' ), 6 );
			remove_filter( 'charitable_query_fields',  array( $this, 'donor_ids_fields' ), 6 );
			remove_filter( 'charitable_query_fields',  array( $this, 'donation_amount_sum_field' ), 6 );
			remove_filter( 'charitable_query_fields',  array( $this, 'referral_fields' ), 10 );
			remove_filter( 'charitable_query_join',    array( $this, 'join_campaign_donations_table_on_donation' ), 5 );
			remove_filter( 'charitable_query_join',    array( $this, 'join_donors_table' ), 6 );
			remove_filter( 'charitable_query_join',    array( $this, 'join_meta_referral_table' ), 6 );
			remove_filter( 'charitable_query_where',   array( $this, 'where_status_is_in' ), 5 );
			remove_filter( 'charitable_query_where',   array( $this, 'where_campaign_is_in' ), 6 );
			remove_filter( 'charitable_query_where',   array( $this, 'where_donor_id_is_in' ), 7 );
			remove_filter( 'charitable_query_where',   array( $this, 'where_referal_not_empty' ), 8 );
			remove_filter( 'charitable_query_groupby', array( $this, 'groupby_donor_id' ) );
			remove_filter( 'charitable_query_orderby', array( $this, 'orderby_date' ) );
			remove_filter( 'charitable_query_orderby', array( $this, 'orderby_count' ) );
			remove_filter( 'charitable_query_orderby', array( $this, 'orderby_donation_amount' ) );

			remove_action( 'charitable_post_query',    array( $this, 'unhook_callbacks' ) );
		}

		/**
		 * Set up callbacks for WP_Query filters.
		 *
		 * @return  void
		 * @access  protected
		 * @since   1.0.0
		 */
		protected function prepare_query() {
			add_action( 'charitable_pre_query',   array( $this, 'setup_fields' ) );
			add_action( 'charitable_pre_query',   array( $this, 'setup_orderby' ) );
			add_action( 'charitable_pre_query',   array( $this, 'setup_grouping' ) );

			add_filter( 'charitable_query_fields',   array( $this, 'referral_fields' ), 10 );
			add_filter( 'charitable_query_join',  array( $this, 'join_campaign_donations_table_on_donation' ), 5 );
			add_filter( 'charitable_query_join',  array( $this, 'join_donors_table' ), 6 );
			add_filter( 'charitable_query_join',  array( $this, 'join_meta_referral_table' ), 6 );
			add_filter( 'charitable_query_where', array( $this, 'where_status_is_in' ), 5 );
			add_filter( 'charitable_query_where', array( $this, 'where_campaign_is_in' ), 6 );
			add_filter( 'charitable_query_where', array( $this, 'where_donor_id_is_in' ), 7 );
			add_filter( 'charitable_query_where', array( $this, 'where_referal_not_empty' ), 8 );
			add_action( 'charitable_post_query',  array( $this, 'unhook_callbacks' ) );
		}
	}

endif; // End class_exists check
