<?php
/**
 * PP Dashboard related functions
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function pp_get_account_menu(){
	$menu_items = array(
		'profile' => array(
			'label' => __('Profile', 'pp'),
			'url' => home_url( 'profile' ),
			'icon' => PP()->get_image_url('accounts/icon-profile.png'),
			'priority' => 10,
		),
		'create-campaign' => array(
			'label' => __('Create Campaign', 'pp'),
			'url' => home_url( 'create-campaign' ),
			'icon' => PP()->get_image_url('accounts/icon-create-campaign.png'),
			'priority' => 20,
		),
		'your-campaigns' => array(
			'label' => __('Your Campaigns', 'pp'),
			'url' => home_url( 'your-campaigns' ),
			'icon' => PP()->get_image_url('accounts/icon-your-campaigns.png'),
			'priority' => 30,
		),
		'your-reports' => array(
			'label' => __('Your Reports', 'pp'),
			'url' => home_url( 'your-campaigns' ),
			'icon' => PP()->get_image_url('accounts/icon-your-campaigns.png'),
			'priority' => 31,
		),
	);

	uasort( $menu_items, 'pp_priority_sort' );

	return apply_filters( 'pp_get_account_menu', $menu_items );
}