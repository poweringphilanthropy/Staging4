<?php 
/**
 * Charitable EDD Upgrade Hooks. 
 *
 * Action/filter hooks used for Charitable EDD Upgrades. 
 * 
 * @package     Charitable EDD/Functions/Upgrades
 * @version     1.3.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

/**
 * Check if there is an upgrade that needs to happen and if so, display a notice to begin upgrading.
 *
 * @see     Charitable_EDD_Upgrade::add_upgrade_notice()
 */
add_action( 'admin_notices', array( Charitable_EDD_Upgrade::get_instance(), 'add_upgrade_notice' ) );

/**
 * Run the upgrade for 1.0.4.
 *
 * @see     Charitable_EDD_Upgrade::deactivate_benefactors_for_expired_campaigns()
 */
add_action( 'charitable_deactivate_benefactors_for_expired_campaigns', array( Charitable_EDD_Upgrade::get_instance(), 'deactivate_benefactors_for_expired_campaigns' ) );
