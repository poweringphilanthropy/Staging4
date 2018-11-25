<?php
/**
 * This file contains the class in charge of handling version upgrades.
 *
 * @class       Reach_Upgrade
 * @version     1.0.0
 * @package     Reach
 * @subpackage  Upgrade
 * @category    Class
 * @author      Studio164a
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) { exit; }

if ( ! class_exists( 'Reach_Upgrade' ) ) :

	/**
	 * Reach_Upgrade
	 *
	 * @since       1.0.0
	 */
	class Reach_Upgrade {

		/**
		 * Current database version. 
		 *
		 * @var     false|string
		 * @access  private
		 */
		private $db_version;

		/**
		 * Edge version.
		 *
		 * @var     string
		 * @access  private
		 */
		private $edge_version;

		/**
		 * Array of methods to perform when upgrading to specific versions.
		 *
		 * @var     array
		 * @access  private
		 */
		private $upgrade_actions = array(
			'0.9.34' => 'update_page_templates',
			'0.9.35' => 'set_thumbnail_size',
			'1.0.3'  => 'add_admin_notice',
		);

		/**
		 * Option key for upgrade log.
		 *
		 * @var     string
		 * @access  private
		 */
		private $upgrade_log_key = 'reach_upgrade_log';

		/**
		 * Option key for plugin version.
		 *
		 * @var     string
		 * @access  private
		 */
		private $version_key = 'reach_version';

		/**
		 * Upgrade from the current version stored in the database to the live version.
		 *
		 * @param   false|string $db_version
		 * @param   string       $edge_version
		 * @return  void
		 * @static
		 * @access  public
		 * @since   1.0.0
		 */
		public static function upgrade_from( $db_version, $edge_version ) {
			if ( self::requires_upgrade( $db_version, $edge_version ) ) {
				new Reach_Upgrade( $db_version, $edge_version );
			}
		}

		/**
		 * Manages the upgrade process.
		 *
		 * @param   false|string $db_version
		 * @param   string       $edge_version
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function __construct( $db_version, $edge_version ) {
			$this->db_version = $db_version;
			$this->edge_version = $edge_version;

			/**
			 * Perform version upgrades.
			 */
			$this->do_upgrades();

			/**
			 * Log the upgrade and update the database version.
			 */
			$this->save_upgrade_log();
			$this->update_db_version();
		}

		/**
		 * Perform version upgrades.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function do_upgrades() {
			if ( empty( $this->upgrade_actions ) || ! is_array( $this->upgrade_actions ) ) {
				return;
			}

			foreach ( $this->upgrade_actions as $version => $method ) {
				if ( self::requires_upgrade( $this->db_version, $version ) ) {
					call_user_func( array( $this, $method ) );
				}
			}
		}

		/**
		 * Evaluates two version numbers and determines whether an upgrade is
		 * required for version A to get to version B.
		 *
		 * @param   false|string $version_a
		 * @param   string       $version_b
		 * @return  bool
		 * @static
		 * @access  public
		 * @since   1.0.0
		 */
		public static function requires_upgrade( $version_a, $version_b ) {
			return false === $version_a || version_compare( $version_a, $version_b, '<' );
		}

		/**
		 * Saves a log of the version to version upgrades made.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function save_upgrade_log() {
			$log = get_option( $this->upgrade_log_key );

			if ( false === $log || ! is_array( $log ) ) {
				$log = array();
			}

			$log[] = array(
				'timestamp' => time(),
				'from'      => $this->db_version,
				'to'        => $this->edge_version,
			);

			update_option( $this->upgrade_log_key, $log );
		}

		/**
		 * Upgrade complete. This saves the new version to the database.
		 *
		 * @return  void
		 * @access  private
		 * @since   1.0.0
		 */
		private function update_db_version() {
			update_option( $this->version_key, $this->edge_version );
		}

		/**
		 * Update page templates automatically for any pages that previously used the old template.
		 *
		 * @global  WPDB $wpdb
		 * @return  void
		 * @access  public
		 * @since   0.9.34
		 */
		public function update_page_templates() {
			global $wpdb;

			$templates = array(
				'page-template-homepage.php'        => 'page-templates/homepage.php',
				'page-template-home-slider.php'     => 'page-templates/homepage.php',
				'page-template-stripped.php'        => 'page-templates/stripped.php',
				'page-template-stripped-narrow.php' => 'page-templates/stripped.php',
				'page-template-fullwidth.php'       => 'page-templates/fullwidth.php',
				'page-template-user-dashboard.php'  => 'page-templates/user-dashboard.php',
			);

			foreach ( $templates as $old => $new ) {

				$wpdb->update( $wpdb->postmeta,
					array( 'meta_value' => $new ),
					array(
						'meta_key' => '_wp_page_template',
						'meta_value' => $old,
					),
					array( '%s' ),
					array( '%s' )
				);

			}
		}

		/**
		 * Set the thumbnail size to 100px, unless it has already been set to something other than the default.
		 *
		 * @return  void
		 * @access  public
		 * @since   0.9.35
		 */
		public function set_thumbnail_size() {
			$width  = get_option( 'thumbnail_size_w' );
			$height = get_option( 'thumbnail_size_h' );

			if ( 150 != $width || 150 != $height ) {
				return;
			}

			update_option( 'thumbnail_size_w', 100 );
			update_option( 'thumbnail_size_h', 100 );
		}

		/**
		 * Add an admin notice for users upgrading to 1.0.3.
		 *
		 * @return  void
		 * @access  public
		 * @since   1.0.3
		 */
		public function add_admin_notice() {
			if ( false == $this->db_version ) {
				return;
			}
			
			set_transient( 'reach_show_custom_logo_notice', true );
		}
	}

endif; // End class_exists check
