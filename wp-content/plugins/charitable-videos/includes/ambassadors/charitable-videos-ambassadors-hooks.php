<?php
/**
 * Charitable Videos Ambassadors Hooks.
 *
 * @package     Charitable Videos/Functions/Templates
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Add a video field to the campaign submission form.
 *
 * @see     Charitable_Videos_Ambassadors::add_video_field()
 */
add_filter( 'charitable_campaign_submission_campaign_fields', array( Charitable_Videos_Ambassadors::get_instance(), 'add_video_field' ), 10, 2 );
