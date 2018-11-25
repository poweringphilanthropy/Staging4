<?php
/**
 * Charitable EDD Install class.
 *
 * The responsibility of this class is to manage the events that need to happen
 * when the plugin is activated.
 *
 * @package   Charitable EDD/Classes/Charitable_EDD_Install
 * @copyright Copyright (c) 2017, Eric Daams
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since    1.0.0
 * @version   1.1.3
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

if ( ! class_exists( 'Charitable_EDD_Install' ) ) :

	/**
	 * Charitable_EDD_Install
	 *
	 * @since 1.0.0
	 */
	class Charitable_EDD_Install {

		/**
		 * Activate the plugin.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public static function activate() {
			self::create_tables();
			self::setup_upgrade_log();
		}

		/**
		 * Create database tables.
		 *
		 * @since  1.0.0
		 *
		 * @return void
		 */
		public static function create_tables() {
			if ( ! class_exists( 'Charitable_DB' ) ) {
				$paths = array(
					charitable()->get_path( 'includes' ) . 'db/abstract-class-charitable-db.php',
					charitable()->get_path( 'includes' ) . 'abstracts/abstract-class-charitable-db.php',
				);

				foreach ( $paths as $path ) {
					if ( is_readable( $path ) ) {
						require_once( $path );
						break;
					}
				}
			}

			require_once( 'db/class-charitable-edd-benefactors-db.php' );
			$table = new Charitable_EDD_Benefactors_DB();
			$table->create_table();

			do_action( 'charitable_activate_addon', 'charitable-benefactors' );
		}

		/**
		 * Set up the upgrade log.
		 *
		 * @since  1.0.5
		 *
		 * @return void
		 */
		public static function setup_upgrade_log() {
			require_once( 'admin/upgrades/class-charitable-edd-upgrade.php' );
			Charitable_EDD_Upgrade::get_instance()->populate_upgrade_log_on_install();
		}
	}

endif;
