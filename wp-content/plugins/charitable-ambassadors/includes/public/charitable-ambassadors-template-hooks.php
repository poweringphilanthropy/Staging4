<?php 
/**
 * Charitable Ambassadors Template Hooks. 
 *
 * Action/filter hooks used for Charitable functions/templates
 * 
 * @package     Charitable Ambassadors/Functions/Templates
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Added before a single campaign's content area.
 *
 * @see charitable_ambassadors_template_edit_campaign_link
 */
add_action( 'charitable_campaign_content_before', 'charitable_ambassadors_template_edit_campaign_link', 2 );