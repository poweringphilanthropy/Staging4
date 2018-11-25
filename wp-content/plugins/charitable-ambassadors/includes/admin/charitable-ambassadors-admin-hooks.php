<?php
/**
 * Charitable Ambassadors Admin Hooks.
 *
 * @package     Charitable Ambassadors/Functions/Admin
 * @version     1.1.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Register custom meta boxes.
 *
 * @see     Charitable_Ambassadors_Admin::register_meta_boxes()
 */
add_filter( 'charitable_campaign_meta_boxes', array( Charitable_Ambassadors_Admin::get_instance(), 'register_meta_boxes' ) );

/**
 * Set the admin view path for Ambassadors views.
 *
 * @see     Charitable_Ambassadors_Admin::admin_view_path()
 */
add_filter( 'charitable_admin_view_path', array( Charitable_Ambassadors_Admin::get_instance(), 'admin_view_path' ), 10, 3 );

/**
 * Add plugin action links.
 *
 * @see     Charitable_Ambassadors_Admin::add_plugin_action_links()
 */
add_filter( 'plugin_action_links_' . plugin_basename( charitable_ambassadors()->get_path() ), array( Charitable_Ambassadors_Admin::get_instance(), 'add_plugin_action_links' ) );

