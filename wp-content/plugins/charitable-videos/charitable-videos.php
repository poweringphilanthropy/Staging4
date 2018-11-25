<?php
/**
 * Plugin Name: 		Charitable - Videos
 * Plugin URI:
 * Description:
 * Version: 			1.0.0
 * Author: 				WP Charitable
 * Author URI: 			https://www.wpcharitable.com
 * Requires at least: 	4.2
 * Tested up to: 		4.6.1
 *
 * Text Domain: 		charitable-videos
 * Domain Path: 		/languages/
 *
 * @package 			Charitable Videos
 * @category 			Core
 * @author 				WP Charitable
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Load plugin class, but only if Charitable is found and activated.
 *
 * @return 	void
 * @since 	1.0.0
 */
function charitable_videos_load() {
	require_once( 'includes/class-charitable-videos.php' );

	$has_dependencies = true;

	/* Check for Charitable */
	if ( ! class_exists( 'Charitable' ) ) {

		if ( ! class_exists( 'Charitable_Extension_Activation' ) ) {

			require_once 'includes/admin/class-charitable-extension-activation.php';

		}

		$activation = new Charitable_Extension_Activation( plugin_dir_path( __FILE__ ), basename( __FILE__ ) );
		$activation = $activation->run();

		$has_dependencies = false;

	} else {

		new Charitable_Videos( __FILE__ );

	}
}

add_action( 'plugins_loaded', 'charitable_videos_load', 1 );
