<?php
/**
 * Plugin Name: 		Charitable - User Avatar
 * Plugin URI: 			http://wpcharitable.com/extensions/charitable-user-avatar/
 * Description: 		Allow your donors and campaign creators to upload their own avatar.
 * Version: 			1.0.5
 * Author: 				Studio 164a
 * Author URI: 			http://164a.com
 * Requires at least: 	4.1
 * Tested up to: 		4.8.1
 *
 * Text Domain: 		charitable-user-avatar
 * Domain Path: 		/languages/
 *
 * @package 			Charitable User_Avatar
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
function charitable_user_avatar_load() {	
	require_once( 'includes/class-charitable-user-avatar.php' );

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

		new Charitable_User_Avatar( __FILE__ );

	}	
}

add_action( 'plugins_loaded', 'charitable_user_avatar_load', 1 );