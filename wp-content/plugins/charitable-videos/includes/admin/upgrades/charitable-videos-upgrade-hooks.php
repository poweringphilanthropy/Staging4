<?php
/**
 * Charitable Videos Upgrade Hooks.
 *
 * Action/filter hooks used for Charitable Videos Upgrades.
 *
 * @package     Charitable Videos/Functions/Upgrades
 * @version     1.0.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2016, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Check if there is an upgrade that needs to happen and if so, display a notice to begin upgrading.
 *
 * @see     Charitable_Videos_Upgrade::add_upgrade_notice()
 */
add_action( 'admin_notices', array( Charitable_Videos_Upgrade::get_instance(), 'add_upgrade_notice' ) );
