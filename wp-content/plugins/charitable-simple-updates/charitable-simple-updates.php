<?php
/**
 * Plugin Name: 		Charitable - Simple Updates
 * Plugin URI: 			http://164a.com
 * Description: 		Adds an Updates section to your campaigns. Integrated with Charitable Ambassadors to allow your campaign creators to post updates about their campaign.
 * Version: 			1.1.1
 * Author: 				Studio 164a
 * Author URI: 			http://164a.com
 * Requires at least: 	4.1
 * Tested up to: 		4.2
 *
 * Text Domain: 		charitable-simple-updates
 * Domain Path: 		/languages/
 *
 * @package 			Charitable Simple Updates
 * @category 			Core
 * @author 				Studio164a
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Load plugin class, but only if Charitable and Easy Digital Downloads are found and activated.
 *
 * @return 	void
 * @since 	1.0.0
 */
function charitable_simple_updates_load() {	
	require_once( 'includes/class-charitable-simple-updates.php' );

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
	else {

		new Charitable_Simple_Updates( __FILE__ );

	}	
}

add_action( 'plugins_loaded', 'charitable_simple_updates_load', 1 );