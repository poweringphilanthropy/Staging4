<?php
/**
 * Charitable EDD Upgrade class.
 *
 * The responsibility of this class is to manage migrations between versions of Charitable EDD.
 *
 * @package		Charitable EDD
 * @subpackage	Charitable EDD/Upgrade
 * @copyright 	Copyright (c) 2017, Eric Daams
 * @license     http://opensource.org/licenses/gpl-1.0.0.php GNU Public License
 * @since 		1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_EDD_Upgrade' ) ) :

	/**
	 * Charitable_EDD_Upgrade
	 *
	 * @see 		Charitable_Upgrade
	 * @since 		1.0.0
	 */
	class Charitable_EDD_Upgrade extends Charitable_Upgrade {

	    /**
	     * @var     Charitable_EDD_Upgrade
	     * @access  private
	     * @static
	     * @since   1.0.4
	     */
	    private static $instance = null;

		/**
		 * Array of methods to perform when upgrading to specific versions.
		 *
		 * @var 	array
		 * @access 	protected
		 */
		protected $upgrade_actions;

		/**
		 * Option key for upgrade log.
		 *
		 * @var 	string
		 * @access 	protected
		 */
		protected $upgrade_log_key = 'charitable_edd_upgrade_log';

		/**
		 * Option key for plugin version.
		 *
		 * @var 	string
		 * @access 	protected
		 */
		protected $version_key = 'charitable_edd_version';

	    /**
	     * Create and return the class object.
	     *
	     * @access  public
	     * @static
	     * @since   1.0.4
	     */
	    public static function get_instance() {
	        if ( is_null( self::$instance ) ) {
	            self::$instance = new Charitable_EDD_Upgrade();
	        }

	        return self::$instance;
	    }

		/**
		 * Manages the upgrade process.
		 *
		 * @param 	false|string 	$db_version
		 * @param 	string 			$edge_version
		 * @access 	protected
		 * @since 	1.0.0
		 */
		protected function __construct( $db_version = '', $edge_version = '' ) {

			$this->upgrade_actions = array(
				'deactivate_benefactors_for_expired_campaigns' => array(
					'version' => '1.0.4',
					'message' => __( 'Charitable needs to deactivate campaign benefactor relationships for inactive campaigns.', 'charitable-edd' ),
					'prompt' => true,
				),
				'show_release_110_upgrade_notice' => array(
					'version' => '1.1.0',
					'notice' => 'edd-release-110',
				),
			);

		}

		/**
		 * Populate the upgrade log when first installing the plugin.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.5
		 */
		public function populate_upgrade_log_on_install() {
			/**
			 * If the log already exists, don't change it.
			 */
			if ( get_option( $this->upgrade_log_key ) ) {
				return;
			}

			$log = array(
				'install' => array(
					'version' => Charitable_EDD::VERSION,
					'message' => __( 'Charitable Easy Digital Downloads Connect was installed.', 'charitable-edd' ),
				),
			);

			foreach ( $this->upgrade_actions as $key => $notes ) {
				$notes['install'] = true;
				$log[ $key ] = $notes;
			}

			add_option( $this->upgrade_log_key, $log );
		}

		/**
		 * Fix expired benefactors.
		 *
		 * @global 	WPDB $wpdb
		 * @return  void
		 * @access  public
		 * @since   1.0.4
		 */
		public function deactivate_benefactors_for_expired_campaigns() {
			global $wpdb;

			if ( ! current_user_can( 'manage_charitable_settings' ) ) {
				wp_die( __( 'You do not have permission to do Charitable upgrades', 'charitable-edd' ), __( 'Error', 'charitable-edd' ), array( 'response' => 403 ) );
			}

			ignore_user_abort( true );

			if ( ! charitable_is_func_disabled( 'set_time_limit' ) && ! ini_get( 'safe_mode' ) ) {
				@set_time_limit( 0 );
			}

			$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
			$number = 20;
			$offset = ($step - 1) * $number;
			$total  = count( $wpdb->get_results( "
				SELECT COUNT(*) 
				FROM {$wpdb->prefix}charitable_benefactors cb
				INNER JOIN {$wpdb->prefix}charitable_campaign_donations cd 
				ON cd.campaign_id = cb.campaign_id
				LEFT JOIN $wpdb->posts p
				ON p.ID = cb.campaign_id
				INNER JOIN $wpdb->posts p2
				ON p2.ID = cd.donation_id
				INNER JOIN $wpdb->postmeta pm
				ON pm.post_id = cd.donation_id
				WHERE cb.date_deactivated > NOW()
				AND (
					p.ID IS NULL
					OR p.post_status <> 'publish'
				)
				AND pm.meta_key = 'donation_from_edd_payment_log'
				GROUP BY cd.donation_id;",
				$offset
			) );

			error_log( $total );

			/* No results, so proceed no further. */
			if ( empty( $total ) ) {
				$this->finish_upgrade( 'deactivate_benefactors_for_expired_campaigns' );
			}

			$results = $wpdb->get_results( $wpdb->prepare( "
				SELECT cb.campaign_benefactor_id,
					cb.campaign_id,
					p.post_modified AS download_modification_date,
					cd.donation_id,
					p2.post_date AS donation_date,
					pm.meta_value AS log
				FROM {$wpdb->prefix}charitable_benefactors cb
				INNER JOIN {$wpdb->prefix}charitable_campaign_donations cd 
				ON cd.campaign_id = cb.campaign_id
				LEFT JOIN $wpdb->posts p
				ON p.ID = cb.campaign_id
				INNER JOIN $wpdb->posts p2
				ON p2.ID = cd.donation_id
				INNER JOIN $wpdb->postmeta pm
				ON pm.post_id = cd.donation_id
				WHERE cb.date_deactivated > NOW()
				AND (
					p.ID IS NULL
					OR p.post_status <> 'publish'
				)
				AND pm.meta_key = 'donation_from_edd_payment_log'
				GROUP BY cd.donation_id
				LIMIT %d
				OFFSET %d;",
				$number, $offset
			) );

			error_log( serialize( $results ) );

			foreach ( $results as $record ) {

				if ( is_null( $record->download_modification_date ) ) {

					/**
					 * Delete any campaign donations made since the
					 * campaign has been trashed.
					 */
					$wpdb->delete( $wpdb->prefix . 'charitable_campaign_donations',
						array( 'campaign_id' => $record->campaign_id, 'campaign_name' => '' ),
						array( '%d', '%s' )
					);

				} elseif ( $record->download_modification_date < $record->donation_date ) {

					$log = maybe_unserialize( $record->log );

					foreach ( $log as $campaign_donation_log ) {

						/* Not a donation coming from a benefactor. */
						if ( ! array_key_exists( 'benefactor_id', $campaign_donation_log ) ) {
							continue;
						}

						/* Not a donation coming from THIS benefactor. */
						if ( $record->campaign_benefactor_id != $campaign_donation_log['benefactor_id'] ) {
							continue;
						}

						/* This donation came from this benefactor, so delete it. */
						$wpdb->delete( $wpdb->prefix . 'charitable_campaign_donations',
							array(
								'campaign_id' => $record->campaign_id,
								'amount' => $campaign_donation_log['amount'],
								'donation_id' => $record->donation_id,
							),
							array( '%d', '%s', '%d' )
						);

					}
				}
			}

			/* Finally, get any donations that still exist in the campaign_donations table. */
			$donations    = wp_list_pluck( $results, 'donation_id' );
			$placeholders = implode( ',', array_fill( 0, count( $donations ), '%s' ) );
			$exists       = $wpdb->get_col( $wpdb->prepare( "
				SELECT donation_id
				FROM {$wpdb->prefix}charitable_campaign_donations
				WHERE donation_id IN ( $placeholders )
				GROUP BY donation_id",
				$donations
			) );

			/* Fully delete any donations that no longer exist. */
			foreach ( $donations as $donation_id ) {

				if ( in_array( $donation_id, $exists ) ) {
					continue;
				}

				wp_delete_post( $donation_id );

			}

			/* We're done. */
			if ( count( $results ) >= $total ) {
				$this->finish_upgrade( 'deactivate_benefactors_for_expired_campaigns' );
			}

			$step++;

			$redirect = add_query_arg( array(
				'page' => 'charitable-upgrades',
				'charitable-upgrade' => 'deactivate_benefactors_for_expired_campaigns',
				'step' => $step,
				'number' => $number,
				'total' => $total,
			), admin_url( 'index.php' ) );

			wp_redirect( $redirect );

			exit;

		}
	}

endif; // End class_exists check
