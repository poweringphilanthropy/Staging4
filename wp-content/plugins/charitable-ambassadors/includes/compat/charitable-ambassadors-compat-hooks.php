<?php
/**
 * Charitable Ambassadors Compatibility Hooks.
 *
 * @package   Charitable Ambassadors/Functions/Compatibility
 * @author    Eric Daams
 * @copyright Copyright (c) 2017, Studio 164a
 * @license   http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     1.1.2
 * @version   1.1.2
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Load public class in admin.
 *
 * This is required to avoid problems some plugins, including
 * Yoast SEO and Relevanssi.
 *
 * @see charitable_ambassadors_compat_load_public_in_admin()
 */
add_action( 'admin_init', 'charitable_ambassadors_compat_load_public_in_admin' );
