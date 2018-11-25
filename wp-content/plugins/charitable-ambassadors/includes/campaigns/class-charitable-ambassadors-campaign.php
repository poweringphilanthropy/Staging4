<?php
/**
 * A class responsible for augmenting the information described about a campaign.
 *
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Campaign
 * @version     1.1.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Ambassadors_Campaign' ) ) :

	/**
	 * Charitable_Ambassadors_Campaign
	 *
	 * @since       1.1.0
	 */
	class Charitable_Ambassadors_Campaign {

		/**
		 * @var     Charitable_Ambassadors_Campaign
		 * @access  private
		 * @static
		 * @since   1.1.0
		 */
		private static $instance = null;

		/**
		 * Create class object. Private constructor.
		 *
		 * @access  private
		 * @since   1.1.0
		 */
		private function __construct() {
		}

		/**
		 * Create and return the class object.
		 *
		 * @access  public
		 * @static
		 * @since   1.1.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new Charitable_Ambassadors_Campaign();
			}

			return self::$instance;
		}

		/**
		 * Return all of the child campaigns.
		 *
		 * @param   int $campaign_id
		 * @return  WP_Query
		 * @access  public
		 * @since   1.1.0
		 */
		public function get_child_campaigns( $campaign_id ) {
			$campaigns = array();

			$children = $this->get_direct_child_campaigns( $campaign_id );

			if ( empty( $children ) ) {
				return $campaigns;
			}

			foreach ( $children as $child_campaign_id ) {
				$campaigns[] = $child_campaign_id;

				$grandchildren = $this->get_child_campaigns( $child_campaign_id );

				if ( ! empty( $grandchildren ) ) {
					$campaigns = array_merge( $campaigns, $grandchildren );
				}
			}

			return $campaigns;
		}

		/**
		 * Return all of the direct child campaigns.
		 *
		 * @param   int $campaign_id
		 * @return  int[]|WP_Post[]
		 * @access  public
		 * @since   1.1.0
		 */
		public function get_direct_child_campaigns( $campaign_id ) {
			$args = array(
				'post_type' => Charitable::CAMPAIGN_POST_TYPE,
				'posts_per_page' => -1,
				'post_parent' => $campaign_id,
				'fields' => 'ids',
			);

			return get_posts( $args );
		}

		/**
		 * Return the donated amount to include any donations to child campaigns.
		 *
		 * @see     Charitable_Campaign::get_donated_amount()
		 *
		 * @param   string 				$amount
		 * @param   Charitable_Campaign $campaign
		 * @param 	boolean 			$sanitize Whether to sanitize the amount. False by default.
		 * @return  string
		 * @access  public
		 * @since   1.1.0
		 */
		public function get_donated_amount( $amount, Charitable_Campaign $campaign, $sanitize = false ) {
			$campaigns = $this->get_child_campaigns( $campaign->ID );

			if ( empty( $campaigns ) ) {
				return $amount;
			}

			$campaigns[] = $campaign->ID;
			$amount      = charitable_get_table( 'campaign_donations' )->get_campaign_donated_amount( $campaigns );

			if ( $sanitize ) {
				$amount = charitable_sanitize_amount( $amount );
			}

			return $amount;
		}

		/**
		 * Return the number of donors, including any donations to child campaigns.
		 *
		 * @see     Charitable_Campaign::get_donor_count()
		 *
		 * @param   int 				$count
		 * @param   Charitable_Campaign $campaign
		 * @return  int
		 * @access  public
		 * @since   1.1.10
		 */
		public function get_donor_count( $count, Charitable_Campaign $campaign ) {

			$campaigns   = $this->get_child_campaigns( $campaign->ID );
			$campaigns[] = $campaign->ID;

			return charitable_get_table( 'campaign_donations' )->count_campaign_donors( $campaigns );
		}

		/**
		 * Filter the donor query args used to retrieve donors in the widget.
		 *
		 * @param 	array $args
		 * @return  array
		 * @access  public
		 * @since   1.1.10
		 */
		public function filter_donor_query_args( $args ) {
			if ( ! array_key_exists( 'campaign', $args ) ) {
				return $args;
			}

			if ( 'all' == $args['campaign'] ) {
				return $args;
			}

			$campaign_id      = 'current' == $args['campaign'] ? charitable_get_current_campaign_id() : $args['campaign'];
			$campaigns        = $this->get_child_campaigns( $campaign_id );
			$campaigns[]      = $campaign_id;
			$args['campaign'] = $campaigns;

			return $args;
		}

		/**
		 * Return the amount that a donor donated to a particular campaign in a given donation, including donations to child campaigns.
		 *
		 * @param 	string 			 $amount
		 * @param 	Charitable_Donor $donor
		 * @param 	int   		     $campaign_id
		 * @return  string
		 * @access  public
		 * @since   1.1.10
		 */
		public function get_donor_donation_amount( $amount, Charitable_Donor $donor, $campaign_id = '' ) {
			if ( empty( $campaign_id ) ) {
				return $amount;
			}

			$campaigns   = $this->get_child_campaigns( $campaign_id );
			$campaigns[] = $campaign_id;

			return charitable_get_table( 'campaign_donations' )->get_donation_amount( $donor->donation_id, $campaigns );
		}

		/**
		 * Return the amount that a donor has donated to a particular campaign in any donation, including donations to child campaigns.
		 *
		 * @param 	array 	 		$args
		 * @param 	Charitable_User $user
		 * @return  array
		 * @access  public
		 * @since   1.1.10
		 */
		public function get_user_donation_amount( $args, Charitable_User $user ) {
			if ( 0 == $args['campaign'] ) {
				return $args;
			}

			$campaigns   = $this->get_child_campaigns( $args['campaign'] );
			$campaigns[] = $args['campaign'];

			$args['campaign'] = $campaigns;

			return $args;
		}

		/**
		 * Correctly order campaigns by amount in the [campaigns] shortcode.
		 *
		 * @param 	array $view_args
		 * @param 	array $args
		 * @return  array
		 * @access  public
		 * @since   1.1.10
		 */
		public function filter_campaigns_shortcode_args( $view_args, $args ) {
			if ( 'popular' !== $args['orderby'] ) {
				return $view_args;
			}

			$campaigns = $view_args['campaigns']->posts;

			if ( empty( $campaigns ) ) {
				return $view_args;
			}

			/**
			 * We're suppressing errors because PHP otherwise throws a warning,
			 * due to how we are using the campaign objects in the sorting callback.
			 *
			 * @see http://stackoverflow.com/questions/3235387/usort-array-was-modified-by-the-user-comparison-function
			 */
			@usort( $campaigns, array( $this, 'sort_campaigns_by_amount' ) );

			$view_args['campaigns']->posts = $campaigns;

			return $view_args;
		}

		/**
		 * Sort campaigns by amount raised.
		 *
		 * @param 	WP_Post $campaign_a
		 * @param 	WP_Post $campaign_b
		 * @return  int
		 * @access  public
		 * @since   1.1.10
		 */
		public function sort_campaigns_by_amount( $campaign_a, $campaign_b ) {
			$a_amount = charitable_get_campaign( $campaign_a->ID )->get_donated_amount();
			$b_amount = charitable_get_campaign( $campaign_b->ID )->get_donated_amount();

			if ( $a_amount == $b_amount ) {
				return 0;
			}

			return ( $a_amount < $b_amount ) ? 1 : -1;
		}
	}

endif; // End class_exists check
