<?php
/**
 * Charitable Donor Comments campaign hooks.
 *
 * @package     Charitable Donor Comments/Functions/Campaign
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Exclude donor comments from the comments template if the user has switched off this option.
 *
 * @see     Charitable_Donor_Comments_Campaign::maybe_exclude_donor_comments_from_comments_template
 */
add_filter( 'comments_template_query_args', array( Charitable_Donor_Comments_Campaign::get_instance(), 'maybe_exclude_donor_comments_from_comments_template' ) );
