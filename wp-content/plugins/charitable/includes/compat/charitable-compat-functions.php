<?php
/**
 * Functions to improve compatibility.
 *
 * @package   Charitable/Functions/Compatibility
 * @author    Eric Daams
 * @copyright Copyright (c) 2018, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.5.0
 * @version   1.5.0
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Load plugin compatibility files on plugins_loaded hook.
 *
 * @since  1.5.0
 *
 * @return void
 */
function charitable_load_compat_functions() {
	$includes_path = charitable()->get_path( 'includes' );

	/* WP Super Cache */
	if ( function_exists( 'wp_super_cache_text_domain' ) ) {
		require_once( $includes_path . 'compat/charitable-wp-super-cache-compat-functions.php' );
	}

	/* W3TC */
	if ( defined( 'W3TC' ) && W3TC ) {
		require_once( $includes_path . 'compat/charitable-w3tc-compat-functions.php' );
	}

	/* WP Rocket */
	if ( defined( 'WP_ROCKET_VERSION' ) ) {
		require_once( $includes_path . 'compat/charitable-wp-rocket-compat-functions.php' );
	}

	/* WP Fastest Cache */
	if ( class_exists( 'WpFastestCache' ) ) {
		require_once( $includes_path . 'compat/charitable-wp-fastest-cache-compat-functions.php' );
	}

	/* Yoast SEO */
	if ( defined( 'WPSEO_VERSION' ) ) {
		require_once( $includes_path . 'compat/charitable-wpseo-compat-functions.php' );
	}
}
