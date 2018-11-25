<?php
/**
 * The class responsible for querying donors by referral.
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'PP_Charitable_Referral_Donor_Query' ) ) :

	/**
	 * PP_Charitable_Referral_Donor_Query
	 *
	 * @since       1.0.0
	 */
	class PP_Charitable_Referral_Donor_Query extends Charitable_Query {

		/**
		 * Create new query object.
		 *
		 * @param   array $args
		 * @access  public
		 * @since   1.0.0
		 */
		public function __construct( $args = array() ) {
			$defaults = apply_filters( 'charitable_referral_donor_query_default_args', array(
				'output'          => 'donors',
				'status'          => array( 'charitable-completed', 'charitable-preapproved' ),
				'orderby'         => 'date',
				'order'           => 'DESC',
				'number'          => 20,
				'paged'           => 1,
				'fields'          => 'all',
				'campaign'        => 0,
				'distinct_donors' => true,
				'donor_id'        => 0,
			) );

			$this->args = wp_parse_args( $args, $defaults );

			$this->position = 0;
			$this->prepare_query();
			$this->results = $this->get_donors();
		}

		/**
		 * Return list of donor IDs together with the number of donations they have made.
		 *
		 * @return  object[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function get_donors() {
			$records = $this->query();

			if ( 'donors' != $this->get( 'output' ) ) {
				return $records;
			}

			$objects = array();

			$i = 0;
			foreach ( $records as $row ) {
				
				$objects[$i]['data'] = $row;

				$donation_id = isset( $row->donation_id ) ? $row->donation_id : false;
				$objects[$i]['object'] = new Charitable_Donor( $row->donor_id, $donation_id );

			$i++;
			}

			return $objects;
		}

		/**
		 * Set up fields query argument.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function setup_fields() {
			if ( ! $this->get( 'distinct_donors', true ) ) {
				add_filter( 'charitable_query_fields', array( $this, 'donation_fields' ), 4 );
			}

			$fields = $this->get( 'fields', 'all' );

			if ( 'all' == $fields ) {
				add_filter( 'charitable_query_fields', array( $this, 'donation_calc_fields' ), 5 );
				add_filter( 'charitable_query_fields', array( $this, 'donor_fields' ), 6 );
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
			if ( ! $this->get( 'distinct_donors', false ) ) {

				add_filter( 'charitable_query_groupby', array( $this, 'groupby_ID' ) );
				return;
			}

			add_filter( 'charitable_query_groupby', array( $this, 'groupby_donor_id' ) );
		}

		public function referral_fields($fields){
			global $wpdb;
			$fields .= ", pm_referral.meta_value AS referral ";
			return $fields;
		}

		public function join_meta_referral_table($join_statement){
			global $wpdb;
			$join_statement .= " INNER JOIN {$wpdb->postmeta} pm_referral ON (pm_referral.post_id = $wpdb->posts.ID) AND (pm_referral.meta_key = 'referral') ";
			return $join_statement;
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
			remove_filter( 'charitable_query_fields',  array( $this, 'donation_amount_sum_field' ), 6 );
			remove_filter( 'charitable_query_fields',  array( $this, 'referral_fields' ), 10 );
			remove_filter( 'charitable_query_join',    array( $this, 'join_campaign_donations_table_on_donation' ), 5 );
			remove_filter( 'charitable_query_join',    array( $this, 'join_donors_table' ), 6 );
			remove_filter( 'charitable_query_join',    array( $this, 'join_meta_referral_table' ), 6 );
			remove_filter( 'charitable_query_where',   array( $this, 'where_status_is_in' ), 5 );
			remove_filter( 'charitable_query_where',   array( $this, 'where_campaign_is_in' ), 6 );
			remove_filter( 'charitable_query_where',   array( $this, 'where_donor_id_is_in' ), 7 );
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
			add_action( 'charitable_query_fields',   array( $this, 'referral_fields' ), 10 );
			add_filter( 'charitable_query_join',  array( $this, 'join_campaign_donations_table_on_donation' ), 5 );
			add_filter( 'charitable_query_join',  array( $this, 'join_donors_table' ), 6 );
			add_filter( 'charitable_query_join',  array( $this, 'join_meta_referral_table' ), 6 );
			add_filter( 'charitable_query_where', array( $this, 'where_status_is_in' ), 5 );
			add_filter( 'charitable_query_where', array( $this, 'where_campaign_is_in' ), 6 );
			add_filter( 'charitable_query_where', array( $this, 'where_donor_id_is_in' ), 7 );
			add_action( 'charitable_post_query',  array( $this, 'unhook_callbacks' ) );
		}
	}

endif; // End class_exists check
