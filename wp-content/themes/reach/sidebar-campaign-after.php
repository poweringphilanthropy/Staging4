<?php
/**
 * The sidebar containing the campaigns widget area.
 *
 * This template is only used if Charitable is active.
 *
 * @package Reach
 */

if ( ! is_active_sidebar( 'campaign_after_content' ) ) {
	return;
}

dynamic_sidebar( 'campaign_after_content' );
