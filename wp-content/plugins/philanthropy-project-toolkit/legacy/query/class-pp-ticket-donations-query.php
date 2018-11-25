<?php
/**
 * A helper class to retrieve Ticket Donations.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'PP_Ticket_Donations_Query' ) ) :

	/**
	 * PP_Ticket_Donations_Query
	 *
	 * @since 	1.4.0
	 */
	class PP_Ticket_Donations_Query extends Charitable_Query {

		/**
		 * Create class object.
		 *
		 * @param 	array $args Arguments used in query.
		 * @access  public
		 * @since   1.4.0
		 */
		public function __construct( $args = array() ) {

			$defaults = array(
				// Set to an array with statuses to only show certain statuses.
				'status'   => false,
				// Currently only supports 'date'.
				'orderby'  => 'date',
				// May be 'DESC' or 'ASC'.
				'order'    => 'DESC',
				// Number of donations to retrieve. Show all for merchandises
				'number'   => -1,
				// For paged results.
				'paged'    => 1,
				// Only get donations for a specific campaign.
				'campaign' => 0,
				// Only get donations by a specific donor.
				'donor_id' => 0,
			);

			$this->args = wp_parse_args( $args, $defaults );

			$this->position = 0;
			$this->prepare_query();
			$this->results = $this->get_donations();

		}

		/**
		 * Return the results of the query.
		 *
		 * @global  WPDB $wpdb
		 *
		 * @return  object[]
		 * @access  public
		 * @since   1.0.0
		 */
		public function query() {
			if ( ! isset( $this->query ) ) {
				global $wpdb;

				do_action( 'charitable_pre_query', $this );

				$this->parameters = array();

				$sql = "SELECT {$this->fields()} {$this->from()} {$this->join()} {$this->where()} {$this->groupby()} {$this->orderby()} {$this->order()} {$this->limit()} {$this->offset()};";

				// echo $wpdb->prepare( $sql, $this->parameters ); exit();

				$this->query = $wpdb->get_results( $wpdb->prepare( $sql, $this->parameters ) );

				do_action( 'charitable_post_query', $this );
			}

			return $this->query;
		}

		/**
		 * Set up fields query argument.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.4.0
		 */
		public function setup_fields() {

			add_filter( 'charitable_query_fields', array( $this, 'donor_fields' ), 4 );

		}

		/**
		 * Return list of donation IDs together with the number of donations they have made.
		 *
		 * @return  object[]
		 * @access  public
		 * @since   1.4.0
		 */
		public function get_donations() {

			$cache_key = 'ticket-donations' . md5( maybe_serialize( $this->args ) );

			// $donations = wp_cache_get( $cache_key , 'pp-toolkit');
			$donations = false;

			if (false === $donations) {

				$donations = array();

				$records = $this->query();

				$currency_helper = charitable_get_currency_helper();

				// echo "<pre>";
				// print_r($records);
				// echo "</pre>";

				if ( !empty($records) ) {

					foreach ( $records as $i => $row ) {

						$payment_id = Charitable_EDD_Payment::get_payment_for_donation( $row->ID );

						$donation_log = get_post_meta( $row->ID, 'donation_from_edd_payment_log', true );
						
						$payment_downloads = edd_get_payment_meta_downloads( $payment_id );

						if(empty($donation_log) || empty($payment_id))
							continue;

						foreach ($donation_log as $log) {
							if(!isset($log['download_id']))
								continue;

							// if(!isset($log['quantity'])){
								echo "<hr>";
								echo 'payment id:' . $payment_id;
								echo "<pre>";
								print_r($log);
								echo "</pre>";
							// }

							// echo "<pre>";
							// print_r($log);
							// echo "</pre>";

							$download_id = absint( $log['download_id'] );
							$price_id = isset($log['price_id']) ? $log['price_id'] : 0;

							if(!has_term( 'ticket', 'download_category', $download_id ))
				 				continue;

				 			$key_payment_downloads = array_search( $download_id, array_column($payment_downloads, 'id') );
				 			$options = isset($payment_downloads[$key_payment_downloads]) && isset($payment_downloads[$key_payment_downloads]['options']) ? $payment_downloads[$key_payment_downloads]['options'] : array();
				 			// ticket holder
		                    $ticket_holder = array();
		                    if (isset($options['tribe-tickets-meta']) 
		                        && is_array($options['tribe-tickets-meta']) 
		                        && !empty($options['tribe-tickets-meta']))
		                    {
		                        $_ticket_holder = wp_list_pluck( $options['tribe-tickets-meta'], 'ticket-holder-name' );
		                        $ticket_holder = implode(', ', $_ticket_holder);
		                    }


							$download_unique_key = $download_id;
	                    	$download_description = get_the_title( $download_id );

							if ( edd_has_variable_prices( $download_id ) ) {
	                            // append unique key
	                            $download_unique_key .= '-' . $price_id;

	                            $download_description .= ' - ' . edd_get_price_option_name( $download_id, $price_id, $payment_id );
	                        }

	                        $obj = new stdClass();
	                        $obj->unique_key = $download_unique_key;
	                        $obj->download_id = $download_id;
	                        $obj->name = $download_description . ' | ' . @$log['amount'] . ' | ' . @$log['quantity'];
	                        $obj->campaign_id = $log['campaign_id'];
	                        $obj->quantity = isset($log['quantity']) ? intval($log['quantity']) : 1;
	                        $obj->amount = floatval($log['amount']);
	                        $obj->donor_id = $row->donor_id;
	                        $obj->ticket_holder = $ticket_holder;

	                        $donations[] = $obj;
						}
					}
				}

				wp_cache_add( $cache_key, $donations, 'pp-toolkit' );
			}

			return $donations;

		}

		public function get_grouped_donations( $unique_key = false ){
			$result = array();

		    foreach ($this->get_donations() as $element) {
		        $result[$element->unique_key][] = $element;
		    }

		    if($unique_key){
		    	return isset($result[$unique_key]) ? $result[$unique_key] : array();
		    }

		    return $result;
		}

		public function get_total_donations( $unique_key = false ){
			if($unique_key){
				$donations = $this->get_grouped_donations( $unique_key );
			} else {
				$donations = $this->get_donations();
			}

			$qtys = wp_list_pluck( $donations, 'quantity' );

			return array_sum( array_map('intval', $qtys) );
		}

		public function get_total_donation_amount( $unique_key = false ){

			if($unique_key){
				$donations = $this->get_grouped_donations( $unique_key );
			} else {
				$donations = $this->get_donations();
			}

			$amounts = wp_list_pluck( $donations, 'amount' );

			return array_sum( array_map('floatval', $amounts) );
			
		}

		public function get_donor_ids( $unique_key = false ){
			if($unique_key){
				$donations = $this->get_grouped_donations( $unique_key );
			} else {
				$donations = $this->get_donations();
			}

			$donor_ids = wp_list_pluck( $donations, 'donor_id' );

			return array_unique($donor_ids);
		}

		public function get_total_donor( $unique_key = false ){
			$donor_ids = $this->get_donor_ids( $unique_key = false );
			return count($donor_ids);
		}

		/**
		 * Remove any hooks that have been attached by the class to prevent contaminating other queries.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.4.0
		 */
		public function unhook_callbacks() {
			remove_action( 'charitable_pre_query',     array( $this, 'setup_fields' ) );
			remove_filter( 'charitable_query_fields',  array( $this, 'donor_fields' ), 4 );
			remove_filter( 'charitable_query_join',    array( $this, 'join_campaign_donations_table_on_donation' ), 5 );
			remove_filter( 'charitable_query_join',    array( $this, 'join_donors_table' ), 6 );
			remove_filter( 'charitable_query_where',   array( $this, 'where_status_is_in' ), 5 );
			remove_filter( 'charitable_query_where',   array( $this, 'where_campaign_is_in' ), 6 );
			remove_filter( 'charitable_query_where',   array( $this, 'where_donor_id_is_in' ), 7 );
			remove_filter( 'charitable_query_orderby', array( $this, 'orderby_date' ) );
			remove_filter( 'charitable_query_orderby', array( $this, 'orderby_donation_amount' ) );
			remove_action( 'charitable_post_query',    array( $this, 'unhook_callbacks' ) );

		}

		/**
		 * Set up callbacks for WP_Query filters.
		 *
		 * @return  void
		 * @access  protected
		 * @since   1.4.0
		 */
		protected function prepare_query() {
			add_action( 'charitable_pre_query',     array( $this, 'setup_fields' ) );
			add_filter( 'charitable_query_join',    array( $this, 'join_campaign_donations_table_on_donation' ), 5 );
			add_filter( 'charitable_query_join',  	array( $this, 'join_donors_table' ), 6 );
			add_filter( 'charitable_query_where',   array( $this, 'where_status_is_in' ), 5 );
			add_filter( 'charitable_query_where',   array( $this, 'where_campaign_is_in' ), 6 );
			add_filter( 'charitable_query_where',   array( $this, 'where_donor_id_is_in' ), 7 );
			add_filter( 'charitable_query_groupby', array( $this, 'groupby_donation_id' ) );
			add_action( 'charitable_post_query',    array( $this, 'unhook_callbacks' ) );

		}
	}

endif;
