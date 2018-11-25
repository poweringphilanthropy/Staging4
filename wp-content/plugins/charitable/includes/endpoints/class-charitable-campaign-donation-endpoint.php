<?php
/**
 * Donate endpoint.
 *
 * @package   Charitable/Classes/Charitable_Campaign_Donation_Endpoint
 * @author    Eric Daams
 * @copyright Copyright (c) 2018, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.5.0
 * @version   1.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Charitable_Campaign_Donation_Endpoint' ) ) :

	/**
	 * Charitable_Campaign_Donation_Endpoint
	 *
	 * @abstract
	 * @since  1.5.0
	 */
	class Charitable_Campaign_Donation_Endpoint extends Charitable_Endpoint {

		/**
		 * @var     string
		 */
		const ID = 'campaign_donation';

		/**
		 * Object instantiation.
		 *
		 * @since 1.5.4
		 */
		public function __construct() {
			$this->cacheable = false;
		}

		/**
		 * Return the endpoint ID.
		 *
		 * @since  1.5.0
		 *
		 * @return string
		 */
		public static function get_endpoint_id() {
			return self::ID;
		}

		/**
		 * Add rewrite rules for the endpoint.
		 *
		 * @since 1.5.0
		 */
		public function setup_rewrite_rules() {
			add_rewrite_endpoint( 'donate', EP_PERMALINK );
		}

		/**
		 * Return the endpoint URL.
		 *		 
		 * @since  1.5.0
		 *
		 * @global WP_Rewrite $wp_rewrite
		 * @param  array $args Mixed args.
		 * @return string
		 */
		public function get_page_url( $args = array() ) {
			global $wp_rewrite;

			$campaign_id  = array_key_exists( 'campaign_id', $args ) ? $args['campaign_id'] : get_the_ID();
			$campaign_url = get_permalink( $campaign_id );

			if ( 'same_page' == charitable_get_option( 'donation_form_display', 'separate_page' ) ) {
				return $campaign_url;
			}

			if ( $wp_rewrite->using_permalinks()
				&& ! in_array( get_post_status( $campaign_id ), array( 'pending', 'draft' ) )
				&& ! isset( $_GET['preview'] ) ) {
				return trailingslashit( $campaign_url ) . 'donate/';
			}

			return esc_url_raw( add_query_arg( array( 'donate' => 1 ), $campaign_url ) );
		}

		/**
		 * Return whether we are currently viewing the endpoint.
		 *
		 * @since  1.5.0
		 *
		 * @global WP_Query $wp_query
		 * @param  array $args Mixed args.
		 * @return boolean
		 */
		public function is_page( $args = array() ) {
			global $wp_query;

			if ( ! $wp_query->is_singular( Charitable::CAMPAIGN_POST_TYPE ) ) {
				return false;
			}

			if ( array_key_exists( 'donate', $wp_query->query_vars ) ) {
				return true;
			}

			/* If 'strict' is set to `true`, this will only return true if this has the /donate/ endpoint. */
			if ( $this->is_strict_check( $args ) ) {
				return false;
			}

			return 'separate_page' != charitable_get_option( 'donation_form_display', 'separate_page' );
		}

		/**
		 * Return the template to display for this endpoint.
		 *
		 * @since  1.5.0
		 *
		 * @param  string $template The default template.
		 * @return string
		 */
		public function get_template( $template ) {
			$campaign_id = charitable_get_current_campaign_id();

			/**
			 * If the campaign doesn't exist or can no longer receive donations,
			 * redirect the user to the campaign page.
			 */
			if ( ! charitable_campaign_can_receive_donations( $campaign_id ) ) {
				wp_safe_redirect( get_permalink( $campaign_id ) );
				exit();
			}

			$donation_id = get_query_var( 'donation_id', false );

			/* If a donation ID is included, make sure it belongs to the current user. */
			if ( $donation_id && ! charitable_user_can_access_donation( $donation_id ) ) {
				wp_safe_redirect( charitable_get_permalink( 'campaign_donation', array(
					'campaign_id' => $campaign_id,
				) ) );
				exit();
			}

			do_action( 'charitable_is_donate_page' );

			return array( 'campaign-donation-page.php', 'page.php', 'index.php' );
		}

		/**
		 * Get the content to display for the endpoint.
		 *
		 * @since  1.5.0
		 *
		 * @param  string $content
		 * @return string
		 */
		public function get_content( $content ) {
			if ( ! charitable_is_main_loop() ) {
				return $content;
			}

			if ( 'separate_page' != charitable_get_option( 'donation_form_display', 'separate_page' )
				&& false === get_query_var( 'donate', false ) ) {
				return $content;
			}

			ob_start();

			charitable_template( 'content-donation-form.php', array() );

			return ob_get_clean();
		}

		/**
		 * Returns whether the current request is for a campaign that can receive donations.
		 *
		 * @since  1.5.4
		 *
		 * @return boolean
		 */
		protected function is_donation_ready_campaign() {
			return charitable_campaign_can_receive_donations( charitable_get_current_campaign_id() );
		}

		/**
		 * Returns whether the passed donation is valid for the current user.
		 *
		 * @since  1.5.4
		 *
		 * @param  int $donation_id The donation ID in query vars.
		 * @return boolean
		 */
		protected function is_invalid_donation( $donation_id ) {
			return ! charitable_user_can_access_donation( $donation_id );
		}

		/**
		 * Returns whether this is a strict check.
		 *
		 * @since  1.5.4
		 *
		 * @param  array $args Mixed args.
		 * @return boolean
		 */
		protected function is_strict_check( $args ) {
			return array_key_exists( 'strict', $args ) && $args['strict'];
		}
	}

endif;
