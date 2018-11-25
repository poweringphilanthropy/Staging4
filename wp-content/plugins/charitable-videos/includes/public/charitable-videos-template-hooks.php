<?php
/**
 * Charitable Videos Template Hooks.
 *
 * Action/filter hooks used for Charitable Videos functions/templates
 *
 * @package     Charitable Videos/Functions/Templates
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Campaigns page, after the campaign title and short description.
 *
 * @see charitable_videos_template_campaign_video
 */
add_action( 'charitable_campaign_content_before', 'charitable_videos_template_campaign_video', 5 );
