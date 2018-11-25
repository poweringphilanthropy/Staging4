<?php
/**
 * Plugin Name: 		Charitable - Easy Digital Downloads Connect
 * Plugin URI: 			https://www.wpcharitable.com/extensions/charitable-easy-digital-downloads-connect/
 * Description: 		Raise money for your campaigns with your Easy Digital Downloads store.
 * Version: 			1.1.4
 * Author: 				WP Charitable
 * Author URI: 			https://www.wpcharitable.com/
 * Requires at least: 	4.2
 * Tested up to: 		4.9
 *
 * Text Domain: 		charitable-edd
 * Domain Path: 		/languages/
 *
 * @package 			Charitable EDD
 * @category 			Core
 * @author 				Studio164a
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Load plugin class, but only if Charitable and Easy Digital Downloads are found and activated.
 *
 * @return 	void
 * @since 	1.0.0
 */
function charitable_edd_load() {
	require_once( 'includes/class-charitable-edd.php' );

	$has_dependencies = true;

	/* Check for Charitable */
	if ( ! class_exists( 'Charitable' ) ) {

		if ( ! class_exists( 'Charitable_Extension_Activation' ) ) {

			require_once 'includes/admin/class-charitable-extension-activation.php';

		}

		$activation = new Charitable_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

		$has_dependencies = false;
	}

	/* Check for Easy Digital Downloads */
	if ( ! class_exists( 'Easy_Digital_Downloads' ) ) {

		if ( ! class_exists( 'EDD_Extension_Activation' ) ) {

			require_once 'includes/admin/class-edd-extension-activation.php';

		}

		$activation = new EDD_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

		$has_dependencies = false;
	}

	/* Finally, if both Charitable and Easy Digital Downloads are installed, start the plugin */
	if ( $has_dependencies ) {

		new Charitable_EDD( __FILE__ );

	}
}

add_action( 'plugins_loaded', 'charitable_edd_load', 1 );

/**
 * Activate plugin.
 *
 * @return 	void
 * @since 	1.0.0
 */
function charitable_edd_activate() {
	if ( class_exists( 'Charitable' ) ) {

		require_once( 'includes/class-charitable-edd.php' );
		require_once( 'includes/class-charitable-edd-install.php' );

		Charitable_EDD_Install::activate();
	}
}

register_activation_hook( __FILE__, 'charitable_edd_activate' );
add_action( 'charitable_install', 'charitable_edd_activate' );

/**
 * Deactivate plugin.
 *
 * @return 	void
 * @since 	1.0.0
 */
function charitable_edd_deactivate() {
	$charitable_options = get_option( 'charitable_settings', array() );

	$cleanup = isset( $charitable_options['delete_data_on_uninstall'] ) && $charitable_options['delete_data_on_uninstall'];

	if ( empty( $charitable_options ) || $cleanup ) {

		require_once( 'includes/class-charitable-edd-uninstall.php' );

		new Charitable_EDD_Uninstall();
	}
}

register_deactivation_hook( __FILE__, 'charitable_edd_deactivate' );
