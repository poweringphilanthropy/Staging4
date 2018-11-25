<?php
/**
 * Display a widget with a link to donate to a campaign.
 *
 * @author  Studio 164a
 * @since   1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! charitable_is_campaign_page() && 'current' == $view_args[ 'campaign_id' ] ) {
    return;
}

$widget_title   = apply_filters( 'widget_title', $view_args['title'] );
$campaign_id    = $view_args[ 'campaign_id' ] == 'current' ? get_the_ID() : $view_args[ 'campaign_id' ];
$campaign       = new Charitable_Campaign( $campaign_id );

if ( ! $campaign->has_goal() ) {
    return;
}

if ( $campaign->has_ended() ) {
    return;
}

$suggested_donations = $campaign->get_suggested_donations();
$currency_helper    = charitable_get_currency_helper();

if ( empty( $suggested_donations ) && ! $campaign->get( 'allow_custom_donations' ) ) {
    return;
}

echo $view_args['before_widget'];

if ( ! empty( $widget_title ) ) :
    echo $view_args['before_title'] . $widget_title . $view_args['after_title'];
endif;

$form = new Charitable_Donation_Amount_Form( $campaign );
$form->render();

echo $view_args['after_widget'];