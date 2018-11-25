<?php 
/**
 * Charitable Ambassadors Compatibility Functions. 
 *
 * @version     1.1.2
 * @package     Charitable Ambassadors/Functions/Compatibility
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License 
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Load public class in admin for certain plugins.
 *
 * @since  1.1.17
 *
 * @return boolean
 */
function charitable_ambassadors_compat_load_public_in_admin() {
    if ( charitable_ambassadors_compat_require_public_in_admin() ) {
        Charitable_Ambassadors_Public::get_instance();
        return true;
    }

    return false;
}

/**
 * Returns whether the public class is required in the admin.
 *
 * @since  1.1.17
 *
 * @return boolean
 */
function charitable_ambassadors_compat_require_public_in_admin() {
    return defined( 'WPSEO_VERSION' ) || function_exists( 'relevanssi_install' );
}
