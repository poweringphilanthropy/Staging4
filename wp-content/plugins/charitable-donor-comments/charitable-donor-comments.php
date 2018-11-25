<?php
/**
 * Plugin Name:       Charitable - Donor Comments
 * Plugin URI:        https://www.wpcharitable.com/extensions/charitable-donor-comments/
 * Description:       Make giving personal. Give your donors the ability to share a message when they donate.
 * Version:           1.0.0
 * Author:            WP Charitable
 * Author URI:        https://www.wpcharitable.com
 * Requires at least: 4.2
 * Tested up to:      4.9.1
 *
 * Text Domain: 		charitable-donor-comments
 * Domain Path: 		/languages/
 *
 * @package 			Charitable Donor Comments
 * @category 			Core
 * @author 				WP Charitable
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Load plugin class, but only if Charitable is found and activated.
 *
 * @return 	false|Charitable_Donor_Comments Whether the class was loaded.
 */
function charitable_donor_comments_load() {
	require_once( 'includes/class-charitable-donor-comments.php' );

	$loaded = false;

	/* Check for Charitable */
	if ( ! class_exists( 'Charitable' ) ) {

		if ( ! class_exists( 'Charitable_Extension_Activation' ) ) {

			require_once 'includes/admin/class-charitable-extension-activation.php';

		}

		$activation = new Charitable_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

	} else {

		$loaded = new Charitable_Donor_Comments( __FILE__ );

	}

	return $loaded;
}

add_action( 'plugins_loaded', 'charitable_donor_comments_load', 1 );
