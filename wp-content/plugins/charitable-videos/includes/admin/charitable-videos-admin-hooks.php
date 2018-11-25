<?php
/**
 * Charitable Videos admin hooks.
 *
 * @package     Charitable Videos/Functions/Admin
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Register custom meta box.
 *
 * @see     Charitable_Videos_Admin::register_campaign_video_meta_box()
 */
add_filter( 'charitable_campaign_meta_boxes', array( Charitable_Videos_Admin::get_instance(), 'register_campaign_video_meta_box' ) );

/**
 * Set the admin view path for the video meta box.
 *
 * @see     Charitable_Videos_Admin::admin_view_path()
 */
add_filter( 'charitable_admin_view_path', array( Charitable_Videos_Admin::get_instance(), 'admin_view_path' ), 10, 3 );

/**
 * Save the campaign video.
 *
 * @see     Charitable_Videos_Admin::save_campaign_video()
 */
add_filter( 'charitable_campaign_meta_keys', array( Charitable_Videos_Admin::get_instance(), 'save_campaign_video' ) );

/**
 * Register scripts.
 *
 * @see     Charitable_Videos_Admin::register_scripts()
 */
add_action( 'admin_enqueue_scripts', array( Charitable_Videos_Admin::get_instance(), 'register_scripts' ) );
