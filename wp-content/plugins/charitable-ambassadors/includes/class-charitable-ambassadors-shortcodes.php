<?php
/**
 * Class responsible for registering the shortcodes that are part of Charitable Ambassadors.
 *
 * @package     Charitable Ambassadors/Classes/Charitable_Ambassadors_Shortcodes
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Charitable_Ambassadors_Shortcodes' ) ) :

	/**
	 * Charitable_Ambassadors_Shortcodes
	 *
	 * @since 	1.0.0
	 */
	class Charitable_Ambassadors_Shortcodes {

		/**
		 * Instantiate the class, but only during the start phase.
		 *
		 * @param   Charitable_Ambassadors $charitable_ambassadors
		 * @return  void
		 * @static
		 * @access  public
		 * @since   1.0.0
		 */
		public static function start( Charitable_Ambassadors $charitable_ambassadors ) {
			if ( ! $charitable_ambassadors->is_start() ) {
				return;
			}

			$charitable_ambassadors->register_object( new Charitable_Ambassadors_Shortcodes( $charitable_ambassadors ) );
		}

		/**
		 * Set up the class.
		 *
		 * Note that the only way to instantiate an object is with the charitable_start method,
		 * which can only be called during the start phase. In other words, don't try
		 * to instantiate this object.
		 *
		 * @access  private
		 * @since   1.0.0
		 */
		private function __construct() {
			$this->register_shortcodes();
			$this->attach_hooks_and_filters();

			/* You can use this hook to obtain the class instance and remove any of the callbacks created .*/
			do_action( 'charitable_ambassadors_shortcodes_start', $this );
		}

		/**
		 * Set up hooks and filters.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function register_shortcodes() {
			add_shortcode( 'charitable_submit_campaign', array( $this, 'charitable_submit_campaign_shortcode' ) );
			add_shortcode( 'charitable_my_campaigns', array( $this, 'charitable_my_campaigns_shortcode' ) );
			add_shortcode( 'charitable_creator_donations', array( $this, 'charitable_creator_donations_shortcode' ) );
		}

		/**
		 * Sets up callback methods for certain hooks. This is responsible for hooking
		 * additional template files to hooks inside the main shortcode templates.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function attach_hooks_and_filters() {
			add_action( 'charitable_user_campaign_summary_before', array( $this, 'render_campaign_thumbnail' ), 10, 2 );
			add_action( 'charitable_user_campaign_summary', array( $this, 'render_campaign_summary' ), 10, 2 );
			add_action( 'charitable_user_campaign_summary_after', array( $this, 'render_campaign_actions' ), 10, 2 );
		}

		/**
		 * The callback method for the charitable_submit_campaign shortcode.
		 *
		 * This receives the user-defined attributes and passes the logic off to the class.
		 *
		 * @param   array $atts User-defined shortcode attributes.
		 * @return  string
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public function charitable_submit_campaign_shortcode( $atts ) {
			wp_enqueue_script( 'charitable-ambassadors-campaign-submission' );

			$args = shortcode_atts( array(), $atts );

			ob_start();

			if ( $this->user_needs_to_log_in_for_campaign_form() ) {
				echo Charitable_Login_Shortcode::display( array(
					'redirect' => charitable_get_current_url(),
				) );
				return ob_get_clean();
			}

			$args = apply_filters( 'charitable_campaign_submission_form_args', $args );

			/* Allows plugins to hide the campaign submission form if need be. */
			if ( isset( $args['hidden'] ) && $args['hidden'] ) {
				do_action( 'charitable_submit_campaign_shortcode_hidden', $args );
				return ob_get_clean();
			}

			charitable_ambassadors_template( 'shortcodes/submit-campaign.php', array(
				'form' => new Charitable_Ambassadors_Campaign_Form( $args ),
			) );

			return apply_filters( 'charitable_submit_campaign_shortcode', ob_get_clean() );
		}

		/**
		 * The callback method for the charitable_my_campaigns shortcode.
		 *
		 * This receives the user-defined attributes and passes the logic off to the template.
		 *
		 * @param   array $atts User-defined shortcode attributes.
		 * @return  string
		 * @access  public
		 * @static
		 * @since   1.0.0
		 */
		public function charitable_my_campaigns_shortcode( $atts ) {
			$args = shortcode_atts( array(), $atts, 'charitable_my_campaigns' );

			ob_start();

			/* If the user is logged out, redirect to login/registration page. */
			if ( ! is_user_logged_in() ) {
				echo Charitable_Login_Shortcode::display( array(
					'redirect' => charitable_get_current_url(),
				) );
				return ob_get_clean();
			}			

			charitable_ambassadors_template( 'shortcodes/my-campaigns.php', $args );

			return apply_filters( 'charitable_my_campaigns_shortcode', ob_get_clean() );

		}

		/**
		 * The callback method for the charitable_creator_donations shortcode.
		 *
		 * @param   array $atts User-defined shortcode attributes
		 * @return  string
		 * @access  public
		 * @since   1.1.0
		 */
		public function charitable_creator_donations_shortcode( $atts ) {
			$args = shortcode_atts( array(), $atts, 'charitable_creator_donations' );

			ob_start();

			/* If the user is logged out, redirect to login/registration page. */
			if ( ! is_user_logged_in() ) {
				echo Charitable_Login_Shortcode::display( array(
					'redirect' => charitable_get_current_url(),
				) );
				return ob_get_clean();
			}	

			charitable_ambassadors_template( 'shortcodes/creator-donations.php', $args );

			return apply_filters( 'charitable_creator_donations_shortcode', ob_get_clean() );
		}

		/**
		 * Display the campaign thumbnail template.
		 *
		 * @param   Charitable_Campaign     $campaign
		 * @param   Charitable_User         $user
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function render_campaign_thumbnail( $campaign, $user ) {
			charitable_ambassadors_template( 'shortcodes/my-campaigns/campaign-thumbnail.php', array(
				'campaign'  => $campaign,
				'user'      => $user,
			) );
		}

		/**
		 * Display the campaign summary template.
		 *
		 * @param   Charitable_Campaign     $campaign
		 * @param   Charitable_User         $user
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function render_campaign_summary( $campaign, $user ) {
			charitable_ambassadors_template( 'shortcodes/my-campaigns/campaign-summary.php', array(
				'campaign'  => $campaign,
				'user'      => $user,
			) );
		}

		/**
		 * Display the campaign actions template.
		 *
		 * @param   Charitable_Campaign     $campaign
		 * @param   Charitable_User         $user
		 * @return  void
		 * @access  public
		 * @since   1.0.0
		 */
		public function render_campaign_actions( $campaign, $user ) {
			charitable_ambassadors_template( 'shortcodes/my-campaigns/campaign-actions.php', array(
				'campaign'  => $campaign,
				'user'      => $user,
			) );
		}

		/**
		 * Whether the user needs to log in for the campaign form.
		 *
		 * @since  1.1.17
		 *
		 * @return boolean
		 */
		private function user_needs_to_log_in_for_campaign_form() {
			return ! is_user_logged_in() && charitable_get_option( 'require_user_account_for_campaign_submission', 1 );
		}
	}

endif;
