<?php
/**
 * Plugin Name:       Charitable - Ambassadors
 * Plugin URI:        https://wpcharitable.com/extensions/charitable-ambassadors/
 * Description:       Transform your website into a platform for peer to peer fundraising or crowdfunding.
 * Version:           1.1.18
 * Author:            WP Charitable
 * Author URI:        https://wpcharitable.com
 * Requires at least: 4.1
 * Tested up to:      4.9.1
 *
 * Text Domain:       charitable-ambassadors
 * Domain Path:       /languages/
 *
 * @package  Charitable EDD
 * @category Core
 * @author   Studio164a
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } //end if

/**
 * Load plugin class, but only if Charitable and Easy Digital Downloads are found and activated.
 *
 * @return 	void
 * @since 	1.0.0
 */
function charitable_ambassadors_load() {
	require_once( 'includes/class-charitable-ambassadors.php' );

	$has_dependencies = true;

	/* Check for Charitable */
	if ( ! class_exists( 'Charitable' ) ) {

		if ( ! class_exists( 'Charitable_Extension_Activation' ) ) {

			require_once 'includes/class-charitable-extension-activation.php';

		}

		$activation = new Charitable_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

		$has_dependencies = false;
	}

	/* Finally, if both Charitable and Easy Digital Downloads are installed, start the plugin */
	if ( $has_dependencies ) {

		new Charitable_Ambassadors( __FILE__ );

	}
}

add_action( 'plugins_loaded', 'charitable_ambassadors_load', 1 );

/**
 * Activate plugin.
 *
 * @return 	void
 * @since 	1.0.0
 */
function charitable_ambassadors_activate() {
	require_once( 'includes/class-charitable-ambassadors-roles.php' );

	Charitable_Ambassadors_Roles::add_roles();

	set_transient( 'charitable_ambassadors_install', true, 0 );
}

register_activation_hook( __FILE__, 'charitable_ambassadors_activate' );
add_action( 'charitable_install', 'charitable_ambassadors_activate' );

/**
 * Deactivate plugin.
 *
 * @return 	void
 * @since 	1.0.0
 */
function charitable_ambassadors_deactivate() {
	require_once( 'includes/class-charitable-ambassadors-roles.php' );

	Charitable_Ambassadors_Roles::remove_caps();
}

register_deactivation_hook( __FILE__, 'charitable_ambassadors_deactivate' );
