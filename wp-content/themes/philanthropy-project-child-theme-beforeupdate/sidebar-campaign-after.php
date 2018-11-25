<?php
/**
 * The sidebar containing the campaigns widget area.
 *
 * @package Reach
 */

$sidebar = PHILANTHROPY_PROJECT_CAMPAIGN_ID == get_the_ID() ? 'tpp_campaign_after_content' : 'campaign_after_content';

if ( ! is_active_sidebar( $sidebar ) ) {
    return;
}

dynamic_sidebar( $sidebar );
