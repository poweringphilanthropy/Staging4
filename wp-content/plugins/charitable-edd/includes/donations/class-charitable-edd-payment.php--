<?php
/**
 * Class responsible for handling EDD purchases and generating donations based on the purchase.
 *
 * @package		Charitable EDD/Classes/Charitable_EDD_Payment
 * @version 	1.0.0
 * @author 		Eric Daams
 * @copyright 	Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_EDD_Payment' ) ) :

	/**
	 * Charitable_EDD_Payment
	 *
	 * @since 		1.0.0
	 */
	class Charitable_EDD_Payment {

	    /**
	     * @var     Charitable_EDD_Payment
	     * @access  private
	     * @static
	     * @since   1.0.0
	     */
	    private static $instance = null;

	    /**
	     * Create class object.
	     *
	     * @access  private
	     * @since   1.0.0
	     */
	    private function __construct() {
	    }

	    /**
	     * Create and return the class object.
	     *
	     * @access  public
	     * @static
	     * @since   1.0.0
	     */
	    public static function get_instance() {
	        if ( is_null( self::$instance ) ) {
	            self::$instance = new Charitable_EDD_Payment();
	        }

	        return self::$instance;
	    }

	    /**
	     * Create a new donation from an EDD payment.
	     *
	     * @param 	int $payment_id
	     * @param 	array $payment_data
	     * @return 	int The donation ID
	     * @access 	public
	     * @since 	1.0.0
	     */
	    public function create_donation( $payment_id, $payment_data ) {

	    	/** If we're in test mode, don't proceed unless expected. **/
			if ( edd_is_test_mode() && ! apply_filters( 'edd_log_test_payment_stats', false ) ) {
				return 0;
			}

	    	$status = isset( $payment_data['status'] ) ? $payment_data['status'] : 'pending';

	    	return $this->add_donation_for_payment( $payment_id, $status );

	    }

		/**
		 * Process a payment. Fired when a payment's status is changed.
		 *
		 * @param 	int 		$payment_id
		 * @param 	string 		$new_status
		 * @param 	string 		$old_status
		 * @return	int 		The number of donations added or removed.
		 * @access  public
		 * @since 	1.0.0
		 */
		public function update_donation_status( $payment_id, $new_status, $old_status ) {

			/** If we're in test mode, don't proceed unless expected. **/
			if ( edd_is_test_mode() && ! apply_filters( 'edd_log_test_payment_stats', false ) ) {
				return 0;
			}

			if ( $new_status == $old_status ) {
				return 0;
			}

			if ( ! $this->payment_has_donation( $payment_id ) ) {
				return $this->add_donation_for_payment( $payment_id, $new_status );
			}

			$donation_id = $this->get_donation_through_payment( $payment_id );
			$donation    = new Charitable_Donation( $this->get_donation_through_payment( $payment_id ) );
			$donation_id = $donation->update_status( $this->get_charitable_donation_status( $new_status ) );

			return $donation_id;

		}

		/**
		 * Add donations for a new payment.
		 *
		 * @param 	int 	         $payment_id
		 * @param 	string 	         $new_status
		 * @param 	boolean|DateTime $at_date Get benefactors at specified date. May be a string or a boolean.
		 * 									  If true (default), this will get any benefactors that are currently active.
		 * 									  If false, this will get any benefactors.
		 * 									  If a DateTime object, this will get any benefactors active at the date.
		 * @param 	array 	         $args
		 * @return 	int				 The new donation's ID.
		 * @access  public
		 * @since 	1.0.0
		 */
		public function add_donation_for_payment( $payment_id, $new_status, $at_date = true, $args = array() ) {

			$edd_cart = Charitable_EDD_Cart::create_with_payment( $payment_id );
			
			if ( false !== $at_date ) {
				$at_date  = new DateTime( get_post_field( 'post_date', $payment_id ) );	
			}

			if ( ! $edd_cart->has_benefactors( $at_date ) && ! $edd_cart->has_donations_as_fees() ) {
				return -2;
			}

			$campaign_donations = $this->get_payment_campaign_donations( $edd_cart, $payment_id );
			$log                = $this->get_payment_donation_log( $edd_cart, $payment_id );
			$user_id            = edd_get_payment_user_id( $payment_id );
			$email              = edd_get_payment_user_email( $payment_id );
			$payment_meta       = edd_get_payment_meta( $payment_id );
			$user_info          = array_key_exists( 'user_info', $payment_meta ) ? $payment_meta['user_info'] : array();
			$address_info       = array_key_exists( 'address', $user_info ) ? $user_info['address'] : array();
			$user_args          = array(
				'email'		 => $email,
				'first_name' => array_key_exists( 'first_name', $user_info ) ? $user_info['first_name'] : '',
				'last_name'	 => array_key_exists( 'last_name', $user_info ) ? $user_info['last_name'] : '',
				'address'    => array_key_exists( 'line1', $address_info ) ? $address_info['line1'] : '',
				'address_2'  => array_key_exists( 'line2', $address_info ) ? $address_info['line2'] : '',
				'city'       => array_key_exists( 'city', $address_info ) ? $address_info['city'] : '',
				'state'      => array_key_exists( 'state', $address_info ) ? $address_info['state'] : '',
				'postcode'   => array_key_exists( 'zip', $address_info ) ? $address_info['zip'] : '',
				'country'    => array_key_exists( 'country', $address_info ) ? $address_info['country'] : '',
			);
			$donation_args = array(
				'user_id'		=> $user_id,
				'user'			=> $user_args,
				'status'		=> $this->get_charitable_donation_status( $new_status ),
				'gateway'		=> 'EDD',
				'campaigns'		=> $campaign_donations,
			);

			$donation_args = wp_parse_args( $args, $donation_args );
			$donation_args = apply_filters( 'charitable_edd_donation_values', $donation_args, $payment_id, $args );
			$donation_id   = Charitable_Donation_Processor::get_instance()->save_donation( $donation_args );

			/* Throw an error if the donation is not inserted correctly. */
			if ( 0 === $donation_id ) {
				charitable_get_deprecated()->doing_it_wrong( __METHOD__, 'Unable to insert donation.', '1.0.0' );
				return 0;
			}

			/**
			 * Save meta. We store the donation ID for the payment ID, and in the
			 * donation's meta we store a log of how the donations are added.
			 */
			add_post_meta( $payment_id, 'charitable_donation_from_edd_payment', $donation_id );
			add_post_meta( $donation_id, 'donation_from_edd_payment_log', $log );

			wp_cache_delete( 'campaign_donation_from_edd_payment_' . $payment_id );

			/* Return the donation ID */
			return $donation_id;

		}

		/**
		 * Get all the campaign donations for this payment.
		 *
		 * @return  array
		 * @access  public
		 * @since   1.1.0
		 */
		public function get_payment_campaign_donations( Charitable_EDD_Cart $edd_cart ) {

			$campaign_donations = array();

			/* Add all campaign donations from benefactor relationships. */
			foreach ( $edd_cart->get_benefactor_benefits() as $benefactor_id => $benefits ) {

				foreach ( $benefits as $download_id => $benefit ) {

					$campaign_donations[] = array(
						'campaign_id' => $benefit['campaign_id'],
						'amount'	  => $benefit['contribution'],
					);

				}
			}

			/* Add all campaign donations created as fees. */
			if ( $edd_cart->has_donations_as_fees() ) {

				foreach ( $edd_cart->get_fees() as $campaign_id => $fees ) {

					$benefit_amount = array_sum( $fees );

					$campaign_donations[] = array(
						'campaign_id' => $campaign_id,
						'amount'	  => $benefit_amount,
					);

				}
			}

			return $campaign_donations;

		}

		/**
		 * Get a log of campaign donations for this payment.
		 *
		 * @return  array
		 * @access  public
		 * @since   1.1.0
		 */
		public function get_payment_donation_log( Charitable_EDD_Cart $edd_cart ) {

			$log = array();

			/* Add all campaign donations from benefactor relationships. */
			foreach ( $edd_cart->get_benefactor_benefits() as $benefactor_id => $benefits ) {

				foreach ( $benefits as $download_id => $benefit ) {

					/* Pull out the price ID */
					$price_id = false;

					if ( false !== strpos( $download_id, '_' ) ) {
						list( $download_id, $price_id ) = explode( '_', $download_id );
					} else {
						$price_id = false;
					}

					$item_log = array(
						'edd_fee'		=> 0,
						'download_id' 	=> $download_id,
						'benefactor_id'	=> $benefactor_id,
						'campaign_id'	=> $benefit['campaign_id'],
						'amount'		=> $benefit['contribution'],
					);

					if ( array_key_exists( 'quantity', $benefit ) ) {
						$item_log['quantity'] = $benefit['quantity'];
					}

					if ( array_key_exists( 'price', $benefit ) ) {
						$item_log['price'] = $benefit['price'];
					}

					if ( false !== $price_id ) {
						$item_log['price_id'] = $price_id;
					}

					$log[] = $item_log;
				}
			}

			/* Add all campaign donations created as fees. */
			if ( $edd_cart->has_donations_as_fees() ) {

				foreach ( $edd_cart->get_fees() as $campaign_id => $fees ) {

					$benefit_amount = array_sum( $fees );

					$log[] = array(
						'edd_fee'	  => 1,
						'campaign_id' => $campaign_id,
						'amount'	  => $benefit_amount,
					);
				}
			}

			return $log;

		}

		/**
		 * Sync campaign donations for a particular donation.
		 *
		 * @param 	int $donation_id
		 * @return 	int              Returns the donation ID or 0 if the donation has been deleted.
		 * @access  public
		 * @since 	1.0.0
		 */
		public function resync_donation_from_payment( $donation_id ) {

			$payment_id = Charitable_EDD_Payment::get_payment_for_donation( $donation_id );
			$edd_cart   = Charitable_EDD_Cart::create_with_payment( $payment_id );
			$at_date    = new DateTime( get_post_field( 'post_date', $payment_id ) );

			/* If there are no benefactors or fees for this payment anymore, delete the donation. */
			if ( ! $edd_cart->has_benefactors( $at_date ) && ! $edd_cart->has_donations_as_fees() ) {
				wp_delete_post( $donation_id );
				return 0;
			}

			$campaign_donations    = $this->get_payment_campaign_donations( $edd_cart );
			$log                   = $this->get_payment_donation_log( $edd_cart );
			$current_donations     = charitable_get_table( 'campaign_donations' )->get_donation_records( $donation_id );
			$current_donations_ref = array();
			$donor_id              = current( $current_donations )->donor_id;
			
			foreach ( $current_donations as $key => $current_donation ) {
				$current_donations_ref[ $key ] = $current_donation->campaign_id . ':' . number_format( $current_donation->amount, 2 );
			}
			
			/* Loop over our newly generated log */
			foreach ( $log as $log_item ) {
				
				$ref         = $log_item['campaign_id'] . ':' . number_format( $log_item['amount'], 2 );
				$current_ref = array_search( $ref, $current_donations_ref );

				/* This donation doesn't exist yet, so we need to create it. */
				if ( false === $current_ref ) {

					$args = array(
						'donation_id' => $donation_id,
						'donor_id'    => $donor_id,
						'campaign_id' => $log_item['campaign_id'],
						'amount'      => $log_item['amount'],
					);

					charitable_get_table( 'campaign_donations' )->insert( $args );

				} else {

					unset( $current_donations_ref[ $current_ref ] );
					unset( $current_donations[ $current_ref ] );

				}
			}

			/* Delete any of the $current_donations that still exist. */
			foreach ( $current_donations as $campaign_donation_id => $object ) {
				charitable_get_table( 'campaign_donations' )->delete( $campaign_donation_id );
			}

			update_post_meta( $donation_id, 'donation_from_edd_payment_log', $log );

			return $donation_id;

		}

		/**
		 * Delete payment meta related to donations when a donation is deleted.
		 *
		 * @global	WPDB $wpdb
		 * @param	int  $donation_id
		 * @return	void
		 * @since	1.0.6
		 */
		public function delete_donation_meta( $donation_id ) {

			global $wpdb;

			$sql        = "SELECT post_id
						   FROM $wpdb->postmeta
						   WHERE meta_key = 'charitable_donation_from_edd_payment'
						   AND meta_value = %d";

			$payment_id = $wpdb->get_var( $wpdb->prepare( $sql, $donation_id ) );

			if ( ! $payment_id ) {
				return;
			}

			wp_cache_delete( 'campaign_donation_from_edd_payment_' . $payment_id );

			delete_post_meta( $payment_id, 'charitable_donation_from_edd_payment' );

		}

		/**
		 * Given a donation ID, this returns the associated EDD payment.
		 *
		 * @global 	WPDB 	$wpdb
		 * @param 	int 	$donation_id
		 * @return  int
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public static function get_payment_for_donation( $donation_id ) {
			global $wpdb;

			$payment_id = wp_cache_get( $donation_id, 'edd_payment_from_charitable_donation' );

			if ( false === $payment_id ) {

				$sql = "SELECT post_id
						FROM $wpdb->postmeta
						WHERE meta_key = 'charitable_donation_from_edd_payment'
						AND meta_value = %d";

				$payment_id = $wpdb->get_var( $wpdb->prepare( $sql, intval( $donation_id ) ) );

				wp_cache_set( $donation_id, $payment_id, 'edd_payment_from_charitable_donation' );
			}

			return $payment_id;
		}

		/**
		 * Get the donation made through the payment.
		 *
		 * @global 	WPDB 	$wpdb
		 * @param 	int 	$payment_id
		 * @return 	int
		 * @access  public
		 * @since 	1.0.0
		 */
		public function get_donation_through_payment( $payment_id ) {
			global $wpdb;

			$donation = wp_cache_get( 'campaign_donation_from_edd_payment_' . $payment_id );

			if ( false === $donation ) {

				$sql = "SELECT meta_value
						FROM $wpdb->postmeta
						WHERE meta_key = 'charitable_donation_from_edd_payment'
						AND post_id = %d";

				$donation = $wpdb->get_var( $wpdb->prepare( $sql, intval( $payment_id ) ) );

				wp_cache_set( 'campaign_donation_from_edd_payment_' . $payment_id, $donation );
			}

			return $donation;
		}

		/**
		 * Returns whether the given payment has a donation.
		 *
		 * @return 	boolean
		 * @access  public
		 * @since 	1.0.0
		 */
		public function payment_has_donation( $payment_id ) {
			return ! is_null( $this->get_donation_through_payment( $payment_id ) );
		}

		/**
	     * Returns all the campaign donations made through the payment.
		 *
	     * An important distinction needs to be made here between a "donation"
	     * and a "campaign donation". One payment will result in one "donation"
	     * at most. However, one "donation" can have multiple "campaign donations".
	     * A donor may contribute to multiple campaigns in a single donation.
	     * In Charitable, donations are stored in the posts table while
	     * campaign donations are stored in the campaign_donations table.
	     *
	     * @param 	int $payment_id
	     * @return 	object[]|false Collection of objects if there is a donation for the payment. False if not.
	     * @access  public
	     * @since 	1.0.0
	     */
		public function get_campaign_donations_through_payment( $payment_id ) {
			$donation_id = $this->get_donation_through_payment( $payment_id );

			if ( ! $donation_id ) {
				return false;
			}
			
			return charitable()->get_db_table( 'campaign_donations' )->get_donation_records( $donation_id );
		}

		/**
	     * Returns the sum of all campaign donations made through the payment.
	     *
	     * @param 	int $payment_id
	     * @return 	int|false Int if there is a donation. False if there is no corresponding donation.
	     * @access  public
	     * @since 	1.0.0
	     */
		public function get_campaign_donation_total_through_payment( $payment_id ) {
			$donation_id = $this->get_donation_through_payment( $payment_id );
			
			if ( ! $donation_id ) {
				return false;
			}
			
			return charitable()->get_db_table( 'campaign_donations' )->get_donation_total_amount( $donation_id );
		}

		/**
		 * Return the Charitable donation status equivalent of an EDD payment status.
		 *
		 * @param 	string 		$edd_status
		 * @return 	string
		 * @access  public
		 * @since 	1.0.0
		 */
		public function get_charitable_donation_status( $edd_status ) {
			switch ( $edd_status ) {

				case 'publish' :
					$status = 'charitable-completed';
					break;

				case 'pending' :
					$status = 'charitable-pending';
					break;

				case 'refunded' :
					$status = 'charitable-refunded';
					break;

				case 'revoked' :
				case 'abandoned' :
				case 'cancelled'  :
					$status = 'charitable-cancelled';
					break;

				case 'failed' :
					$status = 'charitable-failed';
					break;

				case 'preapproval' :
					$status = 'charitable-preapproved';
					break;

				default :
					$status = $edd_status;
			}

			return apply_filters( 'charitable_edd_donation_status', $status, $edd_status );
		}

		/**
		 * Filter EDD receipt attributes to hide products table.
		 *
		 * @param 	array 		$atts
		 * @return 	array
		 * @access  public
		 * @since 	1.0.0
		 */
		public function filter_edd_receipt_attributes( $atts ) {
			$atts['charitable_edd_products'] = $atts['products'];
			$atts['products'] = 0;
			return $atts;
		}

		/**
		 * Make sure that the download ID is always set in the download file url args.
		 *
		 * @return  array 	$args
		 * @access  public
		 * @since   1.0.0
		 */
		public function set_download_id_in_args( $args ) {
			if ( ! isset( $args['download_id'] ) ) {
				if ( isset( $args['ticket'] ) ) {
					$args['download_id'] = $args['ticket'];
				}
			}

			if ( ! isset( $args['file'] ) ) {
				$args['file'] = 0;
			}

			return $args;
		}

		/**
		 * Add donations to subtotal.
		 *
		 * @param 	float 	$subtotal
		 * @param 	int 	$payment_id
		 * @return  float
		 * @access  public
		 * @since   1.0.0
		 */
		public function add_donations_to_subtotal( $subtotal, $payment_id ) {
			return Charitable_EDD_Cart::add_donation_fees_to_subtotal( $subtotal, edd_get_payment_fees( $payment_id, 'item' ) );
		}

		/**
		 * Display donations table on receipt.
		 *
		 * @return 	void
		 * @access  public
		 * @since 	1.0.0
		 */
		public function donations_table_receipt() {
			charitable_edd_template( 'payment-receipt/donations.php' );
		}

		/**
		 * Display products table on receipt.
		 *
		 * @return 	void
		 * @access  public
		 * @since 	1.0.0
		 */
		public function products_table_receipt() {
			charitable_edd_template( 'payment-receipt/products.php' );
		}
	}

endif; // End class_exists check
