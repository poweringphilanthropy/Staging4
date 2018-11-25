<?php
/**
 * Functions to improve compatibility with Yoast SEO.
 *
 * @package   Charitable/Functions/Compatibility
 * @author    Eric Daams
 * @copyright Copyright (c) 2018, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.5.4
 * @version   1.5.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Yoast attempts to executes shortcodes from the admin, so we
 * need to make sure these will work properly.
 *
 * @since  1.5.4
 *
 * @return void
 */
function charitable_wpseo_compat_load_template_files() {
	global $pagenow;

	if ( in_array( $pagenow, array( 'post.php', 'edit.php', 'post-new.php' ) ) ) {
		require_once( charitable()->get_path( 'includes' ) . 'public/charitable-template-functions.php' );
		require_once( charitable()->get_path( 'includes' ) . 'public/charitable-template-hooks.php' );
	}
}

add_action( 'admin_init', 'charitable_wpseo_compat_load_template_files' );
