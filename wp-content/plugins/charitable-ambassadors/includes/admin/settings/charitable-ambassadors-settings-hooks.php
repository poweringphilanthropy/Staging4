<?php 
/**
 * Charitable Ambassadors Settings Hooks. 
 * 
 * @package     Charitable Ambassadors/Functions/Settomgs
 * @version     1.1.0
 * @author      Eric Daams
 * @copyright   Copyright (c) 2017, Studio 164a
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License  
 */

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

add_filter( 'charitable_settings_tabs', array( Charitable_Ambassadors_Settings::get_instance(), 'add_ambassadors_section' ) );
add_filter( 'charitable_ambassadors_admin_settings_groups', array( Charitable_Ambassadors_Settings::get_instance(), 'add_ambassadors_settings_group' ) );
add_filter( 'charitable_settings_tab_fields_ambassadors', array( Charitable_Ambassadors_Settings::get_instance(), 'add_ambassadors_settings_fields' ) );
add_filter( 'charitable_settings_tab_fields_general', array( Charitable_Ambassadors_Settings::get_instance(), 'add_campaign_submission_page_settings' ) );        
