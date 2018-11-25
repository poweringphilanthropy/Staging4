<?php
/**
 * Display a widget with a link to donate to a campaign.
 *
 * Override this template by copying it to yourtheme/charitable/widgets/donate.php
 *
 * @package Charitable/Templates/Widgets
 * @author  Studio 164a
 * @since   1.0.0
 * @version 1.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! charitable_is_campaign_page() && 'current' == $view_args['campaign_id'] ) :
	return;
endif;

$widget_title = apply_filters( 'widget_title', $view_args['title'] );
$campaign_id  = 'current' == $view_args['campaign_id'] ? get_the_ID() : $view_args['campaign_id'];
$campaign     = charitable_get_campaign( $campaign_id );

if ( ! $campaign || ! $campaign->can_receive_donations() ) :
	return;
endif;

$suggested_donations = $campaign->get_suggested_donations();

if ( empty( $suggested_donations ) && ! $campaign->get( 'allow_custom_donations' ) ) :
	return;
endif;

echo $view_args['before_widget'];

if ( ! empty( $widget_title ) ) :
	echo $view_args['before_title'] . $widget_title . $view_args['after_title'];
endif;

$form = new Charitable_Donation_Amount_Form( $campaign );
$form->render();

echo $view_args['after_widget'];
